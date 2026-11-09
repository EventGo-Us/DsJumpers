<?php
ob_start();
session_start();
include_once 'valid_login.php';
include_once 'config/config.php';
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$isEnglish = ($_SESSION['Idioma'] ?? 'es') === 'en';
$fallbackTranslations = $isEnglish
    ? [
        'Blackout dates', 'Manage dates when your company will not operate.',
        'Add blackout period', 'Search by reason or notes', 'All statuses',
        'Active', 'Inactive', 'Date period', 'Reason', 'Status', 'Last updated',
        'Actions', 'No blackout dates found.', 'Loading...', 'Could not load blackout dates.',
        'New blackout period', 'Edit blackout period', 'Start date', 'End date',
        'Use the same date for a single-day closure. Both dates are inclusive.',
        'Reason', 'Internal notes', 'Add optional details for your team.',
        'This closure is active', 'Cancel', 'Save blackout period', 'Delete',
        'Delete this blackout period?', 'Blackout period saved.',
        'Blackout period deleted.', 'Could not save the blackout period.',
        'Could not delete the blackout period.', 'Page', 'of', 'Saving...',
        'Reason is required (up to 150 characters).',
        'Notes cannot exceed 1000 characters.', 'Edit', 'Previous', 'Next', 'Close',
        'End date must be the same as or later than the start date.'
    ]
    : [
        'Fechas no operativas', 'Administra los periodos en que la empresa no operará.',
        'Agregar periodo', 'Buscar por motivo o notas', 'Todos los estados',
        'Activo', 'Inactivo', 'Periodo', 'Motivo', 'Estado', 'Última actualización',
        'Acciones', 'No hay fechas no operativas.', 'Cargando...', 'No se pudieron cargar las fechas no operativas.',
        'Nuevo periodo no operativo', 'Editar periodo no operativo', 'Fecha inicial', 'Fecha final',
        'Usa la misma fecha para un solo día. Ambas fechas son inclusivas.',
        'Motivo', 'Notas internas', 'Agrega detalles opcionales para tu equipo.',
        'Este cierre está activo', 'Cancelar', 'Guardar periodo', 'Eliminar',
        '¿Eliminar este periodo no operativo?', 'Periodo no operativo guardado.',
        'Periodo no operativo eliminado.', 'No se pudo guardar el periodo.',
        'No se pudo eliminar el periodo.', 'Página', 'de', 'Guardando...',
        'El motivo es obligatorio (máximo 150 caracteres).',
        'Las notas no pueden exceder 1000 caracteres.', 'Editar', 'Anterior', 'Siguiente', 'Cerrar',
        'La fecha final debe ser igual o posterior a la fecha inicial.'
    ];

$Traducciones = [''];
foreach ($fallbackTranslations as $translation) {
    $Traducciones[] = $translation;
}
$translationQuery = $db->prepare(
    "SELECT Traduccion FROM programas_traduccion WHERE Programa = 'blackout_dates' AND Idioma = ? ORDER BY Id"
);
$translationQuery->execute([$isEnglish ? 'en' : 'es']);
foreach ($translationQuery->fetchAll(PDO::FETCH_COLUMN) as $index => $translation) {
    $Traducciones[$index + 1] = $translation;
}

function Trd($id)
{
    global $Traducciones;
    return $Traducciones[$id] ?? '';
}

