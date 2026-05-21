<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_date',
        'end_date',
        'banner',
        'theme_color',
        'welcome_message',
        'attendance_type',
        'invitation_template',
        'template_config',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'template_config' => 'json',
    ];

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Stats helpers
    public function getTotalGuestsAttribute(): int
    {
        return $this->guests()->count();
    }

    public function getTotalCheckedInAttribute(): int
    {
        return $this->checkins()->whereNotNull('checkin_time')->count();
    }

    public function getTotalCheckedOutAttribute(): int
    {
        return $this->checkins()->whereNotNull('checkout_time')->count();
    }

    public function getTotalNotArrivedAttribute(): int
    {
        return $this->total_guests - $this->total_checked_in;
    }
}
