<?php
// admin_comments.php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD y sesiones
include_once 'valid_login.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
$customersQuery = $db->query("SELECT id, firstname, lastname, email FROM sale_customers ORDER BY firstname, lastname");
$saleCustomers = $customersQuery->fetchAll(PDO::FETCH_ASSOC);

$Idioma = $_SESSION['Idioma'];
// Cambiamos el programa a 'comments' en las traducciones para que cargue tus textos personalizados
$query = "select Traduccion FROM  programas_traduccion where Programa = 'comments' AND Idioma = ? ORDER BY Id";            
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$Traducciones[]='';
if ($resultados) {
    foreach ($resultados as $registro) {
        $Traducciones[]=$registro['Traduccion'];
    }
}    
function Trd($Id){
    global $Traducciones;
    // Si aún no configuras traducciones, puedes descomentar la línea de abajo como fallback temporal:
    // return "Texto ".$Id; 
    return $Traducciones[$Id];
}

include_once 'head.php';
?>
<style>
        body { background-color: #f4f7f6; }
        .sticky-search { position: sticky; top: 0; z-index: 1020; background: rgba(244, 247, 246, 0.95); padding: 20px 0; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; }
        
        /* Colores laterales adaptados a los estatus de comentarios */
        .status-approved { background: #28a745 !important; }
        .status-pending { background: #ffc107 !important; }
        .status-rejected { background: #dc3545 !important; }

        .clickable-row:hover {
            background-color: rgba(13, 110, 253, 0.05) !important;
            transition: background-color 0.2s ease;
        }        
        .stars-gold { color: #f59e0b; }
</style>
</head>
<body>

<?php
    include_once 'nav.php';
?>
<br>
<br>
<div class="container pb-5">
    <div class="sticky-search">
        <div class="row justify-content-center">
            <div class="col-md-8 d-flex gap-2">
                <div class="card shadow-sm flex-grow-1">
                    <div class="card-body p-2">
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-primary"></i></span>
                            <!-- Trd(1): Placeholder de búsqueda, ej: "Buscar comentarios..." -->
                            <input type="text" id="txtSearch" class="form-control border-0 shadow-none" placeholder="<?php echo Trd(1)?>">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary text-nowrap" id="btnAddComment">
                    <i class="bi bi-plus-lg"></i> <?php echo $Idioma === 'en' ? 'Add comment' : 'Agregar comentario'; ?>
                </button>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <!-- Ajuste de Encabezados con traducciones -->
                        <th class="ps-4"><?php echo Trd(2) /* "ID / Fecha" */?></th>
                        <th><?php echo Trd(3) /* "Cliente" */?></th>
                        <th><?php echo Trd(4) /* "Opinión" */?></th>
                        <th><?php echo Trd(5) /* "Calificación" */?></th>
                        <th class="text-center"><?php echo Trd(6) /* "Destacado" */?></th>
                        <th class="text-end pe-4"><?php echo Trd(7) /* "Estatus" */?></th>
                    </tr>
                </thead>
                <tbody id="commentsData">
                </tbody>
            </table>
        </div>
    </div>

    <div id="loadingIndicator" class="text-center my-4" style="display:none;">
        <div class="spinner-grow text-primary" role="status"></div>
        <p class="text-muted small"><?php echo Trd(8) /* "Cargando..." */?></p>
    </div>
</div>

<!-- Modal para agregar un comentario vinculado a un cliente de ventas -->
<div class="modal fade" id="addCommentModal" tabindex="-1" aria-labelledby="addCommentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCommentModalLabel"><?php echo $Idioma === 'en' ? 'Add comment' : 'Agregar comentario'; ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="newCustomerId" class="form-label"><?php echo $Idioma === 'en' ? 'Customer' : 'Cliente'; ?></label>
          <select id="newCustomerId" class="form-select" required>
            <option value=""><?php echo $Idioma === 'en' ? 'Select a customer' : 'Selecciona un cliente'; ?></option>
            <?php foreach ($saleCustomers as $customer): ?>
              <option value="<?php echo (int) $customer['id']; ?>">
                <?php echo htmlspecialchars(trim($customer['firstname'] . ' ' . $customer['lastname']) . ($customer['email'] ? ' - ' . $customer['email'] : ''), ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="newReviewText" class="form-label"><?php echo $Idioma === 'en' ? 'Comment' : 'Comentario'; ?></label>
          <textarea id="newReviewText" class="form-control" rows="4" maxlength="2000" required></textarea>
        </div>
        <div class="mb-3">
          <label for="newReviewRating" class="form-label"><?php echo $Idioma === 'en' ? 'Rating' : 'Calificación'; ?></label>
          <select id="newReviewRating" class="form-select">
            <option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $Idioma === 'en' ? 'Cancel' : 'Cancelar'; ?></button>
        <button type="button" class="btn btn-primary" id="btnCreateComment" <?php echo empty($saleCustomers) ? 'disabled' : ''; ?>>
          <?php echo $Idioma === 'en' ? 'Add comment' : 'Agregar comentario'; ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para editar un comentario -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="commentModalLabel"><?php echo Trd(9) /* "Moderar Comentario" */?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <input type="hidden" id="modalReviewId">
         <div class="mb-3">
             <label for="modalAuthorName" class="form-label text-muted small"><?php echo Trd(3) /* "Cliente" */?></label>
             <input id="modalAuthorName" class="form-control fw-bold" maxlength="255">
             <label for="modalAuthorMeta" class="form-label text-muted small mt-2"><?php echo $Idioma === 'en' ? 'Customer details' : 'Datos del cliente'; ?></label>
             <input id="modalAuthorMeta" class="form-control" maxlength="255">
         </div>
         <div class="mb-3">
             <label for="modalReviewText" class="form-label text-muted small"><?php echo Trd(4) /* "Opinión" */?></label>
             <textarea id="modalReviewText" class="form-control" rows="4" maxlength="2000"></textarea>
         </div>
         <div class="mb-3">
             <label for="modalRatingSelect" class="form-label fw-semibold small"><?php echo Trd(5) /* "Calificación" */?></label>
             <select id="modalRatingSelect" class="form-select">
                 <option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option>
             </select>
         </div>
         
         <hr>
         
         <!-- Campos modificables -->
         <div class="mb-3">
             <label for="modalStatusSelect" class="form-label fw-semibold small"><?php echo Trd(7) /* "Estatus" */?></label>
             <select id="modalStatusSelect" class="form-select">
                 <option value="pending">Pending</option>
                 <option value="approved">Approved</option>
                 <option value="rejected">Rejected</option>
             </select>
         </div>
         <div class="mb-3 form-check form-switch">
             <input class="form-check-input" type="checkbox" role="switch" id="modalFeaturedSwitch">
             <label class="form-check-input-label fw-semibold small" for="modalFeaturedSwitch"><?php echo Trd(6) /* "Destacar en diseño Bento (Grande)" */?></label>
         </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Trd(10) /* "Cancelar" */?></button>
        <button type="button" class="btn btn-primary" id="btnSaveChanges"><?php echo Trd(11) /* "Guardar Cambios" */?></button>
      </div>
    </div>
  </div>
</div>

<script>
    <?php
    include_once 'js_scripts.php';
    ?>

$(document).ready(function() {
    const requestFailedMessage = <?php echo json_encode($Idioma === 'en' ? 'The request could not be completed. Please try again.' : 'No se pudo completar la solicitud. Inténtalo de nuevo.'); ?>;
    let currentPage = 1;
    let isFetching = false;
    let noMoreData = false;
    let timer;
    let loadedComments = {}; // Almacén en memoria de los comentarios cargados para el modal

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function(character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[character];
        });
    }

    // Función Principal con AJAX (Consume tu API apuntando al nuevo endpoint de comentarios)
    function fetchComments(reset = false) {
        if (isFetching || (noMoreData && !reset)) return;

        if (reset) {
            currentPage = 1;
            noMoreData = false;
            $('#commentsData').empty();
            loadedComments = {};
        }

        isFetching = true;
        $('#loadingIndicator').fadeIn();

        $.ajax({
            url:  API_BASE_URL + 'comments_admin/', // Tu endpoint administrativo
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                page: currentPage,
                search: $('#txtSearch').val()
            },
            success: function(response) {
                if (!response || response.length === 0) {
                    noMoreData = true;
                    if (currentPage === 1) {
                        $('#commentsData').html('<tr><td colspan="6" class="text-center py-5 text-muted"><?php echo Trd(12) /* "No se encontraron comentarios" */?></td></tr>');
                    }
                } else {
                    renderTable(response);
                    currentPage++;
                }
            },
            error: function(xhr, status, error) {
                alert(requestFailedMessage);
            },
            complete: function() {
                isFetching = false;
                $('#loadingIndicator').fadeOut();
            }
        });
    }

    // Renderizar filas
    function renderTable(data) {
        let rows = '';
        $.each(data, function(i, item) {
            // Guardamos en el objeto temporal para el acceso rápido del modal
            loadedComments[item.id] = item;

            const status = String(item.status || '').toLowerCase();
            const rating = Math.max(0, Math.min(5, parseInt(item.rating, 10) || 0));
            const statusClass = "status-" + escapeHtml(status);
            const badgeClass = getBadgeColor(status);
            const stars = '<span class="stars-gold">' + '★'.repeat(rating) + '</span>' + '☆'.repeat(5 - rating);
            
            // Recortamos el texto para que no rompa la tabla si es muy largo
            const reviewText = String(item.review_text || '');
            const shortText = reviewText.length > 60 ? reviewText.substring(0, 60) + '...' : reviewText;
            const featuredBadge = item.is_featured == 1 ? '<span class="badge bg-primary">Bento Box</span>' : '<span class="text-muted small">-</span>';

            rows += `
                <tr class="${statusClass} clickable-row" data-id="${escapeHtml(item.id)}" style="cursor: pointer;">
                    <td class="ps-4">
                        <div class="fw-semibold">#${escapeHtml(item.id)}</div>
                        <div class="small text-muted">${escapeHtml(item.created_at || '')}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${escapeHtml(item.author_name || '')}</div>
                        <div class="small text-muted italic">${escapeHtml(item.author_meta || '')}</div>
                    </td>
                    <td>
                        <div class="small text-secondary italic">"${escapeHtml(shortText)}"</div>
                    </td>
                    <td><div class="small">${stars}</div></td>
                    <td class="text-center">${featuredBadge}</td>
                    <td class="text-end pe-4"><span class="badge rounded-pill ${badgeClass}">${escapeHtml(status.toUpperCase())}</span></td>
                </tr>
            `;
        });
        $('#commentsData').append(rows);
    }

    function getBadgeColor(status) {
        const s = status.toLowerCase();
        if (s.includes('approved')) return 'bg-success';
        if (s.includes('pending')) return 'bg-warning text-dark';
        if (s.includes('rejected')) return 'bg-danger';
        return 'bg-secondary';
    }

    // Evento Scroll (Infinite Scroll)
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
            fetchComments();
        }
    });

    // Evento de Búsqueda (Debounce)
    $('#txtSearch').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetchComments(true);
        }, 500);
    });

    // CLIC EN LA FILA: Abre el Modal con los datos correspondientes
    $('#commentsData').on('click', '.clickable-row', function() {
        const reviewId = $(this).data('id');
        const item = loadedComments[reviewId];
        
        if (item) {
            $('#modalReviewId').val(item.id);
            $('#modalAuthorName').val(item.author_name || '');
            $('#modalAuthorMeta').val(item.author_meta || '');
            $('#modalReviewText').val(item.review_text || '');
            $('#modalRatingSelect').val(item.rating);
            $('#modalStatusSelect').val(item.status);
            $('#modalFeaturedSwitch').prop('checked', item.is_featured == 1);
            
            $('#commentModal').modal('show');
        }
    });    

    // ACCIÓN: Guardar cambios desde el Modal (Hace un PUT / POST a tu API)
    $('#btnSaveChanges').on('click', function() {
        const id = $('#modalReviewId').val();
        const status = $('#modalStatusSelect').val();
        const is_featured = $('#modalFeaturedSwitch').is(':checked') ? 1 : 0;
        const author_name = $('#modalAuthorName').val().trim();
        const author_meta = $('#modalAuthorMeta').val().trim();
        const review_text = $('#modalReviewText').val().trim();
        const rating = parseInt($('#modalRatingSelect').val(), 10);

        if (!author_name || !review_text || review_text.length > 2000) {
            alert(<?php echo json_encode($Idioma === 'en' ? 'Customer name and a comment of up to 2,000 characters are required.' : 'El nombre del cliente y un comentario de hasta 2,000 caracteres son obligatorios.'); ?>);
            return;
        }

        $.ajax({
            url: API_BASE_URL + 'comments_admin_update', // Endpoint de guardado
            type: 'POST', // O 'PUT' dependiendo de tus estándares de API
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify({
                id: id,
                author_name: author_name,
                author_meta: author_meta,
                review_text: review_text,
                rating: rating,
                status: status,
                is_featured: is_featured
            }),
            success: function(response) {
                // Si la actualización es exitosa, cerramos modal y refrescamos la tabla completa
                $('#commentModal').modal('hide');
                fetchComments(true); 
            },
            error: function(xhr) {
                alert(requestFailedMessage);
            }
        });
    });

    $('#btnAddComment').on('click', function() {
        $('#newCustomerId').val('');
        $('#newReviewText').val('');
        $('#newReviewRating').val('5');
        $('#addCommentModal').modal('show');
    });

    $('#btnCreateComment').on('click', function() {
        const customer_id = parseInt($('#newCustomerId').val(), 10);
        const review_text = $('#newReviewText').val().trim();
        const rating = parseInt($('#newReviewRating').val(), 10);
        if (!customer_id || !review_text || review_text.length > 2000) {
            alert(<?php echo json_encode($Idioma === 'en' ? 'Select a customer and enter a comment of up to 2,000 characters.' : 'Selecciona un cliente y escribe un comentario de hasta 2,000 caracteres.'); ?>);
            return;
        }

        const button = $(this).prop('disabled', true);
        $.ajax({
            url: API_BASE_URL + 'comments_admin',
            type: 'POST',
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify({customer_id: customer_id, review_text: review_text, rating: rating}),
            success: function() {
                $('#addCommentModal').modal('hide');
                fetchComments(true);
            },
            error: function() {
                alert(requestFailedMessage);
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });

    // Carga inicial
    fetchComments();
});    
     
</script>
</body>
</html>