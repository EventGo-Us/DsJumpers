<?php
ob_start();
session_start();
include_once 'valid_login.php';
include_once 'config/config.php';
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$isEnglish = ($_SESSION['Idioma'] ?? 'es') === 'en';
include_once 'head.php';
?>
<style>
    body { background-color: #f4f7f6; }
    .banner-table { background: #fff; border-radius: 12px; overflow: hidden; }
    .banner-preview { width: 180px; height: 90px; object-fit: cover; }
</style>
</head>
<body>
<?php include_once 'nav.php'; ?>

<main class="container py-5 mt-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><?= $isEnglish ? 'Banner images' : 'Imágenes de banners' ?></h1>
            <p class="text-muted mb-0"><?= $isEnglish ? 'Create and manage banner images.' : 'Crea y administra las imágenes de banners.' ?></p>
        </div>
        <button type="button" class="btn btn-primary" id="newBannerButton">
            <i class="fa-solid fa-plus me-1"></i><?= $isEnglish ? 'New banner' : 'Nuevo banner' ?>
        </button>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label visually-hidden" for="bannerSearch"><?= $isEnglish ? 'Search banners' : 'Buscar banners' ?></label>
            <input type="search" class="form-control" id="bannerSearch" placeholder="<?= $isEnglish ? 'Search by image or status' : 'Buscar por imagen o estado' ?>">
        </div>
    </div>

    <div id="pageMessage" class="alert d-none" role="alert"></div>
    <div class="banner-table shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><?= $isEnglish ? 'Image' : 'Imagen' ?></th>
                        <th><?= $isEnglish ? 'Order' : 'Orden' ?></th>
                        <th><?= $isEnglish ? 'Status' : 'Estado' ?></th>
                        <th><?= $isEnglish ? 'Created' : 'Fecha de creación' ?></th>
                        <th><?= $isEnglish ? 'Last updated' : 'Última actualización' ?></th>
                        <th class="text-end pe-3"><?= $isEnglish ? 'Actions' : 'Acciones' ?></th>
                    </tr>
                </thead>
                <tbody id="bannersTable">
                    <tr><td colspan="6" class="text-center py-4"><?= $isEnglish ? 'Loading...' : 'Cargando...' ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="bannerForm">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="bannerModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $isEnglish ? 'Close' : 'Cerrar' ?>"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bannerId">
                    <input type="hidden" id="bannerImage">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="bannerImageFile" class="form-label"><?= $isEnglish ? 'Image' : 'Imagen' ?></label>
                            <input type="file" class="form-control" id="bannerImageFile" accept="image/jpeg,image/png,image/webp,image/avif">
                            <div class="form-text"><?= $isEnglish ? 'Images are stored in the banner/originals folder.' : 'Las imágenes se guardan en la carpeta banner/originals.' ?></div>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img id="bannerImagePreview" class="banner-preview rounded d-none" alt="<?= $isEnglish ? 'Banner image preview' : 'Vista previa de imagen del banner' ?>">
                                <button type="button" id="removeBannerImage" class="btn btn-sm btn-outline-danger d-none"><?= $isEnglish ? 'Remove image' : 'Quitar imagen' ?></button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="bannerOrder" class="form-label"><?= $isEnglish ? 'Order' : 'Orden' ?></label>
                            <input type="number" class="form-control" id="bannerOrder" min="0" step="1" required>
                        </div>
                        <div class="col-md-2">
                            <label for="bannerStatus" class="form-label"><?= $isEnglish ? 'Status' : 'Estado' ?></label>
                            <select class="form-select" id="bannerStatus">
                                <option value="pending"><?= $isEnglish ? 'Pending' : 'Pendiente' ?></option>
                                <option value="active"><?= $isEnglish ? 'Active' : 'Activo' ?></option>
                            </select>
                        </div>
                    </div>
                    <div id="formMessage" class="alert d-none mt-3 mb-0" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= $isEnglish ? 'Cancel' : 'Cancelar' ?></button>
                    <button type="submit" class="btn btn-primary" id="saveBannerButton"><?= $isEnglish ? 'Save banner' : 'Guardar banner' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<?php include_once 'js_scripts.php'; ?>

$(function () {
    const english = <?= $isEnglish ? 'true' : 'false' ?>;
    const publicUrl = <?= json_encode(rtrim(CFPUBLICURL, '/')) ?>;
    const clientId = <?= json_encode((string) ($_SESSION['id_cliente'] ?? '')) ?>;
    const text = english ? {
        loading: 'Loading...', noBanners: 'No banners found.', loadError: 'Could not load banners.',
        newBanner: 'New banner', editBanner: 'Edit banner', pending: 'Pending', active: 'Active',
        edit: 'Edit', remove: 'Delete', confirmRemove: 'Delete this banner?',
        removeSuccess: 'Banner deleted successfully.', removeError: 'Could not delete the banner.',
        saveError: 'Could not save the banner.', uploadError: 'Could not upload the image.',
        uploadSuccess: 'Image uploaded successfully.', saved: 'Banner saved successfully.',
        uploading: 'Uploading image...', saving: 'Saving...', imageRequired: 'Please upload an image.'
    } : {
        loading: 'Cargando...', noBanners: 'No se encontraron banners.', loadError: 'No se pudieron cargar los banners.',
        newBanner: 'Nuevo banner', editBanner: 'Editar banner', pending: 'Pendiente', active: 'Activo',
        edit: 'Editar', remove: 'Eliminar', confirmRemove: '¿Eliminar este banner?',
        removeSuccess: 'Banner eliminado correctamente.', removeError: 'No se pudo eliminar el banner.',
        saveError: 'No se pudo guardar el banner.', uploadError: 'No se pudo cargar la imagen.',
        uploadSuccess: 'Imagen cargada correctamente.', saved: 'Banner guardado correctamente.',
        uploading: 'Cargando imagen...', saving: 'Guardando...', imageRequired: 'Carga una imagen para continuar.'
    };
    const modal = new bootstrap.Modal(document.getElementById('bannerModal'));
    let banners = [];
    let imageUploading = false;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function showMessage($element, message, type) {
        $element.removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type).text(message);
    }

    function hideMessage($element) {
        $element.addClass('d-none').text('');
    }

    function imageUrl(file) {
        return publicUrl + '/' + encodeURIComponent(clientId) + '/banner/originals/' + encodeURIComponent(file);
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleString();
    }

    function renderBanners() {
        const search = $('#bannerSearch').val().trim().toLowerCase();
        const filtered = banners.filter(function (banner) {
            return String(banner.Image || '').toLowerCase().includes(search) ||
                String(banner.status || '').toLowerCase().includes(search);
        });

        if (!filtered.length) {
            $('#bannersTable').html('<tr><td colspan="6" class="text-center text-muted py-5">' + text.noBanners + '</td></tr>');
            return;
        }

        const rows = filtered.map(function (banner) {
            const state = banner.status === 'active';
            const image = banner.Image
                ? '<img src="' + escapeHtml(imageUrl(banner.Image)) + '" alt="" class="banner-preview rounded me-2">'
                : '';
            return '<tr>' +
                '<td class="ps-3"><div class="d-flex align-items-center">' + image + '<span class="text-break">' + escapeHtml(banner.Image) + '</span></div></td>' +
                '<td>' + escapeHtml(banner.Orden) + '</td>' +
                '<td><span class="badge ' + (state ? 'text-bg-success' : 'text-bg-warning') + '">' + (state ? text.active : text.pending) + '</span></td>' +
                '<td>' + formatDate(banner.FechaCreacion) + '</td>' +
                '<td>' + formatDate(banner.FechaCambio) + '</td>' +
                '<td class="text-end pe-3 text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-banner" data-id="' + Number(banner.Id) + '">' + text.edit + '</button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-danger delete-banner" data-id="' + Number(banner.Id) + '">' + text.remove + '</button>' +
                '</td></tr>';
        }).join('');
        $('#bannersTable').html(rows);
    }

    function loadBanners() {
        $('#bannersTable').html('<tr><td colspan="6" class="text-center py-4">' + text.loading + '</td></tr>');
        $.ajax({
            url: API_BASE_URL + 'banner_images',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function (response) {
            banners = response.data || [];
            renderBanners();
        }).fail(function () {
            $('#bannersTable').html('<tr><td colspan="6" class="text-center text-danger py-4">' + text.loadError + '</td></tr>');
        });
    }

    function updateImagePreview(file) {
        if (file) {
            $('#bannerImagePreview').attr('src', imageUrl(file)).removeClass('d-none');
            $('#removeBannerImage').removeClass('d-none');
        } else {
            $('#bannerImagePreview').addClass('d-none').attr('src', '');
            $('#removeBannerImage').addClass('d-none');
        }
    }

    function resetForm() {
        document.getElementById('bannerForm').reset();
        $('#bannerId, #bannerImage, #bannerImageFile').val('');
        $('#bannerStatus').val('pending');
        $('#bannerOrder').val(banners.reduce(function (max, banner) {
            return Math.max(max, Number(banner.Orden) || 0);
        }, 0) + 1);
        updateImagePreview('');
        hideMessage($('#formMessage'));
    }

    $('#newBannerButton').on('click', function () {
        resetForm();
        $('#bannerModalTitle').text(text.newBanner);
        modal.show();
    });

    $('#bannerSearch').on('input', renderBanners);

    $('#bannersTable').on('click', '.edit-banner', function () {
        const bannerId = $(this).data('id');
        $.ajax({
            url: API_BASE_URL + 'banner_images/' + bannerId,
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function (response) {
            const banner = response.data;
            resetForm();
            $('#bannerId').val(banner.Id);
            $('#bannerOrder').val(banner.Orden);
            $('#bannerImage').val(banner.Image || '');
            $('#bannerStatus').val(banner.status);
            updateImagePreview(banner.Image || '');
            $('#bannerModalTitle').text(text.editBanner);
            modal.show();
        }).fail(function () {
            showMessage($('#pageMessage'), text.loadError, 'danger');
        });
    });

    $('#bannerImageFile').on('change', function () {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('table', 'banner');
        formData.append('upload_file', file);
        imageUploading = true;
        $('#bannerImageFile').prop('disabled', true);
        showMessage($('#formMessage'), text.uploading, 'warning');

        $.ajax({
            url: 'ajax/uploads.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            if (!response.success || !response.files || !response.files[0] || !response.files[0].success) {
                showMessage($('#formMessage'), response.files && response.files[0] ? response.files[0].message : text.uploadError, 'danger');
                return;
            }
            $('#bannerImage').val(response.files[0].image);
            updateImagePreview(response.files[0].image);
            showMessage($('#formMessage'), text.uploadSuccess, 'success');
        }).fail(function () {
            showMessage($('#formMessage'), text.uploadError, 'danger');
        }).always(function () {
            imageUploading = false;
            $('#bannerImageFile').prop('disabled', false).val('');
        });
    });

    $('#removeBannerImage').on('click', function () {
        $('#bannerImage').val('');
        updateImagePreview('');
    });

    $('#bannerForm').on('submit', function (event) {
        event.preventDefault();
        if (imageUploading) {
            showMessage($('#formMessage'), text.uploading, 'warning');
            return;
        }
        if (!$('#bannerImage').val()) {
            showMessage($('#formMessage'), text.imageRequired, 'danger');
            return;
        }

        const bannerId = $('#bannerId').val();
        const payload = {
            Orden: $('#bannerOrder').val(),
            Image: $('#bannerImage').val(),
            status: $('#bannerStatus').val()
        };
        $('#saveBannerButton').prop('disabled', true).text(text.saving);
        $.ajax({
            url: API_BASE_URL + 'banner_images' + (bannerId ? '/' + bannerId : ''),
            type: bannerId ? 'PUT' : 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify(payload)
        }).done(function () {
            modal.hide();
            showMessage($('#pageMessage'), text.saved, 'success');
            loadBanners();
        }).fail(function (xhr) {
            const response = xhr.responseJSON;
            showMessage($('#formMessage'), response && response.message ? response.message : text.saveError, 'danger');
        }).always(function () {
            $('#saveBannerButton').prop('disabled', false).text(english ? 'Save banner' : 'Guardar banner');
        });
    });

    $('#bannersTable').on('click', '.delete-banner', function () {
        if (!window.confirm(text.confirmRemove)) return;
        $.ajax({
            url: API_BASE_URL + 'banner_images/' + $(this).data('id'),
            type: 'DELETE',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function () {
            showMessage($('#pageMessage'), text.removeSuccess, 'success');
            loadBanners();
        }).fail(function () {
            showMessage($('#pageMessage'), text.removeError, 'danger');
        });
    });

    loadBanners();
});
</script>
</body>
</html>
