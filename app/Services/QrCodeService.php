<?php

namespace App\Services;

use App\Models\Employee;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR PNG for the employee and persist it to the public disk.
     * Returns the storage-relative path (e.g. "employees/qrcodes/{uuid}.png").
     */
    public function generateForEmployee(Employee $employee, int $size = 512): string
    {
        $renderer = new GDLibRenderer($size);
        $writer = new Writer($renderer);

        $publicUrl = url('/'.$employee->uuid);
        $png = $writer->writeString($publicUrl);

        $path = 'employees/qrcodes/'.$employee->uuid.'.png';
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
}
