<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomQr extends Model
{
    public const TYPE_URL = 'url';

    public const TYPE_WIFI = 'wifi';

    public const TYPES = [self::TYPE_URL, self::TYPE_WIFI];

    /** Encryption values offered in the UI (same set as popular QR generators). */
    public const WIFI_ENCRYPTIONS = ['WPA', 'WEP', 'WPA-EAP', 'nopass'];

    /** EAP methods that can be provisioned via a QR code (no client certificate needed). */
    public const WIFI_EAP_METHODS = ['PEAP', 'TTLS', 'PWD'];

    protected $fillable = [
        'short_id',
        'name',
        'type',
        'url',
        'wifi_ssid',
        'wifi_password',
        'wifi_encryption',
        'wifi_hidden',
        'wifi_eap_method',
        'wifi_eap_identity',
        'qr_color',
        'qr_eye_shape',
        'logo_path',
        'qr_code_path',
        'scan_count',
    ];

    protected $attributes = [
        'type' => self::TYPE_URL,
        'wifi_hidden' => false,
    ];

    protected function casts(): array
    {
        return [
            'wifi_hidden' => 'boolean',
            'wifi_password' => 'encrypted',
        ];
    }

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

    public function isWifi(): bool
    {
        return $this->type === self::TYPE_WIFI;
    }

    /**
     * Content encoded in the QR image: the tracked redirect URL for links,
     * or the standard "WIFI:" payload that phones turn into a join prompt.
     */
    public function qrPayload(): string
    {
        return $this->isWifi() ? $this->wifiPayload() : $this->scan_url;
    }

    /**
     * Build the de-facto standard Wi-Fi QR payload, e.g.
     *   WIFI:T:WPA;S:My Network;P:secret;;
     * Special characters (\ ; , : ") in values must be backslash-escaped.
     */
    public function wifiPayload(): string
    {
        $encryption = $this->wifi_encryption ?: 'nopass';

        $fields = [];

        if ($encryption === 'WPA-EAP') {
            $fields['T'] = 'WPA2-EAP';
            $fields['S'] = $this->wifi_ssid;
            $fields['E'] = $this->wifi_eap_method ?: 'PEAP';
            if (($this->wifi_eap_method ?: 'PEAP') !== 'PWD') {
                $fields['PH2'] = 'MSCHAPV2';
            }
            $fields['I'] = $this->wifi_eap_identity;
            $fields['P'] = $this->wifi_password;
        } else {
            $fields['T'] = $encryption;
            $fields['S'] = $this->wifi_ssid;
            if ($encryption !== 'nopass') {
                $fields['P'] = $this->wifi_password;
            }
        }

        if ($this->wifi_hidden) {
            $fields['H'] = 'true';
        }

        $parts = [];
        foreach ($fields as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = $key.':'.self::escapeWifiValue((string) $value);
        }

        return 'WIFI:'.implode(';', $parts).';;';
    }

    private static function escapeWifiValue(string $value): string
    {
        return addcslashes($value, '\\;,:"');
    }
}
