<?php

use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;

test('employee can be created with english job title and website url', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/employees', [
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'job_title' => 'Dyrektor Handlowy',
        'job_title_en' => 'Sales Director',
        'phone' => '+48 123 456 789',
        'website_url' => 'https://example.com',
    ]);

    $response->assertRedirect('/admin/employees');

    $this->assertDatabaseHas('employees', [
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'job_title' => 'Dyrektor Handlowy',
        'job_title_en' => 'Sales Director',
        'website_url' => 'https://example.com',
    ]);
});

test('public card endpoint returns employee details including english job title and website', function () {
    $employee = Employee::create([
        'first_name' => 'Anna',
        'last_name' => 'Nowak',
        'job_title' => 'Kierownik Projektu',
        'job_title_en' => 'Project Manager',
        'email' => 'a.nowak@example.com',
        'website_url' => 'https://reganta.pl',
    ]);

    $response = $this->get('/p/' . $employee->short_id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('BusinessCard')
        ->where('employee.job_title', 'Kierownik Projektu')
        ->where('employee.job_title_en', 'Project Manager')
        ->where('employee.website_url', 'https://reganta.pl')
    );
});

test('vcard download includes website url and inline disposition', function () {
    $employee = Employee::create([
        'first_name' => 'Piotr',
        'last_name' => 'Wisniewski',
        'job_title' => 'Programista',
        'email' => 'p.wisniewski@example.com',
        'website_url' => 'https://example.org',
    ]);

    $response = $this->get('/p/' . $employee->short_id . '/vcard');

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'inline; filename="piotr-wisniewski.vcf"');
    expect($response->getContent())->toContain('URL;TYPE=WORK:https://example.org');
});

test('global settings can save default website and social media links', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/admin/settings', [
        'app_name' => 'Reganta',
        'company_name' => 'Test Corp',
        'vat_id' => 'PL1234567890',
        'qr_color' => '#123456',
        'default_website_url' => 'https://testcorp.com',
        'default_facebook_url' => 'https://facebook.com/testcorp',
        'default_instagram_url' => 'https://instagram.com/testcorp',
        'default_linkedin_url' => 'https://linkedin.com/company/testcorp',
        'default_youtube_url' => 'https://youtube.com/@testcorp',
    ]);

    $response->assertRedirect();

    $settings = Setting::current();
    expect($settings->default_website_url)->toBe('https://testcorp.com');
    expect($settings->default_facebook_url)->toBe('https://facebook.com/testcorp');
});