include_once 'head.php';
?>
<style>
    body { background-color: #f4f7f6; }
    .blackout-table { background: #fff; border-radius: 12px; overflow: hidden; }
</style>
</head>
<body>
<?php include_once 'nav.php'; ?>

<main class="container py-5 mt-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars(Trd(1), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars(Trd(2), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <button type="button" class="btn btn-primary" id="newBlackoutButton">
            <i class="fa-solid fa-plus me-1"></i><?= htmlspecialchars(Trd(3), ENT_QUOTES, 'UTF-8') ?>
        </button>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-9">
                    <label class="form-label visually-hidden" for="blackoutSearch"><?= htmlspecialchars(Trd(4), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="search" class="form-control" id="blackoutSearch" placeholder="<?= htmlspecialchars(Trd(4), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label visually-hidden" for="statusFilter"><?= htmlspecialchars(Trd(5), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" id="statusFilter">
                        <option value=""><?= htmlspecialchars(Trd(5), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="1"><?= htmlspecialchars(Trd(6), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="0"><?= htmlspecialchars(Trd(7), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="pageMessage" class="alert d-none" role="alert"></div>
    <div class="blackout-table shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><?= htmlspecialchars(Trd(8), ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(Trd(9), ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(Trd(10), ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(Trd(11), ENT_QUOTES, 'UTF-8') ?></th>
                        <th class="text-end pe-3"><?= htmlspecialchars(Trd(12), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody id="blackoutTable">
                    <tr><td colspan="5" class="text-center py-4"><?= htmlspecialchars(Trd(14), ENT_QUOTES, 'UTF-8') ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted" id="pageSummary"></small>
        <div class="btn-group" role="group" aria-label="<?= htmlspecialchars(Trd(32), ENT_QUOTES, 'UTF-8') ?>">
            <button type="button" class="btn btn-outline-secondary" id="previousPage"><?= htmlspecialchars(Trd(39), ENT_QUOTES, 'UTF-8') ?></button>
            <button type="button" class="btn btn-outline-secondary" id="nextPage"><?= htmlspecialchars(Trd(40), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </div>
</main>

<div class="modal fade" id="blackoutModal" tabindex="-1" aria-labelledby="blackoutModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="blackoutForm">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="blackoutModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(Trd(41), ENT_QUOTES, 'UTF-8') ?>"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="blackoutId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label"><?= htmlspecialchars(Trd(18), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label"><?= htmlspecialchars(Trd(19), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                        <div class="col-12 form-text"><?= htmlspecialchars(Trd(20), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-12">
                            <label for="reason" class="form-label"><?= htmlspecialchars(Trd(21), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" class="form-control" id="reason" maxlength="150" required>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label"><?= htmlspecialchars(Trd(22), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea class="form-control" id="notes" rows="3" maxlength="1000"></textarea>
                            <div class="form-text"><?= htmlspecialchars(Trd(22), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" checked>
                                <label class="form-check-label" for="isActive"><?= htmlspecialchars(Trd(24), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                        </div>
                    </div>
                    <div id="formMessage" class="alert d-none mt-3 mb-0" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= htmlspecialchars(Trd(25), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="submit" class="btn btn-primary" id="saveBlackoutButton"><?= htmlspecialchars(Trd(26), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<?php include_once 'js_scripts.php'; ?>

$(function () {
    const text = <?= json_encode(array_combine(range(1, count($fallbackTranslations)), array_slice($Traducciones, 1)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const modal = new bootstrap.Modal(document.getElementById('blackoutModal'));
    const pageSize = 15;
    let currentPage = 1;
    let totalRecords = 0;
    let searchTimer;

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

    function formatDate(value) {
        if (!value) return '—';
        const parts = String(value).split('-');
        return parts.length === 3 ? new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2])).toLocaleDateString() : escapeHtml(value);
    }

    function renderBlackouts(records) {
        if (!records.length) {
            $('#blackoutTable').html('<tr><td colspan="5" class="text-center text-muted py-5">' + escapeHtml(text[13]) + '</td></tr>');
            return;
        }

        const rows = records.map(function (item) {
            const active = Number(item.activo) === 1;
            const period = item.fecha_inicio === item.fecha_fin
                ? formatDate(item.fecha_inicio)
                : formatDate(item.fecha_inicio) + ' – ' + formatDate(item.fecha_fin);
            return '<tr>' +
                '<td class="ps-3"><div class="fw-semibold">' + period + '</div>' +
                    (item.notas ? '<small class="text-muted d-block text-truncate" style="max-width:320px">' + escapeHtml(item.notas) + '</small>' : '') + '</td>' +
                '<td>' + escapeHtml(item.motivo) + '</td>' +
                '<td><span class="badge ' + (active ? 'text-bg-success' : 'text-bg-secondary') + '">' + escapeHtml(text[active ? 6 : 7]) + '</span></td>' +
                '<td>' + formatDate(String(item.fecha_actualizacion || '').slice(0, 10)) + '</td>' +
                '<td class="text-end pe-3 text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-blackout" data-id="' + Number(item.id) + '">' + escapeHtml(text[38]) + '</button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-danger delete-blackout" data-id="' + Number(item.id) + '">' + escapeHtml(text[26]) + '</button>' +
                '</td></tr>';
        }).join('');
        $('#blackoutTable').html(rows);
    }

    function loadBlackouts() {
        $('#blackoutTable').html('<tr><td colspan="5" class="text-center py-4">' + escapeHtml(text[14]) + '</td></tr>');
        $.ajax({
            url: API_BASE_URL + 'blackout_dates',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                page: currentPage,
                search: $('#blackoutSearch').val(),
                active: $('#statusFilter').val()
            }
        }).done(function (response) {
            totalRecords = Number(response.total || 0);
            renderBlackouts(response.data || []);
            const pageCount = Math.max(1, Math.ceil(totalRecords / pageSize));
            $('#pageSummary').text(text[33] + ' ' + currentPage + ' ' + text[34] + ' ' + pageCount);
            $('#previousPage').prop('disabled', currentPage <= 1);
            $('#nextPage').prop('disabled', currentPage >= pageCount);
        }).fail(function () {
            $('#blackoutTable').html('<tr><td colspan="5" class="text-center text-danger py-4">' + escapeHtml(text[15]) + '</td></tr>');
        });
    }

    function resetForm() {
        document.getElementById('blackoutForm').reset();
        $('#blackoutId').val('');
        $('#isActive').prop('checked', true);
        hideMessage($('#formMessage'));
    }

    $('#newBlackoutButton').on('click', function () {
        resetForm();
        $('#blackoutModalTitle').text(text[16]);
        modal.show();
    });

    $('#blackoutTable').on('click', '.edit-blackout', function () {
        const blackoutId = Number($(this).data('id'));
        resetForm();
        $.ajax({
            url: API_BASE_URL + 'blackout_dates/' + blackoutId,
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function (response) {
            const item = response.data;
            $('#blackoutId').val(item.id);
            $('#startDate').val(item.fecha_inicio);
            $('#endDate').val(item.fecha_fin);
            $('#reason').val(item.motivo);
            $('#notes').val(item.notas || '');
            $('#isActive').prop('checked', Number(item.activo) === 1);
            $('#blackoutModalTitle').text(text[17]);
            modal.show();
        }).fail(function () {
            showMessage($('#pageMessage'), text[15], 'danger');
        });
    });

    $('#blackoutForm').on('submit', function (event) {
        event.preventDefault();
        hideMessage($('#formMessage'));
        const blackoutId = $('#blackoutId').val();
        const payload = {
            fecha_inicio: $('#startDate').val(),
            fecha_fin: $('#endDate').val(),
            motivo: $('#reason').val().trim(),
            notas: $('#notes').val().trim(),
            activo: $('#isActive').is(':checked') ? 1 : 0
        };
        if (payload.fecha_fin < payload.fecha_inicio) {
            showMessage($('#formMessage'), text[42], 'danger');
            return;
        }
        if (!payload.motivo || payload.motivo.length > 150) {
            showMessage($('#formMessage'), text[36], 'danger');
            return;
        }
        if (payload.notas.length > 1000) {
            showMessage($('#formMessage'), text[37], 'danger');
            return;
        }
        $('#saveBlackoutButton').prop('disabled', true).text(text[35]);
        $.ajax({
            url: API_BASE_URL + 'blackout_dates' + (blackoutId ? '/' + blackoutId : ''),
            type: blackoutId ? 'PUT' : 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify(payload)
        }).done(function () {
            modal.hide();
            showMessage($('#pageMessage'), text[29], 'success');
            currentPage = 1;
            loadBlackouts();
        }).fail(function () {
            showMessage($('#formMessage'), text[31], 'danger');
        }).always(function () {
            $('#saveBlackoutButton').prop('disabled', false).text(text[26]);
        });
    });

    $('#blackoutTable').on('click', '.delete-blackout', function () {
        if (!window.confirm(text[27])) return;
        $.ajax({
            url: API_BASE_URL + 'blackout_dates/' + Number($(this).data('id')),
            type: 'DELETE',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).done(function () {
            showMessage($('#pageMessage'), text[30], 'success');
            loadBlackouts();
        }).fail(function () {
            showMessage($('#pageMessage'), text[32], 'danger');
        });
    });

    $('#blackoutSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentPage = 1;
            loadBlackouts();
        }, 350);
    });
    $('#statusFilter').on('change', function () {
        currentPage = 1;
        loadBlackouts();
    });
    $('#previousPage').on('click', function () {
        if (currentPage > 1) { currentPage--; loadBlackouts(); }
    });
    $('#nextPage').on('click', function () {
        if (currentPage * pageSize < totalRecords) { currentPage++; loadBlackouts(); }
    });

    loadBlackouts();
});
</script>
</body>
</html>
