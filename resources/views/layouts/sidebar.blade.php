<!-- sidebar.blade.php -->
@php
    $role = auth()->user()->role;
@endphp

<div x-data class="h-full">

    <!-- ── Mobile backdrop ── -->
    <div x-show="$store.sidebar.isOpen"
         x-cloak
         x-transition:enter="transition-opacity duration-300 ease-in-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300 ease-in-out"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"
         @click="$store.sidebar.isOpen = false">
    </div>

    <!-- ── Sidebar ── -->
    <aside
        x-show="$store.sidebar.isOpen"
        x-cloak
        x-transition:enter="transform transition-transform duration-300 ease-in-out"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition-transform duration-300 ease-in-out"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        :class="$store.darkMode.on
            ? 'bg-[#0a1628] border-[#1e3a6b]'
            : 'bg-white border-[#c5d8f5]'"
        class="fixed top-16 left-0 z-40 flex flex-col
            h-[calc(100vh-4rem)] w-72 max-w-[85vw]
            border-r
            shadow-xl lg:shadow-md">

        <!-- ── Nav links (scrollable) ── -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden"
             @click="if (($event.target.tagName === 'A' || $event.target.closest('a')) && window.innerWidth < 1024) {
                 $store.sidebar.isOpen = false
             }">
            @if($role === 'superadmin')
                @include('layouts.sidebar-superadmin')
            @elseif($role === 'admin')
                @include('layouts.sidebar-admin')
            @elseif($role === 'trainer')
                @include('layouts.sidebar-trainer')
            @elseif($role === 'trainee')
                @include('layouts.sidebar-trainee')
            @endif
        </div>

        <!-- ── Footer pill ── -->
        <div class="p-3 flex-shrink-0">
            <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl
                        border"
                 :class="$store.darkMode.on
                     ? 'bg-[#0d1f3c] border-[#1e3a6b]'
                     : 'bg-[#e8f0fb] border-[#c5d8f5]'">
                <img src="{{ asset('assets/app_logo.PNG') }}"
                     alt="TESDA Logo"
                     class="w-7 h-7 object-contain">
                <span class="text-xs font-semibold tracking-wide"
                      :class="$store.darkMode.on
                          ? 'text-[#5b9cf6]'
                          : 'text-[#0057B8]'">
                    TCMS v1.0
                </span>
            </div>
        </div>
    </aside>
</div>