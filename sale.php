<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'valid_login.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();


$Idioma = $_SESSION['Idioma'];
$query = "select Traduccion FROM  programas_traduccion where Programa = 'sales' AND Idioma = ? ORDER BY Id";            
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


    $query = "select * FROM account";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account['Pais'] == 'USA' ){
        $account['Pais'] = 'US';
    }else{
        $account['Pais'] = 'MX';
    }

    $query = "select * FROM paypal_account";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $paypal_account = $stmt->fetch(PDO::FETCH_ASSOC);    


?>
<link rel="stylesheet" href="css/lead.css" />

    <style>
        /* ── Integración de Paleta y Tipografía Minimalista de lead.css ── */
        input[type="datetime-local"] {
            font-size: 0.8rem;
            color: #444;
            border: 1px solid #dee2e6;
        }
        input[type="datetime-local"]:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        .input-group-text {
            font-size: 0.8rem;
            color: #6c757d;
            border-color: #dee2e6;
        }

        body {
            background-color: #f8f9fa;
            color: #444;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: .92rem;
            min-height: 100vh;
            padding-top: 70px; /* Espacio para el nav superior fijo */
        }


        /* ── Contenedores y Paneles Estilo lead.css ── */
        .main-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            padding: 1.2rem 1rem 3rem;
        }

        .card-panel {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .card-header-custom {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: .75rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            font-size: .95rem;
            color: #333;
        }
        .card-body-custom { padding: 1.1rem; }

        /* ── Controles de Formulario Unificados ── */
        .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: #6c757d;
            margin-bottom: .3rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: .88rem;
            color: #444;
            transition: border-color .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        /* ── Listado de Productos Estilo custom-row ── */
        .product-row {
            border-bottom: 1px solid #eee;
            padding: 12px 10px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }
        .product-row:hover {
            background-color: #fcfcfc;
        }
        .product-row .prod-name {
            font-weight: 600;
            font-size: .88rem;
            color: #222;
        }
        .product-row .prod-badge {
            font-size: .7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: .4rem 0;
            font-size: .88rem;
        }
        .summary-line.total {
            border-top: 1px solid #dee2e6;
            margin-top: .4rem;
            padding-top: .6rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: #0d6efd;
        }

        /* ── Botones Minimalistas ── */
        .btn-minimal {
            background: transparent;
            border: none;
            padding: 5px 15px;
            margin: 0 2px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-minimal:hover {
            background-color: rgba(0, 0, 0, 0.04);
            color: #000;
        }

        .btn-primary-custom {
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: .5rem 1.2rem;
            font-weight: 500;
            font-size: .88rem;
            transition: background .15s;
        }
        .btn-primary-custom:hover { background: #0b5ed7; color:#fff; }

        .btn-outline-custom {
            border: 1px solid #dee2e6;
            background: transparent;
            color: #555;
            border-radius: 6px;
            padding: .45rem 1.1rem;
            font-weight: 500;
            font-size: .88rem;
            transition: all .15s;
        }
        .btn-outline-custom:hover { border-color: #0d6efd; color: #0d6efd; background-color: rgba(13,110,253,0.02); }

        /* Barras de Mensajes / Alertas Estilo lead.css */
        #barra-mensajes {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            z-index: 1050;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(0,0,0,0.05);
            display: none;
        }
        .msg-minimal-exito   { background: rgba(232, 245, 233, 0.95); color: #2e7d32; padding: 10px 20px; }
        .msg-minimal-error   { background: rgba(253, 235, 235, 0.95); color: #c62828; padding: 10px 20px; }
        .msg-minimal-alerta  { background: rgba(255, 248, 225, 0.95); color: #f9a825; padding: 10px 20px; }
        .msg-minimal-normal  { background: rgba(255, 255, 255, 0.95); color: #333; padding: 10px 20px; }

        .btn-cerrar-mini {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            opacity: 0.5;
            font-weight: bold;
        }
        .btn-cerrar-mini:hover { opacity: 1; }

        .lookup-bar {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: .8rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: 1rem;
        }

        .required-star { color: #dc3545; }
        .tracking-tight { letter-spacing: -0.02em; }
        .select2-results__option { padding: 8px 12px !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear { margin-right: 0.5rem; }

        /* Ajustes responsivos móviles para filas de productos */
        @media (max-width: 767.98px) {
            .product-row {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
                border: 1px solid #eee;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            .product-row > div {
                width: 100%;
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>
<?php
    include_once 'nav.php';
?>


<!-- ══════════════════════════════════════════════
     BARRA DE MENSAJES (ESTILO LEAD.CSS)
══════════════════════════════════════════════ -->
<div id="barra-mensajes">
    <div id="barra-contenido" class="d-flex justify-content-between align-items-center w-100">
        <span id="barra-texto"></span>
        <span class="btn-cerrar-mini" onclick="$('#barra-mensajes').slideUp(200)">Cerrar</span>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- Buscador superior de Venta -->
    <div class="lookup-bar">
        <i class="fa fa-search text-muted"></i>
        <input type="number" id="lookup-sale-id" class="form-control form-control-sm" placeholder="Editar venta existente — ingresa el # de venta" style="max-width:280px">
        <button class="btn-primary-custom py-1" onclick="loadSale()">
            <i class="fa fa-file-pen me-1"></i>Cargar
        </button>
        <span id="lookup-status" class="text-muted small"></span>

    <div class="d-flex gap-2 align-items-center">
        <span id="sale-mode-badge" class="badge bg-light text-secondary border fw-medium" style="font-size:.8rem; display:none;"></span>
        <button class="btn-minimal" onclick="resetAll()">
            <i class="fa fa-rotate-left me-1"></i>Nueva venta
        </button>
    </div>        

    </div>

    <div class="row g-3">
        <div class="col-12">

            <!-- Fila superior: Cliente y Dirección -->
            <div class="row g-3 mb-3">
                <!-- PASO 1: CLIENTE -->
                <div class="col-md-6">
                    <div id="section-customer" class="card-panel h-100">
                        <div class="card-header-custom">
                            <i class="fa fa-user-circle text-muted"></i> Paso 1 — Cliente
                            <span class="ms-auto">
                                <button class="btn-minimal" onclick="openNewCustomerModal()">
                                    <i class="fa fa-plus me-1"></i>Nuevo cliente
                                </button>
                            </span>
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Buscar cliente <span class="required-star">*</span></label>
                                    <select id="customer-select" class="form-select" style="width:100%">
                                        <option value="">— Escribe para buscar —</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2" id="customer-info-box" style="display:none">
                                    <div class="p-2 rounded border bg-light small" id="customer-info-text"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASO 2: DIRECCIÓN -->
                <div class="col-md-6">
                    <div id="section-address" class="card-panel h-100" style="opacity:.5;pointer-events:none">
                        <div class="card-header-custom">
                            <i class="fa fa-location-dot text-muted"></i> Paso 2 — Dirección de Envío
                            <span class="ms-auto">
                                <button class="btn-minimal" onclick="openNewAddressModal()">
                                    <i class="fa fa-plus me-1"></i>Nueva dirección
                                </button>
                            </span>
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Seleccionar dirección <span class="required-star">*</span></label>
                                    <select id="address-select" class="form-select" style="width:100%">
                                        <option value="">— Selecciona primero un cliente —</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2" id="address-info-box" style="display:none">
                                    <div class="p-2 rounded border bg-light small" id="address-info-text"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: PRODUCTOS -->
            <div id="section-products" class="card-panel mb-3" style="opacity:.5;pointer-events:none">
                <div class="card-header-custom">
                    <i class="fa fa-box-open text-muted"></i> Paso 3 — Productos
                </div>
                <div class="card-body-custom">
                    <div class="row g-2 mb-3">
                        <div class="col-md-10">
                            <label class="form-label">Buscar producto</label>
                            <select id="product-search" class="form-select" style="width:100%">
                                <option value="">— Escribe para buscar —</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn-primary-custom w-100" onclick="addProductToCart()">
                                <i class="fa fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>

                    <div id="cart-items" class="border rounded overflow-hidden">
                        <div class="p-4 text-center text-muted small" id="cart-empty">
                            <i class="fa fa-cart-shopping d-block mb-2 fs-4 text-black-50"></i>
                            Sin productos — agrega uno arriba
                        </div>
                    </div>
                </div>
            </div>

<!-- ══════════════════════════════════════════════
     PASO 4: PAGO Y NOTAS (sales.php)
══════════════════════════════════════════════ -->
<div id="section-payment" class="card-panel mb-3" style="opacity:.5;pointer-events:none">
    <div class="card-header-custom">
        <i class="fa fa-credit-card text-muted"></i> Paso 4 — Registro de Venta y Notas
    </div>
    <div class="card-body-custom">
        
        <div class="row g-3 mb-3 bg-fluid p-3 rounded border bg-light">
            <div class="col-md-6">
                <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.7rem;"><i class="fa fa-user me-1"></i> Cliente Confirmado</div>
                <div id="step4-customer-summary" class="small fw-semibold text-dark">—</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.7rem;"><i class="fa fa-map-marker-alt me-1"></i> Dirección Confirmada</div>
                <div id="step4-address-summary" class="small fw-semibold text-dark">—</div>
            </div>
        </div>

        <!-- Campos removidos y simplificados: Solo Notas de la venta -->
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Notas de la venta</label>
                <textarea id="cart-notes" class="form-control" rows="3" placeholder="Instrucciones especiales, observaciones..."></textarea>
            </div>
        </div>



<?php 

    //$IdLead = 0;
    $Idioma = $_SESSION['Idioma'];    
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'lead' AND Idioma = ? ORDER BY Id";            
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


    $PayPlatform = $account['Pay_platform'];
?>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay.v1.min.js"></script>
    <script type='text/javascript' src="https://openpay.s3.amazonaws.com/openpay-data.v1.min.js"></script>
<b> <?= Trd(117) ?> </b>
<button id="btn_toggle_pagos" class="btn btn-link">
    [ <span id="texto_boton"><?= Trd(118) ?></span> ]
</button>    

<!-- Contenedor Principal -->
<div class="container mt-4" id="div_pagos" style="display: none;">
    <div class="row">


        <!-- ── NUEVO: Contenedor del listado de pagos (Visible solo en Consulta/Edición) ── -->
        <div id="container-payments-list" class="mt-4" style="font-size:0.75rem; display:none;">

            <div class="d-flex justify-content-between align-items-center mb-3 text-muted small fw-bold text-uppercase mb-2">
                <i class="fa fa-money-bill-wave me-1"></i> <?= Trd(119) ?>
                <!-- Botón para mostrar el formulario -->
                <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm">
                    + <?= Trd(120) ?>
                </button>
            </div>        


            <div class="table-responsive border rounded bg-white">
                <table class="table table-sm table-hover align-middle mb-0 small text-center">
                    <thead class="table-light text-secondary uppercase text-xs" style="font-size:0.75rem;">
                        <tr>
                            <th>Folio / ID Transacción</th>
                            <th>Plataforma / Tipo</th>
                            <th>Fecha y Hora</th>
                            <th>Monto</th>
                            <th>Estatus</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="payments-table-body">
                        <!-- Renderizado vía JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>    
    </div>    
    <div class="row">
        <!-- SECCIÓN 2: Formulario Oculto (Collapse) -->
        <div class="col-12 col-lg-6 mx-auto collapse mt-3" id="collapseForm">
            <div class="border p-4 bg-light">
                <h6 class="mb-3"><?= Trd(128) ?></h6>
                
                    <div class="row">
                        <!-- Selección de Método -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold"><?= Trd(129) ?></label>
                            <select class="form-select form-select-sm" id="tipo_pago" required>
                                <option value=""><?= Trd(130) ?></option>
                                <option value="efectivo"><?= Trd(131) ?></option>
                                <option value="tarjeta"><?= $PayPlatform ." - ".Trd(132) ?></option>
                                <?php 
                                if ($paypal_account['Active'] == 1){
                                    echo '<option value="paypal">PAYPAL</option>';
                                }
                                ?>
                                <option value="transferencia"><?= Trd(133) ?></option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Monto</label>
                            <input type="text" 
                                style="text-align:right;" 
                                class="form-control form-control-sm" 
                                placeholder="0.00" 
                                id="monto_pago" 
                                required>
                            <!-- Contenedor para mensaje de error -->
                            <div id="monto_error" class="text-danger small d-none"><?= Trd(134) ?></div>
                        </div>
                        <!-- Referencia (Efectivo/Transferencia) -->
                        <div class="col-md-4 mb-3 d-none" id="div_referencia">
                            <label class="form-label small fw-bold"><?= Trd(135) ?></label>
                            <input id="refcia" type="text" class="form-control form-control-sm" placeholder="Número de folio">
                        </div>
                    </div>

                    <!-- Campos de Tarjeta (Fila extra que aparece solo si se elige tarjeta) -->
                    <div class="row d-none" id="div_tarjeta">

                <form id="payment-form" action="#" method="POST">
                    <input type="hidden" name="token_id" id="token_id">
                    <input type="hidden" name="token" id="token" value="0">
                    <input type="hidden" name="monto" id="monto" value="<?php echo '0.00'?>">
                    <input type="hidden" name="referencia" id="referencia" value="">
                    <input type="hidden" name="tipo-pago" id="tipo-pago" value="">
                    
                        <?php if ($PayPlatform == 'OPAY'){?>
                        <h6 class="fw-bold mb-3"><?= Trd(136) ?></h6>

                        <div class="mb-2">
                            <label class="form-label small fw-bold"><?= Trd(137) ?></label>
                            <input type="text" class="form-control form-control-sm" placeholder="Como aparece en la tarjeta" data-openpay-card="holder_name">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold"><?= Trd(138) ?></label>
                            <input type="text" class="form-control form-control-sm only-numbers" placeholder="0000 0000 0000 0000" data-openpay-card="card_number" maxlength="16">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label small fw-bold"><?= Trd(139) ?></label>
                                <input type="text" class="form-control form-control-sm only-numbers" placeholder="MM" data-openpay-card="expiration_month" maxlength="2">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold"><?= Trd(140) ?></label>
                                <input type="text" class="form-control form-control-sm only-numbers" placeholder="AA" data-openpay-card="expiration_year" maxlength="2">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold">CVV</label>
                                <input type="text" class="form-control form-control-sm only-numbers" placeholder="CVV" data-openpay-card="cvv2" maxlength="4">
                            </div>
                        </div>
                        <?php }
                        else{
                        ?>
                        <h6 class="fw-bold mb-3"><?= Trd(141) ?></h6>
                            <div id="card-container" class="mb-3"></div>
                        <?php
                        }
                        ?>


                    <div id="payment-status-container" class="mt-3 text-center">

                    </div>

                    

                    <?php if ($PayPlatform == 'OPAY'){
                            //<button class="btn btn-dark" type="button" id="pay-button">Confirmar Pago</button> 
                        }
                        else{
                            //<button id="card-button" type="button" class="btn btn-dark">Confirmar Pago</button>
                        }
                    ?>
                    <div class="text-center mt-3">

                    <?php if ($PayPlatform == 'OPAY'){?>
                          <img src="https://www.openpay.mx/_ipx/_/img/header/openpay-color.svg" alt="Openpay" style="height: 25px; opacity: 0.6;">
                    <?php }
                        else{
                    ?>
                          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Square%2C_Inc._logo.svg/1280px-Square%2C_Inc._logo.svg.png" alt="Square" style="height: 25px; opacity: 0.6;">
                    <?php
                    }
                    ?>                    

                      
                    </div>
                </form>                    

            

                    </div>

            <div class="d-flex justify-content-between align-items-center mb-2 opacity-75">
                <span class="text-muted"><?= Trd(142) ?>:</span>
                <span class="fw-bold"  id="display-saldo-hoy" >$0.00</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted"><?= Trd(143) ?>:</span>
                <span class="fw-bold"  id="display-saldo-pago">$0.00</span>
            </div>

            <hr>

            <div class="p-3 bg-light border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold d-block text-primary"><?= Trd(144) ?></span>
                    </div>
                    <h2 class="fw-bold mb-0 text-primary" id="display-pago-hoy">$0.00</h2>
                </div>
            </div>                    

            <div class="text-center border-top pt-3 d-none" id="pay-buttons">
                <button type="submit" class="btn btn-dark w-100" id="pay-button"><?= Trd(145) ?></button>
                <button type="button" class="btn btn-link btn-sm text-secondary mt-2" data-bs-toggle="collapse" data-bs-target="#collapseForm"><?= Trd(153) ?></button>
            </div>                        

            <div id="paypal-button-container"  class="d-none" ></div>


            </div>
        </div>
    </div>
</div>


        




        <hr class="my-4" style="border-top:1px solid #dee2e6;">

        <div class="row justify-content-end mb-3">
            <div class="col-md-4">
                <div class="summary-line">
                    <span class="text-muted">Subtotal</span>
                    <span id="sum-subtotal" class="fw-medium">$0.00</span>
                </div>
                <div class="summary-line">
                    <span class="text-muted">Descuentos</span>
                    <span id="sum-discount" class="text-success fw-medium">-$0.00</span>
                </div>

                <div class="summary-line">
                    <span class="text-muted">Pagado</span>
                    <span id="sum-payments" class="text-success fw-medium">-$0.00</span>
                </div>                

                <div class="summary-line total">
                    <input type="hidden" value="0" id="Balance">
                    <span>Total a registrar</span>
                    <span id="sum-total">$0.00</span>
                </div>
            </div>
        </div>

        <div class="row justify-content-end g-2">
            <div class="col-md-4">
                <button class="btn-primary-custom w-100 py-2" id="btn-save-sale" onclick="saveSale()">
                    <i class="fa fa-floppy-disk me-1"></i>
                    <span id="btn-save-label">Registrar venta</span>
                </button>
            </div>
        </div>

        <div id="sale-saved-info" class="mt-3" style="display:none">
            <div class="alert alert-success py-2 mb-0 small text-center border-0" style="background-color:#e8f5e9; color:#2e7d32;">
                <i class="fa fa-check-circle me-1"></i>
                Venta guardada exitosamente — <strong id="saved-sale-id"></strong>
            </div>
        </div>

    </div>
</div>

        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════
     MODAL: NUEVO CLIENTE
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalNewCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark"><i class="fa fa-user-plus me-2 text-muted"></i>Nuevo Cliente</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre <span class="required-star">*</span></label>
                        <input type="text" id="nc-firstname" class="form-control" placeholder="Nombre">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido <span class="required-star">*</span></label>
                        <input type="text" id="nc-lastname" class="form-control" placeholder="Apellido">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" id="nc-email" class="form-control" placeholder="email@ejemplo.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono <span class="required-star">*</span></label>
                        <input type="tel" id="nc-phone" class="form-control" placeholder="10 dígitos">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado (clave) <span class="required-star">*</span></label>
                        <input type="text" id="nc-state" class="form-control" placeholder="JAL, CDMX...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña temporal</label>
                        <input type="password" id="nc-password" class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn-primary-custom py-1" onclick="saveNewCustomer()">
                    <i class="fa fa-save me-1"></i>Guardar cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: NUEVA DIRECCIÓN
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalNewAddress" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark"><i class="fa fa-location-dot me-2 text-muted"></i>Nueva Dirección</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Alias <span class="required-star">*</span></label>
                        <input type="text" id="na-alias" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Ciudad <span class="required-star">*</span></label>
                        <input type="text" id="na-city" class="form-control" placeholder="">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Calle y número <span class="required-star">*</span></label>
                        <input type="text" id="na-street" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colonia <span class="required-star">*</span></label>
                        <input type="text" id="na-colonia" class="form-control" placeholder="">
                    </div>                    
                    <div class="col-md-4">
                        <label class="form-label">C.P. <span class="required-star">*</span></label>
                        <input type="text" id="na-zip" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pais <span class="required-star">*</span></label>
                        <select id="na-country" name="pais" class="form-control">
                            <option value="">Seleccione un país...</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado <span class="required-star">*</span></label>
                        <select id="na-state" name="estado" class="form-control">
                            <option value="">Seleccione un estado...</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Referencias</label>
                        <input type="text" id="na-references" class="form-control" placeholder="">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="na-default">
                            <label class="form-check-label text-muted" for="na-default" style="font-size:0.85rem;">Marcar como dirección principal</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn-primary-custom py-1" onclick="saveNewAddress()">
                    <i class="fa fa-save me-1"></i>Guardar dirección
                </button>
            </div>
        </div>
    </div>
</div>


<script>

    <?php
    include_once 'js_scripts.php';
    ?>
const ID_CLIENTE = '<?= $_SESSION['id_cliente']; ?>'; 
const CFPUBLICURL = '<?= CFPUBLICURL ?>';     


const API_BASE = 'api.php';

const state = {
    mode: 'new',
    saleId: null,
    customer: null,
    address: null,
    cart: [],
    subtotal: 0,
    discountTotal: 0,
    total: 0,
};

$(function () {
    initCustomerSelect();
    initAddressSelect();
    initProductSelect();
    updateSaleBadge();
    
});

function getcountry() {
    $.getJSON(API_BASE, { action: 'get_country' }, function (res) {
        // Asumiendo que tu función resp(true, $datos) devuelve el array en la propiedad 'data' o directo en 'res'
        // Si tu API envuelve los datos en un objeto, ajusta 'res.data' o usa 'res' según corresponda
        const paises = res.data || res; 
        
        const $selectCountry = $('#na-country');
        $selectCountry.empty().append('<option value="">Seleccione un país...</option>');

        $.each(paises, function(index, item) {
            // Usamos item.Codigo como VALUE y item.Pais como TEXTO
            $selectCountry.append($('<option>', {
                value: item.Codigo,
                text: item.Pais
            }));
        });
    });
}

function getstates() {
    const countryVal = $('#na-country').val();
    const $selectState = $('#na-state');

    // Si no hay país seleccionado, limpiamos el select de estados y salimos
    if (!countryVal) {
        $selectState.empty().append('<option value="">Seleccione un estado...</option>');
        return;
    }

    $.getJSON(API_BASE, { action: 'get_states', ctry: countryVal }, function (res) {
        const estados = res.data || res;

        $selectState.empty().append('<option value="">Seleccione un estado...</option>');

        $.each(estados, function(index, item) {
            // Usamos item.Id como VALUE y item.Estado como TEXTO
            $selectState.append($('<option>', {
                value: item.Id,
                text: item.Estado
            }));
        });
    });
}

/* ────────────────────────────────────────────
   SELECT2 — Clientes
──────────────────────────────────────────── */
function initCustomerSelect() {
    $('#customer-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Escribe nombre o email...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: API_BASE,
            dataType: 'json',
            delay: 300,
            data: params => ({ action: 'search_customers', q: params.term }),
            processResults: data => ({
                results: (data.data || []).map(c => ({
                    id: c.id,
                    text: `${c.firstname} ${c.lastname} — ${c.email}`,
                    raw: c
                }))
            }),
        }
    }).on('select2:select', function (e) {
        const c = e.params.data.raw;
        state.customer = c;
        showCustomerInfo(c);
        loadAddresses(c.id);
        enableSection('section-address');
        updateStep4Summary();
    }).on('select2:clear', function () {
        state.customer = null;
        state.address = null;
        $('#address-select').empty().append('<option value="">— Selecciona primero un cliente —</option>');
        $('#customer-info-box').hide();
        $('#address-info-box').hide();
        disableSection('section-address');
        disableSection('section-products');
        disableSection('section-payment');
        updateStep4Summary();
    });
}

function showCustomerInfo(c) {
    $('#customer-info-text').html(`
        <strong>${c.firstname} ${c.lastname}</strong><br>
        <i class="fa fa-envelope fa-xs text-muted me-1"></i>${c.email}<br>
        <i class="fa fa-phone fa-xs text-muted me-1"></i>${c.phone || '—'}
    `);
    $('#customer-info-box').show();
}

/* ────────────────────────────────────────────
   SELECT2 — Direcciones
──────────────────────────────────────────── */
function initAddressSelect() {
    $('#address-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Selecciona una dirección',
        allowClear: true,
    }).on('select2:select', function (e) {
        // En select2 local, los datos del elemento seleccionado se recuperan buscando el option
        const $option = $(this).find(':selected');
        const a = $option.data('raw'); // Recuperamos el objeto guardado en loadAddresses
        
        if (a) {
            state.address = a;
            showAddressInfo(a);
            enableSection('section-products');
            enableSection('section-payment');
            updateStep4Summary();
        }
    }).on('select2:clear', function () {
        state.address = null;
        $('#address-info-box').hide();
        disableSection('section-products');
        disableSection('section-payment');
        updateStep4Summary();
    });
}
function loadAddresses(customerId) {
    return new Promise((resolve) => {
        $.getJSON(API_BASE, { action: 'get_addresses', customer_id: customerId }, function (res) {
            const $sel = $('#address-select');
            $sel.empty().append('<option value="">— Selecciona —</option>');
            
            const direcciones = res.data || [];
            
            direcciones.forEach(a => {
                // Soportar tanto minúsculas como mayúsculas en las llaves del objeto de la BD
                const alias  = a.Alias || a.alias || 'Sin Alias';
                const street = a.Street || a.street || '';
                const city   = a.City || a.city || '';
                const id     = a.Id || a.id;
                const isDef  = a.Is_default || a.is_default;

                const opt = new Option(
                    `${alias} — ${street}, ${city}${isDef == 1 ? ' ★' : ''}`,
                    id, false, isDef == 1
                );
                
                // Guardamos el objeto completo usando tanto jQuery .data() como dataset por seguridad
                $(opt).data('raw', a); 
                $sel.append(opt);
            });
            
            $sel.trigger('change');

            const def = direcciones.find(a => (a.Is_default == 1 || a.is_default == 1));
            if (def) {
                $sel.val(def.Id || def.id).trigger('change');
                state.address = def;
                showAddressInfo(def);
                enableSection('section-products');
                enableSection('section-payment');
                updateStep4Summary();
            }
            resolve();
        });
    });
}
function showAddressInfo(a) {
    $('#address-info-text').html(`
        <strong>${a.Alias}</strong><br>
        ${a.Street}, ${a.Colonia}<br>
        ${a.City}, ${a.State} ${a.Zip}
    `);
    $('#address-info-box').show();
}

/* ────────────────────────────────────────────
   SELECT2 — Productos
──────────────────────────────────────────── */
function initProductSelect() {
    $('#product-search').select2({
        theme: 'bootstrap-5',
        placeholder: 'Escribe nombre de producto...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: API_BASE,
            dataType: 'json',
            delay: 300,
            data: params => ({ action: 'search_products', q: params.term }),
            processResults: data => ({
                results: (data.data || []).map(p => ({
                    id: p.Id,
                    text: `${p.Name} — $${parseFloat(p.SalePrice).toFixed(2)}`,
                    raw: p
                }))
            }),
        }
    });
}

/* ────────────────────────────────────────────
   CART — Acciones
──────────────────────────────────────────── */
async function addProductToCart() {
    const sel = $('#product-search').select2('data')[0];
    if (!sel || !sel.raw) { showBarraMessage('Selecciona un producto', 'alerta'); return; }

    const p = sel.raw;

    if (state.cart.find(i => i.id == p.Id)) {
        showBarraMessage('Este producto ya está en el carrito', 'normal');
        return;
    }

    let stock = null;
    if (p.OnlyRequest != '1') {
        const stockRes = await $.getJSON(API_BASE, { action: 'get_stock', product_id: p.Id });
        stock = stockRes.data ? stockRes.data.total : 0;
        if (stock < 1) {
            showBarraMessage(`Sin existencia para "${p.Name}"`, 'error');
            return;
        }
    }

    const finalPrice = parseFloat(p.SalePrice) - parseFloat(p.Discount || 0);

    state.cart.push({
        id: p.Id,
        name: p.Name,
        salePrice: parseFloat(p.SalePrice),
        discount: parseFloat(p.Discount || 0),
        price: finalPrice,
        qty: 1,
        onlyRequest: p.OnlyRequest == '1',
        stock: stock,
        isSpecial: false,
    });

    renderCart();
    updateTotals();
    $('#product-search').val(null).trigger('change');
}

function renderCart() {
    const $container = $('#cart-items');
    $container.empty();

    if (state.cart.length === 0) {
        $container.html(`
            <div class="p-4 text-center text-muted small" id="cart-empty">
                <i class="fa fa-cart-shopping d-block mb-2 fs-4 text-black-50"></i>
                Sin productos — agrega uno arriba
            </div>`);
        return;
    }

    state.cart.forEach((item, idx) => {
        const stockBadge = item.onlyRequest
            ? `<span class="badge bg-warning-subtle text-warning border prod-badge">Bajo pedido</span>`
            : item.stock > 10
                ? `<span class="badge bg-success-subtle text-success border prod-badge"><i class="fa fa-check me-1"></i>${item.stock} disp.</span>`
                : `<span class="badge bg-warning-subtle text-warning border prod-badge"><i class="fa fa-triangle-exclamation me-1"></i>${item.stock} disp.</span>`;

        const maxQty = item.onlyRequest ? 9999 : item.stock;
        const lineTotal = (item.price * item.qty).toFixed(2);
        const hasDiscount = item.discount > 0;

        const row = `
        <div class="product-row" id="cart-row-${idx}">
            <div style="flex: 2;">
                <div class="prod-name">${item.name}</div>
                <div class="mt-1 d-flex gap-2 flex-wrap align-items-center">
                    ${stockBadge}
                    ${hasDiscount ? `<span class="badge bg-success-subtle text-success border prod-badge">-$${item.discount.toFixed(2)}</span>` : ''}
                    <div class="form-check form-switch ms-1 mb-0 small">
                        <input class="form-check-input" type="checkbox" id="special-${idx}" ${item.isSpecial ? 'checked' : ''} onchange="toggleSpecial(${idx}, this.checked)">
                        <label class="form-check-label text-muted" for="special-${idx}" style="font-size:0.75rem;">Prod. especial</label>
                    </div>
                </div>
            </div>
            <div style="flex: 1;" class="text-md-center">
                <div class="text-muted" style="font-size:.75rem; text-decoration:${hasDiscount?'line-through':''};">${hasDiscount?'$'+item.salePrice.toFixed(2):''}</div>
                <strong class="text-dark">$${item.price.toFixed(2)}</strong>
            </div>
            <div style="flex: 1;" class="text-md-center">
                <input type="number" min="1" max="${maxQty}" value="${item.qty}" class="form-control form-control-sm text-center d-inline-block" style="width:70px;" onchange="updateQty(${idx}, this.value)">
            </div>
            <div style="flex: 1;" class="text-md-center"><strong class="text-dark">$${lineTotal}</strong></div>
            <div style="flex: 0 0 auto;" class="text-end">
                <button class="btn btn-sm btn-light border text-danger" onclick="removeItem(${idx})"><i class="fa fa-trash-can"></i></button>
            </div>
        </div>`;
        $container.append(row);
    });
}

function updateQty(idx, val) {
    const item = state.cart[idx];
    let qty = parseInt(val) || 1;
    if (!item.onlyRequest && qty > item.stock) {
        qty = item.stock;
        showBarraMessage(`Cantidad máxima disponible: ${item.stock}`, 'alerta');
    }
    if (qty < 1) qty = 1;
    state.cart[idx].qty = qty;
    renderCart();
    updateTotals();
}

function removeItem(idx) {
    state.cart.splice(idx, 1);
    renderCart();
    updateTotals();
}

function toggleSpecial(idx, val) {
    state.cart[idx].isSpecial = val;
}

function updateTotals() {
    let subtotal = 0, discountTotal = 0;
    state.cart.forEach(item => {
        subtotal      += item.salePrice * item.qty;
        discountTotal += item.discount  * item.qty;
    });
    const total = subtotal - discountTotal;
    state.subtotal = subtotal;
    state.discountTotal = discountTotal;
    state.total = total;

    const paid =  total - state.balance * 1;
    const balance =  state.balance * 1 ;
    
    
    $('#sum-subtotal').text(`$${subtotal.toFixed(2)}`);
    $('#sum-discount').text(`-$${discountTotal.toFixed(2)}`);

    $('#Balance').val(`${balance.toFixed(2)}`);

    $('#sum-payments').text(`-$${paid.toFixed(2)}`);

    $('#sum-total').text(`$${balance.toFixed(2)}`);
}

/* ────────────────────────────────────────────
   RÉPLICA DE INFORMACIÓN (PASO 4)
──────────────────────────────────────────── */
/* ────────────────────────────────────────────
   CORREGIDO: RÉPLICA DE INFORMACIÓN (PASO 4)
──────────────────────────────────────────── */
function updateStep4Summary() {
    if (state.customer) {
        // Soporta tanto 'firstname' como 'Firstname', etc.
        const fname = state.customer.firstname || state.customer.Firstname || '';
        const lname = state.customer.lastname || state.customer.Lastname || '';
        const email = state.customer.email || state.customer.Email || '';
        $('#step4-customer-summary').html(`${fname} ${lname} (${email})`);
    } else {
        $('#step4-customer-summary').text('— No se ha seleccionado cliente —');
    }

    if (state.address) {
        // Soporta la variación de mayúsculas/minúsculas que viene desde getSale() en la API
        const alias   = state.address.alias || state.address.Alias || 'Sin Alias';
        const street  = state.address.street || state.address.Street || '';
        const city    = state.address.city || state.address.City || '';
        const estado  = state.address.state || state.address.State || '';
        
        $('#step4-address-summary').html(`[${alias}] ${street}, ${city}, ${estado}`);
    } else {
        $('#step4-address-summary').text('— No se ha seleccionado dirección —');
    }
}
/* ────────────────────────────────────────────
   GUARDAR VENTA
──────────────────────────────────────────── */
/* ────────────────────────────────────────────
   CORREGIDO: GUARDAR VENTA (Mapeo Seguro de IDs)
──────────────────────────────────────────── */
async function saveSale() {
    if (!state.customer) { showBarraMessage('Selecciona un cliente', 'error'); return; }
    if (!state.address)  { showBarraMessage('Selecciona una dirección de envío', 'error'); return; }
    if (state.cart.length === 0) { showBarraMessage('Agrega al menos un producto', 'error'); return; }

    // Extraer de forma segura el ID del cliente y de la dirección (soportando id o Id)
    const customerId = state.customer.id || state.customer.Id;
    const addressId  = state.address.id || state.address.Id || state.address.address_id;

    const payload = {
        action: state.mode === 'edit' ? 'update_sale' : 'create_sale',
        sale_id: state.saleId,
        customer_id: customerId,
        address_id: addressId,
        total_amount: state.total.toFixed(2),
        cart_notes: $('#cart-notes').val(),
        // Se preservan los placeholders requeridos por la estructura original de la API
        payer_name: state.customer.firstname || state.customer.Firstname,
        payer_lastname: state.customer.lastname || state.customer.Lastname,
        payer_email: state.customer.email || state.customer.Email,
        payment_method: 'office', 
        payment_status: 'pending',
        device_fingerprint: navigator.userAgent,
        cart_json: JSON.stringify(state.cart),
        items: state.cart.map(i => ({
            product_id: i.id || i.Id,
            product_name_snapshot: i.name || i.Name,
            price: i.price,
            quantity: i.qty,
            is_special_production: i.isSpecial ? 1 : 0,
        })),
    };

    $('#btn-save-sale').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

    try {
        const res = await $.ajax({
            url: API_BASE,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
        });

        if (res.success) {
            state.saleId = res.data.sale_id;
            state.mode = 'edit';
            showBarraMessage(`Venta ${state.mode === 'edit' ? 'actualizada' : 'registrada'} correctamente — #${res.data.sale_id}`, 'exito');
            $('#saved-sale-id').text(`#${res.data.sale_id}`);
            $('#sale-saved-info').show();
            $('#btn-save-label').text('Guardar cambios');
            updateSaleBadge();
            // Recargar datos para traer la tabla de pagos si aplica
            loadSaleDirect(res.data.sale_id);
        } else {
            showBarraMessage(res.message || 'Error al guardar', 'error');
        }
    } catch (e) {
        showBarraMessage('Error de conexión con la API', 'error');
    } finally {
        $('#btn-save-sale').prop('disabled', false).html(`<i class="fa fa-floppy-disk me-1"></i><span id="btn-save-label">${state.mode === 'edit' ? 'Guardar cambios' : 'Registrar venta'}</span>`);
    }
}

// Función auxiliar de recarga tras guardar
async function loadSaleDirect(id) {
    $('#lookup-sale-id').val(id);
    loadSale();
}

/* ────────────────────────────────────────────
   CARGAR VENTA (Edición)
──────────────────────────────────────────── */
async function loadSale() {
    const id = parseInt($('#lookup-sale-id').val());
    if (!id) { showBarraMessage('Ingresa un número de venta válido', 'alerta'); return; }

    $('#lookup-status').html('<span class="spinner-border spinner-border-sm"></span>');

    try {
        const res = await $.getJSON(API_BASE, { action: 'get_sale', sale_id: id });
        $('#token').val(id);
        if (!res.success) { showBarraMessage(res.message || 'Venta no encontrada', 'error'); $('#lookup-status').html(''); return; }

        const s = res.data;
        state.mode   = 'edit';
        state.saleId = s.id;
        state.balance = s.balance;

        $('#cart-notes').val(s.cart_notes);

        state.customer = s.customer;
        
        const optC = new Option(`${s.customer.firstname} ${s.customer.lastname} — ${s.customer.email}`, s.customer.id, true, true);
        $('#customer-select').append(optC).trigger('change');
        showCustomerInfo(s.customer);

        await loadAddresses(s.customer.id);
        $('#address-select').val(s.address_id).trigger('change');

        state.address = s.address;        
        updateStep4Summary();

        state.cart = (s.items || []).map(it => ({
            id: it.product_id,
            name: it.product_name_snapshot,
            salePrice: parseFloat(it.price),
            discount: 0,
            price: parseFloat(it.price),
            qty: parseInt(it.quantity),
            onlyRequest: false,
            stock: null,
            isSpecial: it.is_special_production == 1,
        }));
        renderCart();
        updateTotals();

        // ── Renderizado del Historial de Pagos Recibidos ──
        const $tableBody = $('#payments-table-body');
        $tableBody.empty();
        
        if (s.payments && s.payments.length > 0) {
            s.payments.forEach(p => {
                const badgeStatus = p.Estatus === 'completed' || p.Estatus === 'Aprobado'
                    ? '<span class="badge bg-success-subtle text-success border px-2">Aprobado</span>'
                    : '<span class="badge bg-warning-subtle text-warning border px-2">' + (p.Estatus || 'Pendiente') + '</span>';
                
                const row = `
                    <tr>
                        <td><strong>${p.Folio || '—'}</strong><div class="text-muted text-xs font-monospace">${p.TransactionId || ''}</div></td>
                        <td><span class="fw-semibold">${p.Platform || 'Manual'}</span><br><small class="text-muted">${p.Type || ''}</small></td>
                        <td class="text-muted">${p.DateTime || '—'}</td>
                        <td><strong class="text-dark">$${parseFloat(p.Amount).toFixed(2)}</strong> <small class="text-muted text-xs">${p.Currency || 'MXN'}</small></td>
                        <td>${badgeStatus}</td>
                        <td class="text-muted">${p.Usuario || '—'}</td>
                    </tr>
                `;
                $tableBody.append(row);
            });
            $('#container-payments-list').show();
        } else {
            $tableBody.html('<tr><td colspan="6" class="text-muted py-3">No hay pagos registrados para esta venta.</td></tr>');
            $('#container-payments-list').show();
        }

        enableSection('section-address');
        enableSection('section-products');
        enableSection('section-payment');
        updateStep4Summary();
        updateSaleBadge();
        $('#btn-save-label').text('Guardar cambios');
        $('#lookup-status').html('<span class="text-success small"><i class="fa fa-check me-1"></i>Cargado</span>');

    } catch (e) {
        showBarraMessage('Error al cargar la venta', 'error');
    }
}

/* ────────────────────────────────────────────
   MODALES Y HELPERS
──────────────────────────────────────────── */
function openNewCustomerModal() {
    ['nc-firstname','nc-lastname','nc-email','nc-phone','nc-state','nc-password'].forEach(id => $(`#${id}`).val(''));
    new bootstrap.Modal('#modalNewCustomer').show();
}

async function saveNewCustomer() {
    const data = {
        action: 'create_customer',
        firstname: $('#nc-firstname').val().trim(),
        lastname:  $('#nc-lastname').val().trim(),
        email:     $('#nc-email').val().trim(),
        phone:     $('#nc-phone').val().trim(),
        state:     $('#nc-state').val().trim(),
        password:  $('#nc-password').val(),
    };
    if (!data.firstname || !data.lastname || !data.email || !data.phone) {
        showBarraMessage('Completa los campos obligatorios', 'alerta'); return;
    }
    try {
        const res = await $.ajax({ url: API_BASE, method: 'POST', contentType:'application/json', data: JSON.stringify(data), dataType:'json' });
        if (res.success) {
            showBarraMessage('Cliente creado correctamente', 'exito');
            bootstrap.Modal.getInstance('#modalNewCustomer').hide();
            state.customer = res.data;
            const opt = new Option(`${res.data.firstname} ${res.data.lastname} — ${res.data.email}`, res.data.id, true, true);
            $('#customer-select').append(opt).trigger('change');
            showCustomerInfo(res.data);
            loadAddresses(res.data.id);
            enableSection('section-address');
        }
    } catch(e) { showBarraMessage('Error de conexión', 'error'); }
}

function openNewAddressModal() {
    if (!state.customer) { showBarraMessage('Selecciona primero un cliente', 'alerta'); return; }
    ['na-alias','na-country','na-state','na-city','na-colonia','na-street','na-zip','na-references'].forEach(id => $(`#${id}`).val('') );
    $('#na-default').prop('checked', false);
    new bootstrap.Modal('#modalNewAddress').show();
}

async function saveNewAddress() {
    const data = {
        action: 'create_address',
        customer_id: state.customer.id,
        alias:       $('#na-alias').val().trim(),
        country:     $('#na-country').val().trim(),
        state:       $('#na-state').val().trim(),
        city:        $('#na-city').val().trim(),
        street:      $('#na-street').val().trim(),
        colonia:     $('#na-colonia').val().trim(),
        zip:         $('#na-zip').val().trim(),
        references:  $('#na-references').val().trim(),
        is_default:  $('#na-default').is(':checked') ? 1 : 0,
    };
    if (!data.alias || !data.state || !data.city || !data.street || !data.colonia || !data.zip) {
        showBarraMessage('Completa los campos obligatorios', 'alerta'); return;
    }
    try {
        const res = await $.ajax({ url: API_BASE, method:'POST', contentType:'application/json', data: JSON.stringify(data), dataType:'json' });
        if (res.success) {
            showBarraMessage('Dirección guardada', 'exito');
            bootstrap.Modal.getInstance('#modalNewAddress').hide();
            await loadAddresses(state.customer.id);
            $('#address-select').val(res.data.id).trigger('change');
        }
    } catch(e) { showBarraMessage('Error de conexión', 'error'); }
}

function enableSection(id)  { $(`#${id}`).css({ opacity:'1', 'pointer-events':'all' }); }
function disableSection(id) { $(`#${id}`).css({ opacity:'.5', 'pointer-events':'none' }); }

function updateSaleBadge() {
    const badge = $('#sale-mode-badge');
    if (state.mode === 'edit' && state.saleId) {
        badge.text(`✏️ Editando venta #${state.saleId}`).show();
    } else {
        badge.text('').hide();
    }
}

function resetAll() {
    if (!confirm('¿Iniciar una nueva venta? Se perderán los cambios no guardados.')) return;
    state.mode = 'new'; state.saleId = null; state.customer = null; state.address = null; state.cart = [];
    $('#customer-select').val(null).trigger('change');
    $('#address-select').empty().append('<option value="">— Selecciona primero un cliente —</option>');
    $('#customer-info-box, #address-info-box, #sale-saved-info').hide();
    
    // Ocultar y limpiar tabla de pagos
    $('#container-payments-list').hide();
    $('#payments-table-body').empty();

    $('#cart-notes').val('');
    $('#lookup-sale-id, #lookup-status').val('');
    $('#btn-save-label').text('Registrar venta');
    ['section-address','section-products','section-payment'].forEach(disableSection);
    renderCart();
    updateTotals();
    updateStep4Summary();
    updateSaleBadge();
}

/* Interfaz Unificada del Manejo de Notificaciones mediante el barra-mensajes de lead.css */
function showBarraMessage(msg, type='normal') {
    const classes = ['msg-minimal-normal', 'msg-minimal-exito', 'msg-minimal-error', 'msg-minimal-alerta'];
    const $barra = $('#barra-mensajes');
    const $contenido = $('#barra-contenido');

    $contenido.removeClass(classes.join(' '));

    if (type === 'exito') $contenido.addClass('msg-minimal-exito');
    else if (type === 'error') $contenido.addClass('msg-minimal-error');
    else if (type === 'alerta') $contenido.addClass('msg-minimal-alerta');
    else $contenido.addClass('msg-minimal-normal');

    $('#barra-texto').text(msg);
    $barra.slideDown(250);

    // Auto ocultado a los 4 segundos
    setTimeout(() => { $barra.slideUp(200); }, 4000);
}


document.addEventListener("DOMContentLoaded", function() {
    <?php
    if (isset($_GET['IdSale'])){
        ?>
            $('#lookup-sale-id').val(<?= $_GET['IdSale'] ?>);
            loadSale();
        <?php
    }
    ?>

});


$(document).ready(function() {
    // 1. Cargar los países al iniciar la página
    getcountry();

    // 2. Escuchar cuando cambie el país para cargar sus estados correspondientes
    $('#na-country').on('change', function() {
        getstates();
    });
});
</script>


<?php if ($paypal_account['Active'] ==1):?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= $paypal_account['Id'] ?>&currency=<?= $account['Currency'] ?>&enable-funding=venmo,paylater&buyer-country=<?= $account['Pais'] ?>"></script>

<script>
    const paypalExtraPctj = <?= json_encode((float) ($paypal_account['percentage'] ?? 0) / 100) ?>;

    function getPayPalPaymentAmounts() {
        const baseAmountCents = Math.round((parseFloat($('#monto_pago').val()) || 0) * 100);
        const extraAmountCents = paypalExtraPctj > 0
            ? Math.round(baseAmountCents * paypalExtraPctj)
            : 0;

        return {
            baseAmount: (baseAmountCents / 100).toFixed(2),
            extraAmount: (extraAmountCents / 100).toFixed(2),
            totalAmount: ((baseAmountCents + extraAmountCents) / 100).toFixed(2)
        };
    }

    paypal.Buttons({
        // Fuerza el flujo de captura inmediata
        commit: true, 

        // Estilo de los botones (opcional, para adaptarlos visualmente)
        style: {
            layout: 'vertical',
            color:  'blue',
            shape:  'rect',
            label:  'paypal'
        },

        createOrder: function(data, actions) {
            const amounts = getPayPalPaymentAmounts();
            const currency = '<?= $account['Currency'] ?>';
            const items = [{
                name: 'Pay Sale ' + $('#token').val(),
                sku: 'Pay',
                unit_amount: {
                    currency_code: currency,
                    value: amounts.baseAmount
                },
                quantity: '1'
            }];

            if (parseFloat(amounts.extraAmount) > 0) {
                items.push({
                    name: 'Cargo por tarjeta (' + (paypalExtraPctj * 100).toFixed(2) + '%)',
                    sku: 'CardFee',
                    unit_amount: {
                        currency_code: currency,
                        value: amounts.extraAmount
                    },
                    quantity: '1'
                });
            }
            
            return actions.order.create({
                application_context: {
                    shipping_preference: 'NO_SHIPPING' // Sin dirección de envío
                },
                purchase_units: [{
                    amount: {
                        currency_code: currency,
                        value: amounts.totalAmount,
                        breakdown: {
                            item_total: {
                                currency_code: currency,
                                value: amounts.totalAmount
                            }
                        }
                    },
                    items: items
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(orderData) {
            
                $('#monto').val(getPayPalPaymentAmounts().totalAmount)
                $('#tipo-pago').val($('#tipo_pago').val())
                $('#referencia').val($('#refcia').val())            

                var datosFormulario = $('#payment-form').serialize();
                datosFormulario += '&orderID=' + encodeURIComponent(data.orderID);
                $.ajax({
                    url: 'processpayment_paypal_sale.php',
                    method: 'POST',
                    dataType: "json",
                    data: datosFormulario,
                    success: function(respuestaBackend) {
                        if(respuestaBackend.status === 'success') {
                            const divpaypal = document.getElementById('paypal-button-container');
                            divpaypal.classList.add('d-none');                        
                            lanzarMensaje("<?= Trd(148) ?>","exito",5000);
                            //render_pagos(respuestaBackend)
                            //$btn.prop("disabled", false).text("Aplicar Pago");
                            loadSale()
                        }
                    },
                    error: function(err) {
                        var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno.";
                        lanzarMensaje(`❌ ${errorMsg}`,"error",5000);
                        $btn.prop("disabled", false).text("Aplicar Pago");                        
                    }
                });
            });
        }
    }).render('#paypal-button-container');
</script>
<?php endif;?>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const inputMonto = document.getElementById('monto_pago');
    const btnPago = document.getElementById('pay-button');
    const msgError = document.getElementById('monto_error');

    inputMonto.addEventListener('input', function() {
        // Obtenemos el balance. Si es un span/div usamos textContent, si es un input usamos value.
        const balanceElement = document.getElementById('Balance');
        const balance = parseFloat(balanceElement.value || balanceElement.textContent) || 0;
        const monto = parseFloat(this.value) || 0;

        if (monto > balance) {
            // Estilos de error
            this.classList.add('is-invalid');
            msgError.classList.remove('d-none');
            if(btnPago) btnPago.disabled = true;
        } else {
            // Estilos normales
            this.classList.remove('is-invalid');
            msgError.classList.add('d-none');
            if(btnPago) btnPago.disabled = false;

            $('#display-saldo-hoy').html( formatter.format(balance) );
            $('#display-saldo-pago').html( formatter.format(balance - monto));
            $('#display-pago-hoy').html(formatter.format(monto) );
        }
    });
});

document.getElementById('tipo_pago').addEventListener('change', function() {
    const val = this.value;
    const divRef = document.getElementById('div_referencia');
    const divTar = document.getElementById('div_tarjeta');
    const divpaypal = document.getElementById('paypal-button-container');
    const paybuttons = document.getElementById('pay-buttons');

    // Ocultar todo primero
    divRef.classList.add('d-none');
    divTar.classList.add('d-none');
    divpaypal.classList.add('d-none');
    paybuttons.classList.add('d-none');

    

    // Mostrar según selección
    if (val === 'efectivo' || val === 'transferencia') {
        divRef.classList.remove('d-none');
        paybuttons.classList.remove('d-none');
    } else if (val === 'tarjeta') {
        divTar.classList.remove('d-none');
        paybuttons.classList.remove('d-none');
    } else if (val === 'paypal') {
        divpaypal.classList.remove('d-none');
    }
    //paypal-button-container
});

document.querySelectorAll('.only-numbers').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

document.getElementById('btn_toggle_pagos').addEventListener('click', function(e) {
    e.preventDefault();
    //if ($('#token').val() == 0){
    //    lanzarMensaje('<?= Trd(163) ?>','alerta',4000)
    //    return;
    //}
    const divPagos = document.getElementById('div_pagos');
    const textoBoton = document.getElementById('texto_boton');

    if (divPagos.style.display === 'none') {
        // Mostrar
        //alert('show')
        divPagos.style.display = 'block';
        textoBoton.textContent = '<?= Trd(99) ?>';
    } else {
        // Ocultar
        //alert('hide')
        divPagos.style.display = 'none';
        textoBoton.textContent = '<?= Trd(118) ?>';
    }
});


$('#monto_pago').on('input', function() {
    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
});

$('#pay-button').on('click', function(e) {
    e.preventDefault(); // Detenemos el envío automático del formulario
    
    const btn = $(this);
    const tipoPago = $('#tipo_pago').val();
    const monto = parseFloat($('#monto_pago').val()) || 0;
    const balance = parseFloat($('#Balance').val() || $('#Balance').text()) || 0;

    // 1. Validaciones básicas de negocio
    if (!tipoPago) {
        //alert("Por favor seleccione un método de pago.");
        lanzarMensaje("<?= Trd(146) ?>","alerta",5000);
        return;
    }
    if (monto <= 0 || monto > balance) {
        //alert("Monto inválido o excede el saldo pendiente.");
        lanzarMensaje("<?= Trd(147) ?>","alerta",5000);
        return;
    }

    // Bloquear botón para evitar múltiples clics
    btn.prop('disabled', true).text('<?= Trd(152) ?>');

    // 2. Lógica según el tipo de pago
    if (tipoPago === 'tarjeta') {
        enviarPagoAlServidor('tarjeta');
    } else {
        enviarPagoAlServidor(null); 
    }
});


function enviarPagoAlServidor(tokenId) {
    $('#monto').val($('#monto_pago').val())
    $('#tipo-pago').val($('#tipo_pago').val())
    $('#referencia').val($('#refcia').val())
    
    if (tokenId == 'tarjeta'){
        pago_t();
    }
    else{
        var $btn = $('#pay-button');

        var datosFormulario = $('#payment-form').serialize();
        $.ajax({
            type: "POST",
            url: "processpayment_cash_sale.php",
            data: datosFormulario,
            dataType: "json",
            success: function(respuestaBackend) {
                if(respuestaBackend.status === 'success') {
                    // Lógica de éxito
                    $('#collapseForm').collapse('hide');
                    //alert("Pago procesado");
                    lanzarMensaje("<?= Trd(148) ?>","exito",5000);
                    //render_pagos(respuestaBackend)
                    $btn.prop("disabled", false).text("Aplicar Pago");
                    loadSale()
                }
            },
            error: function(err) {
                var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno.";
                lanzarMensaje(`❌ ${errorMsg}`,"error",5000);
                $btn.prop("disabled", false).text("Aplicar Pago");                        
            }
        });    

    }

}

function render_pagos(response){
    const listado = document.getElementById('listado_pagos');
    const pagos = response.pagos;

    // Limpiar la tabla antes de redibujar
    listado.innerHTML = '';

    // Iterar sobre los pagos y crear las filas
    pagos.forEach(pay => {
        const row = `
            <tr>
                <td style="text-align: center;">${pay.Id}</td>
                <td style="text-align: center;">${pay.DateTime}</td>
                <td style="text-align: center;">${pay.Platform}</td>
                <td style="text-align: right;">$${pay.Amount}</td>
                <td style="text-align: center;">${pay.Currency}</td>
                <td style="text-align: center;">${pay.TransactionId}</td>
                <td style="text-align: center;">${pay.Usuario}</td>
            </tr>
        `;
        listado.insertAdjacentHTML('beforeend', row);
    });
    
    $('#AmountPaid').val( parseFloat($('#AmountPaid').val()) + parseFloat($('#monto_pago').val()));
    $('#tipo_pago').val('')
    $('#refcia').val('');
    $('#monto_pago').val('0.00');

    recalculate_totals();

    $('#display-pago-hoy').html(formatter.format('0.00') );    

}

</script>

<?php if ($PayPlatform == 'OPAY'){
        $stmt = $db->prepare("SELECT * FROM  opay_account");
        $stmt->execute();
        $opay_account = $stmt->fetch();
    ?>
    <script>
            function pago_t(){

            // Configuración Openpay
            OpenPay.setId('<?php echo $opay_account['Id'];?>');
            OpenPay.setApiKey('<?php echo $opay_account['PublicKey'];?>');
            OpenPay.setSandboxMode(true);
            OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");


            // --- PROCESAR PAGO ---
            //$('#pay-button').on('click', function(e) {
            //    e.preventDefault();
                
                // 1. Referencia al botón para feedback visual
                var $btn = $('#pay-button');
            //    $btn.prop("disabled", true).text("Procesando...");

                // 2. Validación manual de campos requeridos (Nombre, Email, etc.)
                let valid = true;
                $('#payment-form input[required]').each(function() {
                    if ($(this).val().trim() === "") {
                        $(this).addClass('is-invalid'); // Clase de Bootstrap para error
                        valid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!valid) {
                    //alert("Por favor completa los datos del cliente marcados como obligatorios.");
                    lanzarMensaje("<?= Trd(149) ?>","alerta",5000);
                    $btn.prop("disabled", false).text("Aplicar Pago");
                    return;
                }

                // 3. Crear Token con Openpay
                // extractFormAndCreate lee automáticamente los campos con 'data-openpay-card'
                OpenPay.token.extractFormAndCreate('payment-form', function(res) {
                    // --- CASO ÉXITO: Token generado ---
                    var token_id = res.data.id;
                    $('#token_id').val(token_id);

                    // Enviamos los datos al backend.php mediante AJAX
                    var datosFormulario = $('#payment-form').serialize();

                    $.ajax({
                        type: "POST",
                        url: "processpayment_sale.php",
                        data: datosFormulario,
                        dataType: "json",
                        success: function(respuestaBackend) {
                            if(respuestaBackend.status === 'success') {
                                //alert("¡Pago Exitoso! ID de transacción: " + respuestaBackend.transaction_id);
                                $('#collapseForm').collapse('hide');
                                //alert("Pago procesado");
                                lanzarMensaje("<?= Trd(150) ?>","exito",5000);
                                //render_pagos(respuestaBackend)
                                //$btn.prop("disabled", false).text("Aplicar Pago");
                                loadSale()
                            //} else if (respuestaBackend.status === 'pending') {
                                // Manejo de 3D Secure (Si el banco pide autenticación extra)
                                //window.location.href = respuestaBackend.url;
                            }
                            $btn.prop("disabled", false).text("Aplicar Pago");                            
                        },
                        error: function(err) {
                            var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno en el servidor.";
                            //alert("Error en el cobro: " + errorMsg);
                            lanzarMensaje(`❌ ${errorMsg}`,"error",5000);
                            $btn.prop("disabled", false).text("Aplicar Pago");
                        }
                    });

                }, function(err) {
                    // --- CASO ERROR: Fallo al generar el token (ej. tarjeta inválida) ---
                    var desc = err.data.description != undefined ? err.data.description : err.message;
                    lanzarMensaje(`❌ ${desc}`,"error",5000);
                    $btn.prop("disabled", false).text("Aplicar Pago");
                });
            }
    </script>
<?php }
    else{

    $stmt = $db->prepare("SELECT * FROM  square_account");
    $stmt->execute();
    $square_account = $stmt->fetch();       

?>
    <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
    <script>
        const appId = '<?php echo $square_account['Id'];?>';
        const locId = '<?php echo $square_account['LocalId'];?>';

        // Declaramos la variable 'card' en el scope superior para que todas las funciones la usen
        let card; 

        async function initSquare() {
            const payments = Square.payments(appId, locId);
            card = await payments.card(); // Asignamos a la variable global
            await card.attach('#card-container');
        }

        // Ahora esta función es GLOBAL y puede ser llamada por cualquier botón
        async function pago_t() {
            try {
                const result = await card.tokenize(); // Usa la variable 'card' global
                if (result.status === 'OK') {
                    await procesarPago(result.token);
                } else {
                    console.error('Error en tokenización:', result.errors);
                }
            } catch (e) {
                console.error('Error al tokenizar:', e);
            }
        }

        async function procesarPago(token) {
            $('#token_id').val(token);

            var datosFormulario = $('#payment-form').serialize();
            $.ajax({
                type: "POST",
                url: "processpayment_square_sale.php",
                data: datosFormulario,
                dataType: "json",
                success: function(respuestaBackend) {
                    if(respuestaBackend.status === 'success') {
                        $('#collapseForm').collapse('hide');
                        lanzarMensaje("<?= Trd(151) ?>","exito",5000);
                        //render_pagos(respuestaBackend)
                        //$btn.prop("disabled", false).text("Aplicar Pago");
                        loadSale()
                    }
                },
                error: function(err) {
                    var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno.";
                    lanzarMensaje(`❌ ${errorMsg}`,"error",5000);
                    $btn.prop("disabled", false).text("Aplicar Pago");                        
                }
            });
        }

        initSquare();
    </script>
<?php }
?>
<script>
function lanzarMensaje(texto, tipo = 'normal', duracion = 4000) {
    alert(texto);
}

const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
});

</script>

</body>
</html>