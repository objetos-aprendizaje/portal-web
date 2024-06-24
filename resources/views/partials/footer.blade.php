<footer class="bg-white">
    <div class="hidden lg:flex justify-between py-20 border-gray-100 container mx-auto items-center">

        <div>
            <a class="no-effect-hover" href="/" class="">

                @if ($general_options['poa_logo'])
                    <img src="{{env('BACKEND_URL') . '/' . $general_options['poa_logo'] }}" class="mr-3 w-[212px] h-[75px]" alt="Logo header">
                @else
                    <img src="/data/images/default_images/logo-default.png" class="mr-3 w-[100px] h-[100px]"
                        alt="Logo header">
                @endif
            </a>
        </div>

        <p class="max-w-[526px]">
            Lorem ipsum dolor sit amet consectetur. At integer et parturient commodo. Egestas dolor suscipit fringilla
            senectus. Dictum enim lacus.
        </p>
    </div>

    <hr>

    <div class="lg:flex lg:gap-8 container mx-auto mt-[51px] mb-[30px] ">
        <div class="lg:w-1/2 text-center lg:text-left">
            <h3 class="font-bold text-color_3 text-xl leading-[22px] mb-7">Enlaces</h3>

            <ul class="mt-6 space-y-4 text-sm">

                @foreach ($footer_pages as $footer_page)
                    <li>
                        <a href="/pages/{{ $footer_page['uid'] }}" class="text-color_3">
                            {{ $footer_page['name'] }}
                        </a>
                    </li>
                @endforeach

            </ul>
        </div>


        <div class="flex-col gap-[40px] hidden lg:flex w-1/2">
            <h3 class="font-bold text-color_3 text-xl leading-[22px]">¿Aún no encuentras lo que buscas?
            </h3>

            <p class="leading-6">Lorem ipsum dolor sit amet consectetur adipiscing elit aliquam mauris sed ma</p>
            <div
                class=" bg-white items-center justify-between p-1 border rounded-xl my-auto  lg:flex hidden min-w-[150px] w-full max-w-[348px]">

                <input
                    class="w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                    type="text" placeholder="Buscar...">

                <div
                    class="bg-color_1 p-2 cursor-pointer mx-1 rounded-[10px] input-search transition duration-300 hover:bg-color_2">
                    {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="container mx-auto flex justify-between py-[38px] lg:flex-row gap-[30px] flex-col-reverse items-center">
        <p class="text-sm text-color_3 text-center">
            © {{ date('Y') }} {{ $general_options['company_name'] }}. Todos los derechos reservados.
        </p>

        <div class="flex gap-[22px]">

            @if ($general_options['facebook_url'])
                <div>
                    <a href="{{ $general_options['facebook_url'] }}" rel="noreferrer" target="_blank"
                        class="text-gray-700 transition hover:opacity-75">
                        <span class="sr-only">Facebook</span>

                        <svg class="h-6 w-6 fill-color_1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @endif

            @if ($general_options['instagram_url'])
                <div>
                    <a href="{{ $general_options['instagram_url'] }}" rel="noreferrer" target="_blank"
                        class="text-gray-700 transition hover:opacity-75">
                        <span class="sr-only">Instagram</span>

                        <svg class="h-6 w-6 fill-color_1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @endif


            @if ($general_options['x_url'])
                <div>
                    <a href="{{ $general_options['x_url'] }}" rel="noreferrer" target="_blank"
                        class="text-gray-700 transition hover:opacity-75">
                        <span class="sr-only">X</span>

                        <svg class="h-6 w-6 fill-color_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 19"
                            fill="none">
                            <g clip-path="url(#clip0_496_13137)">
                                <path
                                    d="M10 0C4.75348 0 0.5 4.25348 0.5 9.5C0.5 14.7465 4.75348 19 10 19C15.2465 19 19.5 14.7465 19.5 9.5C19.5 4.25348 15.2465 0 10 0Z" />
                                <path
                                    d="M11.0386 8.64045L15.1054 3.91309H14.1417L10.6105 8.0178L7.79009 3.91309H4.53711L8.80207 10.1201L4.53711 15.0775H5.50088L9.22995 10.7428L12.2085 15.0775H15.4615L11.0383 8.64045H11.0386ZM5.84812 4.6386H7.32841L14.1421 14.385H12.6619L5.84812 4.6386Z"
                                    fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_496_13137">
                                    <rect width="19" height="19" fill="white" transform="translate(0.5)" />
                                </clipPath>
                            </defs>
                        </svg>
                    </a>
                </div>
            @endif

            @if ($general_options['youtube_url'])
                <div>
                    <a href="{{ $general_options['youtube_url'] }}" rel="noreferrer" target="_blank"
                        class="text-gray-700 transition hover:opacity-75">
                        <span class="sr-only">YouTube</span>

                        <svg class="h-6 w-6 fill-color_1" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            width="21" height="16" viewBox="0 0 21 16">
                            <path
                                d="M11.2882 15.1313L7.22605 15.0554C5.9108 15.0289 4.59228 15.0817 3.30282 14.8076C1.34126 14.3981 1.20229 12.3902 1.05688 10.706C0.856519 8.33814 0.934085 5.92733 1.31219 3.57925C1.52565 2.26174 2.36568 1.47557 3.66476 1.39001C8.05011 1.07954 12.4646 1.11633 16.8403 1.26118C17.3024 1.27446 17.7677 1.34704 18.2234 1.42965C20.4727 1.83257 20.5275 4.10796 20.6733 6.02342C20.8187 7.95864 20.7573 9.9038 20.4794 11.8258C20.2564 13.4172 19.8298 14.7518 18.0294 14.8806C15.7737 15.0491 13.5697 15.1847 11.3076 15.1415C11.3077 15.1313 11.2947 15.1313 11.2882 15.1313ZM8.90006 11.1023C10.6 10.1049 12.2674 9.12411 13.9576 8.13339C12.2545 7.13598 10.5902 6.15519 8.90006 5.16447V11.1023Z" />
                        </svg>
                    </a>
                </div>
            @endif


            @if ($general_options['linkedin_url'])
                <div>
                    <a href="{{ $general_options['linkedin_url'] }}" rel="noreferrer" target="_blank"
                        class="text-gray-700 transition hover:opacity-75">
                        <span class="sr-only">Linkedin</span>

                        <svg class="h-6 w-6 fill-color_1" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            width="19" height="19" viewBox="0 0 19 19">
                            <path
                                d="M0.849609 3.07118C0.849609 2.49396 1.05232 2.01777 1.45772 1.6426C1.86312 1.26742 2.39016 1.07983 3.0388 1.07983C3.67587 1.07983 4.1913 1.26453 4.58513 1.63395C4.99053 2.0149 5.19324 2.51128 5.19324 3.12312C5.19324 3.67724 4.99633 4.13898 4.6025 4.5084C4.19711 4.88936 3.66428 5.07983 3.00405 5.07983H2.98668C2.3496 5.07983 1.83417 4.88936 1.44034 4.5084C1.04651 4.12745 0.849609 3.64837 0.849609 3.07118ZM1.07548 18.2227V6.65559H4.93262V18.2227H1.07548ZM7.06969 18.2227H10.9268V11.7638C10.9268 11.3598 10.9732 11.0481 11.0658 10.8287C11.228 10.4362 11.4741 10.1044 11.8042 9.83308C12.1344 9.56179 12.5484 9.42615 13.0465 9.42615C14.3438 9.42615 14.9925 10.2977 14.9925 12.0409V18.2227H18.8496V11.5907C18.8496 9.88213 18.4442 8.58632 17.6334 7.70321C16.8226 6.82009 15.7512 6.37853 14.4191 6.37853C12.9249 6.37853 11.7608 7.01923 10.9268 8.30061V8.33524H10.9095L10.9268 8.30061V6.65559H7.06969C7.09285 7.02499 7.10444 8.17361 7.10444 10.1015C7.10444 12.0293 7.09285 14.7364 7.06969 18.2227Z" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>

    </div>



    </div>
</footer>
