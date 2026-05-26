<?php

namespace App\Services;

use App\Models\Employee;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR PNG for the employee and persist it to the public disk.
     * Returns the storage-relative path (e.g. "employees/qrcodes/{short_id}.png").
     *
     * @param  string  $color  Foreground color as a hex string, e.g. "#FF5500"
     */
    public function generateForEmployee(Employee $employee, int $size = 512, string $color = '#000000'): string
    {
        $fill = $this->buildFill($color);
        $renderer = new GDLibRenderer($size, margin: 0, fill: $fill);
        $writer = new Writer($renderer);

        $publicUrl = url('/p/'.$employee->short_id);
        $png = $writer->writeString($publicUrl);

        $path = 'employees/qrcodes/'.$employee->short_id.'.png';
        Storage::disk('public')->put($path, $png);

        return $path;
    }

    /**
     * Delete a stored QR code file.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function buildFill(string $hex): Fill
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return Fill::withForegroundColor(
            new Rgb(255, 255, 255),
            new Rgb((int) $r, (int) $g, (int) $b),
            EyeFill::inherit(),
            EyeFill::inherit(),
            EyeFill::inherit(),
        );
    }
}
