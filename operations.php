<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'valid_login.php';
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    define('ID_CLIENTE' , $_SESSION['id_cliente']);
    
    $Idioma = $_SESSION['Idioma'];
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'operations' AND Idioma = ? ORDER BY Id";            
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
        return $Traducciones[$Id];
    }    

    include_once 'head.php';    
?>


<style>

        .product-img { object-fit: cover; border-radius: 8px; background: #fff; }
        .table-success-light { background-color: rgba(25, 135, 84, 0.08) !important; transition: 0.3s; }
        .qty-input { width: 60px !important; font-weight: bold; }
        .row-check { transform: scale(1.4); cursor: not-allowed; }
        .badge-base { background-color: #0dcaf0; color: #000; font-size: 0.65rem; }
        .badge-acc { background-color: #ffc107; color: #000; font-size: 0.65rem; }
        .main-row { border-left: 5px solid var(--ds-primary); }

        tr.estado-rojo td {
            background-color: rgba(220, 53, 69, 0.2); /* 0.7 = 70% opaco, 30% transparente */
            color: black;
        }

        tr.estado-normal td {
            background-color: #ffffff;
            color: black;
        }        

    </style>    

<style>
    /* ... tus estilos anteriores ... */

    /* Ajuste para controles de cantidad en móviles */
    @media (max-width: 576px) {
        .qty-container {
            flex-direction: column; /* Apila el título si fuera necesario */
            align-items: center;
        }
        
        .input-group-mobile {
            width: 100% !important;
            max-width: 140px; /* Evita que sea gigante pero da buen espacio táctil */
            margin: 0 auto;
        }

        .qty-input {
            width: 50px !important;
            font-size: 1.1rem; /* Más fácil de leer en móvil */
        }

        /* Hacer los botones más grandes para dedos (Touch targets) */
        .btn-minus, .btn-plus {
            padding: 0.5rem 0.75rem;
        }
        
        /* Ocultar etiquetas pequeñas en móvil para ganar espacio */
        .ps-5 {
            padding-left: 1rem !important;
        }
    }
</style>

<style>
    /* Estilo para cuando el input está al máximo */
    .assorted-qty:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
    
    /* Animación de "error" si intenta excederse */
    .is-invalid {
        border-color: #dc3545 !important;
        animation: shake 0.2s ease-in-out 0s 2;
    }

    @keyframes shake {
        0% { margin-left: 0rem; }
        25% { margin-left: 0.2rem; }
        75% { margin-left: -0.2rem; }
        100% { margin-left: 0rem; }
    }

    /* Botón deshabilitado visualmente */
    .btn-plus:disabled {
        background-color: #f8f9fa;
        color: #dee2e6;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
</style>
<style>
    .x-small { font-size: 0.65rem; padding: 2px 6px; }
    .table-success-row { background-color: rgba(25, 135, 84, 0.1) !important; transition: 0.3s; }
    
    @media (max-width: 576px) {
        .custom-qty-group { width: 100% !important; max-width: 130px; margin: 0 auto; }
        .btn-minus, .btn-plus { padding: 8px 12px; }
        .assorted-qty { font-size: 1rem; width: 45px !important; }
    }

    /* Animación de error si intenta exceder */
    .qty-limit-error { border-color: #dc3545 !important; animation: shake 0.2s 2; }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(5px); }
    }
</style>
</head>
<body>
<?php
    include_once 'nav.php';
$id_op = $_GET['IdOperation'];
//EN ENTREGA METER LA FIRMA Y LA LIGA PARA EL PROCESO DE LIQUIDACION 
$stages = ['BODEGA','SURTIDO', 'CARGA', 'INSTALACION', 'PRUEBA FUNCIONAMIENTO', 'ENTREGA', 'RECOLECCION', 'ACONDICIONAMIENTO', 'LIMPIEZA', 'LAVADO','REPARACION',  'ALMACENADO','FINALIZADO'];

$stage = $db->query("SELECT id_lead, status FROM operation_master WHERE id_operation = $id_op")->fetch();
$id_lead = $stage['id_lead'];
$currentStage = $stage['status'];

$lead = $db->query("SELECT Balance FROM lead WHERE Id = $id_lead")->fetch();
$Balance = $lead['Balance'];

$LoadProc = $db->query("SELECT LoadProc FROM account")->fetch();
$LoadProc = $LoadProc['LoadProc'];

if ($currentStage == 'ACONDICIONAMIENTO')
    $currentStage = 'REPARACION';

$nextIdx = array_search($currentStage, $stages) + 1;



$rows = $db->query("SELECT * FROM v_operation_checklist WHERE id_operation = $id_op AND stage = '".$stages[$nextIdx]."' ORDER BY id_product,id_accesory,id_accesory_base ")->fetchAll();

$items = [];
foreach ($rows as $row) {
    $pId = $row['id_product'];



    if (!isset($items[$pId])) {

    $query = "SELECT *  from products_images WHERE Product = ".$pId." ORDER BY Orden LIMIT 1";
    $stmtigm = $db->prepare($query);
    $stmtigm->execute();
    $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);
    if ($Img)
        $Img = $Img['Image'];        

        $items[$pId] = [
            'id'        => $row['id_checklist'],
            'name'      => $row['Product'],
            'image'     => $Img,
            'requested' => $row['requested_quantity'],
            'assorted'  => $row['assorted_quantity'],
            'cleaning' => $row['assorted_quantity'],
            'washing' => $row['assorted_quantity'],
            'repair' => $row['assorted_quantity'],            
            'verification_stage' => $row['verification_stage'], 
            'children'  => []
        ];
    }
    if ($row['id_accesory_base'] || $row['id_accesory']) {
        
        if ($row['id_accesory_base'])
            $ItemId = $row['id_accesory_base'];
        else
            $ItemId = $row['id_accesory'];
        
        $query = "SELECT *  from products_images WHERE Product = ".$ItemId." ORDER BY Orden LIMIT 1";
        $stmtigm = $db->prepare($query);
        $stmtigm->execute();
        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);
        if ($Img)
            $Img = $Img['Image'];        

        $items[$pId]['children'][] = [
            'id'       => $row['id_checklist'],
            'name'     => $row['Base'] ?? $row['Accesory'],
            'image'     => $Img,
            'type'     => $row['id_accesory_base'] ? 'BASE' : 'ACCESORIO',
            'requested' => $row['requested_quantity'],
            'assorted' => $row['assorted_quantity'],
            'cleaning' => $row['assorted_quantity'],
            'washing' => $row['assorted_quantity'],
            'repair' => $row['assorted_quantity'],
            'verification_stage' => $row['verification_stage']
        ];
    }
} 

?>
<br><br>
<div id="api-feedback" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<div class="container my-4">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-check me-2 text-warning"></i> <?= Trd(1) ?> [ <b id="current-stage"><?php echo $stage['status']?></b>  ] </h5>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 70px;"><?= Trd(2) ?></th>
                            <th><?= Trd(3) ?></th>
                            <th><?= Trd(4) ?></th>
                            <th class="text-center" style="width: 100px;"><?= Trd(5) ?></th>
                            <?php if ($stage['status'] == 'RECOLECCION'): ?>
                                <th class="text-center"><?= Trd(6) ?></th>
                                <th class="text-center"><?= Trd(7) ?></th>
                                <th class="text-center"><?= Trd(8) ?></th>
                            <?php else: ?>

                            <?php if ($stage['status'] == 'ENTREGA'): ?>
                                    <th class="text-center"><?= Trd(9) ?></th>
                                    <th class="text-center"><?= Trd(10) ?></th>
                            <?php else: ?>
                                <th class="text-center"><?= Trd(10) ?></th>
                            <?php endif; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
<tbody>
    <?php foreach ($items as $pId => $item): ?>

    
        <tr class="main-row border-bottom shadow-sm <?= $item['verification_stage'] == 1 ? 'estado-rojo' : 'estado-normal' ?>">
            <td class="text-center">
                <input class="form-check-input row-check" type="checkbox" data-id="<?= $item['id'] ?>" disabled>
            </td>
            <td>
                <img src="<?= CFPUBLICURL.'/'.ID_CLIENTE.'/products_images/thumbnails/'.$item['image'] ?>" class="rounded border" width="55" height="55" >
            </td>
            <td>
                <div class="fw-bold"><?= $item['name'] ?></div>
                <span class="badge bg-secondary x-small"><?= Trd(11) ?></span>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-center bg-light fw-bold requested-qty" value="<?= $item['requested'] ?>" readonly>
            </td>

            <?php if ($stage['status'] == 'RECOLECCION'): ?>
                <td>
                    <div class="input-group input-group-sm custom-qty-group">
                        <button class="btn btn-outline-secondary btn-minus_ px-2" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center cleaning-qty fw-bold" value="<?= $item['cleaning'] ?? 0 ?>" min="0" max="<?= $item['requested'] ?>">
                        <button class="btn btn-outline-secondary btn-plus_ px-2" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm custom-qty-group">
                        <button class="btn btn-outline-secondary btn-minus_ px-2" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center washing-qty fw-bold" value="<?= $item['washing'] ?? 0 ?>" min="0" max="<?= $item['requested'] ?>">
                        <button class="btn btn-outline-secondary btn-plus_ px-2" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm custom-qty-group">
                        <button class="btn btn-outline-secondary btn-minus_ px-2" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center repair-qty fw-bold" value="<?= $item['repair'] ?? 0 ?>" min="0" max="<?= $item['requested'] ?>">
                        <button class="btn btn-outline-secondary btn-plus_ px-2" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
            <?php else: ?>
                <?php if ($stage['status'] == 'ENTREGA'): ?>

                <td style="text-align: center;">
                    <div class="form-check form-switch" style="display: inline-block;">
                        <input class="form-check-input damage-item" type="checkbox" >
                    </div>
                </td>             

                <td>
                    <div class="input-group input-group-sm custom-qty-group">
                        <button class="btn btn-outline-secondary btn-minus px-3" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center assorted-qty fw-bold" value="<?= $item['assorted'] ?>" min="0" max="<?= $item['requested'] ?>">
                        <button class="btn btn-outline-secondary btn-plus px-3" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </td>                    

                <?php else: ?>
                <td>
                    <div class="input-group input-group-sm custom-qty-group">
                        <button class="btn btn-outline-secondary btn-minus px-3" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center assorted-qty fw-bold" value="<?= $item['assorted'] ?>" min="0" max="<?= $item['requested'] ?>">
                        <button class="btn btn-outline-secondary btn-plus px-3" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
                <?php endif; ?>
            <?php endif; ?>
        </tr>

        <?php foreach ($item['children'] as $child): ?>
            <tr class=" border-bottom border-white <?= $child['verification_stage'] == 1 ? 'estado-rojo' : 'estado-normal' ?>">
                <td class="text-center">
                    <input class="form-check-input row-check" type="checkbox" data-id="<?= $child['id'] ?>" disabled>
                </td>
                <td>
                    <img src="<?= CFPUBLICURL.'/'.ID_CLIENTE.'/products_images/thumbnails/'.$child['image'] ?>" class="rounded border ms-3" width="40" height="40" >
                </td>
                <td>
                    <div class="small fw-bold text-muted"><?= $child['name'] ?></div>
                    <span class="badge <?= ($child['type'] == 'BASE') ? 'bg-info text-dark' : 'bg-warning text-dark' ?> x-small"><?= $child['type'] ?></span>
                </td>
                <td>                    
                    <input type="number" class="form-control form-control-sm text-center bg-light fw-bold requested-qty" value="<?= $child['requested'] ?>" readonly>
                </td>

                <?php if ($stage['status'] == 'RECOLECCION'): ?>
                    <td>
                        <div class="input-group input-group-sm px-3 custom-qty-group">
                            <button class="btn btn-outline-secondary btn-minus_" type="button"><i class="fas fa-minus small"></i></button>
                            <input type="number" class="form-control text-center cleaning-qty bg-white" value="<?= $child['cleaning'] ?>" min="0" max="<?= $child['requested'] ?>">
                            <button class="btn btn-outline-secondary btn-plus_" type="button"><i class="fas fa-plus small"></i></button>
                        </div>                    
                    </td>
                    <td>

                        <div class="input-group input-group-sm px-3 custom-qty-group">
                            <button class="btn btn-outline-secondary btn-minus_" type="button"><i class="fas fa-minus small"></i></button>
                            <input type="number" class="form-control text-center washing-qty bg-white" value="<?= $child['washing'] ?>" min="0" max="<?= $child['requested'] ?>">
                            <button class="btn btn-outline-secondary btn-plus_" type="button"><i class="fas fa-plus small"></i></button>
                        </div>                     

                    </td>
                    <td>

                        <div class="input-group input-group-sm px-3 custom-qty-group">
                            <button class="btn btn-outline-secondary btn-minus_" type="button"><i class="fas fa-minus small"></i></button>
                            <input type="number" class="form-control text-center repair-qty bg-white" value="<?= $child['repair'] ?>" min="0" max="<?= $child['requested'] ?>">
                            <button class="btn btn-outline-secondary btn-plus_" type="button"><i class="fas fa-plus small"></i></button>
                        </div>                     

                    </td>
                <?php else: ?>
                    <?php if ($stage['status'] == 'ENTREGA'): ?>
                        <td style="text-align: center;">
                            <div class="form-check form-switch" style="display: inline-block;">
                                <input class="form-check-input damage-item" type="checkbox" >
                            </div>
                        </td>                                       
                        <td>
                            <div class="input-group input-group-sm px-3 custom-qty-group">
                                <button class="btn btn-outline-secondary btn-minus" type="button"><i class="fas fa-minus small"></i></button>
                                <input type="number" class="form-control text-center assorted-qty bg-white" value="<?= $child['assorted'] ?>" min="0" max="<?= $child['requested'] ?>">
                                <button class="btn btn-outline-secondary btn-plus" type="button"><i class="fas fa-plus small"></i></button>
                            </div>
                        </td>
                    <?php else: ?>
                    <td>
                        <div class="input-group input-group-sm px-3 custom-qty-group">
                            <button class="btn btn-outline-secondary btn-minus" type="button"><i class="fas fa-minus small"></i></button>
                            <input type="number" class="form-control text-center assorted-qty bg-white" value="<?= $child['assorted'] ?>" min="0" max="<?= $child['requested'] ?>">
                            <button class="btn btn-outline-secondary btn-plus" type="button"><i class="fas fa-plus small"></i></button>
                        </div>
                    </td>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</tbody>
                </table>
            </div>



            <div class="row p-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-camera me-1"></i> <?= Trd(12) ?></label>
                    <input type="file" id="evidence-file" class="form-control form-control-sm" accept="image/*" capture="environment">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-map-marker-alt me-1"></i> <?= Trd(13) ?></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="geo-location" class="form-control bg-light" placeholder="<?= Trd(14) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btn-refresh-geo"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
<?php if ($stages[$nextIdx] == 'ENTREGA' AND $Balance > 0){?>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-wallet me-1"></i><?= Trd(15) ?> </label>
                    <div class="card border-primary shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><?= Trd(16) ?>:</span>
                                <span class="fw-bold text-danger" id="pending-balance">$<?php echo number_format($Balance, 2, '.', ',')?></span>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="btn-confirm-payment"
                            
                            onclick="window.open('payments.php?IdLead=<?php echo $id_lead;?>', '_blank')">
                                <i class="fas fa-check-circle me-1"></i> <?= Trd(17) ?>
                            </button>
                        </div>
                    </div>
                </div>
<?php }?>

<?php if ($stages[$nextIdx] == 'RECOLECCION' AND $Balance > 0){?>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-wallet me-1"></i><?= Trd(18) ?> </label>
                    <div class="card border-primary shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><?= Trd(19) ?>:</span>
                                <span class="fw-bold text-danger" id="pending-balance">$<?php echo number_format($Balance, 2, '.', ',')?></span>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="btn-confirm-payment"
                            
                            onclick="window.open('payments.php?IdLead=<?php echo $id_lead;?>', '_blank')">
                                <i class="fas fa-check-circle me-1"></i> <?= Trd(20) ?>
                            </button>
                        </div>
                    </div>
                </div>
<?php }?>

                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-sticky-note me-1"></i><?= Trd(21) ?></label>
                    <textarea id="stage-notes" class="form-control form-control-sm" rows="5" placeholder="<?= Trd(33) ?>"></textarea>
                </div>
<?php if ($stages[$nextIdx] == 'ENTREGA'){?>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold"><i class="fas fa-signature me-1"></i><?= Trd(22) ?> </label>
                    <div class="signature-wrapper border rounded bg-white shadow-sm" style="position: relative; height: 135px; overflow: hidden;">
                        <canvas id="signature-pad" style="width: 100%; height: 100%; cursor: crosshair; touch-action: none;"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-signature" 
                                style="position: absolute; bottom: 5px; right: 5px; --bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                            <i class="fas fa-eraser"></i> <?= Trd(23) ?>
                        </button>
                    </div>
                    <input type="hidden" id="signature-data">
                </div>
<?php }?>                
            </div>


        </div>

        <div class="card-footer bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">

                <?php if ($stage['status'] != 'RECOLECCION'): ?>
            <button id="marcar-todo" class="btn btn-warning px-5 fw-bold shadow-sm" onclick="fillMaxAssorted()">
                <i class="fa-regular fa-square-check"></i> <?= Trd(34) ?>
            </button>
                <?php endif; ?>
                <div id="status-text" class="small text-muted">
                    <i class="fas fa-info-circle me-1"></i> <?= Trd(24) ?>
                </div>
                <button id="btn-update-stage" class="btn btn-success px-5 fw-bold shadow-sm" disabled>
                    <span id="btn-text"><?= Trd(25) ?> <span id="next-stage-label"> <?= Trd(26) ?></span></span>
                    <span id="btn-loader" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>






    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-primary text-white">
      <strong class="me-auto"><i class="fas fa-bell me-2"></i> <?= Trd(27) ?></strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-message">
      </div>
  </div>
</div>




<script>
    <?php
    include_once 'js_scripts.php';
    ?>

    function getCoords() {
        if (navigator.geolocation) {
            $('#geo-location').val("Localizando...");
            navigator.geolocation.getCurrentPosition(function(position) {
                const coords = `${position.coords.latitude}, ${position.coords.longitude}`;
                $('#geo-location').val(coords);
            }, function(error) {
                $('#geo-location').val("Permiso denegado / GPS apagado");
            });
        }
    }


    let firmaUtilizada = false;

$(document).ready(function() {

    const stages = ['SURTIDO', 'CARGA', 'INSTALACION', 'PRUEBA FUNCIONAMIENTO', 'ENTREGA', 'RECOLECCION', 'ACONDICIONAMIENTO', 'LIMPIEZA', 'LAVADO','REPARACION',  'ALMACENADO','FINALIZADO'];

    getCoords();
    $('#btn-refresh-geo').click(getCoords);

// 1. Lógica de botones +/- (Adaptada para múltiples columnas)
$('.btn-plus, .btn-minus, .btn-plus_, .btn-minus_').on('click', function() {
    const row = $(this).closest('tr');
    // Buscamos el input que está justo al lado del botón presionado
    const input = $(this).siblings('input'); 
    const max = parseInt(row.find('.requested-qty').val()) || 0;
    let current = parseInt(input.val()) || 0;

    if ($(this).hasClass('btn-plus') || $(this).hasClass('btn-plus_')) {
        // Calculamos la suma actual de la fila para validar contra el máximo
        const currentTotal = calculateRowSum(row);
        
        if (currentTotal < max) {
            input.val(current + 1).trigger('change');
        } else {
            // Feedback visual de error
            input.addClass('qty-limit-error');
            mostrarToast("<?= Trd(28) ?>(" + max + ")", true);
            setTimeout(() => input.removeClass('qty-limit-error'), 400);
        }
    } else {
        if (current > 0) {
            input.val(current - 1).trigger('change');
        }
    }
});

// Función auxiliar para sumar los valores de recolección en la fila
function calculateRowSum(row) {
    let sum = 0;
    // Sumamos limpieza, lavado y reparación (o assorted-qty si no es recolección)
    row.find('.cleaning-qty, .washing-qty, .repair-qty, .assorted-qty').each(function() {
        sum += parseInt($(this).val()) || 0;
    });
    return sum;
}

// 2. Validación de reglas de negocio
function validateLogic(row) {
    const req = parseInt(row.find('.requested-qty').val()) || 0;
    const check = row.find('.row-check');
    
    let totalRow = calculateRowSum(row);

    // Si el usuario escribe manualmente un número que hace que la suma exceda el total
    if (totalRow > req) {
        mostrarToast("<?= Trd(29) ?>", true);
        
        // Ajuste proporcional o simple: restamos el exceso al input que cambió
        // Para este ejemplo, ajustamos el input actual para que la suma sea exactamente 'req'
        const activeInput = row.find('input:focus'); 
        if(activeInput.length) {
            const otherInputsSum = totalRow - (parseInt(activeInput.val()) || 0);
            activeInput.val(req - otherInputsSum);
            totalRow = req;
        }
    }

    // Regla de éxito: la fila está "lista" si la suma es igual a lo pedido
    if (totalRow === req && req > 0) {
        check.prop('checked', true);
        row.addClass('table-success-row text-success');
    } else {
        check.prop('checked', false);
        row.removeClass('table-success-row text-success');
    }

    checkGlobalStatus();
}

// Listener para todos los tipos de inputs de cantidad
$(document).on('change keyup', '.assorted-qty, .cleaning-qty, .washing-qty, .repair-qty', function() {
    validateLogic($(this).closest('tr'));
});

function checkGlobalStatus() {
    const total = $('.row-check').length;
    const ready = $('.row-check:checked').length;
    // Actualización de progreso visual (opcional)
    const percent = total > 0 ? (ready / total) * 100 : 0;
    $('.progress-bar').css('width', percent + '%');

    // Habilitar botón de siguiente etapa solo si todo está cuadrado
    $('#btn-update-stage').prop('disabled', ready < total);



        const currentStage = $('#current-stage').text().trim();
        let  nextIdx = stages.indexOf(currentStage) + 1;
        if (nextIdx < stages.length) {
            if (nextIdx == 7)
                nextIdx = 10
            $('#next-stage-label').text(stages[nextIdx]);
        }    


}
    window.checkGlobalStatus = checkGlobalStatus;

    $('.assorted-qty').on('change keyup', function() {
        validateLogic($(this).closest('tr'));
    });


$('#btn-update-stage').on('click', function() {


        const btn = $(this);
        const currentStage = $('#current-stage').text().trim();

        let actualStage  ='';
        let nextStage = '';
        
        if (currentStage == 'ACONDICIONAMIENTO' ){
             actualStage  = stages[stages.indexOf(currentStage) + 4];
             nextStage = stages[stages.indexOf(currentStage) + 5];    
        }
        else{
             actualStage  = stages[stages.indexOf(currentStage) + 1];
             nextStage = stages[stages.indexOf(currentStage) + 2];            
        }

        //if(<?php echo $Balance?> > 0 && actualStage == 'RECOLECCION'){
        //    //alert("Error: Es necesario liquidar el saldo pendiente.");
        //    mostrarToast("<?= Trd(30) ?>", true);
        //    return;            
        //}

        if (actualStage == 'ENTREGA' && !firmaUtilizada) {
            //alert("Error: La firma del cliente es obligatoria.");
            mostrarToast("<?= Trd(31) ?>", true);
            return;
        }        

        // Creamos el objeto FormData para soportar el archivo de imagen
        let formData = new FormData();
        
        // Datos básicos
        formData.append('id_op', <?php echo $id_op?>);
        formData.append('currentStage', actualStage);
        formData.append('next_stage', nextStage);
        formData.append('coords', $('#geo-location').val());
        formData.append('notes', $('#stage-notes').val());

        if (actualStage == 'ENTREGA' ) {
            const canvas = document.getElementById('signature-pad');
            const signatureBase64 = canvas.toDataURL('image/png'); // Esto genera el string

            formData.append('sign', signatureBase64);
        }                

        if (actualStage == 'ACONDICIONAMIENTO' ) {        
            const jsonStages = generarJsonStages();
            formData.append('STAGES', jsonStages);
        }

        // Archivo de imagen (si existe)
        const fileInput = $('#evidence-file')[0].files[0];
        if (fileInput) {
            formData.append('evidence_img', fileInput);
        }

        //alert(actualStage)


        // Datos del checklist
        let items = [];
        if (actualStage == 'RECOLECCION'){

            $('.row-check:checked').each(function() {
                const row = $(this).closest('tr');
                if($(this).data('id')) {
                    items.push({
                        id: $(this).data('id'),
//                        qty: row.find('.assorted-qty').val(),
                        chk: row.find('.damage-item').prop('checked')
                    });
                }
            });        

        }
        else{

            $('.row-check:checked').each(function() {
                const row = $(this).closest('tr');
                if($(this).data('id')) {
                    items.push({
                        id: $(this).data('id'),
//                        qty: row.find('.assorted-qty').val(),
                        chk: false
                    });
                }
            });        

        }

        formData.append('items', JSON.stringify(items));
        //alert(JSON.stringify(items))
        //return;
        // UI Feedback: Cargando
        btn.prop('disabled', true);
        $('#btn-text').addClass('d-none');
        $('#btn-loader').removeClass('d-none');

        $.ajax({
            url: API_BASE_URL + 'process_stage_change/',
            method: 'POST',
            data: formData,
            headers: { 'Authorization': 'Bearer ' + TOKEN },            
            processData: false, // Vital para FormData
            contentType: false, // Vital para FormData
            success: function(response) {

            <?php if ($LoadProc == 1) { ?>
                if (nextStage == 'CARGA') {
                    let formData_load = new FormData();
                    // Datos básicos
                    formData_load.append('id_route', <?php echo $id_op?>);
                    formData_load.append('currentStage', 'CARGA');
                    formData_load.append('next_stage', 'INSTALACION');

                    $.ajax({
                        url: API_BASE_URL + 'process_stage_change_em/',
                        method: 'POST',
                        data: formData_load,
                        headers: { 'Authorization': 'Bearer ' + TOKEN },            
                        processData: false, // Vital para FormData
                        contentType: false, // Vital para FormData
                        success: function(response) {
                            history.back();
                        },
                        error: function() {

                        }
                    });     
                } else {
                    if (document.referrer) {
                        window.location.href = document.referrer;
                    } else {
                        history.back(); // Respaldo si no hay historial previo registrado
                    }
                }
            <?php }else{?>
                    if (document.referrer) {
                        window.location.href = document.referrer;
                    } else {
                        history.back(); // Respaldo si no hay historial previo registrado
                    }            
            <?php } ?>

            },
            error: function() {
                showToast('<?= Trd(32) ?>', 'danger');
                btn.prop('disabled', false);
                $('#btn-text').removeClass('d-none');
                $('#btn-loader').addClass('d-none');
            }
        });
    });

    function resetBtn(btn) {
        btn.prop('disabled', false);
        $('#btn-text').removeClass('d-none');
        $('#btn-loader').addClass('d-none');
    }

    function showToast(msg, type) {
        const toast = `<div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
            ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        $('#api-feedback').append(toast);
        setTimeout(() => $('.alert').alert('close'), 4000);
    }

    // Inicializar al cargar
    $('tr').each(function() { validateLogic($(this)); });
});

<?php if ($stages[$nextIdx] == 'ENTREGA'){?>

document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    // Ajustar resolución del canvas al tamaño visual
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX || e.touches[0].clientX) - rect.left,
            y: (e.clientY || e.touches[0].clientY) - rect.top
        };
    }

    function startDrawing(e) {
        drawing = true;
        firmaUtilizada = true;
        const pos = getMousePos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        const pos = getMousePos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDrawing() {
        drawing = false;
        // Guardar en el input hidden para enviarlo al servidor
        document.getElementById('signature-data').value = canvas.toDataURL();
    }

    // Eventos Mouse y Touch
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    // Botón Limpiar
    document.getElementById('btn-clear-signature').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signature-data').value = "";
        firmaUtilizada = false;
    });
});





<?php }?>

function mostrarToast(mensaje, esError = false) {
    const toastElement = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toast-message');
    const toastHeader = toastElement.querySelector('.toast-header');

    // Cambiar color si es error o éxito
    if (esError) {
        toastHeader.classList.replace('bg-primary', 'bg-danger');
    } else {
        toastHeader.classList.replace('bg-danger', 'bg-primary');
    }

    toastMessage.textContent = mensaje;

    // Inicializar y mostrar con Bootstrap 5
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

function generarJsonStages() {
    let stagesData = [];

    // Recorremos todas las filas que tengan un ID (padres e hijos)
    $('tbody tr').each(function() {
        const row = $(this);
        const id = row.find('.row-check').data('id');
        
        // Si la fila no tiene ID (a veces hay filas de espacio), la saltamos
        if (!id) return;

        // Extraemos los valores. Usamos || 0 para asegurar que siempre haya un número
        const requested = parseInt(row.find('.requested-qty').val()) || 0;
        
        // Buscamos específicamente los campos de recolección
        const cleaning  = parseInt(row.find('.cleaning-qty').val()) || 0;
        const washing   = parseInt(row.find('.washing-qty').val()) || 0;
        const repair    = parseInt(row.find('.repair-qty').val()) || 0;
        
        // Si no estamos en recolección, podrías querer capturar assorted-qty
        const assorted  = parseInt(row.find('.assorted-qty').val()) || 0;

        stagesData.push({
            id: id,
            requested: requested,
            cleaning: cleaning,
            washing: washing,
            repair: repair,
            assorted: assorted // Incluido por si acaso
        });
    });

    return JSON.stringify(stagesData);
}

function fillMaxAssorted() {
    // 1. Seleccionamos todos los inputs con la clase específica
    const inputs = document.querySelectorAll('.assorted-qty');

    
    
    inputs.forEach(input => {
        const maxValue = input.getAttribute('max');
        if (maxValue !== null) {
            input.value = maxValue;
        }
    });

    const inputsc = document.querySelectorAll('.row-check');    
    inputsc.forEach(input => {
        input.checked = true; // Forma nativa de marcar un checkbox
    });
    window.checkGlobalStatus()
}


</script>
</body>
</html>