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
$query = "select Traduccion FROM  programas_traduccion where Programa = 'attendance_report' AND Idioma = ? ORDER BY Id";            
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
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .custom-card { border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff; }
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #1e293b; }
        .form-control:focus, .form-select:focus { border-color: #0f172a; box-shadow: none; }
        .table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 15px 10px; }
        .table td { padding: 14px 10px; font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<?php
    include_once 'nav.php';
?>
<br>
<br>


<div class="container my-5">
    <div class="mb-4">
        <h4 class="fw-semibold text-dark mb-1" style="letter-spacing: -0.5px;"><?= Trd(1) ?></h4>
        <p class="text-muted small mb-0"><?= Trd(2) ?></p>
    </div>

    <div class="card custom-card shadow-sm mb-4">
        <div class="card-body p-3">
            <form id="formFiltros" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-medium mb-1"><?= Trd(3) ?></label>
                    <input type="date" class="form-control" id="fecha_inicio" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-medium mb-1"><?= Trd(4) ?></label>
                    <input type="date" class="form-control" id="fecha_fin" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-medium mb-1"><?= Trd(5) ?></label>
                    <select class="form-select" id="operator_id">
                        <option value=""><?= Trd(6) ?></option>
                        <?php
                        //include 'conexion.php';
                        $ops = $db->query("SELECT Id, CONCAT(Nombres, ' ', Apellidos) AS Nombre FROM operators WHERE Estatus = 'A' OR Estatus IS NULL")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($ops as $o) {
                            echo "<option value='{$o['Id']}'>{$o['Nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="btnFiltrar" class="btn btn-dark w-100 py-2 rounded-2 fw-medium" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-filter me-2"></i><?= Trd(7) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card custom-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th><?= Trd(8) ?></th>
                        <th><?= Trd(9) ?></th>
                        <th><?= Trd(10) ?></th>
                        <th><?= Trd(11) ?></th>
                        <th><?= Trd(12) ?></th>
                        <th><?= Trd(13) ?></th>
                        <th><?= Trd(17) ?></th>
                    </tr>
                </thead>
                <tbody id="contenedorReporte">
                    </tbody>
            </table>
            
        </div>
        
    </div>
    <i class='fa-solid fa-flag text-danger'></i> = <?=  Trd(14) ?>
</div>




<script>

    <?php
    include_once 'js_scripts.php';
    ?>
$(document).ready(function() {

// Función centralizada para pedir el reporte al servidor
    function consultarReporte() {
        let fInicio = $('#fecha_inicio').val();
        let fFin = $('#fecha_fin').val();
        let opId = $('#operator_id').val();

        // Mostrar un spinner de carga minimalista provisional en la tabla
        $('#contenedorReporte').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fa-solid fa-circle-notch fa-spin me-2"></i><?= Trd(15) ?></td></tr>');

        $.ajax({
            url: API_BASE_URL+'asistencias/',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                fecha_inicio: fInicio,
                fecha_fin: fFin,
                operator_id: opId
            },
            success: function(response) {
                // Pinta el HTML directo usando la propiedad del objeto JSON de respuesta
                $('#contenedorReporte').html(response.tabla);
            },
            error: function() {
                $('#contenedorReporte').html('<tr><td colspan="7" class="text-center text-danger py-4"><?= Trd(16) ?></td></tr>');
            }
        });
    }

    // Cargar los datos automáticamente al abrir el panel (con la fecha de hoy)
    consultarReporte();

    // Vincular el evento click del botón de filtros
    $('#btnFiltrar').click(function() {
        consultarReporte();
    });
    
    // Opcional: Hacer que los filtros reaccionen automáticamente al cambiar de operador
    $('#operator_id').change(function() {
        consultarReporte();
    });


});    


      


</script>


</body>
</html>
