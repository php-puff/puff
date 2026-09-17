@extends('layout')

@section('content')
    <p>{{ lang('name') }}, PHP {{ $phpVersion }}.</p>
    @include('partials.links')
@endsection
