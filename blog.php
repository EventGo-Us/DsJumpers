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
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
<style>
    body { background-color: #f4f7f6; }
    .blog-table { background: #fff; border-radius: 12px; overflow: hidden; }
    .featured-preview { max-width: 180px; max-height: 120px; object-fit: cover; }
</style>
</head>
<body>
<?php include_once 'nav.php'; ?>

<main class="container py-5 mt-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><?= $isEnglish ? 'Blog posts' : 'Publicaciones del blog' ?></h1>
            <p class="text-muted mb-0"><?= $isEnglish ? 'Create and manage your blog content.' : 'Crea y administra el contenido del blog.' ?></p>
        </div>
        <button type="button" class="btn btn-primary" id="newPostButton">
            <i class="fa-solid fa-plus me-1"></i><?= $isEnglish ? 'New post' : 'Nueva publicación' ?>
        </button>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label visually-hidden" for="postSearch"><?= $isEnglish ? 'Search posts' : 'Buscar publicaciones' ?></label>
            <input type="search" class="form-control" id="postSearch" placeholder="<?= $isEnglish ? 'Search by title or slug' : 'Buscar por título o slug' ?>">
        </div>
    </div>

    <div id="pageMessage" class="alert d-none" role="alert"></div>
    <div class="blog-table shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><?= $isEnglish ? 'Post' : 'Publicación' ?></th>
                        <th><?= $isEnglish ? 'Status' : 'Estado' ?></th>
                        <th><?= $isEnglish ? 'Publication date' : 'Fecha de publicación' ?></th>
                        <th><?= $isEnglish ? 'Last updated' : 'Última actualización' ?></th>
                        <th class="text-end pe-3"><?= $isEnglish ? 'Actions' : 'Acciones' ?></th>
                    </tr>
                </thead>
                <tbody id="postsTable">
                    <tr><td colspan="5" class="text-center py-4"><?= $isEnglish ? 'Loading...' : 'Cargando...' ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted" id="pageSummary"></small>
        <div class="btn-group" role="group" aria-label="<?= $isEnglish ? 'Pagination' : 'Paginación' ?>">
            <button type="button" class="btn btn-outline-secondary" id="previousPage"><?= $isEnglish ? 'Previous' : 'Anterior' ?></button>
            <button type="button" class="btn btn-outline-secondary" id="nextPage"><?= $isEnglish ? 'Next' : 'Siguiente' ?></button>
        </div>
    </div>
</main>

<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="postForm">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="postModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $isEnglish ? 'Close' : 'Cerrar' ?>"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="postId">
                    <input type="hidden" id="featuredImage">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label"><?= $isEnglish ? 'Title' : 'Título' ?></label>
                            <input type="text" class="form-control" id="title" maxlength="255" required>
                        </div>
                        <div class="col-md-4">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <label for="summary" class="form-label"><?= $isEnglish ? 'Summary' : 'Resumen' ?></label>
                            <textarea class="form-control" id="summary" maxlength="500" rows="2"></textarea>
                            <div class="form-text"><?= $isEnglish ? 'Up to 500 characters.' : 'Hasta 500 caracteres.' ?></div>
                        </div>
                        <div class="col-12">
                            <label for="htmlContent" class="form-label"><?= $isEnglish ? 'Content' : 'Contenido' ?></label>
                            <textarea id="htmlContent"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="featuredImageFile" class="form-label"><?= $isEnglish ? 'Featured image' : 'Imagen destacada' ?></label>
                            <input type="file" class="form-control" id="featuredImageFile" accept="image/jpeg,image/png,image/webp,image/avif">
                            <div class="form-text"><?= $isEnglish ? 'The image is uploaded to the blog gallery.' : 'La imagen se carga en la galería blog.' ?></div>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img id="featuredImagePreview" class="featured-preview rounded d-none" alt="<?= $isEnglish ? 'Featured image preview' : 'Vista previa de imagen destacada' ?>">
                                <button type="button" id="removeFeaturedImage" class="btn btn-sm btn-outline-danger d-none"><?= $isEnglish ? 'Remove image' : 'Quitar imagen' ?></button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label"><?= $isEnglish ? 'Status' : 'Estado' ?></label>
                            <select class="form-select" id="status">
                                <option value="borrador"><?= $isEnglish ? 'Draft' : 'Borrador' ?></option>
                                <option value="publicado"><?= $isEnglish ? 'Published' : 'Publicado' ?></option>
                                <option value="archivado"><?= $isEnglish ? 'Archived' : 'Archivado' ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="publicationDate" class="form-label"><?= $isEnglish ? 'Publication date' : 'Fecha de publicación' ?></label>
                            <input type="datetime-local" class="form-control" id="publicationDate">
                        </div>
                    </div>
                    <div id="formMessage" class="alert d-none mt-3 mb-0" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= $isEnglish ? 'Cancel' : 'Cancelar' ?></button>
                    <button type="submit" class="btn btn-primary" id="savePostButton"><?= $isEnglish ? 'Save post' : 'Guardar publicación' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<?php include_once 'js_scripts.php'; ?>

$(function () {
    const english = <?= $isEnglish ? 'true' : 'false' ?>;
    const text = english ? {
        loading: 'Loading...', noPosts: 'No posts found.', loadError: 'Could not load blog posts.',
        newPost: 'New post', editPost: 'Edit post', draft: 'Draft', published: 'Published',
        archived: 'Archived', edit: 'Edit', archive: 'Archive', confirmArchive: 'Archive this post?',
        saveError: 'Could not save the post.', uploadError: 'Could not upload the image.',
        uploadSuccess: 'Image uploaded successfully.', saved: 'Post saved successfully.',
        archivedSuccess: 'Post archived successfully.', archiveError: 'Could not archive the post.',
        page: 'Page', of: 'of', uploading: 'Uploading image...', saving: 'Saving...'
    } : {
        loading: 'Cargando...', noPosts: 'No se encontraron publicaciones.', loadError: 'No se pudieron cargar las publicaciones.',
        newPost: 'Nueva publicación', editPost: 'Editar publicación', draft: 'Borrador', published: 'Publicado',
        archived: 'Archivado', edit: 'Editar', archive: 'Archivar', confirmArchive: '¿Archivar esta publicación?',
        saveError: 'No se pudo guardar la publicación.', uploadError: 'No se pudo cargar la imagen.',
        uploadSuccess: 'Imagen cargada correctamente.', saved: 'Publicación guardada correctamente.',
        archivedSuccess: 'Publicación archivada correctamente.', archiveError: 'No se pudo archivar la publicación.',
        page: 'Página', of: 'de', uploading: 'Cargando imagen...', saving: 'Guardando...'
    };
    const modal = new bootstrap.Modal(document.getElementById('postModal'));
    const pageSize = 15;
    let currentPage = 1;
    let totalPosts = 0;
    let searchTimer;
    let slugEdited = false;
    let imageUploading = false;

    $('#htmlContent').summernote({
        height: 320,
        styleWithSpan: false,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video', 'table']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

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

    function makeSlug(value) {
        return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    function imageUrl(file, thumbnail) {
        if (!file) return '';
        const imageName = thumbnail ? file.replace(/\.avif$/i, '.jpg') : file;
        return '<?= CFPUBLICURL ?>/' + encodeURIComponent('<?= $_SESSION['id_cliente'] ?? '' ?>') +
            '/blog/' + (thumbnail ? 'thumbnails/' : 'originals/') + encodeURIComponent(imageName);
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleString();
    }

    function renderPosts(posts) {
        if (!posts.length) {
            $('#postsTable').html('<tr><td colspan="5" class="text-center text-muted py-5">' + text.noPosts + '</td></tr>');
            return;
        }

        const rows = posts.map(post => {
            const state = String(post.estado || '');
            const stateLabel = state === 'publicado' ? text.published : state === 'archivado' ? text.archived : text.draft;
            const badge = state === 'publicado' ? 'text-bg-success' : state === 'archivado' ? 'text-bg-secondary' : 'text-bg-warning';
            const thumb = post.imagen_destacada
                ? '<img src="' + escapeHtml(imageUrl(post.imagen_destacada, true)) + '" alt="" class="rounded me-2" width="52" height="42" style="object-fit:cover">'
                : '';
            return '<tr>' +
                '<td class="ps-3"><div class="d-flex align-items-center">' + thumb +
                    '<div><div class="fw-semibold">' + escapeHtml(post.titulo) + '</div><small class="text-muted">' + escapeHtml(post.slug) + '</small></div></div></td>' +
                '<td><span class="badge ' + badge + '">' + stateLabel + '</span></td>' +
                '<td>' + formatDate(post.fecha_publicacion) + '</td>' +
                '<td>' + formatDate(post.fecha_actualizacion) + '</td>' +
                '<td class="text-end pe-3 text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-post" data-id="' + Number(post.id) + '">' + text.edit + '</button> ' +
                    (state !== 'archivado' ? '<button type="button" class="btn btn-sm btn-outline-danger archive-post" data-id="' + Number(post.id) + '">' + text.archive + '</button>' : '') +
                '</td></tr>';
        }).join('');
        $('#postsTable').html(rows);
    }

    function loadPosts() {
        $('#postsTable').html('<tr><td colspan="5" class="text-center py-4">' + text.loading + '</td></tr>');
        $.ajax({
            url: API_BASE_URL + 'blog_posts',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: { page: currentPage, search: $('#postSearch').val() }
        }).done(function (response) {
            totalPosts = Number(response.total || 0);
            renderPosts(response.data || []);
            const pageCount = Math.max(1, Math.ceil(totalPosts / pageSize));
            $('#pageSummary').text(text.page + ' ' + currentPage + ' ' + text.of + ' ' + pageCount);
            $('#previousPage').prop('disabled', currentPage <= 1);
            $('#nextPage').prop('disabled', currentPage >= pageCount);
        }).fail(function () {
            $('#postsTable').html('<tr><td colspan="5" class="text-center text-danger py-4">' + text.loadError + '</td></tr>');
        });
    }

    function resetForm() {
        document.getElementById('postForm').reset();
        $('#postId, #featuredImage').val('');
        $('#featuredImageFile').val('');
        $('#htmlContent').summernote('code', '');
        $('#featuredImagePreview').addClass('d-none').attr('src', '');
        $('#removeFeaturedImage').addClass('d-none');
        $('#slug').prop('readonly', false);
        slugEdited = false;
        hideMessage($('#formMessage'));
    }

    function updateImagePreview(file) {
        if (file) {
            $('#featuredImagePreview').attr('src', imageUrl(file, true)).removeClass('d-none');
            $('#removeFeaturedImage').removeClass('d-none');
        } else {
            $('#featuredImagePreview').addClass('d-none').attr('src', '');
            $('#removeFeaturedImage').addClass('d-none');
        }
    }

    $('#newPostButton').on('click', function () {
        resetForm();
        $('#postModalTitle').text(text.newPost);
        modal.show();
    });

    $('#title').on('input', function () {
        if (!slugEdited) $('#slug').val(makeSlug($(this).val()));
    });
    $('#slug').on('input', function () { slugEdited = true; });

    $('#postsTable').on('click', '.edit-post', function () {
        const postId = $(this).data('id');
        resetForm();
        $.ajax({
            url: API_BASE_URL + 'blog_posts/' + postId,
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function (response) {
            const post = response.data;
            $('#postId').val(post.id);
            $('#title').val(post.titulo);
            $('#slug').val(post.slug);
            $('#summary').val(post.resumen || '');
            $('#htmlContent').summernote('code', post.html_contenido || '');
            $('#featuredImage').val(post.imagen_destacada || '');
            $('#status').val(post.estado);
            $('#publicationDate').val(post.fecha_publicacion ? String(post.fecha_publicacion).replace(' ', 'T').slice(0, 16) : '');
            slugEdited = true;
            updateImagePreview(post.imagen_destacada);
            $('#postModalTitle').text(text.editPost);
            modal.show();
        }).fail(function () {
            showMessage($('#pageMessage'), text.loadError, 'danger');
        });
    });

    $('#featuredImageFile').on('change', function () {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('table', 'blog');
        formData.append('upload_file', file);
        imageUploading = true;
        $('#featuredImageFile').prop('disabled', true);
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
            $('#featuredImage').val(response.files[0].image);
            updateImagePreview(response.files[0].image);
            showMessage($('#formMessage'), text.uploadSuccess, 'success');
        }).fail(function () {
            showMessage($('#formMessage'), text.uploadError, 'danger');
        }).always(function () {
            imageUploading = false;
            $('#featuredImageFile').prop('disabled', false).val('');
        });
    });

    $('#removeFeaturedImage').on('click', function () {
        $('#featuredImage').val('');
        updateImagePreview('');
    });

    $('#postForm').on('submit', function (event) {
        event.preventDefault();
        if (imageUploading) {
            showMessage($('#formMessage'), text.uploading, 'warning');
            return;
        }
        hideMessage($('#formMessage'));
        const postId = $('#postId').val();
        const payload = {
            titulo: $('#title').val().trim(),
            slug: $('#slug').val().trim(),
            resumen: $('#summary').val().trim(),
            html_contenido: $('#htmlContent').summernote('code'),
            imagen_destacada: $('#featuredImage').val(),
            estado: $('#status').val(),
            fecha_publicacion: $('#publicationDate').val()
        };
        $('#savePostButton').prop('disabled', true).text(text.saving);
        $.ajax({
            url: API_BASE_URL + 'blog_posts' + (postId ? '/' + postId : ''),
            type: postId ? 'PUT' : 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify(payload)
        }).done(function () {
            modal.hide();
            showMessage($('#pageMessage'), text.saved, 'success');
            currentPage = 1;
            loadPosts();
        }).fail(function (xhr) {
            const response = xhr.responseJSON;
            showMessage($('#formMessage'), response && response.message ? response.message : text.saveError, 'danger');
        }).always(function () {
            $('#savePostButton').prop('disabled', false).text(english ? 'Save post' : 'Guardar publicación');
        });
    });

    $('#postsTable').on('click', '.archive-post', function () {
        if (!window.confirm(text.confirmArchive)) return;
        $.ajax({
            url: API_BASE_URL + 'blog_posts/' + $(this).data('id'),
            type: 'DELETE',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function () {
            showMessage($('#pageMessage'), text.archivedSuccess, 'success');
            loadPosts();
        }).fail(function () {
            showMessage($('#pageMessage'), text.archiveError, 'danger');
        });
    });

    $('#postSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentPage = 1;
            loadPosts();
        }, 350);
    });
    $('#previousPage').on('click', function () {
        if (currentPage > 1) { currentPage--; loadPosts(); }
    });
    $('#nextPage').on('click', function () {
        if (currentPage * pageSize < totalPosts) { currentPage++; loadPosts(); }
    });

    loadPosts();
});
</script>
</body>
</html>