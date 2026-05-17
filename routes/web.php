<?php

use App\Http\Controllers\PublicCardController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

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
