@extends('layouts.app')


@section('body')
    <div class="container-fluid">
        @include('template.nav')

        @livewire('sedes.index')
    </div>
@endsection
