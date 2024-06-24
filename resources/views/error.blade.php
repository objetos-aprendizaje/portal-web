@extends('layouts.app')
@section('content')
    <div class="container mx-auto">

        <h2 class="mt-4">{{ $errorMessage }}</h2>

    </div>
    @include('partials.footer')
@endsection
