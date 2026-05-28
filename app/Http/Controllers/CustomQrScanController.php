<?php

namespace App\Http\Controllers;

use App\Models\CustomQr;
use Illuminate\Http\RedirectResponse;

class CustomQrScanController extends Controller
{
    public function scan(string $shortId): RedirectResponse
    {
        $customQr = CustomQr::where('short_id', $shortId)->firstOrFail();

        $customQr->increment('scan_count');

        return redirect()->away($customQr->url);
    }
}
