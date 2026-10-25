<?php
ob_start();
session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'valid_login.php';
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    $IdTabla = $_GET['Id'];
    $Id2 = '';
    if (isset($_GET['Id2']))
        $Id2 = $_GET['Id2'];
    
    $Idioma = $_SESSION['Idioma'];
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'crud' AND Idioma = ? ORDER BY Id";            
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

$Idioma = $_SESSION['Idioma'] ?? 'es';
$isEn = (strtolower(substr(trim($Idioma), 0, 2)) === 'en');

$t = [
    'page_title' => $isEn ? 'Recursive pricing function' : 'Función recursiva de precios',
    'funcion_precios' => $isEn ? 'Pricing function' : 'Función de precios',
    'funcion_precios_edit' => $isEn ? 'Pricing function' : 'Función de precios',
    'funcion_precios_third' => $isEn ? 'Pricing function' : 'Función de precios',
    'limpiar' => $isEn ? 'Clear' : 'Limpiar',
    'precio_vs_tiempo' => $isEn ? 'Price vs Time' : 'Precio vs Tiempo',
    'precio_vs_tiempo_edit' => $isEn ? 'Price vs Time ' : 'Precio vs Tiempo ',
    'precio_vs_tiempo_third' => $isEn ? 'Price vs Time ' : 'Precio vs Tiempo ',
    'horas' => $isEn ? 'Hours' : 'Horas',
    'dias' => $isEn ? 'Days' : 'Días',
    'semanas' => $isEn ? 'Weeks' : 'Semanas',
    'json_precio' => $isEn ? 'Price JSON' : 'JSON del precio',
    'json_precio_edit' => $isEn ? 'Price JSON ' : 'JSON del precio ',
    'json_precio_third' => $isEn ? 'Price JSON' : 'JSON del precio',
    'aplicar_json' => $isEn ? 'Apply JSON' : 'Aplicar JSON',
];

    include_once 'head.php';
?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/choices.js@9.0.1/public/assets/styles/choices.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>     
<?php
    if ($IdTabla == 'item_prices' OR $IdTabla == 'products'){
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>';
    $query = "select Traduccion FROM programas_traduccion where Programa = 'prices' AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $Idioma);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones2[]=''; // El índice 0 queda vacío según tu lógica original
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones2[]=$registro['Traduccion'];
        }
    }    
    function Trd_2($Id){
        global $Traducciones2;
        echo isset($Traducciones2[$Id]) ? $Traducciones2[$Id] : "Trd_2[$Id]";
    }    
?>

<?php
    }
?>
  <style>
        .table-container {
            overflow-x: auto;
        }
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .metadata-info {
            font-size: 14px;
            color: #6c757d;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .page-link {
            cursor: pointer;
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
            .metadata-info {
                text-align: center;
                width: 100%;
            }
        }

/* Bordes discontinuos finos y redondeados */
.border-dashed {
    border: 2px dashed #cbd5e1 !important;
}

/* Efecto visual interactivo */
#dropzone_bg:hover, 
div[id^="dropzone_"]:hover {
    border-color: #3b82f6 !important;
    background-color: #f8fafc !important;
}

/* Evita interferencias con el texto interior */
.pointer-events-none {
    pointer-events: none;
}        

    </style>    
</head>

