@extends('layouts.app')
@section('content')
    <div class="poa-container">
        <h3>Configuración de notificaciones por email</h3>

        <div class="content-container little">
            <div class="checkbox mb-2">
                <label for="email_notifications_allowed" class="inline-flex relative items-center cursor-pointer">

                    <input type="checkbox" class="sr-only peer" id="email_notifications_allowed"
                        name="email_notifications_allowed" {{ $user->email_notifications_allowed ? 'checked' : '' }}>

                    <div
                        class="checkbox-switch peer-checked:bg-color_1 peer-checked:after:border-white peer-checked:after:translate-x-full">
                    </div>

                    <p class="checkbox-name">Recibir notificaciones por email</p>
                </label>
            </div>

            @foreach ($notification_types as $notification_type)
                <div class="checkbox mb-2 ml-4 flex">
                    <div>
                        <label for="email-{{ $notification_type->uid }}"
                            class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" id="email-{{ $notification_type->uid }}"
                                class="sr-only peer email-notification-type" value="{{ $notification_type->uid }}"
                                {{ !in_array($notification_type->uid, array_column($user->emailNotificationsTypesDisabled->toArray(), 'uid')) ? 'checked' : '' }}>
                            <div
                                class="checkbox-switch peer-checked:bg-color_1 peer-checked:after:border-white peer-checked:after:translate-x-full">
                            </div>
                        </label>
                    </div>
                    <div class="checkbox-name">
                        <div>{{ $notification_type->name }}</div>
                        <small class="italic">{{ $notification_type->description }}</small>
                    </div>
                </div>
            @endforeach

            @foreach ($automaticNotificationTypes as $automaticNotificationType)
                <div class="checkbox mb-2 ml-4 flex">
                    <div>
                        <label for="automatic-email-{{ $automaticNotificationType->uid }}"
                            class="inline-flex relative items-center cursor-pointer">

                            <input type="checkbox" id="automatic-email-{{ $automaticNotificationType->uid }}"
                                class="sr-only peer automatic-email-notification-type"
                                value="{{ $automaticNotificationType->uid }}"
                                {{ !in_array($automaticNotificationType->uid, array_column($user->automaticEmailNotificationsTypesDisabled->toArray(), 'uid')) ? 'checked' : '' }}>

                            <div
                                class="checkbox-switch peer-checked:bg-color_1 peer-checked:after:border-white peer-checked:after:translate-x-full">
                            </div>
                        </label>
                    </div>
                    <div class="checkbox-name">
                        <div>{{ $automaticNotificationType->name }}</div>
                        <small class="italic">{{ $automaticNotificationType->description }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-center mt-[84px]">

            <button class="btn btn-primary-profile" id="save-notifications-btn">Guardar

                {{ e_heroicon('paper-airplane', 'outline') }}
            </button>
        </div>

    </div>
@endsection
