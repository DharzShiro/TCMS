@php
    $tenant       = tenancy()->tenant;
    $brandLogo    = ($tenant?->brand_logo)
                        ? Storage::disk('public')->url($tenant->brand_logo)
                        : asset('assets/app_logo.PNG');
    $brandName    = $tenant?->brand_name    ?? config('app.name', 'TCMS');
    $brandTagline = $tenant?->brand_tagline ?? 'Skills Development Authority';
    $colorPrimary = $tenant?->brand_color_primary ?? '#003087';
    $colorAccent  = $tenant?->brand_color_accent  ?? '#CE1126';

    // Darken accent for hover (simple approach: use a fixed darker shade)
    $colorAccentDark = '#A50E1E'; // fallback; overridden by CSS filter if default

    $navNotifications = [];
    if (isset($notifications) && $notifications instanceof \Illuminate\Support\Collection) {
        $navNotifications = $notifications->map(function($notif) {
            return [
                'id'         => $notif->id,
                'title'      => $notif->title,
                'message'    => $notif->message,
                'created_at' => $notif->created_at->diffForHumans(),
                'is_read'    => (bool) $notif->is_read,
                'link'       => $notif->link ?? null,
            ];
        })->toArray();
    }

    $dashboardRoute = match(Auth::user()->role) {
        'admin'   => route('admin.dashboard'),
        'trainer' => route('trainer.dashboard'),
        'trainee' => route('trainee.dashboard'),
        'superadmin' => route('superadmin.dashboard'),
        default   => '/',
    };
@endphp

@include('layouts.partials.navigation-styles')

