<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Setting;
use App\Services\EmailGeneratorService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmailGeneratorService $emailGenerator,
        private readonly QrCodeService $qrCodeService,
    ) {}

    public function index(): Response
    {
        $employees = Employee::with('location')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Employee $e) => $this->transform($e));

        return Inertia::render('admin/employees/Index', [
            'employees' => $employees,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/employees/Create', [
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'emailDomain' => config('app.domain', env('APP_DOMAIN', 'example.pl')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        if (empty($data['email'])) {
            $data['email'] = $this->emailGenerator->generate($data['first_name'], $data['last_name']);
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        $employee = Employee::create($data);
        $qrColor = Setting::current()->qr_color ?? '#000000';
        $employee->update(['qr_code_path' => $this->qrCodeService->generateForEmployee($employee, color: $qrColor)]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Employee created.')]);

        return to_route('admin.employees.index');
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('admin/employees/Edit', [
            'employee' => $this->transform($employee->load('location')),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'emailDomain' => config('app.domain', env('APP_DOMAIN', 'example.pl')),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validateData($request, $employee);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        $employee->update($data);

        $qrColor = Setting::current()->qr_color ?? '#000000';
        $employee->update(['qr_code_path' => $this->qrCodeService->generateForEmployee($employee, color: $qrColor)]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Employee updated.')]);

        return to_route('admin.employees.index');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        $this->qrCodeService->delete($employee->qr_code_path);

        $employee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Employee deleted.')]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'job_title' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee?->id),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'short_id' => $employee->short_id,
            'uuid' => $employee->uuid,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'full_name' => $employee->full_name,
            'job_title' => $employee->job_title,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'bio' => $employee->bio,
            'photo_url' => $employee->photo_url,
            'qr_code_url' => $employee->qr_code_url,
            'public_url' => $employee->public_url,
            'scan_count' => $employee->scan_count ?? 0,
            'location_id' => $employee->location_id,
            'location' => $employee->location,
            'facebook_url' => $employee->facebook_url,
            'instagram_url' => $employee->instagram_url,
            'linkedin_url' => $employee->linkedin_url,
            'youtube_url' => $employee->youtube_url,
        ];
    }

    private function storePhoto(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = 'employees/photos/' . \Illuminate\Support\Str::uuid() . '.jpg';

        [$origWidth, $origHeight] = getimagesize($file->getRealPath());
        $size = min($origWidth, $origHeight);
        $srcX = (int) (($origWidth - $size) / 2);
        $srcY = (int) (($origHeight - $size) / 2);

        $mime = $file->getMimeType();
        $source = match (true) {
            str_contains($mime, 'png')  => imagecreatefrompng($file->getRealPath()),
            str_contains($mime, 'webp') => imagecreatefromwebp($file->getRealPath()),
            str_contains($mime, 'gif')  => imagecreatefromgif($file->getRealPath()),
            default                     => imagecreatefromjpeg($file->getRealPath()),
        };

        $dest = imagecreatetruecolor(500, 500);
        imagecopyresampled($dest, $source, 0, 0, $srcX, $srcY, 500, 500, $size, $size);
        imagedestroy($source);

        ob_start();
        imagejpeg($dest, null, 90);
        $imageData = (string) ob_get_clean();
        imagedestroy($dest);

        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
