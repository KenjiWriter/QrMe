<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Services\VCardService;
use Inertia\Inertia;
use Inertia\Response;

class PublicCardController extends Controller
{
    public function show(Employee $employee): Response
    {
        $employee->load('location');
        $settings = Setting::current();

        return Inertia::render('BusinessCard', [
            'employee' => [
                'uuid' => $employee->uuid,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->full_name,
                'job_title' => $employee->job_title,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'bio' => $employee->bio,
                'photo_url' => $employee->photo_url,
                'facebook_url' => $employee->facebook_url,
                'instagram_url' => $employee->instagram_url,
                'linkedin_url' => $employee->linkedin_url,
                'youtube_url' => $employee->youtube_url,
                'location' => $employee->location ? [
                    'name' => $employee->location->name,
                    'full_address' => $employee->location->full_address,
                    'maps_url' => $employee->location->maps_url,
                ] : null,
            ],
            'company' => [
                'name' => $settings->company_name,
                'vat_id' => $settings->vat_id,
            ],
            'vcardUrl' => url('/'.$employee->uuid.'/vcard'),
        ]);
    }

    public function downloadVCard(Employee $employee, VCardService $service)
    {
        return $service->download($employee);
    }
}
