<?php
// app/Http/Controllers/Tenant/Admin/AdminBrandingController.php
namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBrandingController extends Controller
{
    public function index()
    {
        $tenant = tenancy()->tenant;

        $brandLogo = $tenant->brand_logo
            ? asset("storage/{$tenant->brand_logo}")
            : null;

        return view('tenants.admin.branding.index', compact('tenant', 'brandLogo'));
    }

    public function update(Request $request)
    {
        $tenant = tenancy()->tenant;

        $validated = $request->validate([
            'brand_name'          => ['nullable', 'string', 'max:100'],
            'brand_tagline'       => ['nullable', 'string', 'max:200'],
            'brand_color_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_color_accent'  => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_logo'          => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ]);

        if ($request->hasFile('brand_logo')) {
            $file      = $request->file('brand_logo');
            $filename  = $file->hashName();
            $directory = "branding/{$tenant->id}";

            // Delete old logo from central storage
            if ($tenant->brand_logo) {
                $oldPath = base_path("storage/app/public/{$tenant->brand_logo}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Save directly to central public storage, bypassing tenant disk
            $centralPath = base_path("storage/app/public/{$directory}");
            if (!file_exists($centralPath)) {
                mkdir($centralPath, 0755, true);
            }

            $file->move($centralPath, $filename);
            $validated['brand_logo'] = "{$directory}/{$filename}";
        }

        $tenant->update($validated);

        return back()->with('success', 'Branding updated successfully.');
    }

    public function resetLogo()
    {
        $tenant = tenancy()->tenant;

        if ($tenant->brand_logo) {
            $path = base_path("storage/app/public/{$tenant->brand_logo}");
            if (file_exists($path)) {
                unlink($path);
            }
            $tenant->update(['brand_logo' => null]);
        }

        return back()->with('success', 'Logo reset to default.');
    }
}