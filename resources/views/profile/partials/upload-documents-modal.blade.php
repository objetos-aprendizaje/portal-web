<div id="upload-documents-modal" data-action="" class="modal">
    <div class="modal-body w-full md:w-[600px]">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="notification-title">Subir documentos</h2>
            </div>

            <div>
                <button data-modal-id="upload-documents-modal" class="modal-close-modal-btn close-modal-btn">
                    <?php e_heroicon('x-mark', 'outline'); ?>
                </button>
            </div>
        </div>

        <p class="mb-4" id="evaluation-criteria"></p>

        <form id="upload-documents-form" prevent-default>
            <div class="poa-form" id="documents-container">

            </div>


            <div class="btn-block">
                <div>
                    <button type="submit" id="upload-documents-btn" class="btn btn-primary-profile w-[200px]">Subir
                        {{ e_heroicon('arrow-up-tray', 'outline') }}</button>
                </div>
            </div>

            <input type="hidden" id="documents-modal-course-uid" name="course_uid" value="">
            <input type="hidden" id="documents-modal-educational-program-uid" name="course_uid" value="">
        </form>

    </div>

    <template id="document-template">
        <div class="field">
            <div class="label-container label-center document-name"></div>

            <div class="content-container content-center">
                <div class="poa-input-file select-file-container">
                    <div class="flex-none">
                        <input type="file" class="hidden document-input" name="resource_input_file" id="">
                        <label for="" class="btn btn-rectangular btn-input-file document-input-label">
                            Seleccionar archivo {{ e_heroicon('arrow-up-tray', 'outline') }}
                        </label>
                    </div>
                    <div class="file-name text-[14px]">
                        Ningún archivo seleccionado
                    </div>
                </div>

                <a aria-label="enlace" class="link-label download-document hidden" data-document_uid=""
                    href="javascript:void(0)">Descargar</a>
            </div>
        </div>
    </template>
</div>
