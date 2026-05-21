<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'created_by',
        'name',
        'email',
        'phone',
        'type',
        'table_number',
        'whatsapp_status',
        'wa_sent_at',
        'qr_code',
        'rsvp_status',
        'plus_one_count',
        'rsvp_at',
        'invitation_link',
    ];

    protected $casts = [
        'wa_sent_at' => 'datetime',
        'rsvp_at' => 'datetime',
        'plus_one_count' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkin(): HasOne
    {
        return $this->hasOne(Checkin::class);
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->checkin?->checkin_time !== null;
    }

    public function getIsCheckedOutAttribute(): bool
    {
        return $this->checkin?->checkout_time !== null;
    }

    public function getCheckinStatusAttribute(): string
    {
        if (!$this->checkin || !$this->checkin->checkin_time) {
            return 'not_arrived';
        }
        if ($this->checkin->status === 'checkout' || $this->checkin->checkout_time) {
            return 'checked_out';
        }
        return 'checked_in';
    }
}
