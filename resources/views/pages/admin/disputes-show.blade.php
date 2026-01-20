@extends('layouts.primary')

@section('content')
    @livewire('admin-disputes', ['dispute' => request()->route('dispute')])
@endsection

