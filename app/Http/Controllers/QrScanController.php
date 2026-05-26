<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;

class QrScanController extends Controller
{
    public function scan(string $shortId): RedirectResponse
    {
        $employee = Employee::where('short_id', $shortId)->firstOrFail();
        $employee->increment('scan_count');

        return redirect()->route('card.show', $shortId);
    }
}
