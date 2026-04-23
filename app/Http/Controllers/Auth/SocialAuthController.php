<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * The ONE URI registered in Google Console:
     *   http://tcm.com/auth/google/callback
     *
     * Never changes, no matter how many tenants you add.
     */
    private function centralCallbackUrl(): string
    {
        return config('services.google.redirect');
    }

    // ─────────────────────────────────────────────────────────────────────
    // STEP 1 — Tenant side: acme.tcm.com/auth/google
    //
    // Encode tenant context (host, port, csrf) into an encrypted state
    // so we can securely recover it after Google bounces back to the
    // central domain. No plain-text subdomain in the URL anymore.
    // ─────────────────────────────────────────────────────────────────────

    public function redirectToGoogle()
    {
        $request = request();

        // Build a structured, encrypted state — tamper-proof unlike a plain subdomain string
        $state = encrypt([
            'subdomain' => tenancy()->tenant?->subdomain,
            'host'      => $request->getHost(),
            'port'      => $request->getPort(),
            'csrf'      => \Illuminate\Support\Str::random(40),
        ]);

        Log::info('Google OAuth: redirecting to Google', [
            'host'      => $request->getHost(),
            'subdomain' => tenancy()->tenant?->subdomain,
        ]);

        return Socialite::driver('google')
            ->redirectUrl($this->centralCallbackUrl())
            ->stateless()                    // consistent with state approach
            ->with(['state' => $state])
            ->redirect();
    }

    // ─────────────────────────────────────────────────────────────────────
    // STEP 2 — Central side: tcm.com/auth/google/callback
    //
    // Registered in Google Console. Decrypts the state, finds/creates the
    // user in the TENANT database, encrypts a short-lived payload, then
    // bounces the browser back to the tenant subdomain.
    //
    // Called from a CENTRAL route — tenancy is NOT initialised here.
    // ─────────────────────────────────────────────────────────────────────

    public function handleGoogleCallback()
    {
        // ── 1. Exchange code with Google ──────────────────────────────────
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->centralCallbackUrl())
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth: callback exchange failed', ['error' => $e->getMessage()]);

            return $this->centralError('Google authentication failed. Please try again.');
        }

        // ── 2. Decrypt and validate the state ────────────────────────────
        try {
            $state = decrypt(request('state'));
        } catch (\Exception $e) {
            Log::error('Google OAuth: state decryption failed', ['error' => $e->getMessage()]);

            return $this->centralError('Invalid OAuth state. Please try again.');
        }

        $subdomain = $state['subdomain'] ?? null;
        $host      = $state['host']      ?? null;
        $port      = $state['port']      ?? null;

        if (! $subdomain || ! $host) {
            Log::warning('Google OAuth: missing subdomain/host in state');

            return $this->centralError('Could not determine your organisation. Please try again.');
        }

        Log::info('Google OAuth: callback received', [
            'email'     => $googleUser->getEmail(),
            'subdomain' => $subdomain,
            'host'      => $host,
        ]);

        // ── 3. Find the approved tenant ───────────────────────────────────
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'approved')
            ->first();

        if (! $tenant) {
            Log::warning('Google OAuth: tenant not found or not approved', ['subdomain' => $subdomain]);

            return $this->centralError('Organisation not found or not yet approved.');
        }

        // ── 4. Find or create the user INSIDE the tenant database ─────────
        $user = null;

        $tenant->run(function () use ($googleUser, &$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                // Auto-register as trainee (same behaviour as before)
                $user = User::create([
                    'name'              => $googleUser->getName(),
                    'email'             => $googleUser->getEmail(),
                    'google_id'         => $googleUser->getId(),
                    'password'          => null,
                    'role'              => 'trainee',
                    'email_verified_at' => now(),
                ]);

                Log::info('Google OAuth: new trainee auto-registered', [
                    'email' => $user->email,
                ]);

                // Notify the tenant admin
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title'   => 'New Trainee Registration (Google)',
                        'message' => "A new trainee '{$user->name}' registered via Google with email {$user->email}.",
                        'link'    => route('admin.trainees.index'),
                    ]);
                }
            } else {
                // Link Google ID to existing account if not yet set
                if (! $user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }
        });

        // ── 5. Build an encrypted short-lived cross-domain token ──────────
        //
        // encrypt() uses Laravel's APP_KEY — cryptographically signed and
        // tamper-proof. No file cache required, no race conditions.
        //
        $payload = encrypt([
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'subdomain' => $subdomain,
            'expires'   => now()->addMinutes(2)->timestamp,
        ]);

        // ── 6. Bounce back to the tenant subdomain ────────────────────────
        //
        // Dynamic host+port construction — no hardcoded URLs.
        //
        $tenantUrl = 'http://' . $host
            . ($port && $port != 80 ? ":$port" : '')
            . '/auth/google/finish';

        Log::info('Google OAuth: forwarding to tenant', ['url' => $tenantUrl]);

        return redirect($tenantUrl . '?' . http_build_query(['token' => $payload]));
    }

    // ─────────────────────────────────────────────────────────────────────
    // STEP 3 — Tenant side: acme.tcm.com/auth/google/finish
    //
    // Decrypts the encrypted payload, validates expiry and subdomain,
    // logs the user into the tenant session, and redirects to dashboard.
    // ─────────────────────────────────────────────────────────────────────

    public function finishGoogleLogin()
    {
        $request = request();
        $raw     = $request->get('token');

        if (! $raw) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Invalid or missing login token.']);
        }

        // ── Decrypt and validate ──────────────────────────────────────────
        try {
            $data = decrypt($raw);
        } catch (\Exception $e) {
            Log::error('Google OAuth: finish token decryption failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Login token is invalid or was tampered with. Please try again.']);
        }

        // Expiry check
        if (now()->timestamp > ($data['expires'] ?? 0)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Login token expired. Please try again.']);
        }

        // Subdomain check — prevents a token from one tenant being used on another
        if (($data['subdomain'] ?? null) !== tenancy()->tenant?->subdomain) {
            Log::warning('Google OAuth: subdomain mismatch', [
                'token_subdomain'  => $data['subdomain'] ?? null,
                'tenant_subdomain' => tenancy()->tenant?->subdomain,
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Token mismatch. Please try again.']);
        }

        // ── Find the user ─────────────────────────────────────────────────
        $user = User::find($data['user_id'] ?? null);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'User not found. Please try again.']);
        }

        // ── Log in ────────────────────────────────────────────────────────
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Activity log — mirrors TenantLoginController behaviour
        ActivityLog::create([
            'tenant_id'   => tenancy()->tenant?->id,
            'tenant_name' => tenancy()->tenant?->name,
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'user_email'  => $user->email,
            'role'        => $user->role,
            'action'      => 'login_success',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'success'     => true,
        ]);

        Log::info('Google OAuth: login successful', [
            'email' => $user->email,
            'role'  => $user->role,
        ]);

        // ── Redirect to role dashboard ────────────────────────────────────
        return match ($user->role) {
            'admin'   => redirect()->intended(route('admin.dashboard')),
            'trainer' => redirect()->intended(route('trainer.dashboard')),
            'trainee' => redirect()->intended(route('trainee.dashboard')),
            default   => redirect('/'),
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Redirect to the central login with an error message.
     * Builds the URL dynamically instead of hardcoding http://tcm.com:8000.
     */
    private function centralError(string $message)
    {
        $centralDomain = config('tenancy.central_domains')[0] ?? 'tcm.com';
        $port          = request()->getPort();
        $base          = 'http://' . $centralDomain . ($port && $port != 80 ? ":$port" : '');

        return redirect($base . '/login')
            ->withErrors(['email' => $message]);
    }
}