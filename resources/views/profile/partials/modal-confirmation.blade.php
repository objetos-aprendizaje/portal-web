<div id="confirmation-modal" data-action="" class="modal">
    <div class="modal-body w-full md:w-[600px]">
        <div class="text-center">
            <h2 class="mb-[22px]" id="modal-title"></h2>
            <p class="mb-[22px]" id="modal-description"></p>
        </div>

        <div class="flex flex-wrap justify-center gap-4">
            <div>
                <button id="confirm-button" class="btn btn-primary w-full min-w-[200px] max-w-[200px]">Aceptar {{e_heroicon('check', 'outline')}}</button>
            </div>

            <div>
                <button id="cancel-button" class="btn btn-secondary w-full min-w-[200px] max-w-[200px]">Cancelar {{e_heroicon('x-mark', 'outline')}}</button>
            </div>
        </div>

    </div>
    <div class="params"></div>

</div>