<body>
    <?php
        include_once 'nav.php';
        $allowed_tables = [
            'clientes',
            'customers',
            'sale_customers',
            'sale_customer_addresses',
            'categories',
            'scategories',
            'customer_type',
            'wharehouses',
            'gifcard',
            'products',
            'distance_charges',
            'account',
            'venues',
            'organizations',
            'surfaces',
            'item_prices',
            'document_center',
            'price_lists',
            'discounts',
            'inventory_stock',
            'operators',
            'referals',
            'vehicles',
            'schedules'
        ];
        if (!in_array($IdTabla, $allowed_tables)) {
            die("<h3>".Trd(3)."</h3></div></body></html>");
        }   
    ?> 
    <div class="container mt-4 ">
        <h3 class="mb-4"> <?php echo Trd(1);?> <?php echo $IdTabla?></h3>
    </div>
    <?php
        include_once 'add_listado.php';     

        include_once 'f_add_form.php'; 
        include_once 'add_form.php'; 
        
        include_once 'f_edit_form.php';        
        include_once 'edit_form.php'; 

        include_once 'delete_form.php'; 
        if ($IdTabla == 'products'){
            add_listado($IdTabla);
?>
        <div class="container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $IdTabla?>_clone" style="display: none; max-width: 100%;">
            
            <h4 class="mb-4 text-dark fw-bold border-bottom pb-2">
                <i class="fa-solid fa-clone text-primary me-2 small"></i>Clonar producto:
            </h4>
            
            <form name="add_<?php echo $IdTabla?>_clone" id="add_<?php echo $IdTabla?>_clone" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5 col-lg-5">
                        <label Bresfor="CodigoOrigen_clone" class="form-label text-secondary fw-semibold small mb-2">Producto a Clonar</label>
                        <?php
                            echo '<select name="CodigoOrigen_clone" id="CodigoOrigen_clone" class="selectpicker form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">';
                            $Valores_Productos ='';
                            $query ="SELECT Id,Name FROM products ";
                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            if ($tabla_dts) {
                                $Valores_Productos .='<option value = "" selected> ... </option>';
                                foreach ($tabla_dts as $tabla_dt) {
                                    $Valor         = $tabla_dt['Id'];
                                    $Descripcion   = $tabla_dt['Name'];
                                    $Valores_Productos.='<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                }
                            }
                            echo $Valores_Productos;
                            echo '</select>';
                        ?>
                    </div>
                    <div class="col-12 col-md-5 col-lg-5">
                        <label for="Name_clone" class="form-label text-secondary fw-semibold small mb-2">Nombre Producto</label>
                        <input name="Name_clone" id="Name_clone" class="form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium" type="text" required minlength="5" maxlength="255" placeholder="Ingresa el nombre para el nuevo producto">
                    </div>                        
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Clonar('add','<?php echo $IdTabla?>')">
                            <i class="fa-solid fa-copy"></i> Clonar
                        </button>                
                    </div>                        
                </div>
            </form>
        </div>
<?php
            add_form($IdTabla,$Idioma,'M',true);

?>
        <div class="container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $IdTabla?>_clone_edit" style="display: none; max-width: 100%;">
            
            <h4 class="mb-4 text-dark fw-bold border-bottom pb-2">
                <i class="fa-solid fa-pen-to-square text-primary me-2 small"></i>Clonar producto a:
            </h4>
            
            <form name="add_<?php echo $IdTabla?>_clone_edit" id="add_<?php echo $IdTabla?>_clone_edit" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-10 col-lg-10">
                        <input name="product_clone_edit" id="product_clone_edit" type="hidden">
                        <label for="Name_clone_edit" class="form-label text-secondary fw-semibold small mb-2">Nombre Producto</label>
                        <input name="Name_clone_edit" id="Name_clone_edit" class="form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium" type="text" required minlength="5" maxlength="255" placeholder="Ingresa el nombre para el nuevo producto">
                    </div>                        
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Clonar('edit','<?php echo $IdTabla?>')">
                            <i class="fa-solid fa-copy"></i> Clonar
                        </button>                
                    </div>                        
                </div>
            </form>
        </div>

<?php            

            edit_form($IdTabla,$Idioma,'I',true);

            echo '<br><h4 class="mb-4">'.Trd(29).'</h4>';
            echo '<div class="container" id="edit_form_'.$IdTabla.'" style="">';
            $TablePrices ='products_item_price';
            ?>
                <form name="edit_<?php echo $TablePrices?>" id="edit_<?php echo $TablePrices?>" class="needs-validation" novalidate>
                    <input type="hidden" name="edit_Producto" id="edit_Producto" >
                    <div class="row">
                        <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                            <?php
                            $Campo = "edit_producto_origen_pl";
                            $TablaDts = "products";
                            $CampoValor = "Id";
                            $CampoDescripcion = "Name";
                            $Filtro =   "";
                            $Titulo = Trd(26);
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="selectpicker form-control border-1  rounded " style="" onchange="get_price(this.value)" >';
                                    $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                                    $columnExists = $checkColumn->fetch();                            
                                    if ($columnExists) {
                                            $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$Idioma' $Filtro ";
                                    }
                                    else{
                                        $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                                    }
                                    $stmt_dts = $db->prepare($query);
                                    $stmt_dts->execute();
                                    $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                                    if ($tabla_dts) {
                                        echo '<option value = "" selected> ... </option>';
                                        foreach ($tabla_dts as $tabla_dt) {
                                            $Valor         = $tabla_dt[$CampoValor];
                                            $Descripcion   = $tabla_dt[$CampoDescripcion];
                                            echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                        }
                                    }
                            echo '</select>';
                            ?>
                        </div>

                        <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                            <?php
                            $Campo = "edit_ItemPrice";
                            $TablaDts = "item_prices";
                            $CampoValor = "Id";
                            $CampoDescripcion = "PriceName";
                            $Filtro =   "";
                            $Titulo = Trd(27);
                                echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="form-control border-1 rounded" onchange="get_json_price(this.value)">';

                                $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                                $columnExists = $checkColumn->fetch();

                                if ($columnExists) {
                                    $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$Idioma' $Filtro";
                                } else {
                                    $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1 $Filtro";
                                }

                                $stmt_dts = $db->prepare($query);
                                $stmt_dts->execute();
                                $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);

                                if ($tabla_dts) {
                                    echo '<option value="" selected> ... </option>';
                                    foreach ($tabla_dts as $tabla_dt) {
                                        $Valor       = $tabla_dt[$CampoValor];
                                        $Descripcion = $tabla_dt[$CampoDescripcion];
                                        echo '<option value="' . $Valor . '">' . $Descripcion . '</option>';
                                    }
                                }

                                echo '</select>';
                            ?>
                        </div>
                        <div class='col-12 col-sm-12 col-md-8 col-lg-2 col-xl-2 col-xxl-2'>
                            <br>
                            <div class="form-check  form-switch">
                                <input name="edit_price_name" id="edit_price_name" type="hidden">
                                <input name="edit_new" id="edit_new" type="hidden">
                                <input name="edit_Taxable" id="edit_Taxable" class="form-check-input" type="checkbox">
                                <label class="form-check-label" for="edit_Taxable">
                                    <?php echo Trd(28)?>
                                </label>
                            </div>
                        </div>

                        <div class='col-12 col-sm-12 col-md-8 col-lg-2 col-xl-2 col-xxl-2'>
                            <br>
                            <button class="btn btn-primary fw-semibold px-4 rounded-3 shadow-sm" type="submit">
                                <i class="fa-solid fa-floppy-disk me-1"></i><?php echo Trd(7)?>
                            </button>
                        </div>                        

                    </div>

  <div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0"><?= $t['funcion_precios_third'] ?></h1>
      <button type="button" id="third_btnClear" class="btn btn-outline-danger btn-sm" onclick="third_clear()"><?= $t['limpiar'] ?></button>
    </div>
    <div id="third_casos" class="vstack gap-2 mb-4"></div>
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['precio_vs_tiempo_third'] ?></h2>
            <select id="third_eje" class="form-select form-select-sm w-auto">
              <option value="1"><?= $t['horas'] ?></option>
              <option value="24"><?= $t['dias'] ?></option>
              <option value="168"><?= $t['semanas'] ?></option>
            </select>
          </div>
          <div style="height:320px"><canvas id="third_grafica"></canvas></div>
        </div></div>
      </div>
    </div>
    <div class="row g-3" style="display: none;">
      <div class="col-lg-12">
        <div class="card h-100"><div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['json_precio_third'] ?></h2>
            <button type="button" id="third_btnAplicar" class="btn btn-sm btn-outline-primary"><?= $t['aplicar_json'] ?></button>
          </div>
          <textarea id="edit_JsonPrice" name="JsonPrice" class="form-control font-monospace flex-grow-1" rows="10" spellcheck="false"></textarea>
          <div id="third_jsonErr" class="text-danger small mt-1"></div>
        </div></div>
      </div>
    </div>
  </div>

                </form>            
            <?php
            echo "</div>";


                $Tabla = 'products_categories';

                echo '<style>
                    .btn-toggle-custom {
                        cursor: pointer;
                        background-color: #f8f9fa;
                        padding: 12px 20px;
                        border-radius: 6px;
                        border-left: 4px solid #0d6efd; /* Línea azul interactiva a la izquierda */
                        transition: all 0.2s ease;
                    }
                    .btn-toggle-custom:hover {
                        background-color: #e9ecef;
                    }
                    /* Añade un signo + o - dinámico con CSS puro */
                    .btn-toggle-custom::after {
                        content: "＋";
                        float: right;
                        font-weight: bold;
                        color: #6c757d;
                    }
                    .btn-toggle-custom[aria-expanded="true"]::after {
                        content: "－";
                    }
                </style>';

                echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#listado_' . $Tabla . '" 
                        aria-expanded="false" 
                        aria-controls="listado_' . $Tabla . '">';
                echo Trd(30);
                echo '</h4>';

                add_listado($Tabla,'collapse');                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');
?>
 <style>
        .gallery-item {
            position: relative;
            cursor: grab;
        }
        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .gallery-item .btn-delete {
            position: absolute;
            top: 5px;
            right: 5px;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .gallery-item.ui-sortable-helper {
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .gallery-item .order-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.75rem;
        }
        .drop-zone {
            border: 2px dashed #adb5bd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            color: #6c757d;
            transition: all .2s;
            cursor: pointer;
        }
        .drop-zone.dragover {
            border-color: #0d6efd;
            background: #e7f1ff;
            color: #0d6efd;
        }

.gallery-item {
    position: relative;
}
.gallery-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.gallery-item .btn-delete {
    position: absolute;
    top: 5px;
    right: 5px;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.gallery-item .order-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    z-index: 2;
}
.order-controls {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 4px;
}
.order-controls .btn {
    flex: 1;
    border: 1px solid #dee2e6;
}
.drop-zone {
    border: 2px dashed #adb5bd;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    color: #6c757d;
    transition: all .2s;
    cursor: pointer;
}
.drop-zone.dragover {
    border-color: #0d6efd;
    background: #e7f1ff;
    color: #0d6efd;
}        
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.loading-box {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* Placeholder mientras se sube cada imagen */
.gallery-item.uploading {
    position: relative;
}
.gallery-item.uploading img {
    opacity: 0.4;
}
.gallery-item .item-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
}
    </style>

<?php                
                $Tabla2 = 'products_images';
                echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#listado_' . $Tabla2 . '" 
                        aria-expanded="false" 
                        aria-controls="listado_' . $Tabla2 . '">';
                echo Trd(31);
                echo '</h4>';
?>
<div class=" collapse container py-4" id = "listado_<?= $Tabla2 ?>">
    <h3 class="mb-4"><?= Trd(44) ?></h3>

    <input type="hidden" id="product_id" > <!-- ID del producto actual -->

    <!-- Zona de carga -->
    <div class="drop-zone mb-3" id="dropZone">
        <i class="bi bi-cloud-upload fs-1"></i>
        <p class="mb-1"><?= Trd(45) ?></p>
        <small><?= Trd(46) ?></small>
        <input type="file" id="fileInput" name="upload_file[]" multiple accept="image/*" class="d-none">
    </div>

    <!-- Barra de progreso -->
    <div class="progress mb-3 d-none" id="uploadProgress" style="height: 6px;">
        <div class="progress-bar bg-success" style="width: 0%"></div>
    </div>

    <!-- Galería -->
    <div class="row g-3" id="galleryContainer">
        <!-- Items dinámicos -->
    </div>

</div>
<?php
                $Tabla3 = 'products_images_sale';
                echo '<h4 class="mb-4 btn-toggle-custom fs-5"
                    data-bs-toggle="collapse"
                    data-bs-target="#listado_' . $Tabla3 . '"
                    aria-expanded="false"
                    aria-controls="listado_' . $Tabla3 . '">';
                echo Trd(74);
                echo '</h4>';
                
                
?>
   


<div class="collapse container py-4" id="listado_<?= $Tabla3 ?>">
    <h3 class="mb-4"><?= Trd(74) ?></h3>

    <div class="drop-zone mb-3" id="dropZoneSale">
        <i class="bi bi-cloud-upload fs-1"></i>
        <p class="mb-1"><?= Trd(45) ?></p>
        <small><?= Trd(46) ?></small>
        <input type="file" id="fileInputSale" name="upload_file[]" multiple accept="image/*" class="d-none">
    </div>

    <div class="progress mb-3 d-none" id="uploadProgressSale" style="height: 6px;">
        <div class="progress-bar bg-success" style="width: 0%"></div>
    </div>

    <div class="row g-3" id="galleryContainerSale"></div>
</div>

<!-- Modal vista previa -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Trd(47) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImg" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="loading-overlay d-none">
    <div class="loading-box">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0" id="loadingText"><?= Trd(48) ?></p>
    </div>
</div>

<?php                


/*
                add_listado($Tabla2,'collapse');
                add_form($Tabla2,$Idioma,'D');
                edit_form($Tabla2,$Idioma,'D');
*/


                $Tabla22 = 'products_videos';

                echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#listado_' . $Tabla22 . '" 
                        aria-expanded="false" 
                        aria-controls="listado_' . $Tabla22 . '">';
                echo Trd(72);
                echo '</h4>';

                add_listado($Tabla22,'collapse');                
                add_form($Tabla22,$Idioma,'D');
                edit_form($Tabla22,$Idioma,'D');



                $Tabla3 = 'packing_list';

                echo '<h4 class="mb-4 btn-toggle-custom fs-5"
                    data-bs-toggle="collapse"
                    data-bs-target="#listado_' . $Tabla3 . '"
                    aria-expanded="false"
                        aria-controls="listado_' . $Tabla3 . '"
                        onclick="toggleElementoClone(\'add_form_' . $Tabla3 . '_clone\')">';
                echo Trd(32);
                echo '</h4>';            

?>
        <div class="collapse container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $Tabla3?>_clone" style="max-width: 100%;">
            <form name="add_<?php echo $Tabla3?>_clone" id="add_<?php echo $Tabla3?>_clone" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-10 col-lg-10">
                        <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla3' class='form-label text-secondary fw-semibold small mb-2'>Copiar información de: </label>";
                            echo '<select name="CodigoOrigen_clone_'.$Tabla3.'" id="CodigoOrigen_clone_'.$Tabla3.'" class="selectpicker form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">';
                            echo $Valores_Productos;
                            echo '</select>';
                        ?>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Copiar(3)">
                            <i class="fa-solid fa-paste"></i> Copiar
                        </button>
                    </div>                        
                </div>
            </form>
        </div>

<?php
                add_listado($Tabla3,'collapse');
                add_form($Tabla3,$Idioma,'D');
                edit_form($Tabla3,$Idioma,'D');
            $Tabla4 = 'related_products';
            echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#listado_' . $Tabla4 . '" 
                    aria-expanded="false" 
                    aria-controls="listado_' . $Tabla4 . '"
                    onclick="toggleElementoClone(\'add_form_' . $Tabla4 . '_clone\')">';
            echo Trd(33);
            echo '</h4>';
?>
        <div class="collapse container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $Tabla4?>_clone" style="max-width: 100%;">
            <form name="add_<?php echo $Tabla4?>_clone" id="add_<?php echo $Tabla4?>_clone" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-10 col-lg-10">
                        <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla4' class='form-label text-secondary fw-semibold small mb-2'>Copiar información de: </label>";
                            echo '<select name="CodigoOrigen_clone_'.$Tabla4.'" id="CodigoOrigen_clone_'.$Tabla4.'" class="selectpicker form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">';
                            echo $Valores_Productos;
                            echo '</select>';
                        ?>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Copiar(4)">
                            <i class="fa-solid fa-paste"></i> Copiar
                        </button>
                    </div>                        
                </div>
            </form>
        </div>
<?php            
                add_listado($Tabla4,'collapse');
                add_form($Tabla4,$Idioma,'D');
                edit_form($Tabla4,$Idioma,'D');                                
            $Tabla5 = 'upselling_products';

            echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#listado_' . $Tabla5 . '" 
                    aria-expanded="false" 
                    aria-controls="listado_' . $Tabla5 . '"
                    onclick="toggleElementoClone(\'add_form_' . $Tabla5 . '_clone\')">';
            echo Trd(34);
            echo '</h4>';            


?>
        <div class="collapse container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $Tabla5?>_clone" style="max-width: 100%;">
            <form name="add_<?php echo $Tabla5?>_clone" id="add_<?php echo $Tabla5?>_clone" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-10 col-lg-10">
                        <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla5' class='form-label text-secondary fw-semibold small mb-2'>Copiar información de: </label>";
                            echo '<select name="CodigoOrigen_clone_'.$Tabla5.'" id="CodigoOrigen_clone_'.$Tabla5.'" class="selectpicker form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">';
                            echo $Valores_Productos;
                            echo '</select>';
                        ?>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Copiar(5)">
                            <i class="fa-solid fa-paste"></i> Copiar
                        </button>
                    </div>                        
                </div>
            </form>
        </div>
<?php            
                add_listado($Tabla5,'collapse');
                add_form($Tabla5,$Idioma,'D');
                edit_form($Tabla5,$Idioma,'D');
            $Tabla6 = 'relationship_products';

            echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#listado_' . $Tabla6 . '" 
                    aria-expanded="false" 
                    aria-controls="listado_' . $Tabla6 . ' "
                    onclick="toggleElementoClone(\'add_form_' . $Tabla6 . '_clone\')">';
            echo Trd(35);
            echo '</h4>';            


?>
        <div class="collapse container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="add_form_<?php echo $Tabla6?>_clone" style="max-width: 100%;">
            <form name="add_<?php echo $Tabla6?>_clone" id="add_<?php echo $Tabla6?>_clone" class="needs-validation" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-10 col-lg-10">
                        <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla6' class='form-label text-secondary fw-semibold small mb-2'>Copiar información de: </label>";
                            echo '<select name="CodigoOrigen_clone_'.$Tabla6.'" id="CodigoOrigen_clone_'.$Tabla6.'" class="selectpicker form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">';
                            echo $Valores_Productos;
                            echo '</select>';
                        ?>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2">
                        <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" onclick="Copiar(6)">
                            <i class="fa-solid fa-paste"></i> Copiar
                        </button>
                    </div>                        
                </div>
            </form>
        </div>
<?php            
                add_listado($Tabla6,'collapse');
                add_form($Tabla6,$Idioma,'D');
                edit_form($Tabla6,$Idioma,'D');                
            $Tabla7 = 'cost_products';

                echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#listado_' . $Tabla7 . '" 
                        aria-expanded="false" 
                        aria-controls="listado_' . $Tabla7 . '">';
                echo Trd(36);
                echo '</h4>';            


                add_listado($Tabla7,'collapse');
                add_form($Tabla7,$Idioma,'D');
                edit_form($Tabla7,$Idioma,'D');
            $Tabla8 = 'products_files';

                echo '<h4 class="mb-4 btn-toggle-custom fs-5" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#listado_' . $Tabla8 . '" 
                        aria-expanded="false" 
                        aria-controls="listado_' . $Tabla8 . '">';
                echo Trd(37);
                echo '</h4>';            

                add_listado($Tabla8,'collapse');
                add_form($Tabla8,$Idioma,'D');
                edit_form($Tabla8,$Idioma,'D');


            echo '</div>';  

            delete_form($IdTabla);
            delete_form($Tabla);
            delete_form($Tabla22);
            delete_form($Tabla3);
            delete_form($Tabla4);
            delete_form($Tabla5);
            delete_form($Tabla6);
            delete_form($Tabla7);
            delete_form($Tabla8);
            

        }
        elseif ($IdTabla == 'distance_charges'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
                //add_listado($Tabla);
                //add_form($Tabla,$Idioma,'F');
            edit_form($IdTabla,$Idioma,'I',true);

            $Tabla = 'distance_charges_zip_code';
            echo '<h4 class="mb-4">'.Trd(38).'</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            $Tabla2 = 'distance_charges_distance';
            echo '<h4 class="mb-4">'.Trd(39).'</h4>';
                add_listado($Tabla2);
                add_form($Tabla2,$Idioma,'D');
                edit_form($Tabla2,$Idioma,'D');

            $Tabla3 = 'distance_charges_states';
            echo '<h4 class="mb-4">'.Trd(40).'</h4>';
                add_listado($Tabla3);
                add_form($Tabla3,$Idioma,'D');
                edit_form($Tabla3,$Idioma,'D');

            $Tabla4 = 'distance_charges_totals';
            echo '<h4 class="mb-4">'.Trd(41).'</h4>';
                add_listado($Tabla4);
                add_form($Tabla4,$Idioma,'D');
                edit_form($Tabla4,$Idioma,'D');

            echo '</div>';            


            delete_form($IdTabla);
            delete_form($Tabla);
            delete_form($Tabla2);
            delete_form($Tabla3);
            delete_form($Tabla4);


        }
        elseif ($IdTabla == 'account'){
            edit_form($IdTabla,$Idioma,'M',true,'');
        }
        elseif ($IdTabla == 'item_prices'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M',true);

            edit_form($IdTabla,$Idioma,'M',true);
            
            delete_form($IdTabla);
            /*
            <div class="container" id="proyeccion_item_prices" style="display: none;">
                <div class="seccion-proyeccion">
                    <h3><?php Trd_2(3)?></h3>

                    <div class="grafica-container">
                        <canvas id="costosChart"></canvas>
                    </div>        
                </div>
            </div>            
            */
            ?>



            <?php
        }        
        elseif ($IdTabla == 'customers'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'I',true);

            $Tabla = 'customer_addresses';
            echo '<h4 class="mb-4">Direcciones Extras</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            echo '</div>';
            delete_form($IdTabla);
            delete_form($Tabla);                

        }
        elseif ($IdTabla == 'price_lists'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'I',true);

                ?>
                
        <div class="container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" style="max-width: 100%;">
            
            <h4 class="mb-3 text-dark fw-bold"><i class="fa-solid fa-sliders text-primary me-2 small"></i><?= Trd(59) ?></h4>
            
            <div class="bg-light p-3 rounded-3 border mb-4">
                <label class="form-label text-secondary fw-semibold small d-block mb-3"><?= Trd(60) ?></label>
                <div class="d-flex flex-wrap gap-4">
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo" id="Copia" value="Copia" onclick="cambiarDivs('copiar_lista')" checked>
                        <label class="form-check-label text-dark fw-medium cursor-pointer" for="Copia">
                            <i class="fa-solid fa-folder-plus text-muted me-1"></i> <?= Trd(61) ?>
                        </label>
                    </div>
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo" id="Ajuste" value="Ajuste" onclick="cambiarDivs('ajustar_lista')">
                        <label class="form-check-label text-dark fw-medium cursor-pointer" for="Ajuste">
                            <i class="fa-solid fa-tags text-muted me-1"></i> <?= Trd(62) ?>
                        </label>
                    </div>                        
                </div>
            </div>

            <form name="ajustar_precios" id="ajustar_precios" class="needs-validation" novalidate>
                
                <div id="copiar_lista" style="display: block;">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="nueva_lista" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(63) ?></label>
                            <input name="nueva_lista" id="nueva_lista" class="form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium" type="text" required minlength="5" maxlength="50" placeholder="Ingresa nombre para la lista">
                        </div>
                    </div>
                    
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label text-secondary fw-semibold small d-block mb-2"><?= Trd(64) ?></label>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo_ajuste" id="Monto" value="$" checked>
                                <label class="form-check-label text-dark fw-medium cursor-pointer" for="Monto"><?= Trd(65) ?></label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo_ajuste" id="Porcentaje" value="%">
                                <label class="form-check-label text-dark fw-medium cursor-pointer" for="Porcentaje"><?= Trd(66) ?></label>
                            </div>
                        </div>                        
                        
                        <div class="row g-3">                            
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="copia_monto" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(67) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3">$</span>
                                    <input name="copia_monto" id="copia_monto" class="form-control bg-light border-start-0 px-3 py-2 text-end rounded-end-3 shadow-none fw-semibold decimals currency" type="text" maxlength="10" value="0.00">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="copia_monto_e" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(68) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3">$</span>
                                    <input name="copia_monto_e" id="copia_monto_e" class="form-control bg-light border-start-0 px-3 py-2 text-end rounded-end-3 shadow-none fw-semibold decimals currency" type="text" maxlength="10" value="0.00">
                                </div>
                            </div>
                        </div>                            
                    </div>
                </div>
                
                <div id="ajustar_lista" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="ajuste_categoria" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(69) ?></label>
                            <select name="ajuste_categoria" id="ajuste_categoria" class="form-control bg-light border px-3 py-2 rounded-3 shadow-none text-dark fw-medium">
                                <option value="0" selected><?= Trd(71) ?></option>
                                <?php
                                    $query = "SELECT Id,Nombre FROM categories ORDER BY Id";
                                    $stmt_dts = $db->prepare($query);
                                    $stmt_dts->execute();
                                    $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                                    if ($tabla_dts) {
                                        foreach ($tabla_dts as $tabla_dt) {
                                            $Valor         = $tabla_dt["Id"];
                                            $Descripcion   = $tabla_dt["Nombre"];
                                            echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                        }
                                    }
                                ?>                                    
                            </select>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label text-secondary fw-semibold small d-block mb-2"><?= Trd(64) ?></label>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo_ajuste_c" id="Monto_c" value="$" checked>
                                <label class="form-check-label text-dark fw-medium cursor-pointer" for="Monto_c"><?= Trd(65) ?></label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input cursor-pointer shadow-none" type="radio" name="tipo_ajuste_c" id="Porcentaje_c" value="%">
                                <label class="form-check-label text-dark fw-medium cursor-pointer" for="Porcentaje_c"><?= Trd(66) ?></label>
                            </div>
                        </div>                        
                        
                        <div class="row g-3">                            
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="ajuste_monto" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(67) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3">$</span>
                                    <input name="ajuste_monto" id="ajuste_monto" class="form-control bg-light border-start-0 px-3 py-2 text-end rounded-end-3 shadow-none fw-semibold decimals" type="text" maxlength="10" value="0.00">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="ajuste_monto_e" class="form-label text-secondary fw-semibold small mb-2"><?= Trd(68) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3">$</span>
                                    <input name="ajuste_monto_e" id="ajuste_monto_e" class="form-control bg-light border-start-0 px-3 py-2 text-end rounded-end-3 shadow-none fw-semibold decimals" type="text" maxlength="10" value="0.00">
                                </div>
                            </div>
                        </div>                        
                    </div>                    
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" type="submit">
                        <i class="fa-solid fa-gears"></i> <?= Trd(70) ?>
                    </button>
                </div>         
            </form>
        </div>   

                </form>
                    <script>
                    function cambiarDivs(idVisible) {
                        // Ocultamos ambos primero
                        document.getElementById('copiar_lista').style.display = 'none';
                        document.getElementById('ajustar_lista').style.display = 'none';
                        
                        // Mostramos el que nos interesa
                        document.getElementById(idVisible).style.display = 'block';
                    }
                    $('#ajustar_precios').on('submit', function(e) {


                        if (!this.checkValidity() && $('#Copia').is(':checked')) {
                            e.preventDefault();
                            e.stopPropagation();
                            $(this).addClass('was-validated');
                            return false;
                        }                      


                        if ($('#Copia').is(':checked')) {
                            //alert('Copia')
                            TipoOpcion = 'C';
                            ListaCategoria = $('#nueva_lista').val();
                            if ($('#Monto').is(':checked')) {
                                TipoMP = '$';
                            }
                            else{
                                TipoMP = '%';
                            }
                            MontoA = $('#copia_monto').val();
                            MontoE = $('#copia_monto_e').val();
                        } else {
                            //alert('Ajuste')
                            TipoOpcion = 'A';
                            ListaCategoria = $('#ajuste_categoria').val();
                            if ($('#Monto_c').is(':checked')) {
                                TipoMP = '$';
                            }
                            else{
                                TipoMP = '%';
                            }
                            MontoA = $('#ajuste_monto').val();
                            MontoE = $('#ajuste_monto_e').val();
                        }

                        $.ajax({
                            url: API_BASE_URL + 'ajustar_precio',
                            type: 'PUT',
                            contentType: 'application/json', // Mantenemos el Content-Type como JSON
                            headers: {
                                'Authorization': 'Bearer ' + TOKEN 
                            },
                            // Enviamos el objeto convertido a JSON String
                            data:JSON.stringify({
                                lista: IdSelected,
                                tipo_opcion: TipoOpcion,
                                lista_categoria: ListaCategoria,
                                tipo_m_p: TipoMP,
                                montoA: MontoA,
                                montoE: MontoE
                            }),
                            success: function(response) {
                                setTimeout(() => {
                                    showToast('✅ <?php echo Trd(12)?> ' + response.message);
                                }, 500);                                
                            },
                            error: function(xhr) {
                                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                                setTimeout(() => {
                                    showToast('❌ <?php echo Trd(15)?> ' + errorMessage);
                                }, 500);
                            }
                        });                          


                        e.preventDefault(); 
                        
                    });                     
                    </script>                  
                <?php            

            $Tabla = 'detail_price_lists';
            echo '<h4 class="mb-4">Detalle lista precios</h4>';
                add_listado($Tabla);

                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            echo '</div>';
            delete_form($IdTabla);
            delete_form($Tabla);                

        }
        elseif ($IdTabla == 'categories'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'I',true);

            $Tabla = 'scategories';
            echo '<h4 class="mb-4">Sub Categorias</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            echo '</div>';
            delete_form($IdTabla);
            delete_form($Tabla);                

        }                
        else{
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'M',true);
            delete_form($IdTabla);            
        }

    ?>    
