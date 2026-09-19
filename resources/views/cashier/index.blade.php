@extends('layouts.app')


@section('body')
    <div class="container-fluid">
        @include('template.nav')

        @livewire('cashier.cashier-shifts')
    </div>
@endsection
