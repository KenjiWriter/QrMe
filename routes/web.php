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

// Public business card routes (UUID-based) — MUST be declared last
// so the wildcard does not shadow named routes above.
Route::get('/{employee}/vcard', [PublicCardController::class, 'downloadVCard'])
    ->where('employee', '[0-9a-fA-F-]{36}')
    ->name('card.vcard');

Route::get('/{employee}', [PublicCardController::class, 'show'])
    ->where('employee', '[0-9a-fA-F-]{36}')
    ->name('card.show');
