<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role', 'scope', 'agence_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const SCOPES = ['dg', 'chef-agence', 'agent', 'employe'];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (!$user->scope) {
                $user->scope = 'agent';
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->scope === 'dg';
    }

    public function isScopeDG(): bool
    {
        return $this->scope === 'dg';
    }

    public function isScopeChefAgence(): bool
    {
        return $this->scope === 'chef-agence';
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function agenceId(): ?int
    {
        return $this->agence_id;
    }
}