<script>
    <?php
    include_once 'js_scripts.php';
    ?>
    const ID_CLIENTE = '<?= $_SESSION['id_cliente']; ?>'; 
    const CFPUBLICURL = '<?= CFPUBLICURL ?>'; 
    
    
    let IdSelected = '<?php echo $Id2;?>';
    let IdDelete = '';

var tablaEstados = {};

function inicializarEstadoTabla(IdTabla) {
    if (!tablaEstados[IdTabla]) {
        tablaEstados[IdTabla] = {
            currentPage: 1,
            limit: 10,
            isLastPage: false,
            isLoading: false,
            // Nuevas propiedades de orden y filtros
            sortField: '',  // Nombre de la columna en la BD
            sortOrder: '',  // 'ASC', 'DESC' o vacío
            columnFilters: {} // Objeto clave-valor para filtros específicos
        };
    }
}


    function Cancel_add(Id){
        $("#listado_"+Id).show();
        $("#add_form_"+Id).hide();
        if (Id=='products'){
            $("#add_form_products_clone").hide();
        }
        if (Id=='item_prices'){
            $("#proyeccion_item_prices").hide();
        }        
    }
    function Cancel_edit(Id){
        $("#listado_"+Id).show();
        $("#edit_form_"+Id).hide();
        if (Id=='products'){
            $("#add_form_products_clone_edit").hide();
        }
        if (Id=='item_prices'){
            $("#proyeccion_item_prices").hide();
        }

    }    

    function AgregarRegistro(Id){
        $("#listado_"+Id).hide();
        $("#add_form_"+Id).show();
        if (Id=='products'){
            $("#add_form_products_clone").show();
        }
        
        if (Id=='item_prices'){
            IdSelected = 0;
        }
        if (Id=='gifcard'){
            $('#Code').val(generarCodigoAlfanumerico());
        } 
        if (Id=='item_prices')
            $("#proyeccion_item_prices").show();
        
    }

    function Clonar(Id,IdTabla){

        if (Id == 'add'){
            if (!$('#CodigoOrigen_clone').val()){
                alert('<?= Trd(49) ?>')
                return;
            }
            if (!$('#Name_clone').val()){
                alert('<?= Trd(50) ?>')
                return;
            }            
            var misHeaders = {
                'Authorization': 'Bearer ' + TOKEN
            };

            misHeaders['ID2'] = 'products';
            misHeaders['ID3'] = 'Id';
            misHeaders['ID4'] = $('#Name_clone').val();

            $.ajax({
                url: API_BASE_URL + "clone_record/"+$('#CodigoOrigen_clone').val(),
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });
                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                    $("#add_form_products_clone").hide();
                    //$("#listado_"+IdTabla).show();
                    $("#add_form_"+IdTabla).hide();
                    getRecordData(IdInsert,IdTabla)
                    //$("#add_form_products_clone_edit").hide();

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });        

        }
        else{
            if (!$('#Name_clone_edit').val()){
                alert('<?= Trd(50) ?>')
                return;
            }         
            //IdSelected

            var misHeaders = {
                'Authorization': 'Bearer ' + TOKEN
            };

            misHeaders['ID2'] = 'products';
            misHeaders['ID3'] = 'Id';
            misHeaders['ID4'] = $('#Name_clone_edit').val();

            $.ajax({
                url: API_BASE_URL + "clone_record/"+IdSelected,
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });
                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                    $("#add_form_products_clone_edit").hide();
                    //$("#listado_"+IdTabla).show();
                    $("#edit_form_"+IdTabla).hide();
                    getRecordData(IdInsert,IdTabla)
                    //$("#add_form_products_clone_edit").hide();

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });              

        }
    }    

    function Copiar(Id){
    
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

            //misHeaders['ID2'] = 'products';
            //misHeaders['ID3'] = 'Id';
            //misHeaders['ID4'] = $('#Name_clone_edit').val();    

        switch (Id) {
        case 3:
            //alert($('#CodigoOrigen_clone_packing_list').val())
            misHeaders['ID2'] = 'packing_list';
            misHeaders['ID3'] = 'Producto_pl';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_packing_list').val();                
            break;
        case 4:
            //alert($('#CodigoOrigen_clone_related_products').val())
            misHeaders['ID2'] = 'related_products';
            misHeaders['ID3'] = 'Producto_rp';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_related_products').val();               
            break;
        case 5:
            //alert($('#CodigoOrigen_clone_upselling_products').val())
            misHeaders['ID2'] = 'upselling_products';
            misHeaders['ID3'] = 'Producto_up';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_upselling_products').val();             
            break;
        case 6:
            //alert($('#CodigoOrigen_clone_relationship_products').val())
            misHeaders['ID2'] = 'relationship_products';
            misHeaders['ID3'] = 'Producto_sp';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_relationship_products').val();               
            break;
        default:
            break;
        }    



            $.ajax({
                url: API_BASE_URL + "copy_records/"+IdSelected,
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });

                    switch (Id) {
                    case 3:
                        listado('packing_list');               
                        break;
                    case 4:
                        listado('related_products');;               
                        break;
                    case 5:
                        listado('upselling_products');             
                        break;
                    case 6:
                        listado('relationship_products');               
                        break;
                    default:
                        break;
                    }                       

                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });            

    }

    function createNewRecord(form,IdTabla) {
        if (!TOKEN) {
            $('#apiMessage').text('Error: Token de autenticación no encontrado. Inicie sesión.').css('color', 'red');
            return;
        }

        const formDataArray = form.serializeArray();
        const formData = {};
        let esValido = true; 

        $.each(formDataArray, function(index, field) {
            // 1. Buscamos el elemento real en el DOM para poder leer sus atributos
            // Usamos el selector de atributo [name="..."]
            const inputReal = $('[name="' + field.name + '"]', form);
            const tipo = inputReal.attr('type');
            //alert(tipo);
            // 2. Aplicamos la lógica según el tipo o clase
            if (inputReal.hasClass('currency')) {
                formData[field.name] = LimpiaMonedaMejorada(field.value);
            } 
            else if (tipo === 'hidden') {
                // Si es hidden, asignamos 
                //alert(IdSelected)
                if(field.name == 'JsonPrice'){
                    formData[field.name] = field.value;
                }else{
                    formData[field.name] = IdSelected;
                }

                
            } 
            else {
                // Para el resto, el valor tal cual
                formData[field.name] = field.value;
            }
            
            if (field.name == 'password'){
                if ($('#password').val() != $('#password_c').val() ){
                    setTimeout(() => {
                        showToast('❌ Es necesario que los password coincidan');
                    }, 500);                    
                    esValido = false;
                    return false;
                }
            }
        });

        if (!esValido) {
            return;
        }        

        // 3. Realizar la solicitud AJAX
        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'POST',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data: JSON.stringify(formData),
            success: function(response) {
                listado(IdTabla);
                $("#listado_"+IdTabla).show();
                $("#add_form_"+IdTabla).hide();
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(12)?> ' + response.message);
                }, 500);
                //form[0].reset(); 
                // Al hacer reset, quitas también la clase que activa la validación visual global
                $('#add_'+IdTabla)[0].reset();
                $('#add_'+IdTabla).removeClass('was-validated');
                $('#add_'+IdTabla).find('.is-invalid, .error').removeClass('is-invalid error');
                if (IdTabla =='item_prices'){
                    $('#btnLimpiar').click();
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                $("#listado_"+IdTabla).show();
                $("#add_form_"+IdTabla).hide();
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                }, 500);
            }
        });
    }


    function updateRecord(form,IdTabla) {
        if (!TOKEN) {
            $('#apiMessage').text('Error: Token de autenticación no encontrado. Inicie sesión.').css('color', 'red');
            return;
        }

        const disabledFields = form.find(':disabled');
        disabledFields.prop('disabled', false);
        const formDataArray = form.serializeArray();
        disabledFields.prop('disabled', true);
        const formData = {};
        // Es crucial que los 'name' del formulario coincidan con las claves JSON de tu API (Nombre, Direccion)
        $.each(formDataArray, function(index, field) {
            // Usamos el campo 'name' del formulario como la clave del objeto
            var originalName = field.name;
            var fieldName = field.name;
            fieldName = fieldName.replace("edit_", "");
            var $input = $("#" + originalName);
// 1. Verificamos si es un campo Summernote (vía data-tipo o si tiene la clase inicializada)
    if ($input.data('tipo') === 'html' || $input.hasClass('note-codable')) {
        formData[fieldName] = $input.summernote('code');
    } 
    // 2. Formato moneda
    else if ($("#" + fieldName).hasClass('currency') || $input.hasClass('currency')) {
        formData[fieldName] = LimpiaMonedaMejorada(field.value);
    } 
    // 3. Valor estándar de formulario
    else {
        formData[fieldName] = field.value;
    }
        });
        // 3. Realizar la solicitud AJAX
        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'PUT',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data: JSON.stringify(formData),
            success: function(response) {
                if (IdTabla != 'products_item_price'){
                    listado(IdTabla);
                    $("#listado_"+IdTabla).show();
                    $("#edit_form_"+IdTabla).hide();
                    form[0].reset(); 
                }
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(14)?> ' + response.message);
                }, 500);
                
            },
            error: function(xhr) {
                $("#listado_"+IdTabla).show();
                $("#edit_form_"+IdTabla).hide();                
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(15)?> ' + errorMessage);
                }, 500);
            }
        });
        
        if (IdTabla=='item_prices'){
            $("#proyeccion_item_prices").hide();
        }


    }    



