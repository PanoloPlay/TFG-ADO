document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const deleteButton = document.getElementById('deleteAvatarBtn');
    const borrarInput = document.getElementById('borrarAvatarInput');

    const previewBoxes = [
        document.getElementById('avatarPreviewBox-184px'),
        document.getElementById('avatarPreviewBox-64px'),
        document.getElementById('avatarPreviewBox-32px')
    ];

    const cropperWrapper = document.getElementById('cropper-wrapper');
    const cropImage = document.getElementById('avatarCropImage');
    const applyButton = document.getElementById('avatarCropApply');
    const cancelButton = document.getElementById('avatarCropCancel');

    const settingsPage = document.querySelector('.settings-page');
    const defaultInitial = settingsPage?.dataset.avatarDefaultInitial || 'U';
    const defaultAvatarClass = settingsPage?.dataset.avatarDefaultClass || 'avatar-1';

    let cropper = null;
    let currentObjectUrl = null;

function clearBox(box) {
    if (!box) return;

    box.classList.remove(
        'avatar-0', 'avatar-1', 'avatar-2',
        'avatar-3', 'avatar-4', 'avatar-5', 'avatar-6'
    );

    box.classList.add(defaultAvatarClass);

    box.style.backgroundImage = '';
    box.style.backgroundSize = '';
    box.style.backgroundPosition = '';
    box.style.backgroundRepeat = '';

    box.innerHTML = `<span>${defaultInitial}</span>`;
}

function restorePreviewToDefault() {
    previewBoxes.forEach(clearBox);
}

if (deleteButton) {
    deleteButton.addEventListener('click', function () {
        if (borrarInput) borrarInput.value = '1';
        if (avatarInput) avatarInput.value = '';

        closeCropper();
        restorePreviewToDefault();
    });
}

    function setPreviewImage(url) {
        previewBoxes.forEach(box => {
            if (!box) return;

            box.classList.remove(
                'avatar-1', 'avatar-2', 'avatar-3',
                'avatar-4', 'avatar-5', 'avatar-6'
            );
            box.classList.add('avatar-0');

            box.style.backgroundImage = `url('${url}')`;
            box.style.backgroundSize = 'cover';
            box.style.backgroundPosition = 'center';
            box.style.backgroundRepeat = 'no-repeat';

            box.innerHTML = '';
        });
    }

    function restoreDefaultPreview() {
        previewBoxes.forEach(clearBox);
    }

    function closeCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }

        if (cropperWrapper) {
            cropperWrapper.style.display = 'none';
        }

        if (cropImage) {
            cropImage.src = '';
        }
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            if (borrarInput) borrarInput.value = '0';

            const file = this.files && this.files[0];
            if (!file) return;

            if (!file.type.match(/^image\/(png|jpe?g)$/i)) {
                alert('Solo se permiten imágenes PNG, JPG y JPEG.');
                this.value = '';
                return;
            }

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            currentObjectUrl = URL.createObjectURL(file);
            cropImage.src = currentObjectUrl;

            cropperWrapper.style.display = 'block';

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                background: false,
                guides: true,
                highlight: false
            });
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            closeCropper();
            if (avatarInput) avatarInput.value = '';
            if (borrarInput) borrarInput.value = '0';
        });
    }

    if (applyButton) {
        applyButton.addEventListener('click', function () {
            if (!cropper) return;

            if (borrarInput) borrarInput.value = '0';

            const canvas = cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            canvas.toBlob(function (blob) {
                if (!blob) {
                    alert('No se ha podido generar la imagen recortada.');
                    return;
                }

                const file = new File([blob], 'avatar.png', { type: 'image/png' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                avatarInput.files = dataTransfer.files;

                const previewUrl = URL.createObjectURL(blob);
                setPreviewImage(previewUrl);

                closeCropper();
            }, 'image/png');
        });
    }

    if (deleteButton) {
        deleteButton.addEventListener('click', function () {
            if (borrarInput) borrarInput.value = '1';
            if (avatarInput) avatarInput.value = '';

            closeCropper();
            restoreDefaultPreview();
        });
    }
});