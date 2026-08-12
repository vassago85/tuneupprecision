<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_verified_member'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_verified_member' => 'boolean',
        ];
    }

    /**
     * Only admins (Dirk) reach the /admin Filament panel. Public members log
     * in on the site itself and never see the panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * True for members Dirk has verified — the gate for "members-only" videos
     * on The Range. Admins always count as verified.
     */
    public function isVerifiedMember(): bool
    {
        return $this->role === UserRole::Admin || (bool) $this->is_verified_member;
    }

    /**
     * First name for greeting the user in the nav.
     */
    public function firstName(): string
    {
        return trim(explode(' ', (string) $this->name)[0] ?? '');
    }
}