$(document).ready(function() {
/*
    attemptLogin('admin', '1234');   
    if (TOKEN) {
        //getRecordData(1); 
    } else {
        console.warn('No se encontró el token. Necesita iniciar sesión primero.');
    }
*/
    // Agregar Bootstrap JS si es necesario para funcionalidades adicionales
    //if (typeof bootstrap === 'undefined') {
    //    $.getScript("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js");
    //}

    listado('<?php echo $IdTabla?>')

    $(".currency").on("blur", function(){
        var numero = LimpiaMonedaMejorada($(this).val());
        $(this).val(formatter.format(numero));
    });
    
    $(".currency").on("focus", function(){
        var valor = $(this).val();
        var numero = LimpiaMonedaMejorada(valor);
        $(this).val(numero === 0 ? "" : numero.toString());
    });

    <?php 
        if ($IdTabla =='account'){
            echo "getRecordData(".$_SESSION['database_id'].",'".$IdTabla."');";
        }
    
    ?>

});
/*
function attemptLogin(username, password) {
    $.ajax({
        url: LOGIN_URL,
        type: 'POST',
        contentType: 'application/json', // Indica que enviamos JSON
        data: JSON.stringify({
            username: username,
            password: password
        }),
        success: function(response) {
            // Éxito: Guardar el token para futuras llamadas
            const jwtToken = response.jwt;
            console.log('Login exitoso. Token:', jwtToken);
            
            // *** Almacena el token de forma segura (ej: localStorage) ***
            localStorage.setItem('apiToken', jwtToken); 
            
        },
        error: function(xhr, status, error) {
            // Error: Credenciales inválidas (401) o error del servidor
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido.';
            console.error('Error de login:', errorMessage);
            alert('Fallo el inicio de sesión: ' + errorMessage);
        }
    });
}
*/
function listado(IdTabla, loadMore = false) {
    inicializarEstadoTabla(IdTabla);

    var estado = tablaEstados[IdTabla];

    if (estado.isLoading || (loadMore && estado.isLastPage)) return;

    if (!loadMore) {
        estado.currentPage = 1;
        estado.isLastPage = false;
    }

    estado.isLoading = true;
    $('.loading-spinner_' + IdTabla).show(); // Mostrar spinner de carga al final

    const $btnBuscar = $('#Srch_' + IdTabla);
    if ($btnBuscar.length > 0) {
        // Guardamos el texto original del botón por si cambia dinámicamente (Buscar / Filtrar)
        $btnBuscar.data('original-html', $btnBuscar.html());
        
        // Deshabilitamos y agregamos el spinner animado de FontAwesome
        $btnBuscar.prop('disabled', true)
                  .html('<i class="fa-solid fa-spinner fa-spin small"></i> <?= Trd(53) ?>');
    }    

    var misHeaders = { 'Authorization': 'Bearer ' + TOKEN };
    if (IdSelected) { misHeaders['ID2'] = IdSelected; }    

    var filterParams = '';
    for (var col in estado.columnFilters) {
        if (estado.columnFilters[col]) {
            filterParams += '&filter_' + col + '=' + encodeURIComponent(estado.columnFilters[col]);
        }
    }    

var camposFiltrados = Object.keys(estado.columnFilters).join(',');
<?php if ($IdTabla == 'inventory_stock'){?>
 if ($('#SalesI').is(':checked')) {
    IdSelected = 'SalesI';
 }else if ($('#RentI').is(':checked')) {
    IdSelected = 'RentI';
 }else if ($('#AllI').is(':checked')) {
    IdSelected = 'AllI';
 }   
var queryUrl = API_BASE_URL + IdTabla + '/' +
    '?page=' + estado.currentPage + 
    '&limit=' + estado.limit + 
    '&like=' + $('#Search_' + IdTabla).val() + 
    '&lang=<?php echo $Idioma?>' +
    '&sort_field=' + estado.sortField + 
    '&sort_order=' + estado.sortOrder + 
    '&sort_fields=' + encodeURIComponent(camposFiltrados) +
    '&inventory_id=' + IdSelected;
<?php } else {?>
var queryUrl = API_BASE_URL + IdTabla + '/' +
    '?page=' + estado.currentPage + 
    '&limit=' + estado.limit + 
    '&like=' + $('#Search_' + IdTabla).val() + 
    '&lang=<?php echo $Idioma?>' +
    '&sort_field=' + estado.sortField + 
    '&sort_order=' + estado.sortOrder + 
    '&sort_fields=' + encodeURIComponent(camposFiltrados);
<?php } ?>
    $.ajax({
        url: queryUrl,
        type: 'GET',
        dataType: 'json',
        headers: misHeaders,
        success: function(response) {
            renderTable(response.data, response.titulos, IdTabla, response.tipos, loadMore);
            
            if (response.data.length < estado.limit) {
                estado.isLastPage = true;
                $('#scroll-detector_' + IdTabla).hide(); // Si no hay más, destruimos el detector
            } else {
                estado.currentPage++;
                $('#scroll-detector_' + IdTabla).show();
            }
        },
        error: function(xhr, status, error) {
            //console.error('Error al obtener registro:', error);
        },
        complete: function() {
            estado.isLoading = false;
            $('.loading-spinner_' + IdTabla).hide(); // Ocultar spinner

            if ($btnBuscar.length > 0 && $btnBuscar.data('original-html')) {
                // Volvemos a habilitar y restauramos el icono de filtro junto al texto original
                $btnBuscar.prop('disabled', false)
                          .html($btnBuscar.data('original-html'));
            }            
        }
    });
}

function getRecordData(Id,IdTabla) {
    if (IdTabla == 'products'){
        IdSelected = Id;
        //alert(Id)
        $('#product_id').val(Id)
        loadGallery('products_images');
        loadGallery('products_images_sale');
    }

    if (IdTabla == 'distance_charges')
        IdSelected = Id;    

    if (IdTabla == 'customers')
        IdSelected = Id;    
        

    if (IdTabla == 'categories'){
        IdSelected = Id;      
        //alert(IdSelected)
    }
        

    //if (IdTabla == 'scategories')
    //    IdSelected = Id;     

    if (IdTabla == 'price_lists')
        IdSelected = Id;

    if (IdTabla == 'item_prices'){
        IdSelected = Id;
        $("#proyeccion_item_prices").show();
    }

    $("#listado_"+IdTabla).hide();
    $("#edit_form_"+IdTabla).show();
    //alert(IdTabla)
    $.ajax({
        url: API_BASE_URL + IdTabla+'/' + Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: {
            // *** Aquí se adjunta el token en el encabezado Authorization ***
            'Authorization': 'Bearer ' + TOKEN 
        },
        success: function(response) {
            //console.log('Datos del registro:', response);
            Object.entries(response).forEach(([clave, valor]) => {
                // Buscar input por id igual a la clave (case-insensitive)
                //alert("edit_"+clave);
                if (clave=='IId')
                    clave ='IId_'+IdTabla;
                const input = document.getElementById("edit_"+clave);
                //alert("edit_"+clave +" "+ input.type + " " + valor)
                if (input) {
                    //alert(input.type)
                    //alert(valor)
                    if (input.type === "checkbox") {
                        // Si el valor es 1, true o "1", se marca (checked = true)
                        input.checked = (valor == 1);
                    }
                    else if (input.type === "select-one") {
                        if (input.dataset.tipo =='complete'){
                            //alert(clave)
                            const instance = myChoicesInstances["edit_" + clave];

                            if (instance) {
                                //alert(valor)
                                // 2. Usamos el método de Choices para cambiar el valor
                                instance.setChoiceByValue(String(valor));
                                //alert(valor)
                            }

                        }
                        else{
                        var checkSelect = setInterval(function() {
                            var $select = $("#edit_" + clave);
                            if ($select.find('option').length > 0) {
                                $select.val(valor).trigger('change');
                                console.log("Asignado con éxito. Deteniendo intentos.");
                                clearInterval(checkSelect); // Esto detiene el bucle de espera
                            } else {
                                console.log("El select aún está vacío, esperando 2 segundos más...");
                            }
                        }, 500); // Se ejecuta cada 500ms
                        //var $select = $("#edit_" + clave);                        
                        //if ($select.find('option').length === 0) {
                        //    setTimeout(function() {
                        //        $select.val(valor).trigger('change');
                        //    }, 500);
                        //} else {
                        //    $select.val(valor).trigger('change');
                        //}                        
                        }
                    }
                    else if (input.type === "textarea") {
                        if (input.dataset.tipo =='html'){
                            //alert(valor)
                            $('#edit_'+clave).summernote('code',valor);
                        }
                        else{
                            //alert(clave)
                            //alert(valor)
                            input.value = valor || '';
                        }
                            
                    }
                    else if (input.type === "hidden") {
                        
                        //alert(clave+ ":"+valor)
                        //$('#edit_'+clave).val(valor);
                        if (clave=='JsonPrice'){

                            let rawString = $("<div>").html(valor).text(); // Esto convierte &quot; en "

                            input.value = rawString || '';
                        }
                        else{
                            input.value = valor || '';
                        }
                        //input.value = valor;


                    }                    
                    else {
                        // Para inputs normales (text, hidden, etc.)
                        //alert(input.type)
                        //alert(valor)
                        if ((IdTabla == 'categories' || IdTabla == 'products_images' || IdTabla == 'related_products' || IdTabla == 'account')  && input.type == 'file'){
                            var $select = $("#file_edit_" + clave+"_1")
                            $select.val(valor);
                            //alert(clave)
                            CargaImagen(clave,'gallery_file2_edit_'+clave);
                        }
                        else{
                            input.value = valor || '';
                        }
                        
                    }
                }
                else{
                    for (let i = 1; i <= 10; i++) {
                        const input = document.getElementById("edit_"+clave+"_"+i);
                        if (input) {

                            if (input.value == valor) {
                                input.checked = true; 
                            } else {
                                input.checked = false; // Opcional: desmarca los demás
                            }                        

                        }
                    }
                }
            });
            if (IdTabla == 'products'){
                $("#add_form_products_clone_edit").show();
                listado('products_categories');
                listado('products_images');
                listado('products_images_sale');
                listado('products_videos');
                listado('packing_list');
                listado('related_products');
                listado('upselling_products');
                listado('relationship_products');
                listado('cost_products');
                listado('products_files');
                getRecordData(IdSelected,'products_item_price')
                $('#edit_Producto').val(IdSelected);
            }
            else if (IdTabla == 'distance_charges'){
                listado('distance_charges_zip_code');
                listado('distance_charges_distance');
                listado('distance_charges_states');
                listado('distance_charges_totals');
            }
            else if (IdTabla == 'customers'){
                listado('customer_addresses');
            }
            else if (IdTabla == 'categories'){
                listado('scategories');
            }            
            else if (IdTabla == 'price_lists'){
                listado('detail_price_lists');
            }else if (IdTabla == 'item_prices' ){

                $('#edit_btnAplicar').click();                

            }else if ( IdTabla == 'products_item_price'){
                //cargar();
                //alert(0)   
                //alert($('#edit_JsonPrice').val())            
                $('#third_btnAplicar').click();  
                const contenedor = document.getElementById('third_casos');
                const inputs = contenedor.querySelectorAll('input');
                inputs.forEach(input => {
                    input.disabled = true;
                });
                $('#third_casos').find('input, select, textarea').prop('disabled', true);
                
            }
            <?php 
                //if ($IdTabla == 'products'){
                //    echo "listado('products_categories')";
                //}
            ?>
        },
        error: function(xhr, status, error) {
            
            if (IdTabla == 'products_item_price'){
                //ejecutarFuncion1(); 
                //$('#third_casos').empty();
                //configTotal = [];
                //ejecutarFuncion1();
                //if (myChart) {myChart.destroy()};
                $('#third_btnLimpiar').click()
                $('#edit_ItemPrice').closest('form')[0].reset();
                $('#edit_producto_origen_pl').closest('form')[0].reset();
                $('#edit_new').val(2);
                $('#edit_price_name').val($('#edit_Name').val());
            }
            
            if (xhr.status === 401) {
                // console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

function questionDelete(Id,IdTabla){
    IdDelete= Id;
}

function deleteRecord(Id,IdTabla) {
    if (!TOKEN) return;
    if (IdTabla == 'vehicles'){

        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'DELETE',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + TOKEN
            },
            data: JSON.stringify({
                id_vehicle: Id,
                IId: Id
            }),
            success: function(response) {
                listado(IdTabla);
                console.log('Registro borrado:', response.message);
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(16)?> ' + response.message);
                }, 500);            
            },
            error: function(xhr) {
                console.error('Error al crear:', xhr.responseJSON.message);
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(17)?> ' + errorMessage);
                }, 500);            
            }
        });        

    }
    else{

        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'DELETE',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + TOKEN
            },
            data: JSON.stringify({
                Id: Id,
                IId: Id
            }),
            success: function(response) {
                listado(IdTabla);
                console.log('Registro borrado:', response.message);
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(16)?> ' + response.message);
                }, 500);            
            },
            error: function(xhr) {
                console.error('Error al crear:', xhr.responseJSON.message);
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(17)?> ' + errorMessage);
                }, 500);            
            }
        });    

    }

}

