@extends('layouts.error')
@section('title', __('Server Error'))
@section('content')
    <h1 class="mb-3">500</h1>
    <p class="fs-20px">{{ __('Server Error') }}</p>
    <p class="text-white font-weight-bold">{{ __('Try again later') }}</p>
    <p class="fs-20px"><a class="text-decoration-none text-danger" href="/">{{ __('Go to home page') }}</a></p>
@endsection
