@extends('layouts.app')
@section('title', isset($plan) ? 'Edit Plan: ' . $plan->name : 'Create Subscription Plan')

@section('content')

@include('superadmin.plans._manage_styles')

<div class="pm-wrap space-y-0">

    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-{{ isset($plan) ? 'pencil-alt' : 'plus-circle' }} mr-2" style="color:var(--sa-accent);"></i>
                {{ isset($plan) ? 'Edit Plan: ' . $plan->name : 'Create Subscription Plan' }}
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-muted);">
                {{ isset($plan) ? 'Update plan details, limits, availability, and feature flags.' : 'Define a new plan available to tenant organizations.' }}
            </p>
        </div>
        <a href="{{ route('superadmin.plans.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Plans
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-xl border-2 p-4 mb-4" style="background:rgba(22,163,74,.05);border-color:var(--sa-success);">
            <p style="color:var(--sa-success);" class="font-semibold flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </p>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border-2 p-4 mb-4" style="background:rgba(206,17,38,.05);border-color:var(--sa-danger);">
            <div style="color:var(--sa-danger);" class="font-semibold flex items-start gap-2">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>@foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach</div>
            </div>
        </div>
    @endif

    <form action="{{ isset($plan) ? route('superadmin.plans.manage.update', $plan) : route('superadmin.plans.manage.store') }}"
          method="POST">
        @csrf
        @if(isset($plan)) @method('PATCH') @endif

        @include('superadmin.plans._manage_form_identity')

        @include('superadmin.plans._manage_form_pricing')

        @include('superadmin.plans._manage_form_limits')

        @include('superadmin.plans._manage_form_exports')

        @include('superadmin.plans._manage_form_features')

        @include('superadmin.plans._manage_form_availability')

        @include('superadmin.plans._manage_form_status')

        @include('superadmin.plans._manage_form_actions')
    </form>

    @include('superadmin.plans._manage_danger_zone')

</div>

@include('superadmin.plans._manage_scripts')

@endsection