function renderTable(data, titulos, IdTabla, tipos, loadMore = false) {
    const $container = $('#table-container_' + IdTabla);
    
    if (data.length === 0 && !loadMore) {
        $container.html('<div class="alert alert-info text-center shadow-sm my-3"><?php echo Trd(42)?></div>');
        return;
    }
    
    const columns = Object.keys(data[0] || {});
    let alineaciones = [];
    titulos.forEach(row => { alineaciones.push(row['Alineacion'] || 'center'); });

    let Tipos = [];
    tipos.forEach(row => { Tipos.push(row['TipoCampo']); });

    // Construir las filas HTML
    let htmlRows = '';
    data.forEach(row => {
        htmlRows += '<tr>';
        let Id = '';
        let idx = 0;

        columns.forEach(col => {                
            if (Tipos[idx] == 'checkbox') {
                if (row[col] == 1) {
                    htmlRows += `<td style="text-align: ${alineaciones[idx]}"><span class="badge rounded-pill bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-check me-1"></i>Activo</span></td>`;
                } else {
                    htmlRows += `<td style="text-align: ${alineaciones[idx]}"><span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1"><i class="fa-solid fa-xmark me-1"></i>Inactivo</span></td>`;
                }
            }
            else if (Tipos[idx] == 'file') {
                let fileName = row[col] || '';
                htmlRows += `<td style="text-align: ${alineaciones[idx]}">`;
                if(fileName !== '') {
                    htmlRows += `<a href="ajax/tmp/${fileName}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-medium text-primary"><i class="fa-solid fa-paperclip me-1 small"></i>${fileName}</a>`;
                } else {
                    htmlRows += `<span class="text-muted small">-</span>`;
                }
                htmlRows += `</td>`;
            }                
            else {
                if (col != 'Id' && col != 'IId' && col != 'Producto_rup' && col != 'Producto_rsp' && col != 'id_vehicle') {
                    htmlRows += `<td style="text-align: ${alineaciones[idx]}" class="text-dark">${row[col] || ''}</td>`;
                }
            }

            if (col == 'Id' || col == 'IId' || col == 'IdWhareHouse' || col == 'id_vehicle') {
                Id = row[col];
            }
            idx = idx + 1;
        });

        if (IdTabla == 'products_images') {
            htmlRows += `<td style="text-align: center">
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-light border text-secondary" onclick="orden('I',${IdSelected},${Id})"><i class="fa-solid fa-angles-left"></i></button>
                    <button class="btn btn-light border text-secondary" onclick="orden('A',${IdSelected},${Id})"><i class="fa-solid fa-angle-left"></i></button>
                    <button class="btn btn-light border text-secondary" onclick="orden('S',${IdSelected},${Id})"><i class="fa-solid fa-angle-right"></i></button>
                    <button class="btn btn-light border text-secondary" onclick="orden('U',${IdSelected},${Id})"><i class="fa-solid fa-angles-right"></i></button>
                </div>
            </td>`;                
        }
            
        if (IdTabla == 'detail_price_lists') {
            htmlRows += `<td style="text-align: center">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-3" onclick="questionDelete('${Id}','${IdTabla}')" data-bs-toggle="modal" data-bs-target="#delete_${IdTabla}"><i class="fas fa-trash-can"></i></button>
            </td>`;            
        } else {
            htmlRows += `<td style="text-align: center">
                <div class="btn-group btn-group-sm rounded-3" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="getRecordData('${Id}','${IdTabla}')"><i class="fas fa-pen"></i></button>
                    <button type="button" class="btn btn-outline-danger" onclick="questionDelete('${Id}','${IdTabla}')" data-bs-toggle="modal" data-bs-target="#delete_${IdTabla}"><i class="fas fa-trash-can"></i></button>
                </div>
            </td>`;
        }
        htmlRows += '</tr>';
    });

    // Inserción en el DOM
    if (loadMore && $container.find('table').length > 0) {
        $container.find('tbody').append(htmlRows);
    } else {


// ... (dentro de renderTable, en la sección de carga inicial)
let htmlStructure = '<div class="table-responsive">';
htmlStructure += '<table class="table table-sm table-striped table-hover align-middle mb-0" style="font-size: 0.9rem;">';
htmlStructure += '<thead class="table-light border-bottom"><tr>';

var estado = tablaEstados[IdTabla];

titulos.forEach(row => {
    if (row['Titulo'] != 'Id' && row['Titulo'] != 'IId' && row['Titulo'] != 'Producto_rup' && row['Titulo'] != 'Producto_rsp' && row['Titulo'] != 'ID') {
        let alineacion = row['Alineacion'] || 'center';
        let campoBD = row['Campo'] || row['Titulo']; 
        
        // 1. Icono de Ordenamiento (Un clic)
        let orderIcon = '<i class="fa-solid fa-sort text-muted ms-1 small opacity-50"></i>';
        if (estado.sortField === campoBD) {
            if (estado.sortOrder === 'ASC') {
                orderIcon = '<i class="fa-solid fa-sort-up text-primary ms-1 small"></i>';
            } else if (estado.sortOrder === 'DESC') {
                orderIcon = '<i class="fa-solid fa-sort-down text-primary ms-1 small"></i>';
            }
        }

        // 2. Icono de Filtrado Activado (Doble clic)
        // Si el campo existe en nuestro objeto de filtros, agregamos un embudo azul
        let filterIcon = '';
        if (estado.columnFilters[campoBD]) {
            filterIcon = ' <i class="fa-solid fa-filter text-info ms-1 small" title="Filtrando por este campo"></i>';
        }

        htmlStructure += `
            <th style="text-align: ${alineacion}; cursor: pointer; user-select: none;" 
                class="text-secondary fw-semibold py-2 th-sortable" 
                data-tabla="${IdTabla}" 
                data-campo="${campoBD}"
                title="Un clic: Ordenar | Doble clic: Activar/Quitar campo de filtro">
                ${row['Titulo'] || ''}${orderIcon}${filterIcon}
            </th>`;
    }
});

if (IdTabla == 'products_images') {
    htmlStructure += `<th style="text-align: center;" class="text-secondary fw-semibold py-2">Orden</th>`;
}
htmlStructure += `<th style="width: 100px;"></th></tr></thead>`;
htmlStructure += '<tbody>' + htmlRows + '</tbody>';
        htmlStructure += '</table></div>';
        
        // ELEMENTO DETECTOR: Un div centrado con un spinner sutil que se muestra solo al cargar
        htmlStructure += `
            <div id="scroll-detector_${IdTabla}" class="text-center my-3 text-muted" style="height: 20px;">
                <span class="loading-spinner_${IdTabla}" style="display:none;">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> <?= Trd(54) ?>
                </span>
            </div>`;

        $container.html(htmlStructure);
        
        // ACTIVAR EL OBSERVADOR DE SCROLL
        activarScrollInfinito(IdTabla);
    }
}

        // Función para formatear nombres de columnas
        function formatColumnName(columnName) {
            return columnName
                .replace(/([A-Z])/g, ' $1')
                .replace(/^./, str => str.toUpperCase());
        }

        // Función para renderizar la paginación
        function renderPagination(metadata,IdTabla) {
            const $container = $('#pagination-container_'+IdTabla);
            
            // Información de metadata
            const metadataHtml = `
                <div class="metadata-info">
                    <?php echo Trd(18)?> <strong>${(metadata.pagina_actual - 1) * metadata.registros_por_pagina + 1}</strong> 
                    <?php echo Trd(19)?> <strong>${Math.min(metadata.pagina_actual * metadata.registros_por_pagina, metadata.total_registros)}</strong> 
                    <?php echo Trd(20)?> <strong>${metadata.total_registros}</strong> <?php echo Trd(21)?>
                </div>
            `;
            
            // Generar paginación solo si hay más de una página
            let paginationHtml = '';
            if (metadata.total_paginas > 1) {
                paginationHtml = '<nav><ul class="pagination">';
                
                // Botón Anterior
                if (metadata.pagina_actual > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${metadata.pagina_actual - 1},'${IdTabla}')" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    `;
                }
                
                // Números de página
                const totalPages = metadata.total_paginas;
                const currentPage = metadata.pagina_actual;
                
                // Lógica para mostrar páginas (máximo 5 páginas visibles)
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                // Ajustar si estamos cerca del final
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }
                
                // Primera página si no está incluida
                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(1,'${IdTabla}')">1</a>
                        </li>
                    `;
                    if (startPage > 2) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                // Páginas visibles
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" onclick="changePage(${i},'${IdTabla}')">${i}</a>
                        </li>
                    `;
                }
                
                // Última página si no está incluida
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${totalPages},'${IdTabla}')">${totalPages}</a>
                        </li>
                    `;
                }
                
                // Botón Siguiente
                if (metadata.pagina_actual < metadata.total_paginas) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${metadata.pagina_actual + 1},'${IdTabla}')" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    `;
                }
                
                paginationHtml += '</ul></nav>';
            }
            
            $container.html(metadataHtml + paginationHtml);
        }

        // Función para cambiar de página (simulación)
        function changePage(pageNumber,IdTabla) {
            // Aquí normalmente harías una nueva llamada AJAX
            // Para este ejemplo, solo actualizamos la página actual
            console.log(`Cambiando a página ${pageNumber}`);
            
            // Simular carga
            const loadingAlert = '<div class="alert alert-info text-center"><?php echo Trd(43)?> ' + pageNumber + '...</div>';
            $('#table-container').html(loadingAlert);
            $.ajax({
                url: API_BASE_URL + IdTabla +'/?page='+pageNumber+'&limit=10&like='+$('#Search_'+IdTabla).val()+'&lang=<?php echo $Idioma?>',
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                success: function(response) {
                    setTimeout(() => {
                            renderTable(response.data,response.titulos,IdTabla,response.tipos);
                            renderPagination(response.metadata,IdTabla);                            
                        // Mostrar mensaje de éxito
                        showToast(`<?php echo Trd(22)?> ${pageNumber} <?php echo Trd(23)?>`);
                    }, 500);                    
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        //console.error('Acceso denegado. Token expirado o inválido.');
                        // Aquí puedes redirigir al login o limpiar el token
                    } else {
                        //console.error('Error al obtener registro:', error);
                    }
                }
            });            


        }

        // Función para mostrar notificaciones toast
        function showToast(message) {
            // Crear toast si no existe
            if ($('.toast').length === 0) {
                $('body').append(`
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <strong class="me-auto">Sistema</strong>
                                <small><?php echo Trd(24)?></small>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">${message}</div>
                        </div>
                    </div>
                `);
            }
            
            // Mostrar toast
            const toastElement = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastElement);
            $('.toast-body').text(message);
            toast.show();
        }

        // Función principal para inicializar
        //function initializeTable() {
        //    renderTable(responseData.data);
        //    renderPagination(responseData.metadata);
        //}

        //VALIDACION
        (function () {
        'use strict'
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
            })
        })()

        //SELECCION AUTOMATICA
        function activarSeleccionAutomatica(formSelector) {
            const formulario = document.querySelector(formSelector);
            if (!formulario) return;

            formulario.addEventListener('focusin', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                e.target.select();
            }
            });
        }        
        activarSeleccionAutomatica("#add_<?php  echo $IdTabla; ?>" );
        activarSeleccionAutomatica("#edit_<?php  echo $IdTabla; ?>" );

        //SOLO CAPUTAR NUMEROS
        $(".numbers-only").keypress(function (e) {
            if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });
        //CAPTURAR DECIMALES
