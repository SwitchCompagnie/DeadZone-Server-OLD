<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AuthenticatesWithGameApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthenticatesWithGameApi;

    /**
     * Check if turnstile is enabled.
     */
    private function isTurnstileEnabled(): bool
    {
        return (bool) config('app.turnstile_enabled', env('TURNSTILE_ENABLED', false));
    }

    private function redirectIfAuthenticated()
    {
        if (Auth::check()) {
            return redirect()->route('game.index');
        }

        return null;
    }

    private function validateTurnstileIfEnabled($token)
    {
        return ! $this->isTurnstileEnabled() || $this->validateTurnstile($token);
    }

    /**
     * Return error response (JSON or redirect with errors).
     */
    private function errorResponse(Request $request, string $key, string $message, int $statusCode = 422)
    {
        if ($request->wantsJson()) {
            return response()->json(['errors' => [$key => [$message]]], $statusCode);
        }

        return back()->withErrors([$key => $message])->withInput();
    }

    /**
     * Return success response (JSON or redirect).
     */
    private function successResponse(Request $request, string $token, string $redirectRoute, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'redirect' => route($redirectRoute),
            ]);
        }

        return redirect()->intended(route($redirectRoute))->with('status', $message);
    }

    public function showLoginForm()
    {
        // Don't redirect from login page to avoid redirect loops
        // when session state is inconsistent (e.g., expired session cookie).
        // Let the user access the login page; if they're truly authenticated,
        // they can navigate to /game directly.
        return view('welcome', [
            'maintenanceMode' => \App\Models\Setting::isMaintenanceMode(),
        ]);
    }

    public function login(Request $request)
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:6|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|min:6',
            'cf-turnstile-response' => $this->isTurnstileEnabled() ? 'required|string' : '',
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        if (! $this->validateTurnstileIfEnabled($request->input('cf-turnstile-response'))) {
            return $this->errorResponse($request, 'captcha', 'Captcha validation failed.');
        }

        $apiToken = $this->authenticateWithGameApi($request->username, $request->password);
        if (! $apiToken) {
            return $this->errorResponse($request, 'login', 'Invalid credentials or API error.', 401);
        }

        $user = User::where('name', $request->username)->first();
        if (! $user) {
            return $this->errorResponse($request, 'login', 'User not found. Please register first.', 401);
        }

        Auth::login($user, $request->boolean('remember-me'));
        $request->session()->regenerate();
        $request->session()->put('api_token', $apiToken);

        return $this->successResponse($request, $apiToken, 'game.index', 'Logged in successfully!');
    }

    public function register(Request $request)
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:6|regex:/^[a-zA-Z0-9]+$/',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'cf-turnstile-response' => $this->isTurnstileEnabled() ? 'required|string' : '',
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        if (! $this->validateTurnstileIfEnabled($request->input('cf-turnstile-response'))) {
            return $this->errorResponse($request, 'captcha', 'Captcha validation failed.');
        }

        $countryCode = $this->getCountryCodeFromIp($request->ip());
        $apiToken = $this->authenticateWithGameApi(
            $request->username,
            $request->password,
            $request->email,
            $countryCode,
            '/api/register'
        );

        if (! $apiToken) {
            return $this->errorResponse($request, 'register', 'Registration failed. Username may already exist.', 409);
        }

        $user = User::firstOrCreate(
            ['name' => $request->username],
            [
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'auth_token' => \Illuminate\Support\Str::random(64),
            ]
        );

        if ($user->wasRecentlyCreated || ! $user->hasVerifiedEmail()) {
            $code = $user->generateEmailVerificationCode();
            $user->notify(new \App\Notifications\EmailVerificationCode($code));
        }

        Auth::login($user, $request->boolean('remember-me'));
        $request->session()->regenerate();
        $request->session()->put('api_token', $apiToken);

        return $this->successResponse($request, $apiToken, 'game.index', 'Account created successfully!');
    }

    public function showForgotPasswordForm()
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'cf-turnstile-response' => $this->isTurnstileEnabled() ? 'required|string' : '',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (! $this->validateTurnstileIfEnabled($request->input('cf-turnstile-response'))) {
            return back()->withErrors(['captcha' => 'Captcha validation failed.'])->withInput();
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm($token)
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'cf-turnstile-response' => $this->isTurnstileEnabled() ? 'required|string' : '',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (! $this->validateTurnstileIfEnabled($request->input('cf-turnstile-response'))) {
            return back()->withErrors(['captcha' => 'Captcha validation failed.'])->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
                $apiResponse = Http::post(config('app.api_base_url').'/api/update-password', [
                    'username' => $user->name,
                    'password' => $password,
                ]);
                if (! $apiResponse->ok()) {
                    throw new \Exception('Failed to update password in external API');
                }
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('welcome')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resendVerificationCode(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'Email already verified!');
        }

        $code = $user->generateEmailVerificationCode();
        $user->notify(new \App\Notifications\EmailVerificationCode($code));

        return back()->with('message', 'Verification code sent!');
    }

    public function verifyEmailWithCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = $request->user();

        if ($user->verifyEmailWithCode($request->code)) {
            return redirect()->route('game.index')->with('status', 'Email verified successfully!');
        }

        return back()->withErrors(['code' => 'Invalid or expired verification code.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }

    public function showGame()
    {
        $token = self::getOrGenerateApiToken();

        if (! $token) {
            return redirect()->route('login')->with('error', 'Failed to authenticate with game server. Please try logging in again.');
        }

        return view('game', ['token' => $token]);
    }

    public function showVerifyEmailNotice()
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('game.index')->with('message', 'Email already verified!');
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return redirect()->route('game.index')->with('message', 'Email verified successfully!');
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('message', 'Email already verified!');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }

    private function validateTurnstile($token)
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => env('TURNSTILE_SECRET'),
            'response' => $token,
        ]);

        return $response->successful() && $response->json()['success'] === true;
    }

    private function updateGameServerUserInfo($username, $email, $countryCode = null)
    {
        try {
            $response = Http::timeout(5)->post(config('app.api_base_url').'/api/update-user-info', [
                'username' => $username,
                'email' => $email,
                'countryCode' => $countryCode,
            ]);

            if (! $response->successful()) {
                \Log::warning("Failed to update game server user info for {$username}: ".$response->body());
            }
        } catch (\Exception $e) {
            \Log::error("Error updating game server user info for {$username}: ".$e->getMessage());
        }
    }

    public static function getOrGenerateApiToken()
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $token = session('api_token');

        if ($token) {
            return $token;
        }

        \Log::warning('No API token in session for user: '.$user->name);

        return null;
    }
}
