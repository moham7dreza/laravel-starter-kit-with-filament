@extends('layouts.error')
@section('title', __('Page Expired'))
@section('content')
    <h1 class="mb-3">419</h1>
    <p class="fs-20px">{{ __('Page Expired') }}</p>
    <p class="text-white font-weight-bold">{{ __('Please try again') }}</p>
    <p class="fs-20px"><a class="text-decoration-none text-danger" href="/">{{ __('Go to home page') }}</a></p>
@endsection