// CAPTURAR DECIMALES Y NEGATIVOS
$(".decimals").keypress(function (e) {
    var caracter = e.which;
    var valorActual = $(this).val();

    // 1. Permitir el signo menos (-) solo si está al principio
    if (caracter == 45) { // 45 es el código ASCII del menos '-'
        // Si ya hay un signo menos o el cursor no está al inicio, lo bloquea
        if (valorActual.indexOf('-') != -1 || this.selectionStart !== 0) {
            return false;
        }
        return true; // Es válido si está al inicio
    }

    // 2. Permitir el punto (.) solo una vez
    if (caracter == 46) { // 46 es el código ASCII del punto '.'
        if (valorActual.indexOf('.') != -1) {
            return false;
        }
        return true;
    }    

    // 3. Permitir teclas de control (Backspace = 8, Enter/Flechas = 0) y números (48 al 57)
    if (caracter != 8 && caracter != 0 && (caracter < 48 || caracter > 57)) {
        return false;
    }
});     


        //PONER FORMATO DE MONEDA A CAMPOS CURRENCY
        var formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        function FormatoMoneda(Campo){
            var valor = $("#"+Campo).val();
            var numero = LimpiaMonedaMejorada(valor);
            $("#"+Campo).val(formatter.format(numero));
        }

        function FormatoMonedav(Valor){
            return formatter.format(Valor);
        }

        function LimpiaMonedaMejorada(Valor){
            // Eliminar todos los símbolos no numéricos excepto punto decimal
            var valorLimpio = Valor.replace(/[^0-9.-]/g, '');
            
            // Convertir a número (float)
            var numero = parseFloat(valorLimpio);
            
            // Si no es número válido, retornar 0
            return isNaN(numero) ? 0 : numero;
        }


        function change_send2(valor,campo,receptor,tabla,valor2){
            $('#'+receptor+' option').remove();
            var parametros = {"action":"ajax","valor":valor,'campo':campo,'receptor':receptor,'tabla':tabla,"valor2":valor2};
            $.ajax({
                type: "POST",
                url:'ajax/buscarvalor2.php',
                data: parametros,
                dataType: 'json', 
                beforeSend: function(objeto){
                    //$('#loadModal').show();
                    //$('.modal-backdrop').show();
                },
                success:function(data){
                    valor = data.Valor;
                    data = data.Registros;
                    $('#'+receptor+' option').remove();
                    //var obj = JSON.parse(JSON.stringify(data));
                    $.each(data, function(key,value) {
                        $('#'+receptor).append($('<option>', { 
                            value: value.Id,
                            text : value.Descripcion ,
                            selected: value.Id == valor
                        }));
                    });
                }
            }).fail( function( jqXHR, textStatus, errorThrown ) {
                alert( 'Error!!' );
            });
        }
        
        
        function change_send3(valor,campo,receptor,tabla,valor2){
            $('#edit_'+receptor+' option').remove();
            var parametros = {"action":"ajax","valor":valor,'campo':campo,'receptor':receptor,'tabla':tabla,"valor2":valor2};
            $.ajax({
                type: "POST",
                url:'ajax/buscarvalor2.php',
                data: parametros,
                dataType: 'json', 
                beforeSend: function(objeto){
                    //$('#loadModal').show();
                    //$('.modal-backdrop').show();
                },
                success:function(data){
                    valor = data.Valor;
                    data = data.Registros;
                    $('#edit_'+receptor+' option').remove();
                    //var obj = JSON.parse(JSON.stringify(data));
                    $.each(data, function(key,value) {
                        $('#edit_'+receptor).append($('<option>', { 
                            value: value.Id,
                            text : value.Descripcion ,
                            selected: value.Id == valor
                        }));
                    });
                }
            }).fail( function( jqXHR, textStatus, errorThrown ) {
                alert( 'Error!!' );
            });
        }          



        function uploadFile(file,field,type){
            //alert(file)
            //alert(field)
            //alert(type)
            var url = 'ajax/upload.php';
            var xhr = new XMLHttpRequest();
            var fd = new FormData();
            xhr.open("POST", url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Every thing ok, file uploaded
                    //console.log(xhr.responseText); // handle response.
                    //alert(field)
                    //alert(type)
                    //alert(xhr.responseText)
                    //alert(field)
                    $("#"+field).val(xhr.responseText);
                    //alert($("#"+field).val())

                }
            };
            fd.append("upload_file", file);
            xhr.send(fd);
        }

        function previewImage(file,field) {
            
            var galleryId = "gallery_"+field;
            //alert(file)
            //alert(galleryId)
            //$("#galleryId").html("");

            var gallery = document.getElementById(galleryId);
            gallery.innerHTML = ""
            //alert(gallery.innerHTML)
            var imageType = /image.*/;
        
            if (!file.type.match(imageType)) {
                throw "File Type must be an image";
            }
        
            var thumb = document.createElement("div");
            thumb.classList.add('thumbnail'); // Add the class thumbnail to the created div
        
            var img = document.createElement("img");
            img.file = file;
            img.style.width = "150px";
            //img.style.height = "100%";

            thumb.appendChild(img);
            gallery.appendChild(thumb);
            //$("#galleryId").html(thumb);
        
            // Using FileReader to display the image content
            var reader = new FileReader();
            reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
            reader.readAsDataURL(file);
        }        


        function CargaImagen(Id,div){
            //alert(div)
            //alert("ajax/tmp/"+$('#file_edit_'+Id+"_1").val())
            <?php 
            $folder = $IdTabla;
                if ($IdTabla=="products"){ $folder = "products_images"; } 
            ?>
            img = $('#file_edit_'+Id+"_1").val();
            url = `${CFPUBLICURL}/${ID_CLIENTE}/<?=  $folder  ?>/originals/${img || ''}`;
            $("#"+div).attr("src",url);
            $("#"+div).attr("width","120px");
        }


        const myChoicesInstances = {};

        // Seleccionamos todos los elementos con la clase .selectpicker
        const pickers = document.querySelectorAll('.selectpicker, select[data-tipo="complete"]');

        pickers.forEach((picker) => {
            // 1. Inicializamos Choices para cada picker individualmente
            const instance = new Choices(picker, {
                    placeholder: false,
                    itemSelectText: '',
                    searchEnabled: true,
                    // Forzamos el renderizado si hay problemas de timing
                    silent: false
            });


            const key = picker.id || picker.name;
            if (key) {
                myChoicesInstances[key] = instance;
            }    

            // 2. Aplicamos las clases personalizadas al contenedor generado
            // Obtenemos las clases originales del select
            let customClasses = picker.getAttribute('class');
            
            // Choices envuelve el select en un contenedor padre. 
            // Accedemos a él y le transferimos las clases.
            if (picker.parentElement) {
                picker.parentElement.classList.add(...customClasses.split(' '));
            }
        });

        function orden(orden,Idp,Id){
        $.ajax({
            url: API_BASE_URL + 'orden',
            type: 'PUT',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data:JSON.stringify({
                orden: orden,
                Idp: Idp,
                Id:Id
            }),
            success: function(response) {
                listado('products_images');
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(15)?> ' + errorMessage);
                }, 500);
            }
        });            

        }
<?php
//ESTE BLOQUE ES PARA PINTAR EL PROCESO DE PRECIOS !!
if ($IdTabla == 'item_prices' OR $IdTabla == 'products'){
//if ($IdTabla == 'item_prices' ){
$showItemPrices = isset($IdTabla) && $IdTabla === 'item_prices';
$showProductPrice = isset($IdTabla) && $IdTabla === 'products';
?>


const I18N = <?= json_encode([
    'units' => [
        'dia' => [$isEn ? 'day' : 'día', 24],
        'hora' => [$isEn ? 'hour' : 'hora', 1],
        'semana' => [$isEn ? 'week' : 'semana', 168],
        'dias' => [$isEn ? 'days' : 'días', 24],
        'horas' => [$isEn ? 'hours' : 'horas', 1],
        'semanas' => [$isEn ? 'weeks' : 'semanas', 168],
    ],
    'unit_placeholder' => $isEn ? 'Unit…' : 'Unidad…',
    'select_placeholder' => $isEn ? 'Select…' : 'Selecciona…',
    'qty_placeholder' => $isEn ? 'Qty.' : 'Cant.',
    'price_placeholder' => $isEn ? 'Price' : 'Precio',
    'case_badge' => $isEn ? 'Case' : 'Caso',
    'price_is' => $isEn ? 'The price is:' : 'El precio es:',
    'charge_prefix' => $isEn ? 'Charge' : 'Cargo',
    'per_each' => $isEn ? 'per each' : 'por cada',
    'after' => $isEn ? 'After' : 'Después de',
    'price_becomes' => $isEn ? 'the price is' : 'el precio es de',
    'modos' => [
        'indefinido' => $isEn ? 'Indefinite' : 'Indefinido',
        'hasta' => $isEn ? 'Until' : 'Hasta',
        'cada' => $isEn ? 'Every' : 'Cada',
    ],
    'err_greater' => $isEn ? 'Must be greater than ' : 'Debe ser mayor a ',
    'chart_price' => $isEn ? 'Price' : 'Precio',
    'chart_time_title' => $isEn ? 'Time' : 'Tiempo',
    'chart_price_title' => $isEn ? 'Price ($)' : 'Precio ($)',
    'err_missing_pasos' => $isEn ? 'Missing "pasos" array' : 'Falta el arreglo "pasos"',
    'err_invalid_json' => $isEn ? 'Invalid JSON: ' : 'JSON inválido: ',
], JSON_UNESCAPED_UNICODE) ?>;
const SHOW_ITEM_PRICES = <?= json_encode($showItemPrices) ?>;
const SHOW_PRODUCT_PRICE = <?= json_encode($showProductPrice) ?>;

const U = I18N.units;
const ALL = ['dia','hora','semana','dias','horas','semanas'], PL = ['dias','horas','semanas'];
const toH = (c,u) => c * U[u][1], lab = (c,u) => `${c} ${U[u][0]}`, fmt = h => `${+h.toFixed(2)} h`;

// ==========================================
// 1. FUNCIONALIDAD ORIGINAL
// ==========================================
let steps=[], stash={i:-1,tail:[]}, chart;

/* Convierte los pasos en segmentos de tiempo: {S inicio, E fin, P precio, N periodo (si es "por cada")} */
function segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}
function price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function sync(row,n){
  const m=row.find('.m').val(), show=m&&m!=='indefinido';
  row.find('.q,.u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function read(row,n,p){
  const s={caso:n}, m=row.find('.m').val();
  if(n===2){s.precio=p.precio;s.cada={cantidad:p.cantidad,unidad:p.unidad};}
  else{
    const pr=row.find('.pr').val(); if(pr===''||+pr<0)return null;
    s.precio=+pr; if(n===3)s.despues_de={cantidad:p.cantidad,unidad:p.unidad};
  }
  if(!m)return null; s.tipo=m;
  if(m!=='indefinido'){
    const q=+row.find('.q').val(), u=row.find('.u').val();
    if(!(q>0)||!u)return null; s.cantidad=q; s.unidad=u;
  }
  return s;
}

/* Valida el caso actual y, si está completo, detona (recursivamente) el siguiente */
function evaluate(i,row,n,p,over){
  let s=read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=segs(steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.err').text(err);
  if(over===undefined&&s&&steps[i]&&JSON.stringify(s)===JSON.stringify(steps[i]))return;
  const tail=over!==undefined?over:(steps.length>i+1?steps.slice(i+1):(stash.i===i?stash.tail:[]));
  if(over===undefined&&steps.length>i+1)stash={i,tail};
  row.nextAll().remove(); steps.length=i;
  if(s){steps[i]=s; if(s.tipo!=='indefinido')buildCase(i+1,tail);}
  if(over===undefined)refresh();
}

function buildCase(i,tail){
  tail=tail||[];
  const p=steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
  let d=tail[0]; if(d&&d.caso!==n){d=null;tail=[];}
  const price=`<div class="input-group input-group-sm w-auto"><span class="input-group-text">$</span><input type="number" min="0" step="any" class="form-control pr" style="width:100px" placeholder="${I18N.price_placeholder}"></div>`;
  const pre=n===1?`<b>${I18N.price_is}</b>${price}`
    :n===2?`<span>${I18N.charge_prefix} <b>$${p.precio}</b> ${I18N.per_each} <b>${lab(p.cantidad,p.unidad)}</b></span>`
    :`<span>${I18N.after} <b>${lab(p.cantidad,p.unidad)}</b> ${I18N.price_becomes}</span>${price}`;
  const modos=(n===2?['indefinido','hasta']:['indefinido','hasta','cada'])
    .map(m=>`<option value="${m}">${I18N.modos[m] || (m[0].toUpperCase()+m.slice(1))}</option>`).join('');
  const row=$(`<div class="caso card card-body py-2 px-3"><div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge text-bg-primary">${I18N.case_badge} ${n}</span>${pre}
    <select class="form-select form-select-sm w-auto m"><option value="">${I18N.select_placeholder}</option>${modos}</select>
    <input type="number" min="0" step="any" class="form-control form-control-sm q d-none" style="width:90px" placeholder="${I18N.qty_placeholder}">
    <select class="form-select form-select-sm w-auto u d-none"></select>
    <span class="text-danger small err"></span></div></div>`).appendTo('#casos');
  sync(row,n);
  const go=()=>{sync(row,n);evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.pr').val(d.precio);
    row.find('.m').val(d.tipo); sync(row,n);
    row.find('.q').val(d.cantidad||''); row.find('.u').val(d.unidad||'');
    evaluate(i,row,n,p,tail.slice(1));
  }
}

function refresh(){
  $('#JsonPrice').val(JSON.stringify({pasos:steps},null,2));
  const {out}=segs(steps), k=+$('#eje').val(), ds=chart.data.datasets[0];
  chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];chart.update();return;}
  let base=0; out.forEach(g=>{base=Math.max(base,g.S,isFinite(g.E)?g.E:0);});
  let H=0;
  out.forEach(g=>{
    if(isFinite(g.E))H=Math.max(H,g.E*1.2);
    else H=Math.max(H,g.N?g.S+6*g.N:Math.max(base*1.5,24));
  });
  const ev=new Set([0,H]);
  out.forEach(g=>{
    if(g.S<=H)ev.add(g.S); if(isFinite(g.E)&&g.E<=H)ev.add(g.E);
    if(g.N)for(let j=1,x=g.S+g.N;x<H&&x<=g.E&&j<1500;j++,x=g.S+j*g.N)ev.add(x);
  });
  const eps=H/5000, pts=[];
  [...ev].sort((a,b)=>a-b).forEach(e=>{
    pts.push({x:e/k,y:price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:price(out,e+eps)});
  });
  ds.data=pts; chart.update();
}

function reset(tail){
  $('#casos').empty(); steps=[]; stash={i:-1,tail:[]};
  buildCase(0,tail); refresh();
}

// ==========================================
// 2. FUNCIONALIDAD DUPLICADA (EDIT_)
// ==========================================
let edit_steps=[], edit_stash={i:-1,tail:[]}, edit_chart;

function edit_segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}

