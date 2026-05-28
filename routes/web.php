<?php

use App\Http\Controllers\CustomQrScanController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\QrScanController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', fn () => redirect()->away('https://' . config('app.domain'), 301))
    ->name('home');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';

// Public business card routes. Short-id based, prefixed with /p/ so the
// wildcard cannot shadow any other named routes.
Route::get('/p/{employee}/vcard', [PublicCardController::class, 'downloadVCard'])
    ->where('employee', '[A-Za-z0-9]{6,16}')
    ->name('card.vcard');

Route::get('/p/{employee}', [PublicCardController::class, 'show'])
    ->where('employee', '[A-Za-z0-9]{6,16}')
    ->name('card.show');

// Universal QR scan endpoint — redirects to the target URL and increments counter.
// Must be declared BEFORE the employee scan route to avoid the /qr/{shortId} match.
Route::get('/qr/link/{shortId}', [CustomQrScanController::class, 'scan'])
    ->where('shortId', '[A-Za-z0-9]{6,16}')
    ->name('custom_qr.scan');

// QR code scan endpoint — only hit when a physical QR code is scanned.
// Increments the scan counter then redirects to the public card page.
Route::get('/qr/{shortId}', [QrScanController::class, 'scan'])
    ->where('shortId', '[A-Za-z0-9]{6,16}')
    ->name('card.scan');

