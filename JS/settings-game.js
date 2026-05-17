(() => {
    const form = document.getElementById('gameForm');
    const fallbackImage = form ? (form.dataset.fallbackImage || '') : '';

    const carouselList = document.getElementById('carouselList');
    const addCarouselItemBtn = document.getElementById('addCarouselItem');

    const cropModalEl = document.getElementById('imageCropModal');
    const cropTarget = document.getElementById('imageCropTarget');
    const cropTitle = document.getElementById('imageCropModalLabel');
    const applyCropBtn = document.getElementById('applyCropBtn');
    const cropModal = (window.bootstrap && cropModalEl) ? bootstrap.Modal.getOrCreateInstance(cropModalEl) : null;

    const variantSpecs = {
        logo: { crop: false, fit: 'contain', aspect: 16 / 9, width: 1280, height: 720, type: 'image/png' },
        header: { crop: true, fit: 'cover', aspect: 92 / 43, width: 920, height: 430, type: 'image/jpeg' },
        capsule: { crop: true, fit: 'cover', aspect: 77 / 29, width: 770, height: 290, type: 'image/jpeg' },
        background: { crop: true, fit: 'cover', aspect: 3840 / 1240, width: 3840, height: 1240, type: 'image/jpeg' },
        'wide-cover': { crop: true, fit: 'cover', aspect: 77 / 29, width: 770, height: 290, type: 'image/jpeg' },
        banner: { crop: true, fit: 'cover', aspect: 77 / 29, width: 770, height: 290, type: 'image/jpeg' },
        cover: { crop: true, fit: 'cover', aspect: 600 / 900, width: 600, height: 900, type: 'image/jpeg' },
        icon: { crop: true, fit: 'cover', aspect: 1, width: 64, height: 64, type: 'image/png' },
        carousel: { crop: true, fit: 'cover', aspect: 16 / 9, width: 1600, height: 900, type: 'image/jpeg' }
    };

    let cropper = null;
    let cropState = null;
    let carouselIndex = carouselList ? carouselList.querySelectorAll('.carousel-item-row').length : 0;

    function setPreviewBox(box, url, fit = 'cover') {
        if (!box) return;
        box.style.backgroundImage = `url('${url}')`;
        box.style.backgroundSize = fit;
        box.style.backgroundPosition = 'center';
        box.style.backgroundRepeat = 'no-repeat';
        box.style.display = 'block';
    }

    function revokeObjectUrl(url) {
        try {
            if (url && String(url).startsWith('blob:')) {
                URL.revokeObjectURL(url);
            }
        } catch (_) {}
    }

    function clearVideoPreview(videoEl) {
        if (!videoEl) return;

        if (videoEl.src) {
            revokeObjectUrl(videoEl.src);
        }

        videoEl.removeAttribute('src');
        videoEl.load();
        videoEl.style.display = 'none';
    }

    function cleanupCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (cropState && cropState.previewUrl) {
            revokeObjectUrl(cropState.previewUrl);
        }

        cropState = null;

        if (cropTarget) {
            cropTarget.src = '';
        }
    }

    if (cropModalEl) {
        cropModalEl.addEventListener('hidden.bs.modal', () => {
            if (cropState && !cropState.applied && cropState.requiresCrop && cropState.input) {
                cropState.input.value = '';
            }
            cleanupCropper();
        });
    }

    function openCropper({ file, input, previewBox, variant, spec }) {
        if (!cropModalEl || !cropTarget || !cropModal || typeof Cropper === 'undefined') return;

        cleanupCropper();

        const objectUrl = URL.createObjectURL(file);

        cropState = {
            input,
            previewBox,
            variant,
            spec,
            requiresCrop: true,
            previewUrl: objectUrl,
            applied: false,
            originalName: file.name || 'image'
        };

        cropTitle.textContent = `Recortar ${variant}`;
        cropTarget.src = objectUrl;

        cropTarget.onload = () => {
            cropper = new Cropper(cropTarget, {
                aspectRatio: spec.aspect,
                viewMode: 2,
                autoCropArea: 1,
                background: false,
                responsive: true,
                checkOrientation: true
            });
        };

        cropModal.show();
    }

    function processLogoFile(file, previewBox) {
        const reader = new FileReader();
        reader.onload = () => {
            setPreviewBox(previewBox, String(reader.result), 'contain');
        };
        reader.readAsDataURL(file);
    }

    function applyCroppedBlob(blob) {
        if (!cropState || !cropState.input || !cropState.previewBox) return;

        const ext = cropState.spec.type === 'image/png' ? 'png' : 'jpg';
        const cleanName = (cropState.originalName || 'image').replace(/\.[^.]+$/, '');
        const fileName = `${cleanName}_cropped.${ext}`;
        const file = new File([blob], fileName, { type: cropState.spec.type });

        const dt = new DataTransfer();
        dt.items.add(file);
        cropState.input.files = dt.files;

        const previewUrl = URL.createObjectURL(blob);
        setPreviewBox(cropState.previewBox, previewUrl, cropState.spec.fit);

        if (cropState.previewUrl) {
            revokeObjectUrl(cropState.previewUrl);
            cropState.previewUrl = null;
        }

        cropState.applied = true;

        if (cropModal) {
            cropModal.hide();
        }
    }

    if (applyCropBtn) {
        applyCropBtn.addEventListener('click', () => {
            if (!cropper || !cropState) return;

            const canvas = cropper.getCroppedCanvas({
                width: cropState.spec.width,
                height: cropState.spec.height,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) return;

            canvas.toBlob((blob) => {
                if (!blob) return;
                applyCroppedBlob(blob);
            }, cropState.spec.type, cropState.spec.type === 'image/jpeg' ? 0.92 : undefined);
        });
    }

    function updateCarouselOrders() {
        if (!carouselList) return;

        const rows = carouselList.querySelectorAll('.carousel-item-row');
        rows.forEach((row, index) => {
            const orderInput = row.querySelector('.js-carousel-order');
            if (orderInput) orderInput.value = index + 1;
        });
    }

    function createCarouselRow() {
        const index = carouselIndex++;

        const row = document.createElement('div');
        row.className = 'carousel-item-row';
        row.innerHTML = `
            <div class="carousel-item__handle">
                <button type="button" class="btn-drag" title="Arrastrar">
                    <span class="material-symbols-outlined">reorder</span>
                </button>
            </div>

            <div class="carousel-item__preview js-carousel-image-preview" style="background-image: url('${fallbackImage}');"></div>
            <video class="carousel-item__video js-carousel-video-preview" controls style="display:none;"></video>

            <div class="carousel-item__body">
                <div class="carousel-item__fields">
                    <div class="carousel-item__field carousel-item__field--file">
                        <label class="form-label mb-1">Archivo</label>
                        <input
                            type="file"
                            name="carousel_files[]"
                            class="form-control js-carousel-file"
                            accept="image/png,image/jpeg,image/webp,video/mp4,video/webm,video/ogg"
                        >
                    </div>

                    <div class="carousel-item__field carousel-item__field--order">
                        <label class="form-label mb-1">Orden</label>
                        <input
                            type="number"
                            name="carousel_orders[]"
                            class="carousel-order-input js-carousel-order"
                            min="1"
                            value="${index + 1}"
                        >
                    </div>

                    <div class="carousel-item__field carousel-item__field--type">
                        <label class="form-label mb-1">Tipo</label>
                        <select name="carousel_types[]" class="form-control js-carousel-type">
                            <option value="">Auto</option>
                            <option value="imagen">Imagen</option>
                            <option value="video">Vídeo</option>
                        </select>
                    </div>

                    <button type="button" class="btn-danger btn-sm js-remove-carousel-item">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>
        `;
        return row;
    }

    if (carouselList && window.Sortable) {
        new Sortable(carouselList, {
            animation: 150,
            handle: '.btn-drag',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onSort: updateCarouselOrders
        });
    }

    if (addCarouselItemBtn && carouselList) {
        addCarouselItemBtn.addEventListener('click', () => {
            carouselList.appendChild(createCarouselRow());
            updateCarouselOrders();
        });
    }

    function getCarouselRows() {
        return Array.from(carouselList.querySelectorAll('.carousel-item-row'));
    }

    function handleCarouselOrderChange(orderInput) {
        const allRows = getCarouselRows();
        const row = orderInput.closest('.carousel-item-row');
        if (!row) return;

        const totalRows = allRows.length;
        let newOrder = parseInt(orderInput.value, 10);
        if (Number.isNaN(newOrder) || newOrder < 1) {
            newOrder = 1;
        }

        if (newOrder > totalRows) {
            newOrder = totalRows;
        }

        orderInput.value = newOrder;

        const currentIndex = allRows.indexOf(row);
        const targetIndex = newOrder - 1;
        if (targetIndex === currentIndex) {
            return;
        }

        if (targetIndex >= allRows.length - 1) {
            carouselList.appendChild(row);
        } else if (targetIndex < currentIndex) {
            carouselList.insertBefore(row, allRows[targetIndex]);
        } else {
            carouselList.insertBefore(row, allRows[targetIndex + 1]);
        }

        updateCarouselOrders();
    }

    if (carouselList) {
        carouselList.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.js-remove-carousel-item');
            if (!removeBtn) return;

            const row = removeBtn.closest('.carousel-item-row');
            if (!row) return;

            const isExisting = row.dataset.multimediaId && row.dataset.isExisting === 'true';
            
            if (isExisting) {
                // Para elementos existentes, marcar para eliminar
                const deleteFlag = row.querySelector('.js-delete-flag');
                const mediaIdField = row.querySelector('.js-media-id');
                const isMarkedForDelete = deleteFlag && deleteFlag.value !== '';
                
                if (isMarkedForDelete) {
                    // Desmarcar eliminación
                    deleteFlag.value = '';
                    row.classList.remove('is-marked-for-deletion');
                    removeBtn.classList.remove('is-active');
                    removeBtn.title = 'Eliminar este elemento';
                    const icon = removeBtn.querySelector('.material-symbols-outlined');
                    if (icon) icon.textContent = 'delete';
                } else {
                    // Marcar para eliminación
                    if (mediaIdField && mediaIdField.value) {
                        deleteFlag.value = mediaIdField.value;
                        row.classList.add('is-marked-for-deletion');
                        removeBtn.classList.add('is-active');
                        removeBtn.title = 'Deshacer (se eliminará al guardar)';
                        const icon = removeBtn.querySelector('.material-symbols-outlined');
                        if (icon) icon.textContent = 'undo';
                    }
                }
            } else {
                // Para elementos nuevos, eliminar directamente del DOM
                const videoPreview = row.querySelector('.js-carousel-video-preview');
                clearVideoPreview(videoPreview);
                row.remove();
                updateCarouselOrders();
            }
        });

        carouselList.addEventListener('change', (event) => {
            const orderInput = event.target.closest('.js-carousel-order');
            if (orderInput) {
                handleCarouselOrderChange(orderInput);
                return;
            }

            const input = event.target.closest('.js-carousel-file');
            if (!input || !input.files || !input.files[0]) return;

            const row = input.closest('.carousel-item-row');
            if (!row) return;

            // Si es un elemento existente marcado para eliminar y se carga un archivo nuevo, desmarcar
            const deleteFlag = row.querySelector('.js-delete-flag');
            const removeBtn = row.querySelector('.js-remove-carousel-item');
            if (deleteFlag && deleteFlag.value !== '') {
                deleteFlag.value = '';
                row.classList.remove('is-marked-for-deletion');
                if (removeBtn) {
                    removeBtn.classList.remove('is-active');
                    removeBtn.title = 'Eliminar este elemento';
                    const icon = removeBtn.querySelector('.material-symbols-outlined');
                    if (icon) icon.textContent = 'delete';
                }
            }

            const file = input.files[0];
            const imgPreview = row.querySelector('.js-carousel-image-preview');
            const videoPreview = row.querySelector('.js-carousel-video-preview');
            const typeSelect = row.querySelector('.js-carousel-type');

            if (file.type.startsWith('image/')) {
                clearVideoPreview(videoPreview);

                if (typeSelect && !typeSelect.value) {
                    typeSelect.value = 'imagen';
                }

                openCropper({
                    file,
                    input,
                    previewBox: imgPreview,
                    variant: 'carousel',
                    spec: variantSpecs.carousel
                });

                return;
            }

            if (file.type.startsWith('video/')) {
                if (typeSelect) {
                    typeSelect.value = 'video';
                }

                if (imgPreview) {
                    setPreviewBox(imgPreview, fallbackImage, 'cover');
                    imgPreview.style.display = 'none';
                }

                if (videoPreview) {
                    clearVideoPreview(videoPreview);
                    videoPreview.src = URL.createObjectURL(file);
                    videoPreview.style.display = 'block';
                }
            }
        });
    }

    document.querySelectorAll('.js-static-image-input').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            const previewId = input.dataset.preview;
            const variant = input.dataset.variant || '';
            const previewBox = previewId ? document.getElementById(previewId) : null;

            if (!file || !previewBox) return;

            const spec = variantSpecs[variant];
            if (!spec) return;

            if (!spec.crop) {
                processLogoFile(file, previewBox);
                return;
            }

            if (file.type.startsWith('image/')) {
                openCropper({
                    file,
                    input,
                    previewBox,
                    variant,
                    spec
                });
            }
        });
    });

    document.querySelectorAll('.js-filter-input').forEach((input) => {
        input.addEventListener('input', () => {
            const targetId = input.dataset.filterTarget;
            const list = targetId ? document.getElementById(targetId) : null;
            if (!list) return;

            const query = input.value.trim().toLowerCase();

            list.querySelectorAll('.js-filter-item').forEach((item) => {
                const text = (item.textContent || '').toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('.js-clear-filter').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.filterTarget;
            const list = targetId ? document.getElementById(targetId) : null;
            if (!list) return;

            const wrapper = button.closest('.search-box');
            const input = wrapper ? wrapper.querySelector('.js-filter-input') : null;

            if (input) {
                input.value = '';
                input.focus();
            }

            list.querySelectorAll('.js-filter-item').forEach((item) => {
                item.style.display = '';
            });
        });
    });

    const achievementsList = document.getElementById('achievementsList');
    const addAchievementItemBtn = document.getElementById('addAchievementItem');

    function removeAchievementEmptyState() {
        const emptyState = achievementsList ? achievementsList.querySelector('.empty-state') : null;
        if (emptyState) {
            emptyState.remove();
        }
    }

    function createAchievementRow(logro = {}) {
        const row = document.createElement('div');
        row.className = 'achievement-item-row';
        row.dataset.logroId = logro.id_logro ? String(logro.id_logro) : '';
        row.innerHTML = `
            <div class="achievement-item__handle">
                <button type="button" class="btn-drag" title="Arrastra para reordenar">
                    <span class="material-symbols-outlined">drag_handle</span>
                </button>
            </div>
            <div class="achievement-item__body">
                <div class="achievement-item__fields">
                    <div class="achievement-item__field achievement-item__field--name">
                        <label class="form-label">Nombre</label>
                        <input
                            type="text"
                            name="achievement_names[]"
                            class="form-control js-achievement-name"
                            placeholder="Nombre del logro"
                            value="${logro.nombre_logro || ''}"
                            required
                        >
                    </div>
                    <div class="achievement-item__field achievement-item__field--description">
                        <label class="form-label">Descripción</label>
                        <input
                            type="text"
                            name="achievement_descriptions[]"
                            class="form-control js-achievement-description"
                            placeholder="Descripción del logro"
                            value="${logro.descripcion_logro || ''}"
                        >
                    </div>
                    <div class="achievement-item__field achievement-item__field--rarity">
                        <label class="form-label">Rareza</label>
                        <select name="achievement_rarities[]" class="form-control js-achievement-rarity" required>
                            <option value="cobre" ${logro.rareza === 'cobre' ? 'selected' : ''}>Cobre</option>
                            <option value="plata" ${logro.rareza === 'plata' ? 'selected' : ''}>Plata</option>
                            <option value="oro" ${logro.rareza === 'oro' ? 'selected' : ''}>Oro</option>
                            <option value="platino" ${logro.rareza === 'platino' ? 'selected' : ''}>Platino</option>
                            <option value="lotus" ${logro.rareza === 'lotus' ? 'selected' : ''}>Lotus</option>
                        </select>
                    </div>
                    <div class="achievement-item__field achievement-item__field--identifier">
                        <label class="form-label">ID Único</label>
                        <input
                            type="text"
                            class="form-control js-achievement-id"
                            value="${logro.identificador_unico || '(nuevo)'}"
                            readonly
                        >
                    </div>
                    <input type="hidden" name="achievement_ids[]" value="${logro.id_logro ? String(logro.id_logro) : ''}">
                    <button type="button" class="btn-danger js-remove-achievement-item" title="Eliminar logro">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </div>
        `;
        return row;
    }

    if (addAchievementItemBtn && achievementsList) {
        addAchievementItemBtn.addEventListener('click', () => {
            removeAchievementEmptyState();
            achievementsList.appendChild(createAchievementRow());
        });
    }

    if (achievementsList) {
        achievementsList.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.js-remove-achievement-item');
            if (!removeBtn) return;
            const row = removeBtn.closest('.achievement-item-row');
            if (row) {
                row.remove();
            }
        });
    }

    updateCarouselOrders();
})();