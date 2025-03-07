@extends('layouts.app')

@section('content')
    <div class="poa-container mb-8">
        <h2>Datos de perfil</h2>
        <form id="user-profile-form" prevent-default>
            @csrf

            <div class="poa-form">

                <div class="field">
                    <div class="label-container">
                        <label for="image_input_file">Foto</label>
                    </div>
                    <div class="content-container">

                        <div class="poa-input-image">
                            <img alt="imagen" id="photo_path_preview"
                                src="{{ $user->photo_path ? env('BACKEND_URL') . '/' . $user->photo_path : asset('images/no-user.svg') }}" />

                            <span class="dimensions">*Dimensiones: Alto: 50px x Ancho: 300px. Formato: PNG, JPG. Tam. Máx.:
                                1MB</span>

                            <div class="select-file-container">
                                <input accept="image/*" type="file" id="photo_path" name="photo_path" class="hidden" />

                                <label for="photo_path" class="btn btn-rectangular min-w-[110px]">
                                    Subir {{ e_heroicon('arrow-up-tray', 'outline') }}
                                </label>

                                <button type="button" class="btn btn-rectangular min-w-[110px]" id="delete-photo">
                                    Eliminar {{ e_heroicon('x-mark', 'outline') }}
                                </button>

                                <span id="image-name" class="image-name text-[14px]">Ningún archivo seleccionado</span>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="field">
                    <div class="label-container label-center">
                        <label for="first_name">Nombre <span class="text-danger">*</span></label>
                    </div>
                    <div class="content-container little">
                        <input value="{{ $user->first_name }}" placeholder="Manuel" class="poa-input" type="text"
                            id="first_name" name="first_name" />
                    </div>
                </div>

                <div class="field">
                    <div class="label-container label-center">
                        <label for="last_name">Apellidos <span class="text-danger">*</span></label>
                    </div>
                    <div class="content-container little">
                        <input value="{{ $user->last_name }}" placeholder="Pérez Gutiérrez" type="text" id="last_name"
                            name="last_name" class="poa-input" />
                    </div>
                </div>

                <div class="field">
                    <div class="label-container label-center">
                        <label for="nif">NIF <span class="text-danger">*</span></label>
                    </div>
                    <div class="content-container little">
                        <input value="{{ $user->nif }}" placeholder="12345678X" type="text" id="nif"
                            class="poa-input" name="nif" />
                    </div>
                </div>

                <div class="field">
                    <div class="label-container label-center">
                        <label for="department">Departamento</label>
                    </div>
                    <div class="content-container little">
                        <select name="department_uid" class="poa-input">
                            <option value="">No especificado</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->uid }}" @selected($user->department_uid == $department->uid)>{{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="label-container">
                        <label for="curriculum">Currículum</label>
                    </div>

                    <div class="content-container little">
                        <textarea placeholder="" type="text" id="curriculum" name="curriculum" class="poa-input" rows="6">{{ $user->curriculum }}</textarea>
                    </div>

                </div>

                <div class="flex justify-center">
                    <button type="submit" class="btn btn-primary-profile mt-[88px]">Guardar
                        {{ e_heroicon('paper-airplane', 'outline') }}</button>
                </div>

            </div>
        </form>

    </div>
@endsection
