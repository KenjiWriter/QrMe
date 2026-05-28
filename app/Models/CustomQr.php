<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomQr extends Model
{
    protected $fillable = [
        'short_id',
        'name',
        'url',
        'qr_color',
        'qr_eye_shape',
        'logo_path',
        'qr_code_path',
        'scan_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $customQr): void {
            if (empty($customQr->short_id)) {
                $customQr->short_id = self::generateUniqueShortId();
            }
        });
    }

    public static function generateUniqueShortId(int $length = 12): string
    {
        do {
            $candidate = bin2hex(random_bytes((int) ceil($length / 2)));
            $candidate = substr($candidate, 0, $length);
        } while (self::where('short_id', $candidate)->exists());

        return $candidate;
    }

    public function getRouteKeyName(): string
    {
        return 'short_id';
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset('storage/'.$this->qr_code_path) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    public function getScanUrlAttribute(): string
    {
        return url('/qr/link/'.$this->short_id);
    }
}
