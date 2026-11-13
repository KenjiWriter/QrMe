<?php

namespace App\Services\Qr;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;

/**
 * Finder-pattern ("eye") with rounded corners — the "round" eye shape.
 *
 * Geometry follows the library's SquareEye, in module units around (0, 0):
 * a 7×7 frame with a 5×5 hole, and a 3×3 inner square; only the corners
 * are rounded, so the pattern keeps its proportions and stays scannable.
 */
final class RoundedEye implements EyeInterface
{
    private const OUTER_RADIUS = 1.75;

    private const HOLE_RADIUS = 0.75;

    private const INNER_RADIUS = 0.8;

    public function getExternalPath(): Path
    {
        $path = self::roundedRect(new Path(), 3.5, self::OUTER_RADIUS, clockwise: true);

        // Hole drawn in the opposite direction so it is cut out under any fill rule.
        return self::roundedRect($path, 2.5, self::HOLE_RADIUS, clockwise: false);
    }

    public function getInternalPath(): Path
    {
        return self::roundedRect(new Path(), 1.5, self::INNER_RADIUS, clockwise: true);
    }

    /**
     * Append a square of side 2×$half centred on (0, 0) with corners of radius $r.
     * "Clockwise" is as seen on screen (y axis pointing down).
     */
    private static function roundedRect(Path $path, float $half, float $r, bool $clockwise): Path
    {
        $h = $half;

        if ($clockwise) {
            return $path
                ->move(-$h + $r, -$h)
                ->line($h - $r, -$h)
                ->ellipticArc($r, $r, 0.0, false, true, $h, -$h + $r)
                ->line($h, $h - $r)
                ->ellipticArc($r, $r, 0.0, false, true, $h - $r, $h)
                ->line(-$h + $r, $h)
                ->ellipticArc($r, $r, 0.0, false, true, -$h, $h - $r)
                ->line(-$h, -$h + $r)
                ->ellipticArc($r, $r, 0.0, false, true, -$h + $r, -$h)
                ->close();
        }

        return $path
            ->move(-$h + $r, -$h)
            ->ellipticArc($r, $r, 0.0, false, false, -$h, -$h + $r)
            ->line(-$h, $h - $r)
            ->ellipticArc($r, $r, 0.0, false, false, -$h + $r, $h)
            ->line($h - $r, $h)
            ->ellipticArc($r, $r, 0.0, false, false, $h, $h - $r)
            ->line($h, -$h + $r)
            ->ellipticArc($r, $r, 0.0, false, false, $h - $r, -$h)
            ->close();
    }
}
