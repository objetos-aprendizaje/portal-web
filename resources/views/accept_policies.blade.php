@extends('layouts.app')
@section('content')
    <div class="container mx-auto">
        <h3 class="text-color_1 mt-[43px] mb-[23px]">Aceptación de políticas</h3>

        <p class="mb-[23px]">Antes de seguir usando la plataforma, debes aceptar las siguientes políticas</p>

        <form type="POST" prevent-default id="submit-acceptance-policies">
            @csrf
            @foreach ($policiesMustAccept as $policy)
                <div class="flex gap-2 items-center mb-[23px]"><input type="checkbox" class="text-color_1"
                        id="{{ $policy->uid }}" name="{{ $policy->uid }}" />
                    <label for="{{ $policy->uid }}">
                        <a class="text-color_1" href="/page/{{ $policy->slug }}" target="_blank">{{ $policy->name }}</a>
                    </label>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary btn-save-policies">Enviar</button>

        </form>
    </div>
@endsection
