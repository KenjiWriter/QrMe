<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'short_id',
        'first_name',
        'last_name',
        'job_title',
        'email',
        'phone',
        'bio',
        'photo_path',
        'qr_code_path',
        'location_id',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $employee): void {
            if (empty($employee->short_id)) {
                $employee->short_id = self::generateUniqueShortId();
            }
        });
    }

    public static function generateUniqueShortId(int $length = 12): string
    {
        do {
            // 12 lowercase hex chars (matches the uniqid() style, e.g. "673f0077859c").
            $candidate = bin2hex(random_bytes((int) ceil($length / 2)));
            $candidate = substr($candidate, 0, $length);
        } while (self::withTrashed()->where('short_id', $candidate)->exists());

        return $candidate;
    }

    public function getRouteKeyName(): string
    {
        return 'short_id';
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset('storage/'.$this->qr_code_path) : null;
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/p/'.$this->short_id);
    }
}
