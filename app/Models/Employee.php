<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
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
            if (empty($employee->uuid)) {
                $employee->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
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
        return url('/'.$this->uuid);
    }
}
