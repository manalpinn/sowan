<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'guest_id',
        'checkin_time',
        'checkout_time',
        'status',
        'method',
        'checkout_method',
    ];

    public function getFormattedMethodAttribute()
    {
        $in = $this->formatMethodName($this->method ?? '');
        $out = $this->formatMethodName($this->checkout_method ?? '');

        if ($out && $out !== $in && $this->status === 'checkout') {
            // Check if both have offline flag
            if (str_ends_with($in, '(OFFLINE)') && str_ends_with($out, '(OFFLINE)')) {
                $cleanIn = str_replace(' (OFFLINE)', '', $in);
                $cleanOut = str_replace(' (OFFLINE)', '', $out);
                return $cleanIn . ' + ' . $cleanOut . ' (OFFLINE)';
            }
            return $in . ' + ' . $out;
        }

        return $in ?: '-';
    }

    private function formatMethodName(string $method): string
    {
        if (empty($method)) return '';

        $isOffline = str_ends_with($method, '_offline') || $method === 'offline';
        
        $base = str_replace('_offline', '', $method);
        
        $formatted = match($base) {
            'qr' => 'QR',
            'token' => 'TOKEN',
            'manual' => 'MANUAL',
            'offline' => 'QR', // legacy fallback
            default => strtoupper($base),
        };

        if ($isOffline) {
            $formatted .= ' (OFFLINE)';
        }

        return $formatted;
    }

    protected $casts = [
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
