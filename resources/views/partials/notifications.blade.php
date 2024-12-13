@if (!empty($general_notifications))

    @foreach ($general_notifications as $index => $notification)
        <div class="notification cursor-pointer" data-notification_uid="{{ $notification['uid'] }}"
            data-notification_type="{{ $notification['type'] }}">
            <div class="select-none py-[18px] flex gap-2">
                @if (!$notification['is_read'])
                    <div class="not-read">
                        <svg class="mt-3" xmlns="http://www.w3.org/2000/svg" width="4" height="4"
                            viewBox="0 0 4 4" fill="none">
                            <circle cx="2" cy="2" r="2" fill="#FF0000" />
                        </svg>
                    </div>
                @endif
                <div class=" w-full cursor-pointer">
                    <div class=" flex justify-between items-center gap-2 mb-[4px]">
                        <div class="font-bold flex-shrink truncate">
                            {{ $notification['title'] }}
                        </div>
                        <p class="text-color_4 text-[10px] flex-auto whitespace-nowrap text-right">
                            {{ formatDateTimeNotifications($notification['date']) }}
                        </p>
                    </div>
                    <p class="truncate">
                        {{ strip_tags($notification['description']) }}
                    </p>
                </div>
            </div>
        </div>

        @if ($index < count($general_notifications) - 1)
            <hr class="border-gray-300" />
        @endif
    @endforeach
@else
    <div class=" text-center mt-[28px]">
        <p class="font-bold text-[16px]">
            Sin notificaciones
        </p>
        <p class="mt-1.5 text-[14px]">
            Te avisaremos tan pronto haya una actualización o se suba un nuevo curso.
        </p>
    </div>
@endif
