@extends('layouts.primary')

@section('content')
    @livewire('user-disputes', ['dispute' => request()->route('dispute')])
@endsection
