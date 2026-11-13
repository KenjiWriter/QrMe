<?php

use App\Models\CustomQr;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('link qr code can still be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/qrcodes', [
        'name' => 'Ankieta',
        'type' => 'url',
        'url' => 'https://example.com/ankieta',
        'qr_color' => '#000000',
        'qr_eye_shape' => 'square',
    ])->assertRedirect('/admin/qrcodes');

    $qr = CustomQr::firstOrFail();

    expect($qr->type)->toBe('url')
        ->and($qr->wifi_ssid)->toBeNull()
        ->and($qr->qrPayload())->toBe(url('/qr/link/'.$qr->short_id));

    Storage::disk('public')->assertExists($qr->qr_code_path);
});

test('wifi qr code can be created and encodes the network', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/qrcodes', [
        'name' => 'Wi-Fi dla gości',
        'type' => 'wifi',
        'url' => 'https://ignored.example.com',
        'wifi_ssid' => 'Blue Hotel',
        'wifi_password' => 'tajne-haslo',
        'wifi_encryption' => 'WPA',
        'wifi_hidden' => '0',
        'qr_color' => '#000000',
        'qr_eye_shape' => 'square',
    ])->assertSessionHasNoErrors()->assertRedirect('/admin/qrcodes');

    $qr = CustomQr::firstOrFail();

    expect($qr->type)->toBe('wifi')
        ->and($qr->url)->toBeNull()
        ->and($qr->wifi_password)->toBe('tajne-haslo')
        ->and($qr->qrPayload())->toBe('WIFI:T:WPA;S:Blue Hotel;P:tajne-haslo;;');

    // Password is encrypted at rest.
    $raw = \DB::table('custom_qrs')->value('wifi_password');
    expect($raw)->not->toBe('tajne-haslo');

    Storage::disk('public')->assertExists($qr->qr_code_path);
});

test('wifi payload escapes special characters and marks hidden networks', function () {
    $qr = new CustomQr([
        'type' => 'wifi',
        'wifi_ssid' => 'Cafe;"Bar":1',
        'wifi_password' => 'a\\b,c',
        'wifi_encryption' => 'WEP',
        'wifi_hidden' => true,
    ]);

    expect($qr->wifiPayload())->toBe('WIFI:T:WEP;S:Cafe\\;\\"Bar\\"\\:1;P:a\\\\b\\,c;H:true;;');
});

test('open wifi network omits password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/qrcodes', [
        'name' => 'Otwarta sieć',
        'type' => 'wifi',
        'wifi_ssid' => 'Guest',
        'wifi_password' => 'should-be-dropped',
        'wifi_encryption' => 'nopass',
        'qr_color' => '#000000',
        'qr_eye_shape' => 'square',
    ])->assertSessionHasNoErrors();

    $qr = CustomQr::firstOrFail();

    expect($qr->wifi_password)->toBeNull()
        ->and($qr->qrPayload())->toBe('WIFI:T:nopass;S:Guest;;');
});

test('wpa-eap network encodes method and identity', function () {
    $qr = new CustomQr([
        'type' => 'wifi',
        'wifi_ssid' => 'Corp',
        'wifi_password' => 'secret',
        'wifi_encryption' => 'WPA-EAP',
        'wifi_eap_method' => 'PEAP',
        'wifi_eap_identity' => 'jan',
    ]);

    expect($qr->wifiPayload())->toBe('WIFI:T:WPA2-EAP;S:Corp;E:PEAP;PH2:MSCHAPV2;I:jan;P:secret;;');
});

test('wifi qr code requires ssid and a valid wpa password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/qrcodes', [
        'name' => 'Zła sieć',
        'type' => 'wifi',
        'wifi_ssid' => '',
        'wifi_password' => 'short',
        'wifi_encryption' => 'WPA',
        'qr_color' => '#000000',
        'qr_eye_shape' => 'square',
    ])->assertSessionHasErrors(['wifi_ssid', 'wifi_password']);

    expect(CustomQr::count())->toBe(0);
});

test('wifi qr code has no redirect endpoint', function () {
    $qr = CustomQr::create([
        'name' => 'Wi-Fi',
        'type' => 'wifi',
        'wifi_ssid' => 'Guest',
        'wifi_encryption' => 'nopass',
    ]);

    $this->get('/qr/link/'.$qr->short_id)->assertNotFound();
});

test('edit page exposes the wifi password for the form', function () {
    $user = User::factory()->create();
    $qr = CustomQr::create([
        'name' => 'Wi-Fi',
        'type' => 'wifi',
        'wifi_ssid' => 'Guest',
        'wifi_password' => 'tajne-haslo',
        'wifi_encryption' => 'WPA',
    ]);

    $this->actingAs($user)->get('/admin/qrcodes/'.$qr->short_id.'/edit')
        ->assertInertia(fn ($page) => $page
            ->component('admin/qrcodes/Edit')
            ->where('item.type', 'wifi')
            ->where('item.wifi_password', 'tajne-haslo')
        );

    $this->actingAs($user)->get('/admin/qrcodes')
        ->assertInertia(fn ($page) => $page
            ->where('items.0.wifi_password', null)
        );
});

test('choosing the square eye shape (sent empty by the style picker) saves as square', function () {
    $user = User::factory()->create();

    // QrStylePicker emits null for "square"; Inertia's FormData turns that into ''.
    $this->actingAs($user)->post('/admin/qrcodes', [
        'name' => 'Kwadrat',
        'type' => 'url',
        'url' => 'https://example.com',
        'qr_color' => '#000000',
        'qr_eye_shape' => '',
    ])->assertSessionHasNoErrors()->assertRedirect('/admin/qrcodes');

    expect(CustomQr::firstOrFail()->qr_eye_shape)->toBe('square');
});

test('each eye shape renders differently and round eyes have rounded corners', function () {
    $svgFor = function (string $shape): string {
        $qr = CustomQr::create([
            'name' => $shape,
            'type' => 'url',
            'url' => 'https://example.com',
            'qr_eye_shape' => $shape,
        ]);
        $path = app(\App\Services\QrCodeService::class)->generateForCustomQr($qr);

        return Storage::disk('public')->get($path);
    };

    $square = $svgFor('square');
    $round = $svgFor('round');
    $dots = $svgFor('dots');

    // Arc commands ("A<rx> <ry> ...") only appear in curved shapes.
    $arcs = fn (string $svg) => preg_match_all('/A[\d.]+ [\d.]+ /', $svg);

    expect($arcs($square))->toBe(0)
        ->and($arcs($round))->toBe(3 * 12)  // 3 eyes × (4 outer + 4 hole + 4 inner corners)
        ->and($arcs($dots))->toBe(3 * 4);   // 3 eyes × 4 quarter-circles
});
