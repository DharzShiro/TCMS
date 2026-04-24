<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Unavailable — {{ config('app.name', 'Training Course Management System') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-amber-50 flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-lg">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-amber-200 overflow-hidden">

            {{-- Top accent bar --}}
            <div class="h-1.5 bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500"></div>

            <div class="px-8 py-10 text-center">

                {{-- Icon --}}
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 border-2 border-amber-200 mb-6">
                    <i class="fas fa-lock text-amber-500 text-3xl"></i>
                </div>

                {{-- Heading --}}
                <h1 class="text-2xl font-bold text-gray-800 mb-3">
                    Account Temporarily Unavailable
                </h1>

                {{-- Tenant name badge --}}
                @if (!empty($tenant?->name))
                    <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                        {{ $tenant->name }}
                    </span>
                @endif

                {{-- Message --}}
                <p class="text-gray-600 leading-relaxed mb-2">
                    Your organization's access to this system has been
                    <span class="font-semibold text-amber-700">temporarily disabled</span>.
                </p>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    Please contact your service provider or system administrator to restore access.
                </p>

                {{-- Divider --}}
                <div class="border-t border-gray-100 mb-6"></div>

                {{-- Details --}}
                <div class="text-sm text-gray-500 mb-8 space-y-1">
                    @php
                        $supportEmail = config('app.support_email', '2301113288@student.buksu.edu.ph');
                        $appName      = config('app.name', 'Training Course Management System');
                    @endphp
                    <p>
                        <i class="fas fa-building mr-1.5 text-gray-400"></i>
                        {{ $appName }}
                    </p>
                    <p>
                        <i class="fas fa-envelope mr-1.5 text-gray-400"></i>
                        <a href="mailto:{{ $supportEmail }}" class="text-amber-600 hover:text-amber-700 underline">
                            {{ $supportEmail }}
                        </a>
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="mailto:{{ $supportEmail }}?subject=Account+Access+Request&body=Hello%2C%0A%0AI+would+like+to+request+restoration+of+access+for+my+organization.%0A%0AOrganization%3A+{{ urlencode($tenant?->name ?? '') }}%0A%0AThank+you."
                       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors duration-150 shadow-sm">
                        <i class="fas fa-envelope"></i>
                        Contact Support
                    </a>
                    <button onclick="window.location.reload()"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 transition-colors duration-150">
                        <i class="fas fa-rotate-right"></i>
                        Retry
                    </button>
                </div>

            </div>

        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} {{ config('app.name', 'Training Course Management System') }}
        </p>

    </div>

</body>
</html>
