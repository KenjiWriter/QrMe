<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomQr;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomQrController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
    ) {}

    public function index(): Response
    {
        $items = CustomQr::orderByDesc('created_at')
            ->get()
            ->map(fn (CustomQr $q) => $this->transform($q));

        return Inertia::render('admin/qrcodes/Index', [
            'items' => $items,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/qrcodes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $logo = null;
        if ($request->hasFile('logo')) {
            $logo = $this->storeLogo($request->file('logo'));
            $data['logo_path'] = $logo;
        }

        unset($data['logo']);

        $customQr = CustomQr::create($data);
        $customQr->update([
            'qr_code_path' => $this->qrCodeService->generateForCustomQr($customQr),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QR code created.')]);

        return to_route('admin.qrcodes.index');
    }

    public function edit(CustomQr $customQr): Response
    {
        return Inertia::render('admin/qrcodes/Edit', [
            'item' => $this->transform($customQr, withSecrets: true),
        ]);
    }

    public function update(Request $request, CustomQr $customQr): RedirectResponse
    {
        $data = $this->validateData($request, $customQr);

        if ($request->hasFile('logo')) {
            if ($customQr->logo_path) {
                Storage::disk('public')->delete($customQr->logo_path);
            }
            $data['logo_path'] = $this->storeLogo($request->file('logo'));
        }

        if ($request->boolean('remove_logo') && $customQr->logo_path) {
            Storage::disk('public')->delete($customQr->logo_path);
            $data['logo_path'] = null;
        }

        unset($data['logo'], $data['remove_logo']);

        $customQr->update($data);

        $customQr->update([
            'qr_code_path' => $this->qrCodeService->generateForCustomQr($customQr),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QR code updated.')]);

        return to_route('admin.qrcodes.index');
    }

    public function destroy(CustomQr $customQr): RedirectResponse
    {
        $this->qrCodeService->delete($customQr->qr_code_path);

        if ($customQr->logo_path) {
            Storage::disk('public')->delete($customQr->logo_path);
        }

        $customQr->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QR code deleted.')]);

        return back();
    }

    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?CustomQr $customQr = null): array
    {
        $type = $request->input('type', CustomQr::TYPE_URL);
        $isWifi = $type === CustomQr::TYPE_WIFI;
        $encryption = $request->input('wifi_encryption');

        $passwordRules = ['nullable', 'string', 'max:255'];
        if ($isWifi && $encryption !== 'nopass') {
            $passwordRules[0] = 'required';
        }
        if ($isWifi && $encryption === 'WPA') {
            // WPA/WPA2/WPA3 passphrase: 8–63 characters.
            $passwordRules = ['required', 'string', 'min:8', 'max:63'];
        }

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'type'              => ['required', 'string', Rule::in(CustomQr::TYPES)],
            'url'               => [Rule::requiredIf(! $isWifi), 'nullable', 'url', 'max:2048'],
            'wifi_ssid'         => [Rule::requiredIf($isWifi), 'nullable', 'string', 'max:32'],
            'wifi_encryption'   => [Rule::requiredIf($isWifi), 'nullable', Rule::in(CustomQr::WIFI_ENCRYPTIONS)],
            'wifi_password'     => $passwordRules,
            'wifi_hidden'       => ['nullable', 'boolean'],
            'wifi_eap_method'   => [Rule::requiredIf($isWifi && $encryption === 'WPA-EAP'), 'nullable', Rule::in(CustomQr::WIFI_EAP_METHODS)],
            'wifi_eap_identity' => [Rule::requiredIf($isWifi && $encryption === 'WPA-EAP'), 'nullable', 'string', 'max:255'],
            'qr_color'          => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            // QrStylePicker sends an empty value for "square" (the default).
            'qr_eye_shape'      => ['nullable', 'string', 'in:square,round,dots'],
            'logo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg+xml', 'max:2048'],
            'remove_logo'       => ['nullable', 'boolean'],
        ]);

        // Keep only the fields that belong to the chosen type.
        if ($isWifi) {
            $data['url'] = null;
            $data['wifi_hidden'] = $request->boolean('wifi_hidden');

            if ($encryption === 'nopass') {
                $data['wifi_password'] = null;
            }
            if ($encryption !== 'WPA-EAP') {
                $data['wifi_eap_method'] = null;
                $data['wifi_eap_identity'] = null;
            }
        } else {
            $data['wifi_ssid'] = null;
            $data['wifi_password'] = null;
            $data['wifi_encryption'] = null;
            $data['wifi_hidden'] = false;
            $data['wifi_eap_method'] = null;
            $data['wifi_eap_identity'] = null;
        }

        $data['qr_eye_shape'] ??= 'square';

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(CustomQr $q, bool $withSecrets = false): array
    {
        return [
            'id'                => $q->id,
            'short_id'          => $q->short_id,
            'name'              => $q->name,
            'type'              => $q->type,
            'url'               => $q->url,
            'wifi_ssid'         => $q->wifi_ssid,
            'wifi_password'     => $withSecrets ? $q->wifi_password : null,
            'wifi_encryption'   => $q->wifi_encryption,
            'wifi_hidden'       => (bool) $q->wifi_hidden,
            'wifi_eap_method'   => $q->wifi_eap_method,
            'wifi_eap_identity' => $q->wifi_eap_identity,
            'qr_color'          => $q->qr_color,
            'qr_eye_shape'      => $q->qr_eye_shape,
            'logo_url'          => $q->logo_url,
            'qr_code_url'       => $q->qr_code_url,
            'scan_url'          => $q->scan_url,
            'scan_count'        => $q->scan_count,
        ];
    }

    private function storeLogo(\Illuminate\Http\UploadedFile $file): string
    {
        $ext      = $file->getClientOriginalExtension() ?: 'png';
        $filename = 'custom_qrs/logos/'.bin2hex(random_bytes(8)).'.'.$ext;
        Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));

        return $filename;
    }
}
