<?php 
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
        <!-- SECCIÓN 1: Listado de Pagos -->
        <div class="col-12 mb-4">
            <div class="border p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><?= Trd(119) ?></h5>
                    <!-- Botón para mostrar el formulario -->
                    <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm">
                        + <?= Trd(120) ?>
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th><?= Trd(121) ?></th>
                                <th><?= Trd(122) ?></th>
                                <th><?= Trd(123) ?></th>
                                <th><?= Trd(124) ?></th>
                                <th><?= Trd(125) ?></th>
                                <th><?= Trd(126) ?></th>
                                <th><?= Trd(127) ?></th>
                            </tr>
                        </thead>
                        <tbody id="listado_pagos">
                            <?php
                                $query = "select * FROM payments WHERE IdLead = ?";
                                $stmt = $db->prepare($query);
                                $stmt->bindParam(1, $IdLead);
                                $stmt->execute();
                                $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                if ($payments) {
                                    foreach ($payments as $pay) {
                                    ?>
                                        <tr>
                                            <td style="text-align: center;"><?= $pay['Id'] ?></td>
                                            <td style="text-align: center;"><?= $pay['DateTime'] ?></td>
                                            <td style="text-align: center;"><?= $pay['Platform'] ?></td>
                                            <td style="text-align: right;" >$<?= $pay['Amount'] ?></td>
                                            <td style="text-align: center;"><?= $pay['Currency'] ?></td>
                                            <td style="text-align: center;"><?= $pay['TransactionId'] ?></td>
                                            <td style="text-align: center;"><?= $pay['Usuario'] ?></td>
                                        </tr>
                                    <?php
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                    <input type="hidden" name="usuario" id="usuario" value='<?= $_SESSION['user'] ?>'>
                    <input type="hidden" name="token_id" id="token_id">
                    <input type="hidden" name="token" id="token" value="<?php echo $IdLead ?>">
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

<?php if ($paypal_account['Active'] ==1):?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= $paypal_account['Id'] ?>&currency=<?= $account['Currency'] ?>&enable-funding=venmo,paylater&buyer-country=<?= $account['Pais'] ?>"></script>

<script>
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
            const monto = parseFloat($('#monto_pago').val()) || 0;
            
            return actions.order.create({
                application_context: {
                    shipping_preference: 'NO_SHIPPING' // Sin dirección de envío
                },
                purchase_units: [{
                    amount: {
                        currency_code: '<?= $account['Currency'] ?>',
                        value: monto, // El TOTAL absoluto de la orden (25 + 10)
                        breakdown: {
                            item_total: {
                                currency_code: '<?= $account['Currency'] ?>',
                                value: monto // La suma de los subtotales de los items
                            }
                        }
                    },
                    // AQUÍ PONES TU LISTA DE PRODUCTOS
                    items: [
                        {
                            name: 'Pay Lead <?= $IdLead ?>',       // Nombre del producto 1
                            sku: 'Pay',                // Identificador único (opcional)
                            unit_amount: {
                                currency_code: '<?= $account['Currency'] ?>',
                                value: monto               // Precio unitario
                            },
                            quantity: '1'                    // Cantidad
                        }
                    ]
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(orderData) {
            
                $('#monto').val($('#monto_pago').val())
                $('#tipo-pago').val($('#tipo_pago').val())
                $('#referencia').val($('#refcia').val())            

                var datosFormulario = $('#payment-form').serialize();
                datosFormulario += '&orderID=' + encodeURIComponent(data.orderID);
                $.ajax({
                    url: 'processpayment_paypal.php',
                    method: 'POST',
                    dataType: "json",
                    data: datosFormulario,
                    success: function(respuestaBackend) {
                        if(respuestaBackend.status === 'success') {
                            const divpaypal = document.getElementById('paypal-button-container');
                            divpaypal.classList.add('d-none');                        
                            lanzarMensaje("<?= Trd(148) ?>","exito",5000);
                            render_pagos(respuestaBackend)
                            //$btn.prop("disabled", false).text("Aplicar Pago");
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
    if ($('#token').val() == 0){
        lanzarMensaje('<?= Trd(163) ?>','alerta',4000)
        return;
    }
    const divPagos = document.getElementById('div_pagos');
    const textoBoton = document.getElementById('texto_boton');

    if (divPagos.style.display === 'none') {
        // Mostrar
        divPagos.style.display = 'block';
        textoBoton.textContent = '<?= Trd(99) ?>';
    } else {
        // Ocultar
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
            url: "processpayment_cash.php",
            data: datosFormulario,
            dataType: "json",
            success: function(respuestaBackend) {
                if(respuestaBackend.status === 'success') {
                    // Lógica de éxito
                    $('#collapseForm').collapse('hide');
                    //alert("Pago procesado");
                    lanzarMensaje("<?= Trd(148) ?>","exito",5000);
                    render_pagos(respuestaBackend)
                    $btn.prop("disabled", false).text("Aplicar Pago");
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
                        url: "processpayment.php",
                        data: datosFormulario,
                        dataType: "json",
                        success: function(respuestaBackend) {
                            if(respuestaBackend.status === 'success') {
                                //alert("¡Pago Exitoso! ID de transacción: " + respuestaBackend.transaction_id);
                                $('#collapseForm').collapse('hide');
                                //alert("Pago procesado");
                                lanzarMensaje("<?= Trd(150) ?>","exito",5000);
                                render_pagos(respuestaBackend)
                                $btn.prop("disabled", false).text("Aplicar Pago");
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
    <script type="text/javascript" src="https://web.squarecdn.com/v1/square.js"></script>
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
                url: "processpayment_square.php",
                data: datosFormulario,
                dataType: "json",
                success: function(respuestaBackend) {
                    if(respuestaBackend.status === 'success') {
                        $('#collapseForm').collapse('hide');
                        lanzarMensaje("<?= Trd(151) ?>","exito",5000);
                        render_pagos(respuestaBackend)
                        $btn.prop("disabled", false).text("Aplicar Pago");
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