function edit_price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function edit_sync(row,n){
  const m=row.find('.edit_m, .m').val(), show=m&&m!=='indefinido';
  row.find('.edit_q, .q, .edit_u, .u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.edit_u, .u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function edit_read(row,n,p){
  const s={caso:n}, m=row.find('.edit_m, .m').val();
  if(n===2){s.precio=p.precio;s.cada={cantidad:p.cantidad,unidad:p.unidad};}
  else{
    const pr=row.find('.edit_pr, .pr').val(); if(pr===''||+pr<0)return null;
    s.precio=+pr; if(n===3)s.despues_de={cantidad:p.cantidad,unidad:p.unidad};
  }
  if(!m)return null; s.tipo=m;
  if(m!=='indefinido'){
    const q=+row.find('.edit_q, .q').val(), u=row.find('.edit_u, .u').val();
    if(!(q>0)||!u)return null; s.cantidad=q; s.unidad=u;
  }
  return s;
}

function edit_evaluate(i,row,n,p,over){
  let s=edit_read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=edit_segs(edit_steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.edit_err, .err').text(err);
  if(over===undefined&&s&&edit_steps[i]&&JSON.stringify(s)===JSON.stringify(edit_steps[i]))return;
  const tail=over!==undefined?over:(edit_steps.length>i+1?edit_steps.slice(i+1):(edit_stash.i===i?edit_stash.tail:[]));
  if(over===undefined&&edit_steps.length>i+1)edit_stash={i,tail};
  row.nextAll().remove(); edit_steps.length=i;
  if(s){edit_steps[i]=s; if(s.tipo!=='indefinido')edit_buildCase(i+1,tail);}
  if(over===undefined)edit_refresh();
}

function edit_buildCase(i,tail){
  tail=tail||[];
  const p=edit_steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
  let d=tail[0]; if(d&&d.caso!==n){d=null;tail=[];}
  const price=`<div class="input-group input-group-sm w-auto"><span class="input-group-text">$</span><input type="number" min="0" step="any" class="form-control edit_pr pr" style="width:100px" placeholder="${I18N.price_placeholder}"></div>`;
  const pre=n===1?`<b>${I18N.price_is}</b>${price}`
    :n===2?`<span>${I18N.charge_prefix} <b>$${p.precio}</b> ${I18N.per_each} <b>${lab(p.cantidad,p.unidad)}</b></span>`
    :`<span>${I18N.after} <b>${lab(p.cantidad,p.unidad)}</b> ${I18N.price_becomes}</span>${price}`;
  const modos=(n===2?['indefinido','hasta']:['indefinido','hasta','cada'])
    .map(m=>`<option value="${m}">${I18N.modos[m] || (m[0].toUpperCase()+m.slice(1))}</option>`).join('');
  const row=$(`<div class="edit_caso caso card card-body py-2 px-3"><div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge text-bg-primary">${I18N.case_badge} ${n}</span>${pre}
    <select class="form-select form-select-sm w-auto edit_m m"><option value="">${I18N.select_placeholder}</option>${modos}</select>
    <input type="number" min="0" step="any" class="form-control form-control-sm edit_q q d-none" style="width:90px" placeholder="${I18N.qty_placeholder}">
    <select class="form-select form-select-sm w-auto edit_u u d-none"></select>
    <span class="text-danger small edit_err err"></span></div></div>`).appendTo('#edit_casos');
  edit_sync(row,n);
  const go=()=>{edit_sync(row,n);edit_evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.edit_pr, .pr').val(d.precio);
    row.find('.edit_m, .m').val(d.tipo); edit_sync(row,n);
    row.find('.edit_q, .q').val(d.cantidad||''); row.find('.edit_u, .u').val(d.unidad||'');
    edit_evaluate(i,row,n,p,tail.slice(1));
  }
}

function edit_refresh(){
  $('#edit_JsonPrice').val(JSON.stringify({pasos:edit_steps},null,2));
  const {out}=edit_segs(edit_steps), k=+$('#edit_eje').val(), ds=edit_chart.data.datasets[0];
  edit_chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#edit_eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];edit_chart.update();return;}
  let base=0; out.forEach(g=>{base=Math.max(base,g.S,isFinite(g.E)?g.E:0);});
  let H=0;
  out.forEach(g=>{
    if(isFinite(g.E))H=Math.max(H,g.E*1.2);
    else H=Math.max(H,g.N?g.S+6*g.N:Math.max(base*1.5,24));
  });
  const ev=new Set([0,H]);
  out.forEach(g=>{
    if(g.S<=H)ev.add(g.S); if(isFinite(g.E)&&g.E<=H)ev.add(g.E);
    if(g.N)for(let j=1,x=g.S+g.N;x<H&&x<=g.E&&j<1500;j++,x=g.S+j*g.N)ev.add(x);
  });
  const eps=H/5000, pts=[];
  [...ev].sort((a,b)=>a-b).forEach(e=>{
    pts.push({x:e/k,y:edit_price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:edit_price(out,e+eps)});
  });
  ds.data=pts; edit_chart.update();
}

function edit_reset(tail){
  $('#edit_casos').empty(); edit_steps=[]; edit_stash={i:-1,tail:[]};
  edit_buildCase(0,tail); edit_refresh();
}

// ==========================================
// 3. FUNCIONALIDAD DENTRO DEL FORMULARIO
// ==========================================
let third_steps=[], third_stash={i:-1,tail:[]}, third_chart;

function third_segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}

function third_price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function third_sync(row,n){
  const m=row.find('.m').val(), show=m&&m!=='indefinido';
  row.find('.q,.u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function third_read(row,n,p){
  const s={caso:n}, m=row.find('.m').val();
  if(n===2){s.precio=p.precio;s.cada={cantidad:p.cantidad,unidad:p.unidad};}
  else{
    const pr=row.find('.pr').val(); if(pr===''||+pr<0)return null;
    s.precio=+pr; if(n===3)s.despues_de={cantidad:p.cantidad,unidad:p.unidad};
  }
  if(!m)return null; s.tipo=m;
  if(m!=='indefinido'){
    const q=+row.find('.q').val(), u=row.find('.u').val();
    if(!(q>0)||!u)return null; s.cantidad=q; s.unidad=u;
  }
  return s;
}

function third_evaluate(i,row,n,p,over){
  let s=third_read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=third_segs(third_steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.err').text(err);
  if(over===undefined&&s&&third_steps[i]&&JSON.stringify(s)===JSON.stringify(third_steps[i]))return;
  const tail=over!==undefined?over:(third_steps.length>i+1?third_steps.slice(i+1):(third_stash.i===i?third_stash.tail:[]));
  if(over===undefined&&third_steps.length>i+1)third_stash={i,tail};
  row.nextAll().remove(); third_steps.length=i;
  if(s){third_steps[i]=s; if(s.tipo!=='indefinido')third_buildCase(i+1,tail);}
  if(over===undefined)third_refresh();
}

function third_buildCase(i,tail){
  tail=tail||[];
  const p=third_steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
  let d=tail[0]; if(d&&d.caso!==n){d=null;tail=[];}
  const price=`<div class="input-group input-group-sm w-auto"><span class="input-group-text">$</span><input type="number" min="0" step="any" class="form-control pr" style="width:100px" placeholder="${I18N.price_placeholder}"></div>`;
  const pre=n===1?`<b>${I18N.price_is}</b>${price}`
    :n===2?`<span>${I18N.charge_prefix} <b>$${p.precio}</b> ${I18N.per_each} <b>${lab(p.cantidad,p.unidad)}</b></span>`
    :`<span>${I18N.after} <b>${lab(p.cantidad,p.unidad)}</b> ${I18N.price_becomes}</span>${price}`;
  const modos=(n===2?['indefinido','hasta']:['indefinido','hasta','cada'])
    .map(m=>`<option value="${m}">${I18N.modos[m] || (m[0].toUpperCase()+m.slice(1))}</option>`).join('');
  const row=$(`<div class="caso card card-body py-2 px-3"><div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge text-bg-primary">${I18N.case_badge} ${n}</span>${pre}
    <select class="form-select form-select-sm w-auto m"><option value="">${I18N.select_placeholder}</option>${modos}</select>
    <input type="number" min="0" step="any" class="form-control form-control-sm q d-none" style="width:90px" placeholder="${I18N.qty_placeholder}">
    <select class="form-select form-select-sm w-auto u d-none"></select>
    <span class="text-danger small err"></span></div></div>`).appendTo('#third_casos');
  third_sync(row,n);
  const go=()=>{third_sync(row,n);third_evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.pr').val(d.precio);
    row.find('.m').val(d.tipo); third_sync(row,n);
    row.find('.q').val(d.cantidad||''); row.find('.u').val(d.unidad||'');
    third_evaluate(i,row,n,p,tail.slice(1));
  }
}

function third_refresh(){
  $('#edit_JsonPrice').val(JSON.stringify({pasos:third_steps},null,2));
  const {out}=third_segs(third_steps), k=+$('#third_eje').val(), ds=third_chart.data.datasets[0];
  third_chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#third_eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];third_chart.update();return;}
  let base=0; out.forEach(g=>{base=Math.max(base,g.S,isFinite(g.E)?g.E:0);});
  let H=0;
  out.forEach(g=>{
    if(isFinite(g.E))H=Math.max(H,g.E*1.2);
    else H=Math.max(H,g.N?g.S+6*g.N:Math.max(base*1.5,24));
  });
  const ev=new Set([0,H]);
  out.forEach(g=>{
    if(g.S<=H)ev.add(g.S); if(isFinite(g.E)&&g.E<=H)ev.add(g.E);
    if(g.N)for(let j=1,x=g.S+g.N;x<H&&x<=g.E&&j<1500;j++,x=g.S+j*g.N)ev.add(x);
  });
  const eps=H/5000, pts=[];
  [...ev].sort((a,b)=>a-b).forEach(e=>{
    pts.push({x:e/k,y:third_price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:third_price(out,e+eps)});
  });
  ds.data=pts; third_chart.update();
}

function third_reset(tail){
  $('#third_casos').empty(); third_steps=[]; third_stash={i:-1,tail:[]};
  third_buildCase(0,tail); third_refresh();
  
    //$('#edit_ItemPrice').closest('form')[0].reset();
    //$('#edit_producto_origen_pl').closest('form')[0].reset();  
}

function third_clear(){
    third_reset();
  
    $('#edit_ItemPrice').closest('form')[0].reset();
    $('#edit_producto_origen_pl').closest('form')[0].reset();
    $('#edit_new').val(2);
    $('#edit_price_name').val($('#edit_Name').val());
}


// ==========================================
// INICIALIZACIÓN
// ==========================================
$(function(){
  if(SHOW_PRODUCT_PRICE){
    $('form.needs-validation').on('submit',e=>e.preventDefault());
  }
  if(SHOW_ITEM_PRICES){
  // 1. Gráfica y eventos originales
  chart=new Chart($('#grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#eje').on('change',refresh);
  $('#btnLimpiar').on('click',()=>{$('#jsonErr').text('');reset();});
  $('#btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#JsonPrice').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#jsonErr').text(''); reset(o.pasos);
    }catch(e){$('#jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  reset();

  // 2. Gráfica y eventos edit_
  edit_chart=new Chart($('#edit_grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#edit_eje').on('change',edit_refresh);
  $('#edit_btnLimpiar').on('click',()=>{$('#edit_jsonErr').text('');edit_reset();});
  $('#edit_btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#edit_JsonPrice').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#edit_jsonErr').text(''); edit_reset(o.pasos);
    }catch(e){$('#edit_jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  edit_reset();
  }

  if(SHOW_PRODUCT_PRICE){
  third_chart=new Chart($('#third_grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#third_eje').on('change',third_refresh);
  $('#third_btnLimpiar').on('click',()=>{$('#third_jsonErr').text('');third_reset();});
  $('#third_btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#edit_JsonPrice').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#third_jsonErr').text(''); third_reset(o.pasos);
    }catch(e){$('#third_jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  third_reset();
  }
});



    $('#edit_products_item_price').on('submit', function(e) {

        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('was-validated');
            return false;
        }        

        e.preventDefault(); 
        updateRecord($(this),'products_item_price'); // Pasamos el formulario a la función
    });  

    function get_price(Idp){
        $.ajax({
            url: API_BASE_URL +'get_price/' + Idp,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                //$('#edit_ItemPrice').val('').trigger('change');

                // Opción para Choices.js:
                //const selectElement = document.querySelector('#edit_ItemPrice');
                
                if (myChoicesInstances['edit_ItemPrice']) {
                    // Opción A: Establecer el valor a vacío utilizando setChoiceByValue
                    myChoicesInstances['edit_ItemPrice'].setChoiceByValue('');
                    
                    // O alternativamente, si prefieres limpiar ítems activos:
                    // myChoicesInstances['edit_producto_origen_pl'].removeActiveItems();
                }                


                // O si prefieres asegurarte de limpiar también el select nativo por debajo:
                $('#edit_ItemPrice').val('');                
                
                //console.log('Datos del registro:', response);
                Object.entries(response).forEach(([clave, valor]) => {

                const input = document.getElementById("edit_"+clave);
                    //alert(clave)
                    switch (clave) {
                    case 'ItemPrice':
                            //const instance = myChoicesInstances["edit_" + clave];
                            //if (instance) {
                            //    instance.setChoiceByValue(String(valor));
                            //} else if (input) {
                            //    input.value = String(valor);
                            //    input.dispatchEvent(new Event('change', { bubbles: true }));
                            //}
                        break;
                    case 'JsonPrice':
                            $('#third_btnLimpiar').click();   
                            //ejecutarFuncion1();
                            input.value = valor || '';
                            $('#third_btnAplicar').click();

                            $('#edit_new').val(1);
                            
                            //cargar();
                            const contenedor = document.getElementById('third_casos');
                            const inputs = contenedor.querySelectorAll('input');
                            inputs.forEach(input => {
                                input.disabled = true;
                            });
                            $('#third_casos').find('input, select, textarea').prop('disabled', true);                          
                        break;
                    case 'Taxable':
                            input.checked = (valor == 1);
                        break;    
                    default:

                    }
                });
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    // console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                    setTimeout(() => {
                        showToast('❌' + errorMessage);
                    }, 500);                
                    //console.error('Error al obtener registro:', error);
                }
            }
        });
    }
    function get_json_price(Idp){
        $.ajax({
            url: API_BASE_URL +'get_json_price/' + Idp,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                // Opción para Choices.js:
                if (myChoicesInstances['edit_producto_origen_pl']) {
                    // Opción A: Establecer el valor a vacío utilizando setChoiceByValue
                    myChoicesInstances['edit_producto_origen_pl'].setChoiceByValue('');
                    
                    // O alternativamente, si prefieres limpiar ítems activos:
                    // myChoicesInstances['edit_producto_origen_pl'].removeActiveItems();
                }

                // O si prefieres asegurarte de limpiar también el select nativo por debajo:
                $('#edit_producto_origen_pl').val('');

                Object.entries(response).forEach(([clave, valor]) => {

                const input = document.getElementById("edit_"+clave);

                    switch (clave) {
                    case 'JsonPrice':
                            $('#third_btnLimpiar').click();   
                            //ejecutarFuncion1();
                            input.value = valor || '';
                            $('#third_btnAplicar').click(); 
                            $('#edit_new').val(1);  
                            //cargar();
                            const contenedor = document.getElementById('third_casos');
                            const inputs = contenedor.querySelectorAll('input');
                            inputs.forEach(input => {
                                input.disabled = true;
                            });
                            $('#third_casos').find('input, select, textarea').prop('disabled', true);                            
                        break;
                    case 'Taxable':
                            input.checked = (valor == 1);
                        break;    
                    default:

                    }
                });
            },
            error: function(xhr, status, error) {
                
                if (xhr.status === 401) {
                    //console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {

                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                    setTimeout(() => {
                        showToast('❌' + errorMessage);
                    }, 500);                
                    //console.error('Error al obtener registro:', error);
                }
            }
        });
    }

<?php
}
?>


    $('#edit_Copiar').on('click', function() {
        //var idBoton = $(this).attr('id'); // Obtiene el ID del botón
        getTemplate( $('#edit_IdTemplate').val(),'edit_Template' );
    });

    $('#Copiar').on('click', function() {
        //var idBoton = $(this).attr('id'); // Obtiene el ID del botón
        getTemplate( $('#IdTemplate').val(),'Template' );
    });    

const generarCodigoAlfanumerico = () => {
    const ts = Date.now().toString(36).toUpperCase();
    const rd = Array.from({length: 16}, () => 
        "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 36)]
    ).join("");
    
    return (ts + rd).slice(0, 16).replace(/(.{4})/g, '$1-').replace(/-$/, '');
};    

    function getTemplate(Id,IdCampo) {

        $.ajax({
            url: API_BASE_URL + 'template/' + Id,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                Object.entries(response).forEach(([clave, valor]) => {
                    $('#'+IdCampo).summernote('code',valor);
                });
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    //console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {
                    console.error('Error al obtener registro:', error);
                }
            }
        });
    }



