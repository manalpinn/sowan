<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_demo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_demo' => 'boolean',
        ];
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdminEvent(): bool
    {
        return $this->hasAnyRole(['admin_event', 'admin']);
    }

    /**
     * Get the events the admin is managing (for admin_event role).
     */
    public function getManagedEventIds(): array
    {
        if ($this->isSuperAdmin()) {
            return []; // Superadmin has access to all, usually doesn't need specific IDs
        }
        return $this->events()->pluck('events.id')->toArray();
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->is_demo) {
                // Delete all events managed by the demo user
                $user->events()->each(function ($event) {
                    $event->delete();
                });
            }
        });
    }
}
