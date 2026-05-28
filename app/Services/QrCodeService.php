<?php

namespace App\Services;

use App\Models\CustomQr;
use App\Models\Employee;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR SVG for the employee and persist it to the public disk.
     * Returns the storage-relative path (e.g. "employees/qrcodes/{short_id}.svg").
     *
     * @param  string  $color      Foreground color as a hex string, e.g. "#FF5500"
     * @param  string  $eyeShape   One of: square | round | dots
     * @param  string|null  $logoPath  Storage-relative path to a logo file (nullable)
     */
    public function generateForEmployee(
        Employee $employee,
        int $size = 512,
        string $color = '#000000',
        string $eyeShape = 'square',
        ?string $logoPath = null
    ): string {
        $url = url('/qr/'.$employee->short_id);
        $path = 'employees/qrcodes/'.$employee->short_id.'.svg';

        $this->generate($url, $size, $color, $eyeShape, $logoPath, $path);

        return $path;
    }

    /**
     * Generate a QR SVG for a custom QR code and persist it to the public disk.
     * Returns the storage-relative path (e.g. "custom_qrs/{short_id}.svg").
     */
    public function generateForCustomQr(CustomQr $customQr, int $size = 512): string
    {
        $url = url('/qr/link/'.$customQr->short_id);
        $path = 'custom_qrs/'.$customQr->short_id.'.svg';

        $this->generate(
            $url,
            $size,
            $customQr->qr_color ?? '#000000',
            $customQr->qr_eye_shape ?? 'square',
            $customQr->logo_path,
            $path
        );

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

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function generate(
        string $url,
        int $size,
        string $color,
        string $eyeShape,
        ?string $logoPath,
        string $storagePath
    ): void {
        $fill = $this->buildFill($color);
        $style = new RendererStyle($size, 0, $this->buildModule($eyeShape), $this->buildEye($eyeShape), $fill);
        $renderer = new ImageRenderer($style, new SvgImageBackEnd());
        $writer = new Writer($renderer);

        // Use higher error correction when a logo occludes part of the QR code.
        $ecLevel = $logoPath ? ErrorCorrectionLevel::H() : ErrorCorrectionLevel::L();
        $svg = $writer->writeString($url, 'UTF-8', $ecLevel);

        if ($logoPath) {
            $svg = $this->embedLogo($svg, $logoPath);
        }

        Storage::disk('public')->put($storagePath, $svg);
    }

    private function buildFill(string $hex): Fill
    {
        $hex = ltrim($hex, '#');
        $r = (int) hexdec(substr($hex, 0, 2));
        $g = (int) hexdec(substr($hex, 2, 2));
        $b = (int) hexdec(substr($hex, 4, 2));

        return Fill::withForegroundColor(
            new Rgb(255, 255, 255),
            new Rgb($r, $g, $b),
            EyeFill::inherit(),
            EyeFill::inherit(),
            EyeFill::inherit(),
        );
    }

    private function buildModule(string $eyeShape): \BaconQrCode\Renderer\Module\ModuleInterface
    {
        return SquareModule::instance();
    }

    private function buildEye(string $eyeShape): \BaconQrCode\Renderer\Eye\EyeInterface
    {
        return match ($eyeShape) {
            'dots'  => SimpleCircleEye::instance(),
            default => SquareEye::instance(),
        };
    }

    /**
     * Inject a logo image element into the centre of an SVG QR code string.
     */
    private function embedLogo(string $svg, string $logoPath): string
    {
        if (! Storage::disk('public')->exists($logoPath)) {
            return $svg;
        }

        $logoData = Storage::disk('public')->get($logoPath);
        if ($logoData === null) {
            return $svg;
        }

        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'svg'         => 'image/svg+xml',
            default       => 'image/png',
        };

        $logoBase64 = base64_encode($logoData);

        // Parse SVG dimensions from viewBox attribute.
        preg_match('/viewBox="0 0 ([\d.]+) ([\d.]+)"/', $svg, $m);
        $w = (float) ($m[1] ?? 512);
        $h = (float) ($m[2] ?? 512);

        // Logo occupies 28% of QR width, centred.
        $logoSize = $w * 0.28;
        $logoX = ($w - $logoSize) / 2;
        $logoY = ($h - $logoSize) / 2;

        // White backing rect with 2% padding on each side.
        $pad = $w * 0.02;
        $bgX = $logoX - $pad;
        $bgY = $logoY - $pad;
        $bgSize = $logoSize + $pad * 2;

        $inject = sprintf(
            '<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" fill="white" rx="%.2f"/>'.
            '<image href="data:%s;base64,%s" x="%.2f" y="%.2f" width="%.2f" height="%.2f" preserveAspectRatio="xMidYMid meet"/>',
            $bgX, $bgY, $bgSize, $bgSize, $pad,
            $mime, $logoBase64,
            $logoX, $logoY, $logoSize, $logoSize
        );

        return str_replace('</svg>', $inject.'</svg>', $svg);
    }
}
