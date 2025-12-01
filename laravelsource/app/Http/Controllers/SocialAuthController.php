<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AuthenticatesWithGameApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use AuthenticatesWithGameApi;

    private $allowedProviders = ['facebook', 'discord', 'twitter', 'github'];

    public function redirectToProvider($provider)
    {
        if (Auth::check()) {
            return redirect()->route('game.index');
        }

        if (! in_array($provider, $this->allowedProviders)) {
            return redirect()->route('welcome')->with('error', 'Invalid social provider.');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        if (! in_array($provider, $this->allowedProviders)) {
            return redirect()->route('welcome')->with('error', 'Invalid social provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error("Social auth error for {$provider}: ".$e->getMessage());

            return redirect()->route('welcome')->with('error', 'Authentication failed. Please try again.');
        }

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail() ?? $providerId.'@'.$provider.'.social';
        $countryCode = $this->getCountryCodeFromIp(request()->ip());

        $existingUser = User::where($provider.'_id', $providerId)->first()
            ?? (($email && ! str_ends_with($email, '.social')) ? User::where('email', $email)->first() : null);

        if ($existingUser && ! $existingUser->auth_token) {
            return redirect()->route('welcome')->with('error', 'An account with this email already exists. Please login with your username and password first, then you can link your social account.');
        }

        $isNewUser = ! $existingUser;
        $username = $existingUser?->name ?? $this->generateUsername($socialUser, $provider);
        $authToken = $existingUser?->auth_token ?? Str::random(64);

        $apiToken = $this->authenticateWithGameApi($username, $authToken, $email, $countryCode);

        if (! $apiToken) {
            return redirect()->route('welcome')->with('error', 'Unable to connect to game server. Please try again.');
        }

        if ($existingUser) {
            $existingUser->update([
                $provider.'_id' => $providerId,
                'auth_token' => $authToken,
                'email_verified_at' => $existingUser->email_verified_at ?? ($socialUser->getEmail() ? now() : null),
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $username,
                'email' => $email,
                $provider.'_id' => $providerId,
                'password' => bcrypt(Str::random(32)),
                'auth_token' => $authToken,
                'email_verified_at' => $socialUser->getEmail() ? now() : null,
            ]);
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        if (! $user->hasVerifiedEmail() && $user->email && ! str_ends_with($user->email, '.social')) {
            $code = $user->generateEmailVerificationCode();
            $user->notify(new \App\Notifications\EmailVerificationCode($code));

            return redirect()->route('verification.notice')->with('message', 'Please verify your email address to continue.');
        }

        request()->session()->put('api_token', $apiToken);

        $message = $isNewUser
            ? 'Account created successfully! Welcome to Deadzone.'
            : 'Logged in successfully via '.ucfirst($provider).'!';

        return redirect()->route('game.index')->with('status', $message);
    }

    private function generateUsername($socialUser, $provider)
    {
        $baseName = $socialUser->getNickname() ?? $socialUser->getName() ?? $socialUser->getEmail();
        $baseName = preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '', $baseName));

        if (strlen($baseName) < 6) {
            $baseName = $provider.$baseName;
        }

        $username = substr($baseName, 0, 20);
        $counter = 1;

        while (User::where('name', $username)->exists()) {
            $username = substr($baseName, 0, 16).$counter;
            $counter++;
        }

        return $username;
    }
}
