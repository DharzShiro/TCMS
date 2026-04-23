@extends('layouts.app')
@section('title', 'Platform Analytics')
@section('content')

@include('superadmin.analytics._styles')
@include('superadmin.analytics._header')
@include('superadmin.analytics._kpi_cards')
@include('superadmin.analytics._platform_totals')

@if($expiringSoon->count() > 0)
    @include('superadmin.analytics._expiring_soon')
@endif

@include('superadmin.analytics._renewals_storage')
@include('superadmin.analytics._plans')
@include('superadmin.analytics._tenant_table')

@endsection
