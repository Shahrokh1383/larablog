<?php

namespace Modules\Identity\Models;

use Shared\Models\User as BaseUser;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Identity\UserFactory;

class User extends BaseUser implements MustVerifyEmail, \Shared\Contracts\HasRolesContract
{
    use HasApiTokens, HasRoles, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar',
        'bio',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Override the default verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \Modules\Identity\Notifications\VerifyEmailNotification);
    }

    /**
     * Override the default password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \Modules\Identity\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}