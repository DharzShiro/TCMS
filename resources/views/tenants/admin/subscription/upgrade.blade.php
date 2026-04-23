@extends('layouts.app')
@section('title', 'Upgrade Your Plan')

@push('styles')
@include('tenants.admin.subscription._styles')
@endpush

@section('content')
@php
    $tenant       = tenancy()->tenant;
    $currentPlan  = $tenant->subscription ?? 'basic';
    $planSlugs    = $plans->pluck('slug')->toArray();
    $currentIndex = array_search($currentPlan, $planSlugs);
    $planIcons    = ['basic' => '🌱', 'standard' => '🚀', 'premium' => '💎'];
    $featuredSlug = 'standard';
@endphp

<div class="up-page">
    <div class="up-bg"></div>
    <div class="up-inner">
        @include('tenants.admin.subscription._header')

        {{-- ── Promo banner + code widget (new) ── --}}
        @include('tenants.admin.subscription._promo_banner')

        @include('tenants.admin.subscription._plan_cards')
        @include('tenants.admin.subscription._comparison_table')
    </div>
</div>

@include('tenants.admin.subscription._modal_upgrade')
@endsection

@push('scripts')
@include('tenants.admin.subscription._scripts')
@endpush