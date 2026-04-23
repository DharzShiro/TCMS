@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<style>
  :root {
    --us-surface: #ffffff;
    --us-border: #c5d8f5;
    --us-text: #001a4d;
    --us-text-sec: #5a7aaa;
    --us-accent: #0057B8;
    --us-accent-bg: #e8f0fb;
    --us-red: #CE1126;
    --us-red-bg: #fff0f2;
    --us-gold: #F5C518;
    --us-gold-bg: rgba(245, 197, 24, 0.15);
    --us-green: #16a34a;
    --us-green-bg: #f0fdf4;
  }
  .dark {
    --us-surface: #0d1f3c;
    --us-border: #1e3a6b;
    --us-text: #dde8ff;
    --us-text-sec: #3a5a8a;
    --us-accent: #0057B8;
    --us-accent-bg: #122550;
    --us-red: #CE1126;
    --us-red-bg: #5a0a0a;
    --us-gold: #F5C518;
    --us-gold-bg: rgba(245, 197, 24, 0.1);
    --us-green: #86efac;
    --us-green-bg: rgba(52, 168, 83, 0.1);
  }
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--us-accent);">
                <i class="fas fa-users mr-2" style="color:var(--us-red);"></i>
                Users Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--us-text-sec);">
                Manage all system users and their roles.
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg,var(--us-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--us-green-bg); border:1px solid var(--us-green); color:var(--us-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @php
        $tenant  = tenancy()->tenant;
        $plan    = $tenant->subscription;
        $limit   = \App\Helpers\SubscriptionHelper::getLimit($plan, 'users');
        $count   = \App\Models\User::whereIn('role', ['admin'])->count();
        $atLimit = $limit !== null && $count >= $limit;
    @endphp

    @if($atLimit)
        <div style="background:#fff7ed; border:1.5px solid #fed7aa; border-radius:12px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-exclamation-triangle" style="color:#f97316; font-size:18px;"></i>
            <div>
                <strong style="color:#9a3412;">Trainee limit reached</strong>
                <span style="color:#c2410c; font-size:13px;"> — You have {{ $count }}/{{ $limit }} admins on your {{ ucfirst($plan) }} plan.</span>
                <a href="{{ route('admin.subscription.index') }}" style="color:#0057B8; font-weight:600; font-size:13px; margin-left:8px;">
                    Upgrade to add more →
                </a>
            </div>
        </div>
    @else
        @if($limit !== null)
            <div style="font-size:12px; color:#5a7aaa; margin-bottom:12px;">
                <i class="fas fa-users"></i> {{ $count }} / {{ $limit }} trainees used on your {{ ucfirst($plan) }} plan.
            </div>
        @endif
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--us-surface); border-color:var(--us-border);">
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--us-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or email..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--us-border); color:var(--us-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--us-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--us-border'); this.style.boxShadow='none'">
            </div>
            <select name="role"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                            dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--us-border); color:var(--us-text);"
                    onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--us-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--us-border'); this.style.boxShadow='none';">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="trainer" {{ request('role') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                <option value="trainee" {{ request('role') === 'trainee' ? 'selected' : '' }}>Trainee</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--us-accent),#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('role'))
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--us-border); color:var(--us-text-sec);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--us-surface); border-color:var(--us-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--us-accent-bg); border-bottom:1px solid var(--us-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--us-accent);">#</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--us-accent);">User</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--us-accent);">Role</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--us-accent);">Joined</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--us-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-[#1e3a6b]" style="divide-color:var(--us-accent-bg);">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-[#f0f5ff] dark:hover:bg-[#122550]">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--us-text-sec);">
                                {{ $users->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,var(--us-accent),#003087);">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-700 dark:text-white" style="color:var(--us-text);">{{ $user->name }}</div>
                                        <div class="text-xs" style="color:var(--us-text-sec);">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1.5 rounded-lg text-xs font-700 inline-block"
                                      style="background:{{ $user->role === 'admin' ? 'var(--us-accent-bg)' : ($user->role === 'trainer' ? 'var(--us-gold-bg)' : 'var(--us-green-bg)') }};
                                              color:{{ $user->role === 'admin' ? 'var(--us-accent)' : ($user->role === 'trainer' ? 'var(--us-gold)' : 'var(--us-green)') }};">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs" style="color:var(--us-text-sec);">
                                {{ $user->created_at?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--us-accent-bg); color:var(--us-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--us-gold-bg); color:var(--us-gold);" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Delete user {{ addslashes($user->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                    style="background:var(--us-red-bg); color:var(--us-red);" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs"
                                             style="background:#e8e8e8; color:#999;" title="Cannot delete yourself">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--us-text-sec);">
                                    <i class="fas fa-users text-4xl opacity-25"></i>
                                    <p class="font-600">No users found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-5 py-4 border-t dark:border-[#1e3a6b]" style="border-color:var(--us-border);">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
