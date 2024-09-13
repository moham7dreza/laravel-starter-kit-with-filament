@extends('layouts.error')
@section('title', __('Unauthorized'))
@section('content')
    <h1 class="mb-3">403</h1>
    <p class="fs-20px">{{ __('Unauthorized') }}</p>
    <p class="text-white font-weight-bold">{{ __('Contact to admin for get permission') }}</p>
    <p class="fs-20px"><a class="text-decoration-none text-danger" href="/">{{ __('Go to home page') }}</a></p>
@endsection