{{-- Inject brand CSS variables so dropdowns + sidebar also pick them up --}}
<style>
    :root {
        --brand-primary:       {{ $colorPrimary }};
        --brand-accent:        {{ $colorAccent }};
        --brand-primary-mid:   color-mix(in srgb, {{ $colorPrimary }} 60%, #0057B8 40%);
    }
</style>

<div x-data="navigationComponent()" x-init="init()">
    <nav class="fixed top-0 left-0 right-0 z-50 border-b shadow-lg"
         style="background: linear-gradient(90deg, {{ $colorPrimary }} 0%, color-mix(in srgb, {{ $colorPrimary }} 60%, #0057B8 40%) 50%, {{ $colorPrimary }} 100%); border-bottom: 3px solid {{ $colorAccent }};"
         @scroll.window="isScrolled = (window.pageYOffset > 10)"
         :class="{ 'backdrop-blur-md': isScrolled }">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                <!-- Left Side -->
                <div class="flex items-center gap-4">
                    <button @click="$store.sidebar.toggle()"
                        class="p-3 rounded-xl text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 group"
                        style="background: linear-gradient(135deg, {{ $colorAccent }} 0%, color-mix(in srgb, {{ $colorAccent }} 70%, #000 30%) 100%);">
                        <i class="fas fa-bars-staggered transition-transform duration-200 group-hover:rotate-12"></i>
                    </button>

                    <a href="{{ $dashboardRoute }}" class="flex items-center gap-4">
                        <div class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <img src="{{ $brandLogo }}" alt="{{ $brandName }} Logo" class="h-8 w-8 object-contain" />
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-white block">
                                {{ $brandName }}
                            </span>
                            <span class="text-xs font-medium tracking-widest uppercase block"
                                  style="color: rgba(255,255,255,0.65); margin-top: -2px;">
                                {{ $brandTagline }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-3">

                    <!-- ══════════════════════════════════
                         NOTIFICATION BELL + DROPDOWN
                    ══════════════════════════════════ -->
                    <div class="relative">
                        <button @click="toggleNotifications()"
                                class="relative p-2 rounded-xl transition-colors duration-200"
                                style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.15);"
                                onmouseover="this.style.background='rgba(255,255,255,0.20)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.10)'">
                            <i class="fas fa-bell text-white text-xl"></i>
                            <div x-show="unreadCount > 0"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-0"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center animate-pulse"
                                 style="background: {{ $colorAccent }}; border: 2px solid {{ $colorPrimary }};">
                                <span class="text-white text-xs font-bold" x-text="unreadCount"></span>
                            </div>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="notificationOpen"
                             @click.away="notificationOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="nd-dropdown nd-notif-dropdown">

                            <div class="nd-accent-bar"></div>

                            <div class="nd-dropdown-header">
                                <span class="nd-dropdown-header-title">
                                    <i class="fas fa-bell mr-2" style="color: {{ $colorPrimary }};"></i>Notifications
                                </span>
                                <div class="nd-dropdown-header-meta">
                                    <div x-show="loading">
                                        <div class="animate-spin rounded-full h-3.5 w-3.5 border-b-2" style="border-color: {{ $colorPrimary }};"></div>
                                    </div>
                                    <span x-show="unreadCount > 0"
                                          class="px-2 py-0.5 rounded-full text-xs font-700"
                                          style="background: var(--nd-unread-bg); color: var(--nd-unread-dot);"
                                          x-text="unreadCount + ' unread'">
                                    </span>
                                    <span x-show="!loading && allRead"
                                          style="color: var(--nd-read-dot);">
                                        <i class="fas fa-check-circle mr-1"></i>All read
                                    </span>
                                </div>
                            </div>

                            <div class="nd-notif-list">
                                <template x-for="notif in notifications" :key="notif.id">
                                    <a :href="notif.link || '#'"
                                       @click="notif.link ? handleNotificationClick($event, notif) : $event.preventDefault()"
                                       class="nd-notif-item"
                                       :class="notif.is_read ? 'read' : 'unread'">
                                        <div class="nd-notif-icon" :class="notif.is_read ? 'read' : 'unread'">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div class="nd-notif-body">
                                            <div class="nd-notif-title" x-text="notif.title"></div>
                                            <div class="nd-notif-msg" x-text="notif.message"></div>
                                            <div class="nd-notif-time" x-text="notif.created_at"></div>
                                        </div>
                                        <div class="nd-notif-dot" :class="notif.is_read ? 'read' : 'unread'"></div>
                                    </a>
                                </template>

                                <div x-show="notifications.length === 0" class="nd-notif-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications yet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════
                         PROFILE BUTTON + DROPDOWN
                    ══════════════════════════════════ -->
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 group"
                                style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.18);"
                                onmouseover="this.style.background='rgba(255,255,255,0.18)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow"
                                 style="background: linear-gradient(135deg, {{ $colorAccent }} 0%, color-mix(in srgb, {{ $colorAccent }} 70%, #000 30%) 100%);">
                                <span class="text-sm font-bold text-white" x-text="userInitial">
                                    {{ Auth::check() ? substr(Auth::user()->name, 0, 1) : 'G' }}
                                </span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-sm font-bold text-white leading-tight" x-text="userName">
                                    {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                                </div>
                                <div class="text-xs leading-tight" style="color: rgba(255,255,255,0.65);">System User</div>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                               style="color: rgba(255,255,255,0.65);"
                               :class="{ 'rotate-180': profileOpen }"></i>
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-show="profileOpen"
                             @click.away="profileOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="nd-dropdown nd-profile-dropdown">

                            <div class="nd-accent-bar"></div>

                            <div class="nd-profile-hero">
                                <div class="nd-profile-avatar">
                                    <span x-text="userInitial">{{ Auth::check() ? substr(Auth::user()->name, 0, 1) : 'G' }}</span>
                                    <div class="nd-profile-avatar-dot"></div>
                                </div>
                                <div>
                                    <div class="nd-profile-name" x-text="userName">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>
                                    <div class="nd-profile-role">
                                        <i class="fas fa-shield-halved" style="font-size: 9px;"></i> System User
                                    </div>
                                </div>
                            </div>

                            <div class="nd-menu-body">

                                <a :href="profileEditRoute" class="nd-menu-item">
                                    <div class="nd-menu-icon blue">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="nd-menu-item-label">
                                        <span>My Profile</span>
                                        <span class="sub">Manage account settings</span>
                                    </div>
                                    <i class="fas fa-chevron-right" style="font-size: 11px; color: var(--nd-muted);"></i>
                                </a>

                                <button @click="$store.darkMode.toggle()" class="nd-menu-item">
                                    <div class="nd-menu-icon green">
                                        <template x-if="$store.darkMode.on">
                                            <i class="fas fa-sun" style="color: #f59e0b;"></i>
                                        </template>
                                        <template x-if="!$store.darkMode.on">
                                            <i class="fas fa-moon"></i>
                                        </template>
                                    </div>
                                    <div class="nd-menu-item-label">
                                        <span x-text="$store.darkMode.on ? 'Light Mode' : 'Dark Mode'">Dark Mode</span>
                                        <span class="sub">Switch theme appearance</span>
                                    </div>
                                    <div class="nd-toggle-track" :class="{ 'on': $store.darkMode.on }">
                                        <div class="nd-toggle-thumb"></div>
                                    </div>
                                </button>

                                <div class="nd-divider"></div>

                                <button @click="logout()" class="nd-menu-item">
                                    <div class="nd-menu-icon red">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </div>
                                    <div class="nd-menu-item-label">
                                        <span>Sign Out</span>
                                        <span class="sub">End your current session</span>
                                    </div>
                                    <i class="fas fa-chevron-right" style="font-size: 11px; color: var(--nd-muted);"></i>
                                </button>
                            </div>

                            <div class="nd-dropdown-footer">
                                {{ $brandName }} &nbsp;·&nbsp; v1.0
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </nav>
</div>

<script>
function navigationComponent() {
    return {
        isScrolled: false,
        notificationOpen: false,
        profileOpen: false,
        loading: false,
        allRead: false,

        userName: @json(Auth::check() ? Auth::user()->name : 'Guest'),
        userInitial: @json(Auth::check() ? substr(Auth::user()->name, 0, 1) : 'G'),
        unreadCount: 0,
        notifications: @json($navNotifications),

        profileEditRoute: '{{ route("profile.edit") }}',
        logoutRoute: '{{ route("logout") }}',
        csrfToken: '{{ csrf_token() }}',

        init() {
            this.updateUnreadCount();
            this.updateAllReadStatus();
        },

        toggleNotifications() {
            this.notificationOpen = !this.notificationOpen;
        },

        handleNotificationClick(event, notif) {
            if (!notif.is_read) {
                notif.is_read = true;
                this.updateUnreadCount();
                this.updateAllReadStatus();
                // Mark as read on server
                fetch(`{{ url('/notifications') }}/${notif.id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                }).catch(err => console.error('Failed to mark notification as read', err));
            }
            this.notificationOpen = false;
        },

        updateUnreadCount() {
            this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        },

        updateAllReadStatus() {
            this.allRead = this.notifications.length > 0 && this.unreadCount === 0;
        },

        logout() {
            if (confirm('Are you sure you want to log out?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = this.logoutRoute;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = this.csrfToken;
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>