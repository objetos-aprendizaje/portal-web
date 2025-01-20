@extends('layouts.app')
@section('content')
    <div class="container mx-auto">
        <h1 class="text-color_1 mt-[43px]">Selección</h1>

        <table class="mt-[35px] w-full">
            <thead>
                <tr class=" text-[16px] border-b ">
                    <td colspan="2" class="py-[10px] pr-[10px]">Nombre</td>
                    <td class="py-[10px] pr-[10px] hidden lg:block">Descripción</td>
                    <td class="py-[10px] pr-[10px]">Subtotal</td>
                </tr>
            </thead>

            <tbody>
                <tr class="lg:border-b">
                    <td class="py-[40px] w-[124px] pr-[24px]">
                        <img src="{{ env('BACKEND_URL') . '/' . $learningObjectData['image_path'] }}" alt="{{ $learningObjectData['title'] }}"
                            class="w-[100px] h-[100px]">
                    </td>

                    <td class="py-[40px] pr-[10px]">
                        <p class="font-roboto-bold mb-[7px]">{{ $learningObjectData['title'] }}</p>
                        <p class="text-color_1">{{ $learningObjectData['ects_workload'] }} ECTS</p>
                    </td>

                    <td class="py-[40px] pr-[10px] hidden lg:block">{{ $learningObjectData['description'] }}</td>
                    <td class="py-[40px] pr-[10px] whitespace-nowrap">{{ $learningObjectData['cost'] ?? 0 }} €</td>

                    <input type="hidden" class="course-uid" value="{{ $learningObjectData['uid'] }}" />
                </tr>
                <tr class="lg:hidden border-b">
                    <td colspan="4" class="py-[20px]">{{ $learningObjectData['description'] }}</td>

                </tr>
            </tbody>
        </table>

        <p class="hidden text-right mt-[24px]">Subtotal: {{ $learningObjectData['cost'] }}€</p>


        <div class="flex sm:justify-end justify-between py-[18px] sm:border-none border-b gap-1">
            <p class="font-roboto-bold">Subtotal</p>
            <p>{{ $learningObjectData['cost'] }}€</p>
        </div>

        <div class="flex justify-end mt-[16px] mb-[112px]">
            <button type="button" id="inscribe-learning-object-btn" class="btn btn-primary"
                data-learning_object_type="{{ $learning_object_type }}"
                data-learning_object_uid="{{ $learning_object_uid }}">Inscribirse
                {{ e_heroicon('chevron-right', 'outline') }}</button>
        </div>

    </div>

@endsection
