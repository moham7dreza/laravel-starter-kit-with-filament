@extends('layouts.error')
@section('title', __('Page Not Found'),)
@section('content')
    <h1 class="mb-3">404</h1>
    <p class="fs-20px">{{ __('Page not found') }}</p>
    <p class="text-white text-center font-weight-bold">{{ __('Wrong url ???') }}</p>
    <p class="fs-20px"><a class="text-decoration-none text-danger" href="/">{{ __('Go to home page') }}</a></p>
@endsection
