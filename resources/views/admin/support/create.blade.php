@extends('layouts.app')

@section('title', 'New Support Ticket')

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    label{display:block;font-size:12px;font-weight:600;letter-spacing:.03em;text-transform:uppercase;margin-bottom:6px;color:var(--sa-text-muted);}
    .field{width:100%;border-radius:10px;border:1.5px solid var(--sa-border);padding:10px 14px;font-size:14px;background:var(--sa-bg);color:var(--sa-text);transition:border-color .15s;outline:none;}
    .field:focus{border-color:var(--sa-accent);}
</style>

<div class="max-w-2xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.support.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border-2 hover:bg-gray-50 dark:hover:bg-white/5 transition"
           style="border-color:var(--sa-border);color:var(--sa-text-muted)">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold" style="color:var(--sa-primary)">New Support Ticket</h1>
            <p class="text-sm mt-0.5" style="color:var(--sa-text-muted)">Describe your issue and our team will respond shortly.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="rounded-xl p-4 text-sm" style="background:rgba(206,17,38,.08);color:#CE1126;border:1px solid rgba(206,17,38,.2)">
        <ul class="space-y-1">
            @foreach($errors->all() as $e)
            <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.support.store') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border-2 p-6 space-y-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
        @csrf

        {{-- Subject --}}
        <div>
            <label for="subject">Subject *</label>
            <input id="subject" name="subject" type="text" class="field" required maxlength="255"
                   value="{{ old('subject') }}" placeholder="Brief description of your issue">
        </div>

        {{-- Category + Priority --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category">Category *</label>
                <select id="category" name="category" class="field" required>
                    <option value="">Select category…</option>
                    @foreach([
                        'bug_report'      => 'Bug Report',
                        'technical_issue' => 'Technical Issue',
                        'account_concern' => 'Account Concern',
                        'billing_concern' => 'Billing Concern',
                        'feature_request' => 'Feature Request',
                        'general_inquiry' => 'General Inquiry',
                    ] as $val => $label)
                    <option value="{{ $val }}" @selected(old('category')===$val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority" class="field">
                    <option value="low"    @selected(old('priority','medium')==='low')>Low</option>
                    <option value="medium" @selected(old('priority','medium')==='medium')>Medium</option>
                    <option value="high"   @selected(old('priority')==='high')>High</option>
                    <option value="urgent" @selected(old('priority')==='urgent')>Urgent</option>
                </select>
            </div>
        </div>

        {{-- Message --}}
        <div>
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="8" class="field resize-none" required maxlength="10000"
                      placeholder="Describe your issue in as much detail as possible. Include steps to reproduce, what you expected vs. what happened, and any error messages you see.">{{ old('message') }}</textarea>
        </div>

        {{-- Attachments --}}
        <div>
            <label>Attachments (optional)</label>
            <div class="rounded-xl border-2 border-dashed p-4 text-center"
                 style="border-color:var(--sa-border)">
                <i class="fas fa-cloud-upload-alt text-2xl mb-2" style="color:var(--sa-text-muted)"></i>
                <p class="text-xs" style="color:var(--sa-text-muted)">
                    Upload screenshots or files. Max 5 files · 5 MB each.
                    <br>Allowed: JPG, PNG, GIF, WEBP, PDF, TXT
                </p>
                <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.txt"
                       class="mt-3 text-xs mx-auto block" style="color:var(--sa-text-muted)">
            </div>
            @error('attachments.*')
            <p class="text-xs mt-1" style="color:#CE1126">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('admin.support.index') }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold border"
               style="border-color:var(--sa-border);color:var(--sa-text-muted)">Cancel</a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-white text-sm font-bold hover:opacity-90 transition"
                    style="background:var(--sa-accent)">
                <i class="fas fa-paper-plane mr-2"></i>Submit Ticket
            </button>
        </div>
    </form>

    {{-- Help text --}}
    <div class="rounded-xl p-4 text-xs" style="background:rgba(0,87,184,.06);border:1px solid rgba(0,87,184,.15);color:var(--sa-text-muted)">
        <i class="fas fa-info-circle mr-2" style="color:var(--sa-accent)"></i>
        Our support team typically responds within 24 hours on business days.
        For urgent issues, select <strong>Urgent</strong> priority.
    </div>
</div>
@endsection
