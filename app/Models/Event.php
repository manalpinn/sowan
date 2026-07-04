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
        'location_name',
        'address',
        'latitude',
        'longitude',
        'google_maps_link',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
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

    public function getIsActiveAttribute($value)
    {
        if (!(bool)$value) {
            return false;
        }

        $now = now(config('app.timezone'));
        $endDate = $this->end_date ?? $this->start_date;
        
        if ($endDate) {
            $endCarbon = \Carbon\Carbon::parse($endDate, config('app.timezone'))->endOfDay();
            if ($this->end_time) {
                $endParts = explode(':', $this->end_time);
                $endCarbon->startOfDay()->setHour((int)$endParts[0])->setMinute((int)$endParts[1])->setSecond((int)($endParts[2] ?? 0));
            }
            
            if ($now->gt($endCarbon)) {
                return false;
            }
        }

        return true;
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'event_user');
    }

    public function getEventStatusAttribute(): string
    {
        if (!(bool)$this->attributes['is_active']) {
            return 'Selesai'; // if forcefully deactivated
        }

        $now = now(config('app.timezone'));
        $startDate = $this->start_date;
        $endDate = $this->end_date ?? $this->start_date;

        if ($startDate) {
            $startCarbon = \Carbon\Carbon::parse($startDate, config('app.timezone'))->startOfDay();
            if ($this->start_time) {
                $startParts = explode(':', $this->start_time);
                $startCarbon->setHour((int)$startParts[0])->setMinute((int)$startParts[1])->setSecond((int)($startParts[2] ?? 0));
            }
            if ($now->lt($startCarbon)) {
                return 'Akan Datang';
            }
        }

        if ($endDate) {
            $endCarbon = \Carbon\Carbon::parse($endDate, config('app.timezone'))->endOfDay();
            if ($this->end_time) {
                $endParts = explode(':', $this->end_time);
                $endCarbon->startOfDay()->setHour((int)$endParts[0])->setMinute((int)$endParts[1])->setSecond((int)($endParts[2] ?? 0));
            }
            if ($now->gt($endCarbon)) {
                return 'Selesai';
            }
        }

        return 'Aktif';
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

    /**
     * Get the Google Maps embed URL for the event.
     */
    public function getGoogleMapsEmbedUrlAttribute(): ?string
    {
        if ($this->google_maps_link) {
            $embed = self::parseGoogleMapsEmbedUrl($this->google_maps_link);
            if ($embed) {
                return $embed;
            }
        }

        // Fallback: use location
        if ($this->location) {
            return "https://maps.google.com/maps?q=" . urlencode($this->location) . "&t=&z=15&ie=UTF8&iwloc=&output=embed";
        }

        return null;
    }

    private static function parseGoogleMapsEmbedUrl(string $url): ?string
    {
        // 1. If it's an iframe tag, extract the src attribute
        if (preg_match('/<iframe.*?src=["\'](.*?)["\']/', $url, $matches)) {
            $url = $matches[1];
        }

        $url = html_entity_decode($url);

        // 2. Already an embed URL?
        if (str_contains($url, 'google.com/maps/embed')) {
            return $url;
        }

        // 3. Resolve short URLs
        if (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps')) {
            $resolved = self::resolveShortUrl($url);
            if ($resolved) {
                $url = $resolved;
            }
        }

        // 4. Extract place name/query
        if (preg_match('/\/maps\/place\/([^\/]+)/', $url, $matches)) {
            $query = urldecode($matches[1]);
            // check for coordinates
            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $coordMatches)) {
                $query = "{$coordMatches[1]},{$coordMatches[2]}";
            }
            return "https://maps.google.com/maps?q=" . urlencode($query) . "&t=&z=15&ie=UTF8&iwloc=&output=embed";
        }

        // 5. Extract coordinates directly
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return "https://maps.google.com/maps?q={$matches[1]},{$matches[2]}&t=&z=15&ie=UTF8&iwloc=&output=embed";
        }

        // 6. Search URL pattern
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            if (isset($queryParams['q'])) {
                return "https://maps.google.com/maps?q=" . urlencode($queryParams['q']) . "&t=&z=15&ie=UTF8&iwloc=&output=embed";
            }
        }

        return null;
    }

    private static function resolveShortUrl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            try {
                $headers = @get_headers($url, 1);
                if (isset($headers['Location'])) {
                    $location = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
                    return $location;
                }
            } catch (\Throwable $e) {
                return null;
            }
            return null;
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            if ($info['http_code'] >= 300 && $info['http_code'] < 400) {
                preg_match('/[Ll]ocation:\s*(.*)/i', $response, $matches);
                if (isset($matches[1])) {
                    $location = trim($matches[1]);
                    if (str_contains($location, 'maps.app.goo.gl') || str_contains($location, 'goo.gl/maps')) {
                        return self::resolveShortUrl($location);
                    }
                    return $location;
                }
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
