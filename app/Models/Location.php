<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'street',
        'building_number',
        'apartment_number',
        'city',
        'postal_code',
        'country',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Single-line formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        $building = $this->building_number;
        if ($this->apartment_number) {
            $building .= '/'.$this->apartment_number;
        }

        return trim(sprintf(
            '%s %s, %s %s, %s',
            $this->street,
            $building,
            $this->postal_code,
            $this->city,
            $this->country
        ));
    }

    /**
     * Google Maps URL for the location.
     */
    public function getMapsUrlAttribute(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->full_address);
    }
}
