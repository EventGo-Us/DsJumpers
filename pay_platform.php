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
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'pay_config' AND Idioma = ? ORDER BY Id";            
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
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Trd(1);?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php
    include_once 'nav.php';
?>
<br><br>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white"><?= Trd(2);?></div>
                <div class="card-body">
                    <form id="paymentForm">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold"><?= Trd(3);?></label>
                            <select class="form-select" id="pay_platform" name="pay_platform" required>
                                <option value=""><?= Trd(4);?></option>
                                <option value="OPAY">OPAY</option>
                                <option value="SQUARE">SQUARE</option>
                            </select>
                            <div class="form-text"><?= Trd(5);?></div>
                        </div>

                        <div id="section_OPAY" class="payment-section d-none border p-3 rounded bg-light mb-4">
                            <h6 class="text-primary border-bottom pb-2 fw-bold"><?= Trd(6);?></h6>
                            <div class="mb-2">
                                <label class="small fw-bold">ID</label>
                                <input type="text" class="form-control form-control-sm" name="opay_id" id="opay_id">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Secret Key</label>
                                <input type="text" class="form-control form-control-sm" name="opay_secret" id="opay_secret">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Public Key</label>
                                <input type="text" class="form-control form-control-sm" name="opay_public" id="opay_public">
                            </div>
                        </div>

                        <div id="section_SQUARE" class="payment-section d-none border p-3 rounded bg-light mb-4">
                            <h6 class="text-primary border-bottom pb-2 fw-bold"><?= Trd(7);?></h6>
                            <div class="mb-2">
                                <label class="small fw-bold">ID</label>
                                <input type="text" class="form-control form-control-sm" name="square_id" id="square_id">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Local ID</label>
                                <input type="text" class="form-control form-control-sm" name="square_local" id="square_local">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Token</label>
                                <input type="text" class="form-control form-control-sm" name="square_token" id="square_token">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="paypal_active" id="paypal_active">
                                <label class="form-check-label fw-bold" for="paypal_active"><?= Trd(8);?></label>
                            </div>
                            <div class="form-text"><?= Trd(9);?></div>
                        </div>

                        <div id="section_PAYPAL" class="payment-section d-none border p-3 rounded bg-light mb-4">
                            <h6 class="text-warning border-bottom pb-2 fw-bold"><?= Trd(10);?></h6>
                            <div class="mb-2">
                                <label class="small fw-bold">ID</label>
                                <input type="text" class="form-control form-control-sm" name="paypal_id" id="paypal_id">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Secret Key</label>
                                <input type="text" class="form-control form-control-sm" name="paypal_secret" id="paypal_secret">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success" id="btnSave">
                                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="btnText"><?= Trd(11);?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-primary text-white">
      <strong class="me-auto"><i class="fas fa-bell me-2"></i> <?= Trd(12);?></strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-message">
      </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    <?php
    include_once 'js_scripts.php';
    ?>


$(document).ready(function() {
    // 1. Cargar datos actuales al abrir el programa
    loadCurrentConfig();

    function loadCurrentConfig() {
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };        
        $.ajax({
            url: API_BASE_URL + 'get_pay_platform',
            type: 'GET',
            dataType: 'json',
            headers: misHeaders,    
            success: function(data) {
                if (data) {
                    const sectionPaypal = document.getElementById("section_PAYPAL");
                    // Seleccionar la plataforma y disparar el evento change para mostrar la sección
                    $('#pay_platform').val(data.pay_platform).trigger('change');
                    
                    // Llenar campos OPAY
                    $('#opay_id').val(data.opay.Id);
                    $('#opay_secret').val(data.opay.SecretKey);
                    $('#opay_public').val(data.opay.PublicKey);
                    
                    $('#paypal_id').val(data.paypal.Id);
                    $('#paypal_secret').val(data.paypal.SecretKey);
                    if (data.paypal.Active == 1){
                        sectionPaypal.classList.remove("d-none");
                        $('#paypal_active').prop('checked', true);
                    }
                    else{
                        sectionPaypal.classList.add("d-none");
                        $('#paypal_active').prop('checked', false);
                    }
                        

                    // Llenar campos SQUARE
                    $('#square_id').val(data.square.Id);
                    $('#square_local').val(data.square.LocalId);
                    $('#square_token').val(data.square.Token);
                }
            }
        });
    }

    // Manejar visibilidad de secciones
    $('#pay_platform').on('change', function() {
        let platform = $(this).val();
        $('.payment-section').addClass('d-none');
        if (platform) {
            $('#section_' + platform).removeClass('d-none');
        }
    });

    // Envío del formulario (igual que el anterior)
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        let $btn = $('#btnSave');
        let $spinner = $('#spinner');
        let $text = $('#btnText');
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };        
        $.ajax({
            url: API_BASE_URL + 'update_pay_platform',
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: misHeaders,    
            beforeSend: function() {
                // 1. Mostrar spinner y deshabilitar botón
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $text.text('<?= Trd(13);?>'); // Opcional: cambiar el texto
            },            
            success: function(res) {
                if(res.status === 'success') {
                    mostrarToast('<?= Trd(14);?>', false);
                    
                } else {
                    mostrarToast('Error: ' + res.message, true);
                    
                }
            },
            error: function() {
                mostrarToast('<?= Trd(15);?>' , true);
                
            },
            complete: function() {
                // 2. Restaurar botón al finalizar (éxito o error)
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $text.text('<?= Trd(11);?>');
            }
        });
    });
});    

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



document.addEventListener("DOMContentLoaded", function() {
    const payPlatformSelect = document.getElementById("pay_platform");
    const paypalCheckbox = document.getElementById("paypal_active");
    
    const sectionOpay = document.getElementById("section_OPAY");
    const sectionSquare = document.getElementById("section_SQUARE");
    const sectionPaypal = document.getElementById("section_PAYPAL");

    // Control de Plataforma Principal (OPAY / SQUARE)
    payPlatformSelect.addEventListener("change", function() {
        // Ocultamos ambas primero
        sectionOpay.classList.add("d-none");
        sectionSquare.classList.add("d-none");

        // Mostramos la seleccionada
        if (this.value === "OPAY") {
            sectionOpay.classList.remove("d-none");
        } else if (this.value === "SQUARE") {
            sectionSquare.classList.remove("d-none");
        }
    });

    // Control de PayPal Opcional
    paypalCheckbox.addEventListener("change", function() {
        if (this.checked) {
            sectionPaypal.classList.remove("d-none");
        } else {
            sectionPaypal.classList.add("d-none");
        }
    });
});

</script>
</body>
</html>