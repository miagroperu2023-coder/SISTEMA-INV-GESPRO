@extends('layouts.app')


@section('body')
    <div class="container-fluid">
        @include('template.nav')

        @livewire('super-admin.negocios')
    </div>
@endsection
