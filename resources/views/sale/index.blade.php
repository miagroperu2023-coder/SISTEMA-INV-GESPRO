@extends('layouts.app')


@section('body')
    <div class="container-fluid">
        @include('template.nav')

        @livewire('sales.register')
    </div>
@endsection
