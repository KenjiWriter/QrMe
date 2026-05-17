<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['company_name', 'vat_id'];

    /**
     * Get the singleton settings row (creates one if missing).
     */
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'company_name' => null,
            'vat_id' => null,
        ]);
    }
}
