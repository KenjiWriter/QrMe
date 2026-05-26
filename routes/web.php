<?php

use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\QrScanController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

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

// QR code scan endpoint — only hit when a physical QR code is scanned.
// Increments the scan counter then redirects to the public card page.
Route::get('/qr/{shortId}', [QrScanController::class, 'scan'])
    ->where('shortId', '[A-Za-z0-9]{6,16}')
    ->name('card.scan');
