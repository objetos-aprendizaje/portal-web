<div id="notification-info-modal" data-action="" class="modal">
    <div class="modal-body w-full md:w-[600px]">
        <div class="text-center mb-4">
            <h2 class="modal-title" id="notification-title">
            </h2>
            <p id="notification-description">
            </p>
        </div>

        <div class="flex sm:flex-row flex-col justify-center gap-2">
            <div class="hidden" id="action-btn-container">
                <a aria-label="enlace" id="notification-action-href" href="javascript:void(0)">
                    <button id="notification-action-btn"
                        class="btn btn-primary w-full sm:w-[200px] close-modal-btn"><span></span>
                        {{ e_heroicon('arrow-up-right', 'outline') }}</button>
                </a>
            </div>

            <div>
                <button data-modal-id="notification-info-modal"
                    class="btn btn-secondary w-full sm:w-[200px] close-modal-btn">Cerrar
                    {{ e_heroicon('x-mark', 'outline') }}</button>
            </div>


        </div>

    </div>
</div>
