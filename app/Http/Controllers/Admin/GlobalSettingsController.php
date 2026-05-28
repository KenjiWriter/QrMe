<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSettingsController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('admin/settings/Index', [
            'settings' => Setting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'vat_id' => ['required', 'string', 'max:32'],
            'qr_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $settings = Setting::current();
        $colorChanged = $settings->qr_color !== $validated['qr_color'];
        $settings->update($validated);

        if ($colorChanged) {
            $color = $validated['qr_color'];
            // Only regenerate employees that inherit the global color (no custom color set).
            Employee::whereNotNull('qr_code_path')->whereNull('qr_color')->each(
                fn (Employee $employee) => $employee->update([
                    'qr_code_path' => $this->qrCodeService->generateForEmployee($employee, color: $color),
                ])
            );
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings updated.')]);

        return back();
    }
}
