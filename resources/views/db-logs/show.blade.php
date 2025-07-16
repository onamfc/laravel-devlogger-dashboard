@extends('devlogger-dashboard::layouts.app')

@section('title', 'Log Details')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <livewire:devlogger-log-details :log-id="$log->id" />
</div>
@endsection