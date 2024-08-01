@extends('layouts.app')


@section('content')
    <div class="container mx-auto my-8">

        <section class="mb-8">
            <h1 class="text-color_1 ">{{ $page->name }}</h1>
        </section>

        <section>
            {!! $page->content !!}
        </section>
    </div>

@include('partials.footer')

@endsection


