<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Services\Pdf\TesdaCertificatePdf;
use Illuminate\Http\Request;

class TraineeCertificateController extends Controller
{
    /**
     * Get enrollment IDs belonging to this trainee.
     */
    private function traineeEnrollmentIds(): \Illuminate\Support\Collection
    {
        return Enrollment::where('trainee_id', auth()->id())->pluck('id');
    }

    /**
     * List all certificates earned by the authenticated trainee.
     */
    public function index(Request $request)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        $query = Certificate::with(['enrollment.course', 'trainer'])
            ->whereIn('enrollment_id', $enrollmentIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('enrollment.course', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
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

        return view('tenants.trainee.certificates.index', compact('certificates'));
    }

    /**
     * View details of a specific certificate.
     */
    public function show(Certificate $certificate)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        abort_if(! $enrollmentIds->contains($certificate->enrollment_id), 403);

        $certificate->load(['enrollment.trainee', 'enrollment.course', 'trainer']);

        return view('tenants.trainee.certificates.show', compact('certificate'));
    }

    /**
     * Download the certificate as a PDF.
     */
    public function download(Certificate $certificate)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        abort_if(! $enrollmentIds->contains($certificate->enrollment_id), 403);

        $certificate->load(['enrollment.trainee', 'enrollment.course', 'trainer']);

        return (new TesdaCertificatePdf($certificate))->download(
            "certificate-{$certificate->certificate_number}.pdf"
        );
    }

    /**
     * Preview/stream the certificate PDF in the browser.
     */
    public function preview(Certificate $certificate)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        abort_if(! $enrollmentIds->contains($certificate->enrollment_id), 403);

        $certificate->load(['enrollment.trainee', 'enrollment.course', 'trainer']);

        return (new TesdaCertificatePdf($certificate))->stream(
            "certificate-{$certificate->certificate_number}.pdf"
        );
    }
}