<?php if ($IdTabla == 'productss'){?>

document.addEventListener('DOMContentLoaded', function () {

    const el = document.getElementById('<?= $Campo ?>');

    const choices = new Choices(el, {
        allowHTML: false,
        searchEnabled: true,
        searchPlaceholderValue: '<?= Trd(55) ?>',
        noResultsText: '<?= Trd(56) ?>',
        noChoicesText: '<?= Trd(57) ?>',

        // ✅ Permite agregar elemento nuevo si no existe
        addItems: true,
        addChoices: true,
        addItemText: (value) => `Enter para agregar "<b>${value}</b>"`,
        allowHtmlUserInput: false,

        // Solo agrega si no hay coincidencia exacta
        duplicateItemsAllowed: false,
    });

    // Evento cuando se agrega un item nuevo (no existía en la lista)
    el.addEventListener('addItem', function (event) {
        const nuevoValor = event.detail.value;
        const fueAgregado = event.detail.customProperties?.nuevo;


        // También dispara tu función original si la necesitas
        //get_json_price(nuevoValor);
        $('#edit_new').val(1);
        //$('#contenedor-funciones').empty();
        //$('#third_casos').empty();        
        $('#third_btnLimpiar').click();   
    });

    // Para los cambios normales (selección de existente)
    el.addEventListener('choice', function (event) {
        //get_json_price(event.detail.choice.value);
    });
});    
<?php } ?>

function toggleElementoClone(idElemento) {
    // Buscamos el h4 que disparó el evento para saber si se está abriendo o cerrando
    // Buscamos el que tenga el data-bs-target apuntando al listado correspondiente
    var idListado = idElemento.replace('add_form_', 'listado_').replace('_clone', '');
    var $btn = $('[data-bs-target="#' + idListado + '"]');

    // Le damos un mini timeout para esperar a que Bootstrap actualice el estado del aria-expanded
    setTimeout(function() {
        var estaExpandido = $btn.attr('aria-expanded') === 'true';
        var $formClone = $('#' + idElemento); // Aquí usamos el ID dinámico que enviaste

        if (estaExpandido) {
            $formClone.show();
        } else {
            $formClone.hide();
        }
    }, 10);
}

// Objeto global para almacenar los observadores activos de cada tabla
var observadoresTablas = {};

function activarScrollInfinito(IdTabla) {
    const detector = document.getElementById('scroll-detector_' + IdTabla);
    if (!detector) return;

    if (observadoresTablas[IdTabla]) {
        observadoresTablas[IdTabla].disconnect();
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // 1. Ejecutamos la carga de datos
                listado(IdTabla, true); 
                
                /* 2. TRUCO: Si la pantalla es gigante, el detector podría seguir visible.
                     Esperamos un microsegundo a que el DOM se actualice con las nuevas filas 
                     y revisamos si el detector sigue cruzándose en pantalla.
                */
                setTimeout(() => {
                    // entry.target es nuestro detector. 
                    // Usamos boudingClientRect para ver si sigue dentro del alto de la pantalla
                    const rect = entry.target.getBoundingClientRect();
                    const visibleEnPantalla = rect.top < window.innerHeight;

                    if (visibleEnPantalla) {
                        // Si sigue visible, forzamos otra carga recursiva hasta llenar el monitor
                        listado(IdTabla, true);
                    }
                }, 100); // Un pequeño delay para dar tiempo al render del navegador
            }
        });
    }, {
        root: null,
        rootMargin: '50px', 
        threshold: 0
    });

    observer.observe(detector);
    observadoresTablas[IdTabla] = observer;

// --- EL PARCHE PARA PANTALLAS GRANDES ---
    // Al cargar la función, verificamos inmediatamente si el detector ya está visible en la pantalla maximizada
    const rect = detector.getBoundingClientRect();
    if (rect.top < window.innerHeight) {
        // Si está visible de entrada, ejecutamos la primera carga extra para intentar llenar el vacío
        listado(IdTabla, true);
    }    

}

$(document).ready(function() {
    let clickTimer = null;
    const clickDelay = 250; // Tiempo límite para detectar el doble clic (ms)

    // Unificamos todo en un solo escuchador de CLICK
    $(document).on('click', '.th-sortable', function(e) {
        let $th = $(this);
        let IdTabla = $th.data('tabla');
        let campo = $th.data('campo');
        let estado = tablaEstados[IdTabla];

        // e.detail nos dice cuántos clics consecutivos se han hecho en este elemento
        if (e.detail === 1) {
            // --- PRIMER CLICK (Posible Ordenamiento) ---
            // Creamos el temporizador esperando a ver si viene otro clic en camino
            clickTimer = setTimeout(function() {
                if (estado.sortField === campo) {
                    if (estado.sortOrder === 'ASC') {
                        estado.sortOrder = 'DESC';
                    } else if (estado.sortOrder === 'DESC') {
                        estado.sortField = '';
                        estado.sortOrder = '';
                    }
                } else {
                    estado.sortField = campo;
                    estado.sortOrder = 'ASC';
                }
                
                // Ejecutamos el listado solo con el cambio de orden
                listado(IdTabla, false);
            }, clickDelay);

        } else if (e.detail === 2) {
            // --- SEGUNDO CLICK (¡Es un Doble Clic confirmado!) ---
            // Cancelamos inmediatamente el temporizador del primer clic para que NO ordene
            clearTimeout(clickTimer);

            if (!estado.columnFilters) {
                estado.columnFilters = {};
            }

            // Alternamos (Toggle) el filtro de la columna
            if (estado.columnFilters[campo]) {
                delete estado.columnFilters[campo];
            } else {
                estado.columnFilters[campo] = true; 
            }

            // Ejecutamos el listado con el filtro actualizado (manteniendo el orden intacto)
            listado(IdTabla, false);
        }
    });
});

</script>


<script>

    
    

    // ===== Cargar galería existente =====
    function gallerySelector(table, element) {
        return '#' + element + (table === 'products_images_sale' ? 'Sale' : '');
    }

    function loadGallery(table = 'products_images') {
        let productId = $('#product_id').val();
        $.ajax({
            url: 'api/image_actions',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: { action: 'list', product_id: productId, table: table },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $(gallerySelector(table, 'galleryContainer')).empty();
                    res.data.forEach(item => {
                        addItemToDOM(item.IId, item.Image, item.Orden, table);
                    });
                }
            }
        });
    }

    function addItemToDOM(id, image, orden, table = 'products_images') {
        const thumb = "<?= CFPUBLICURL ?>/" + ID_CLIENTE + "/" + table + "/thumbnails/" + image;
        const full  = "<?= CFPUBLICURL ?>/" + ID_CLIENTE + "/" + table + "/originals/" + image;

    const html = `
        <div class="col-6 col-md-3 col-lg-2 gallery-item" data-id="${id}" data-table="${table}">
            <span class="order-badge">${orden}</span>
            <img src="${thumb}" data-full="${full}" class="preview-img">
            <button class="btn btn-danger btn-sm btn-delete" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
            <div class="order-controls">
                <button class="btn btn-light btn-sm btn-move-up" title="Subir">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <button class="btn btn-light btn-sm btn-move-down" title="Bajar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>`;
    $(gallerySelector(table, 'galleryContainer')).append(html);
    }

    // ===== Drag & drop zona de carga =====
    [
        { dropZoneId: 'dropZone', fileInputId: 'fileInput', table: 'products_images' },
        { dropZoneId: 'dropZoneSale', fileInputId: 'fileInputSale', table: 'products_images_sale' }
    ].forEach(gallery => {
        const dropZone = document.getElementById(gallery.dropZoneId);
        const fileInput = document.getElementById(gallery.fileInputId);
        if (!dropZone || !fileInput) return;

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', event => {
            event.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', event => {
            event.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(event.dataTransfer.files, gallery.table);
        });
        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files, gallery.table);
            fileInput.value = '';
        });
    });

function handleFiles(files, table) {
    let productId = $('#product_id').val();
    if (!files.length) return;

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('table', table);

    let validFiles = 0;
    for (let i = 0; i < files.length; i++) {
        if (!files[i].type.startsWith('image/')) continue;
        formData.append('upload_file[]', files[i]);
        validFiles++;
    }

    if (validFiles === 0) {
        alert('<?= Trd(51) ?>');
        return;
    }

    // Mostrar placeholders "uploading" en la galería
    const placeholders = [];
    const $gallery = $(gallerySelector(table, 'galleryContainer'));
    for (let i = 0; i < validFiles; i++) {
        const $ph = $(`
            <div class="col-6 col-md-3 col-lg-2 gallery-item uploading">
                <span class="order-badge">...</span>
                <img src="https://via.placeholder.com/150x150?text=...">
                <div class="spinner-border spinner-border-sm text-light item-spinner" role="status"></div>
            </div>
        `);
        $gallery.append($ph);
        placeholders.push($ph);
    }

    // Overlay general + barra de progreso de subida
    $('#loadingText').text('Subiendo imágenes...');
    $('#loadingOverlay').removeClass('d-none');

    const $progress = $(gallerySelector(table, 'uploadProgress'));
    const $bar = $progress.find('.progress-bar');
    $progress.removeClass('d-none');
    $bar.css('width', '0%');

    $.ajax({
        url: 'ajax/uploads.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        xhr: function () {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    $bar.css('width', percent + '%');

                    // Cuando termina la subida (100%), cambia el texto a "procesando"
                    if (percent >= 100) {
                        $('#loadingText').text('<?= Trd(58) ?>');
                    }
                }
            });
            return xhr;
        },
        success: function (res) {
            $progress.addClass('d-none');
            $('#loadingOverlay').addClass('d-none');

            // Quitar placeholders
            placeholders.forEach($ph => $ph.remove());

            if (res.success) {
                res.files.forEach(f => {
                    if (f.success) {
                        addItemToDOM(f.id, f.image, f.orden, table);
                    } else {
                        alert(f.message);
                    }
                });
                updateOrderBadges(table);
            } else {
                alert(res.message || '<?= Trd(52) ?>');
            }
        },
        error: function () {
            $progress.addClass('d-none');
            $('#loadingOverlay').addClass('d-none');
            placeholders.forEach($ph => $ph.remove());
            alert('<?= Trd(52) ?>');
        }
    });
}

    // ===== Vista previa =====
    $(document).on('click', '.preview-img', function () {
        $('#previewImg').attr('src', $(this).data('full'));
        $('#previewModal').modal('show');
    });

$(document).on('click', '.btn-delete', function () {
    const $item = $(this).closest('.gallery-item');
    const id = $item.data('id');
    const table = $item.data('table');

    if (!confirm('¿Eliminar esta imagen?')) return;

    $item.addClass('uploading');
    $item.append('<div class="spinner-border spinner-border-sm text-danger item-spinner" role="status"></div>');

$.ajax({
    url: 'api/image_actions',
    type: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN },
    data: { action: 'delete', id: id, table: table },
    dataType: 'json',
    success: function (res) {
        if (res.success) {
            $item.fadeOut(200, function () {
                $(this).remove();
                updateOrderBadges(table);
                saveOrder(table);
            });
        } else {
            $item.removeClass('uploading');
            $item.find('.item-spinner').remove();
            alert(res.message || 'Error al eliminar');
        }
    }
});

});

// ===== Mover izquierda (subir orden) =====
$(document).on('click', '.btn-move-up', function () {
    const $item = $(this).closest('.gallery-item');
    const table = $item.data('table');
    const $prev = $item.prev('.gallery-item');

    if ($prev.length) {
        $item.insertBefore($prev);
        updateOrderBadges(table);
        saveOrder(table);
    }
});

// ===== Mover derecha (bajar orden) =====
$(document).on('click', '.btn-move-down', function () {
    const $item = $(this).closest('.gallery-item');
    const table = $item.data('table');
    const $next = $item.next('.gallery-item');

    if ($next.length) {
        $item.insertAfter($next);
        updateOrderBadges(table);
        saveOrder(table);
    }
});

    function updateOrderBadges(table) {
        $(gallerySelector(table, 'galleryContainer') + ' .gallery-item').each(function (index) {
            $(this).find('.order-badge').text(index + 1);
        });
    }

    function saveOrder(table) {
        const order = $(gallerySelector(table, 'galleryContainer') + ' .gallery-item').map(function () {
            return $(this).data('id');
        }).get();

$.ajax({
    url: 'api/image_actions',
    type: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN },
    data: {
        action: 'reorder',
        order: JSON.stringify(order),
        table: table
    },
    dataType: 'json',
    success: function (res) {
        if (!res.success) {
            console.error('Error al guardar orden', res.message);
        }
    }
});
    }

</script>    


</body>
</html>
