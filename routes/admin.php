<?php

use App\Http\Controllers\Admin\CustomQrController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\GlobalSettingsController;
use App\Http\Controllers\Admin\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('settings', [GlobalSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [GlobalSettingsController::class, 'update'])->name('settings.update');

    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('qrcodes', [CustomQrController::class, 'index'])->name('qrcodes.index');
    Route::get('qrcodes/create', [CustomQrController::class, 'create'])->name('qrcodes.create');
    Route::post('qrcodes', [CustomQrController::class, 'store'])->name('qrcodes.store');
    Route::get('qrcodes/{customQr}/edit', [CustomQrController::class, 'edit'])->name('qrcodes.edit');
    Route::put('qrcodes/{customQr}', [CustomQrController::class, 'update'])->name('qrcodes.update');
    Route::delete('qrcodes/{customQr}', [CustomQrController::class, 'destroy'])->name('qrcodes.destroy');
});

