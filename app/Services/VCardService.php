<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
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

        $websiteUrl = $employee->website_url ?: $settings->default_website_url;
        $facebookUrl = $employee->facebook_url ?: $settings->default_facebook_url;
        $instagramUrl = $employee->instagram_url ?: $settings->default_instagram_url;
        $linkedinUrl = $employee->linkedin_url ?: $settings->default_linkedin_url;
        $youtubeUrl = $employee->youtube_url ?: $settings->default_youtube_url;

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

        $itemIndex = 1;

        if ($employee->email) {
            $lines[] = "item{$itemIndex}.EMAIL;TYPE=INTERNET:".$employee->email;
            $lines[] = "item{$itemIndex}.X-ABLabel:Mail";
            $itemIndex++;
        }

        if ($websiteUrl) {
            $lines[] = "item{$itemIndex}.URL:".$websiteUrl;
            $lines[] = "item{$itemIndex}.X-ABLabel:www";
            $itemIndex++;
        }

        if ($facebookUrl) {
            $lines[] = "item{$itemIndex}.URL:".$facebookUrl;
            $lines[] = "item{$itemIndex}.X-ABLabel:Facebook";
            $itemIndex++;
        }

        if ($instagramUrl) {
            $lines[] = "item{$itemIndex}.URL:".$instagramUrl;
            $lines[] = "item{$itemIndex}.X-ABLabel:Instagram";
            $itemIndex++;
        }

        if ($linkedinUrl) {
            $lines[] = "item{$itemIndex}.URL:".$linkedinUrl;
            $lines[] = "item{$itemIndex}.X-ABLabel:LinkedIn";
            $itemIndex++;
        }

        if ($youtubeUrl) {
            $lines[] = "item{$itemIndex}.URL:".$youtubeUrl;
            $lines[] = "item{$itemIndex}.X-ABLabel:YouTube";
            $itemIndex++;
        }

        $lines[] = "item{$itemIndex}.URL:".$employee->public_url;
        $lines[] = "item{$itemIndex}.X-ABLabel:Wizytówka";
        $itemIndex++;

        if ($location) {
            // ADR: PO Box ; Extended ; Street ; City ; Region ; Postal ; Country
            $street = trim($location->street.' '.$location->building_number.
                ($location->apartment_number ? '/'.$location->apartment_number : ''));
            $lines[] = "item{$itemIndex}.ADR;TYPE=WORK:;;".$this->escape($street).';'.
                $this->escape($location->city).';;'.
                $this->escape($location->postal_code).';'.
                $this->escape($location->country);
            $lines[] = "item{$itemIndex}.X-ABLabel:Adres";
            $itemIndex++;
        }

        if ($employee->bio) {
            $lines[] = 'NOTE:'.$this->escape($employee->bio);
        }

        if ($employee->photo_path && Storage::disk('public')->exists($employee->photo_path)) {
            $photoData = base64_encode((string) Storage::disk('public')->get($employee->photo_path));
            $photoHeader = 'PHOTO;TYPE=JPEG;ENCODING=b:';
            $fullPhoto = $photoHeader.$photoData;
            $lines[] = rtrim(chunk_split($fullPhoto, 75, "\r\n "), "\r\n ");
        }

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
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
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
