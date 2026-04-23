<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminTrainersManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'trainer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $trainers = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.admin.trainers.index', compact('trainers'));
    }

    public function create()
    {
        return view('tenants.admin.trainers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // ✅ Check BEFORE creating
        $limits = $request->attributes->get('plan_limits');
        if ($limits && $limits['trainers'] !== null) {
            if ($limits['trainers'] === 0) {
                return back()->withErrors(['limit' => 'Your plan does not support trainers.']);
            }
            $count = User::where('role', 'trainer')->count();
            if ($count >= $limits['trainers']) {
                return back()->withInput()
                    ->withErrors(['limit' => "Your plan allows a maximum of {$limits['trainers']} trainers. Please upgrade."]);
            }
        }

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'trainer',
        ]);

        return redirect()->route('admin.trainers.index')
                        ->with('success', 'Trainer created successfully.');
    }

    public function show(User $trainer)
    {
        $trainer->load(['assessments', 'schedules']);

        return view('tenants.admin.trainers.show', compact('trainer'));
    }

    public function edit(User $trainer)
    {
        return view('tenants.admin.trainers.edit', compact('trainer'));
    }

    public function update(Request $request, User $trainer)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $trainer->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $trainer->update($data);

        return redirect()->route('admin.trainers.index')
                         ->with('success', 'Trainer updated successfully.');
    }

    public function destroy(User $trainer)
    {
        $trainer->delete();

        return redirect()->route('admin.trainers.index')
                         ->with('success', 'Trainer deleted successfully.');
    }
}