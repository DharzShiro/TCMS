<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Services\Pdf\TesdaCertificatePdf;
use App\Models\User;



class AdminCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::with(['enrollment.trainee', 'enrollment.course']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('enrollment.trainee', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('enrollment.course', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('expired')) {
            if ($request->expired === 'yes') {
                $query->whereNotNull('expires_at')->whereDate('expires_at', '<', now());
            } elseif ($request->expired === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhereDate('expires_at', '>=', now());
                });
            }
        }

        $certificates = $query->latest('issued_at')->paginate(10)->withQueryString();

        return view('tenants.admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        $enrollments = Enrollment::with(['trainee', 'course'])
                                ->where('status', 'completed')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.certificates.create', compact('enrollments', 'trainers'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id'      => ['required', 'exists:enrollments,id'],
            'certificate_number' => ['required', 'string', 'unique:certificates,certificate_number'],
            'issued_at'          => ['required', 'date'],
            'expires_at'         => ['nullable', 'date', 'after:issued_at'],
            'trainer_id' => ['required', 'exists:users,id'],
        ]);

        // One certificate per enrollment only
        $exists = Certificate::where('enrollment_id', $validated['enrollment_id'])->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['enrollment_id' => 'A certificate for this enrollment already exists.']);
        }

        Certificate::create($validated);

        return redirect()->route('admin.certificates.index')
                         ->with('success', 'Certificate issued successfully.');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['enrollment.trainee', 'enrollment.course']);

        return view('tenants.admin.certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        $enrollments = Enrollment::with(['trainee', 'course'])
                                ->where('status', 'completed')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.certificates.edit', compact('certificate', 'enrollments', 'trainers'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'enrollment_id'      => ['required', 'exists:enrollments,id'],
            'certificate_number' => ['required', 'string', 'unique:certificates,certificate_number,' . $certificate->id],
            'issued_at'          => ['required', 'date'],
            'expires_at'         => ['nullable', 'date', 'after:issued_at'],
            'trainer_id'         => ['nullable', 'exists:users,id'], // add this
        ]);

        $exists = Certificate::where('enrollment_id', $validated['enrollment_id'])
                            ->where('id', '!=', $certificate->id)
                            ->exists();

        if ($exists) {
            return back()->withInput()
                        ->withErrors(['enrollment_id' => 'A certificate for this enrollment already exists.']);
        }

        $certificate->update($validated);

        return redirect()->route('admin.certificates.index')
                        ->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('admin.certificates.index')
                         ->with('success', 'Certificate deleted successfully.');
    }

    public function preview(Certificate $certificate)
    {
        $certificate->load('enrollment.trainee', 'enrollment.course', 'trainer');

        return (new TesdaCertificatePdf($certificate))->stream(
            "certificate-{$certificate->certificate_number}.pdf"
        );
    }

    public function download(Certificate $certificate)
    {
        $certificate->load('enrollment.trainee', 'enrollment.course', 'trainer');

        return (new TesdaCertificatePdf($certificate))->download(
            "certificate-{$certificate->certificate_number}.pdf"
        );
    }

    public function save(Certificate $certificate)
    {
        $certificate->load('enrollment.trainee', 'enrollment.course');

        $path = storage_path("app/certificates/{$certificate->certificate_number}.pdf");
        (new TesdaCertificatePdf($certificate))->save($path);
    }
}