<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_token',
        'email_verification_code',
        'email_verification_code_expires_at',
        'discord_id',
        'twitter_id',
        'github_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'auth_token',
        'email_verification_code',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_code_expires_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function hasVerifiedEmail()
    {
        if (is_null($this->email)) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    public function generateEmailVerificationCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => now()->addMinutes(15),
        ]);

        return $code;
    }

    public function verifyEmailWithCode(string $code): bool
    {
        if (
            $this->email_verification_code === $code &&
            $this->email_verification_code_expires_at &&
            $this->email_verification_code_expires_at->isFuture()
        ) {
            $this->markEmailAsVerified();
            $this->update([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ]);

            return true;
        }

        return false;
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumLikes(): HasMany
    {
        return $this->hasMany(ForumLike::class);
    }
}
