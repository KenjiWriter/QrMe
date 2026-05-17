<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class VCardService
{
    /**
     * Build a vCard 3.0 string for the given employee.
     */
    public function build(Employee $employee): string
    {
        $settings = Setting::current();
        $location = $employee->location;

        $lines = [];
        $lines[] = 'BEGIN:VCARD';
        $lines[] = 'VERSION:3.0';
        $lines[] = 'N:'.$this->escape($employee->last_name).';'.$this->escape($employee->first_name).';;;';
        $lines[] = 'FN:'.$this->escape($employee->full_name);

        if ($settings->company_name) {
            $lines[] = 'ORG:'.$this->escape($settings->company_name);
        }

        if ($employee->job_title) {
            $lines[] = 'TITLE:'.$this->escape($employee->job_title);
        }

        if ($employee->phone) {
            $lines[] = 'TEL;TYPE=CELL,VOICE:'.$employee->phone;
        }

        if ($employee->email) {
            $lines[] = 'EMAIL;TYPE=WORK:'.$employee->email;
        }

        if ($location) {
            // ADR: PO Box ; Extended ; Street ; City ; Region ; Postal ; Country
            $street = trim($location->street.' '.$location->building_number.
                ($location->apartment_number ? '/'.$location->apartment_number : ''));
            $lines[] = 'ADR;TYPE=WORK:;;'.$this->escape($street).';'.
                $this->escape($location->city).';;'.
                $this->escape($location->postal_code).';'.
                $this->escape($location->country);
        }

        if ($employee->bio) {
            $lines[] = 'NOTE:'.$this->escape($employee->bio);
        }

        foreach (array_filter([
            $employee->facebook_url,
            $employee->instagram_url,
            $employee->linkedin_url,
            $employee->youtube_url,
        ]) as $url) {
            $lines[] = 'URL:'.$url;
        }

        $lines[] = 'URL:'.$employee->public_url;
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Build a downloadable HTTP response with the vCard.
     */
    public function download(Employee $employee): Response
    {
        $vcard = $this->build($employee);
        $filename = Str::slug($employee->full_name, '-').'.vcf';

        return response($vcard, 200, [
            'Content-Type' => 'text/vcard; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($vcard),
        ]);
    }

    /**
     * Escape vCard special characters.
     */
    private function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return str_replace(
            ['\\', "\r\n", "\n", ',', ';'],
            ['\\\\', '\\n', '\\n', '\\,', '\\;'],
            $value
        );
    }
}
