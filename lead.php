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
        @media only screen and (max-width: 600px) {
            .responsive-table { width: 100% !important; }
            .stack-column { display: block !important; width: 100% !important; box-sizing: border-box; border: none !important; }
            .item-description { width: auto !important; }
            .policy-columns { column-count: 1 !important; }
            .signature-box { width: 100% !important; margin-bottom: 20px; }
            .total-table { width: 100% !important; }
        }


        #watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 10vw; /* Ajusta el tamaño según necesites */
            color: rgba(200, 200, 200, 0.3); /* Transparente para no tapar el contenido */
            pointer-events: none; /* Permite hacer clic a través de la marca */
            z-index: 9999;
            white-space: nowrap;
            user-select: none;
            font-weight: bold;
            text-transform: uppercase;
        }        

        #watermark_cancel {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 10vw; /* Ajusta el tamaño según necesites */
            color: rgba(253, 3, 3, 0.3); /* Transparente para no tapar el contenido */
            pointer-events: none; /* Permite hacer clic a través de la marca */
            z-index: 9999;
            white-space: nowrap;
            user-select: none;
            font-weight: bold;
            text-transform: uppercase;
        }         

    </style>    

</head>
<body >
<?php
    include_once 'nav.php';
?>
<div class="container-fluid px-3">
    <?php
        $IdLead = 0;
        if (isset($_GET['IdLead']) AND $_GET['IdLead'] > 0 ){
            $IdLead = $_GET['IdLead'];
            $query = "select * FROM lead WHERE Id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $IdLead);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($lead) {
                $query = "select * FROM lead_detail WHERE IdLead = $IdLead ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $lead_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($lead_details) {
                }        
                $query = "select * FROM lead_discounts WHERE IdLead = $IdLead ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $lead_discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($lead_discounts) {

                }                
            }

            $query = "select * FROM quotes WHERE IdQuote = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $IdLead);
            $stmt->execute();
            $UUID = $stmt->fetch(PDO::FETCH_ASSOC);      

            $query = "select SUM(amount) as Monto FROM payments WHERE IdLead = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $IdLead);
            $stmt->execute();
            $Pagado = $stmt->fetch(PDO::FETCH_ASSOC);            

        }



        require 'lead_grid.php';
        require 'lead_pagos.php';
        echo "<br>";
        require 'lead_customer.php';
        require 'lead_venues.php';
        require 'bottom.php';

        
            if (isset($lead) AND $lead['Status'] == 'confirmed')
                echo "<div id='watermark'>".Trd(158)."</div>";
            if (isset($lead) AND $lead['Status'] == 'canceled')
                echo "<div id='watermark_cancel'>".Trd(159)."</div>";        


    ?>



<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 0; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalContratoLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div id="Contract">

                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><?php echo Trd(69)?></button>
                <button type="button" class="btn btn-dark btn-sm" id="ShareButton" onclick="var newTab = window.open('<?= $account['WebSite'] ?>quote.php?Id='+$('#UUID').val(), '_blank');">
                    <i class="fa-solid fa-share-nodes"></i><?php echo Trd(70)?>
                </button>

            </div>
        </div>
    </div>
</div>    

<div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><?php echo Trd(71)?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-7 p-4 border-end d-flex justify-content-center bg-white">
                        <input type="text" id="calendarioRango" class="d-none">
                    </div>
                    
                    <div class="col-md-5 p-4 bg-light">
                        <div class="mb-4">
                            <label class="small fw-bold text-primary text-uppercase d-block mb-2"><?php echo Trd(72)?></label>
                            <select id="hInicio" class="form-select hour-select"></select>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold text-success text-uppercase d-block mb-2"><?php echo Trd(73)?></label>
                            <select id="hFin" class="form-select hour-select"></select>
                        </div>
                        <div class="alert alert-info py-2 small border-0 shadow-sm">
                            <?php echo Trd(74)?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?php echo Trd(75)?></button>
                <?php 
                    if ($IdLead > 0 ){
                        echo '<button type="button" id="btnReagendar" class="btn btn-primary px-4 fw-bold">'.Trd(98).'</button>';
                    }
                    else{
                        echo '<button type="button" id="btnConfirmar" class="btn btn-primary px-4 fw-bold">'.Trd(76).'</button>';
                    }
                ?>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><?= Trd(105) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-7 p-4 border-end d-flex flex-column justify-content-center bg-white">
                        <?= Trd(100) ?>

                        <div class="w-100">
                            <label class="form-label mb-2 fw-semibold text-dark" for="MotivoCancelacion">
                                <?= Trd(165) ?>
                            </label>        

                            <textarea class="form-control form-control-minimal" rows="5" placeholder="<?= Trd(166) ?>" id="MotivoCancelacion" name="MotivoCancelacion"></textarea>        
                        </div>
                    </div>
                    <div class="col-md-5 p-4 bg-light">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="candelationtype" id="GifCard" checked >
                            <label class="form-check-label" for="GifCard">
                                <?= Trd(101) ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="candelationtype" id="Devolution" >
                            <label class="form-check-label" for="Devolution">
                                <?= Trd(102) ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="checkcargo" id="checkcargo" >
                            <label class="form-check-label" for="checkcargo">
                                <?= Trd(156) ?>
                            </label>
                        </div>

                        <div >
                            <label class="form-label small fw-bold"><i class="fas fa-camera me-1"></i><?= Trd(157) ?></label>
                            <input type="file" id="evidence-file" class="form-control form-control-sm" accept="image/*" capture="environment">
                        </div>                        

                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?= Trd(103) ?></button>
                <button type="button" id="btnCancelarEvnto" class="btn btn-primary px-4 fw-bold" onclick="btnCancelarEvnto()"><?= Trd(104) ?></button>
            </div>
        </div>
    </div>
</div>


<!-- Modal con el diseño de tu proyecto -->
<div class="modal fade" id="modalMapa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><?= Trd(169) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Panel Izquierdo: Buscador de Dirección y Mapa -->
                    <div class="col-md-8 p-4 border-end bg-white">

                        
                        <!-- Contenedor del Mapa -->
                        <div id="mapa" style="height: 350px; width: 100%; border-radius: 6px; border: 1px solid #dee2e6;"></div>
                    </div>

                    <!-- Panel Derecho: Instrucciones e Información -->
                    <div class="col-md-4 p-4 bg-light d-flex flex-column justify-content-between">
                        <div>
                            <p class="small text-muted mb-3">
                                <?= Trd(170) ?>
                            </p>
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= Trd(171) ?>:</label>
                                <input type="text" id="temp_lat" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= Trd(172) ?>:</label>
                                <input type="text" id="temp_lng" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?= Trd(173) ?></button>
                <button type="button" id="btnConfirmarUbicacion" class="btn btn-primary px-4 fw-bold" data-bs-dismiss="modal"><?= Trd(167) ?></button>
            </div>
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

    const USER = '<?= $_SESSION['user'] ?>';

    let Row = 0;
    let Rltpc = 0;
    let TrDsc = 0;

    let SCat = 0;

    //AUTO GUARDADO !!
    // Variable para controlar el tiempo de espera (debounce)
    let autoSaveTimer;
    let autoSaveTimerQuant;    

    let StartEvent =0
    let EndEvent =0
    let DeliveryDate =0

    let CurrentOrganization = 0;
    let CurrentCustomer = 0;
    let CurrentVenue = 0;
    let CurrentSurface = 0;

    class ProductCounter {
        constructor() {
            this.cart = {};
        }

        // Verificar si un producto ya está en la lista
        hasProduct(id) {
            return Object.prototype.hasOwnProperty.call(this.cart, id);
        }

        // Recuperar cantidad
        getQuantity(id) {
            return this.hasProduct(id) ? this.cart[id].quantity : 0;
        }
        getInventory(id) {
            return this.hasProduct(id) ? this.cart[id].inventory : 0;
        }

        // Eliminar el producto completamente
        remove(id) {
            if (this.hasProduct(id)) {
            const name = this.cart[id].name;
            delete this.cart[id];
            //console.log(`"${name}" ha sido eliminado totalmente.`);
            this.debugInventory();
            return true;
            }
            return false;
        }

        // Métodos anteriores mejorados con hasProduct
        add(id, name, amount = 1, invent = 0) {
            if (this.hasProduct(id)) {
                this.cart[id].quantity += amount;
            } else {
                this.cart[id] = { name, quantity: amount, inventory: invent };
            }
            this.debugInventory();
        }

        subtract(id, amount = 1) {
            if (!this.hasProduct(id)) return;

            this.cart[id].quantity -= amount;
            if (this.cart[id].quantity <= 0) {
            this.remove(id);
            }
            this.debugInventory();
        }

        showSummary() {
            return Object.entries(this.cart).map(([id, data]) => ({ id, ...data }));
        }

        // --- Método de Debugging ---
        debugInventory() {
            //console.log("%c--- ESTADO DEL INVENTARIO SELECCIONADO ---", "color: #007bff; font-weight: bold;");
            
            if (Object.keys(this.cart).length === 0) {
            //console.log("El carrito está vacío.");
            } else {
            // Formateamos los datos para que la tabla sea clara
            const dataToPrint = Object.entries(this.cart).map(([id, data]) => ({
                ID: id,
                Producto: data.name,
                Cantidad: data.quantity,
                Inventario: data.inventory
            }));
            
            //console.table(dataToPrint);
            
            const total = dataToPrint.reduce((acc, item) => acc + item.Cantidad, 0);
            //console.log(`Total de artículos: ${total}`);
            }
            //console.log("------------------------------------------");
        }        

    }


function inicializarSelectDescuento(selector) {
    let IdTyp = '';
    let IdCst = '';
    if ($('#IdOrganization').val() > 0){
        IdTyp =  'O';
        IdCst = $('#Organization').val();
    }
    else{
        IdTyp =  'C';
        IdCst = $('#Customer').val();
    }
        
    $(selector).select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        placeholder: "Enter Gift Card Code",
        ajax: {
            url: API_BASE_URL + "get_gif_card/",
            type: 'GET',
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            delay: 300,
            data: function (params) {
                return {
                    q: params.term,
                    Tp: IdTyp,
                    Id:IdCst
                };
            },
            processResults: function (data) {
                // 1. Accedemos a la propiedad 'items' de tu JSON
                // Si 'items' no existe, usamos un array vacío
                var items = data.items;

                if (!items) {
                    return { results: [] };
                }

                // 2. Si 'items' es un objeto único (como el que mostraste), lo metemos en un array [].
                // Si ya es un array, lo dejamos como está.
                var listaItems = Array.isArray(items) ? items : [items];

                // 3. Ahora sí podemos usar .map con seguridad
                return {
                    results: listaItems.map(item => ({
                        id: item.Id,
                        text: item.Code,
                        amount: item.Amount
                    }))
                };
            },
            cache: true
        },
        // --- MANEJO DE MENSAJES ---
        language: {
            noResults: function () {
                return "No se encontraron resultados para este código";
            },
            searching: function () {
                return "Buscando...";
            },
            errorLoading: function () {
                return "Error al conectar con el servidor";
            }
        }
    }).on('select2:select', function (e) {
        // 1. Obtenemos la data del item seleccionado
        const data = e.params.data; 

        // 2. Obtenemos el ID dinámico del atributo data-idgc del select
        const idDinamico = $(this).data('idgc'); 

        // 3. Buscamos el campo de destino y le asignamos el valor de amount
        const targetInput = $(`#Discount_Amount_${idDinamico}`);
        
        if (data.amount) {
            targetInput.val(data.amount);
            // Si usas algún plugin de máscaras de dinero, dispara el evento change
            targetInput.trigger('change');
            recalculate_totals() 
        }
    })
    .on('select2:unselect', function() {
        // Opcional: Limpiar el campo si el usuario borra la selección
        const idDinamico = $(this).data('idgc');
        $(`#Discount_Amount_${idDinamico}`).val('');
    });
}



    //let Codes = [];
    const inventario = new ProductCounter();

        $(document).ready(function() {
/*
            attemptLogin('admin', '1234'); 
            
            if (TOKEN) {
                //getRecordData(1); 
            } else {
                console.warn('No se encontró el token. Necesita iniciar sesión primero.');
            }    
                
            
*/

            $(document).on('focus', '.select-on-focus', function() {
                this.select();
            });

            $('.select-auto').each(function() {
                    $(this).select2({
                        theme: "bootstrap-5",
                        width: '100%',
                        placeholder: $(this).data('placeholder') || "Selecciona una opción",
                        allowClear: true
                    });
            });



            $('#Referal').select2({
                theme: "bootstrap-5",
                width: '100%',
                allowClear: true,
                selectOnClose: false,
                ajax: {
                    url:  API_BASE_URL+"get_referals/", // URL de tu Web Service
                    dataType: 'json',
                    headers: {
                        // *** Aquí se adjunta el token en el encabezado Authorization ***
                        'Authorization': 'Bearer ' + TOKEN 
                    }, 
                    delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                    data: function (params) {
                        return {
                            q: params.term // El texto que el usuario escribió
                        };
                    },
                    processResults: function (data) {
                        // 'data' es la respuesta de tu WS. 
                        // Debes retornar un objeto con la propiedad 'results'.
                        return {
                            results: data.items.map(function(item) {
                                return {
                                    id: item.Id,
                                    text: item.Nombre, // Primera fila (Nombre)
                                    direccion: item.Direccion // Segunda fila (Dirección)
                                };
                            })
                        };
                    },
                    cache: true
                },
                templateResult: formatResult,   // Cómo se ve en la lista desplegable
                templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
            });                



    var $select = $('#Organization').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: false,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección
        ajax: {
            url: API_BASE_URL + "get_organization/",
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre,
                            direccion: item.Direccion
                        };
                    })
                };
            }
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        templateResult: formatResult,
        templateSelection: formatRepo
    });

    // USA ESTE MÉTODO PARA CAPTURAR LA SELECCIÓN
    $select.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag === true) {
            
            if ($('#Cell').data('select2')) {
                $('#Cell').select2('destroy');
            }
            
            // 2. Limpiamos el valor por si tenía algo y le quitamos las opciones viejas
            //$('#Cell').empty().append('<option value=""></option>').val('');
            $('#Cell').replaceWith('<input type="text" id="Cell" name = "Cell" class="form-control" placeholder="Escriba el celular del nuevo cliente...">');
            
            // 3. Enfocamos el celular para que el usuario digite el teléfono
            $('#Street').focus();                 

            registrarOrganizacion(data.text);
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_organization === "function") {
                load_organization(data.id);
            }
        }
    });

    function registrarOrganizacion(nombreNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_organization/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Organization').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Organization').append(newOption).trigger('change');
                
                $('#IdOrganization').val(response.id)

                //console.log("Registrado con éxito!");
                lanzarMensaje("<?php echo Trd(77)?>", "exito", 5000);
            },
            error: function () {
                lanzarMensaje("<?= Trd(106) ?>",'alerta',4000);
                $('#Organization').val(null).trigger('change');
            }
        });
    }    


    function triggerAutoSave() {
        if ($('#IdOrganization').val() > 0 || $('#IdCustomer').val() > 0){
            // Obtenemos el formulario y lo convertimos a un objeto plano
            const formArray = $('#customers').serializeArray();
            const formData = {};
            
            $.map(formArray, function(n, i){
                formData[n['name']] = n['value'];
            });
            URL_DESTINO = '';
            // Añadimos metadatos si es necesario
            if ($('#IdOrganization').val() > 0 ){
                formData['action'] = 'autosave_organization';
                URL_DESTINO = 'save_organization/"';
            }
            else{
                formData['action'] = 'autosave_customer';
                URL_DESTINO = 'save_customer/"';
            }
            $.ajax({
                url: API_BASE_URL + URL_DESTINO,
                type: 'PUT',
                contentType: 'application/json',
                    headers: { 
                        'Authorization': 'Bearer ' + TOKEN,
                        'Content-Type': 'application/json' 
                    },        
                data: JSON.stringify(formData), // Enviamos como JSON para que el PHP lo lea fácil
                success: function(response) {
                    //const res = typeof response === 'string' ? JSON.parse(response) : response;
                    // Si el servidor nos devuelve un ID nuevo (porque el cliente no existía)
                    //if (res.newIdCustomer) {
                    //    $('#IdCustomer').val(res.newIdCustomer);
                    //}
                    lanzarMensaje("<?php echo Trd(78)?>", "exito", 5000);
                },
                error: function(xhr, status, error) {
                    //console.error('Error en el autoguardado:', error);
                }
            });
        }
    }    


    $('#customers select, #customers input[type="checkbox"]').on('change', function() {
        triggerAutoSave();
    });

    // 2. Para inputs de texto y textareas (con delay de 1.5 segundos para no agobiar)
    $('#customers input[type="text"], #customers input[type="email"], #CustomerNote').on('keyup', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(triggerAutoSave, 1500);
    });




    var $selectcustomer = $('#Customer').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: false,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección
        ajax: {
            url:  API_BASE_URL+"get_customers/", // URL de tu Web Service
            dataType: 'json',
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            }, 
            delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
            data: function (params) {
                return {
                    q: params.term // El texto que el usuario escribió
                };
            },
            processResults: function (data) {
                // 'data' es la respuesta de tu WS. 
                // Debes retornar un objeto con la propiedad 'results'.
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre, // Primera fila (Nombre)
                            direccion: item.Direccion // Segunda fila (Dirección)
                        };
                    })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        templateResult: formatResult,   // Cómo se ve en la lista desplegable
        templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
    });

    $selectcustomer.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag === true) {
            //alert("Detectado nuevo tag: " + data.text);

        if ($('#Cell').data('select2')) {
            $('#Cell').select2('destroy');
        }
        
        // 2. Limpiamos el valor por si tenía algo y le quitamos las opciones viejas
        //$('#Cell').empty().append('<option value=""></option>').val('');
        $('#Cell').replaceWith('<input type="text" id="Cell" name = "Cell" class="form-control" placeholder="Escriba el celular del nuevo cliente...">');
        
        // 3. Enfocamos el celular para que el usuario digite el teléfono
        $('#Street').focus();            

            

            registrarCustomer(data.text,'');
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_customer === "function") {
                load_customer(data.id);
            }
        }
    });


    var $selectCell = $('#Cell').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: false,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección
        ajax: {
            url:  API_BASE_URL+"get_customers_cell/", // URL de tu Web Service
            dataType: 'json',
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            }, 
            delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
            data: function (params) {
                return {
                    q: params.term // El texto que el usuario escribió
                };
            },
            processResults: function (data) {
                // 'data' es la respuesta de tu WS. 
                // Debes retornar un objeto con la propiedad 'results'.
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.TelefonoCelular, // Primera fila (Nombre)
                            direccion: item.Direccion, // Segunda fila (Dirección)
                            nombre: item.Nombre // Segunda fila (Dirección)
                        };
                    })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        templateResult: formatResult,   // Cómo se ve en la lista desplegable
        templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
    });

    $selectCell.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag === true) {
            //alert("Detectado nuevo tag: " + data.text);

            if ($('#Customer').data('select2')) {
                $('#Customer').select2('destroy');
            }
            
            // 2. Limpiamos cualquier selección previa
            //$('#Customer').empty().append('<option value=""></option>').val('');
            $('#Customer').replaceWith('<input type="text" id="Customer" name="Customer" class="form-control" placeholder="Escriba el nombre del nuevo cliente...">');
            
            // 3. Enfocamos el campo nombre para que el usuario lo escriba
            $('#Customer').focus();            

            

            registrarCustomer('',data.text);
        } else {
            let Idsrch = data.id
            Idsrch = Idsrch.split('-')
            if (Idsrch[1] == "C")
                load_customer(Idsrch[0]);
            else
                load_organization(Idsrch[0]);
        }
    });    


    function registrarCustomer(nombreNuevo,cellNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_customer/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo,cell: cellNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Customer').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Customer').append(newOption).trigger('change');
                
                $('#IdCustomer').val(response.id)

                //console.log("Registrado con éxito!");
                lanzarMensaje("<?php echo Trd(79)?>", "exito", 5000);
            },
            error: function () {
                //alert("No se pudo guardar el cliente.");
                lanzarMensaje("<?php echo Trd(80)?>", "error", 5000);
                $('#Customer').val(null).trigger('change');
            }
        });
    }      
    


    


    var $selectvenue = $('#Venue').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: false,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección          
        ajax: {
            url:  API_BASE_URL+"get_venues/", // URL de tu Web Service
            dataType: 'json',
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
            data: function (params) {
                return {
                    q: params.term // El texto que el usuario escribió
                };
            },
            processResults: function (data) {
                // 'data' es la respuesta de tu WS. 
                // Debes retornar un objeto con la propiedad 'results'.
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre, // Primera fila (Nombre)
                            direccion: item.Direccion // Segunda fila (Dirección)
                        };
                    })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },        
        templateResult: formatResult,   // Cómo se ve en la lista desplegable
        templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
    });              

    $selectvenue.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag === true) {
            //alert("Detectado nuevo tag: " + data.text);
            registrarVenue(data.text);
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_venue === "function") {
                load_venue(data.id);
            }
        }
    });

    window.registrarVenue = function(nombreNuevo){
    //function registrarVenue(nombreNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_venue/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Venue').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Venue').append(newOption).trigger('change');
                
                $('#IdVenue').val(response.id)

                //console.log("Registrado con éxito!");
                if(nombreNuevo=='')
                    triggerAutoSaveVenue();
                else
                    lanzarMensaje("<?php echo Trd(81)?>", "exito", 5000);
            },
            error: function () {
                //alert("No se pudo guardar el ligar del evento.");
                lanzarMensaje("<?php echo Trd(82)?>", "error", 5000);
                $('#Venue').val(null).trigger('change');
            }
        });
    }        


window.triggerAutoSaveVenue = function(){
 //   function triggerAutoSaveVenue() {
        if ($('#IdVenue').val() > 0 ){
            // Obtenemos el formulario y lo convertimos a un objeto plano
            const formArray = $('#venues').serializeArray();
            const formData = {};
            
            $.map(formArray, function(n, i){
                formData[n['name']] = n['value'];
            });
            URL_DESTINO = '';
            // Añadimos metadatos si es necesario
            formData['action'] = 'autosave_venue';
            URL_DESTINO = 'save_venue/"';
            $.ajax({
                url: API_BASE_URL + URL_DESTINO,
                type: 'PUT',
                contentType: 'application/json',
                    headers: { 
                        'Authorization': 'Bearer ' + TOKEN,
                        'Content-Type': 'application/json' 
                    },        
                data: JSON.stringify(formData), // Enviamos como JSON para que el PHP lo lea fácil
                success: function(response) {
                    //const res = typeof response === 'string' ? JSON.parse(response) : response;
                    // Si el servidor nos devuelve un ID nuevo (porque el cliente no existía)
                    //if (res.newIdCustomer) {
                    //    $('#IdCustomer').val(res.newIdCustomer);
                    //}
                    //console.log('Autoguardado exitoso');
                    lanzarMensaje("<?php echo Trd(83)?>", "exito", 5000);
                },
                error: function(xhr, status, error) {
                    //console.error('Error en el autoguardado:', error);
                }
            });
        }
    }    


    $('#venues select, #venues input[type="checkbox"]').on('change', function() {
        triggerAutoSaveVenue();
    });    

    $('#venues input[type="text"], #venues input[type="email"], #CustomerNote').on('keyup', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(triggerAutoSaveVenue, 1500);
    });    



        //$("input").on("focus", function() {
        //    var $this = $(this);
        //    //setTimeout(function() {
        //        $this.select();
        //    //}, 10);
        //});        

        $(document).on("focus", "input", function() {
            var $this = $(this);
            // El setTimeout a veces es necesario en navegadores móviles o Chrome 
            // para ganarle al evento de click que deselecciona el texto
            //setTimeout(function() {
            //    $this.select();
            //}, 50); 
        });        

        
        <?php 
            if (isset($_GET['IdLead']) AND $_GET['IdLead'] > 0 AND $lead){ 
            

                if ($lead['Organization']>0){

                    $query = "select Nombre FROM organizations WHERE Id = ". $lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);


                    echo "
                    load_organization(".$lead['Organization'].");
                        var dataorganization = {
                            id: ".$lead['Organization'].",
                            text: '".$organization['Nombre']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(dataorganization.text, dataorganization.id, true, true);
                        $(newOption).data('data', dataorganization); 
                        $('#Organization').append(newOption).trigger('change');                    
                    ";
                }            

                //if (isset($lead) AND $lead['Status'] == 'confirmed'){
                //}

                if ($lead['Customer']>0){


                    $query = "select Nombres, Apellidos FROM customers WHERE Id = ". $lead['Customer'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $customers = $stmt->fetch(PDO::FETCH_ASSOC);


                    echo "
                    load_customer(".$lead['Customer'].");
                        var datacustomer = {
                            id: ".$lead['Customer'].",
                            text: '".$customers['Nombres']." ".$customers['Apellidos']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(datacustomer.text, datacustomer.id, true, true);
                        $(newOption).data('data', datacustomer); 
                        $('#Customer').append(newOption).trigger('change');                    
                    ";
                }

                if ($lead['Venue']>0){

                    $query = "select Nombre FROM venues WHERE Id = ". $lead['Venue'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);                

                    echo "
                    load_venue(".$lead['Venue'].",true);
                        var datavenue = {
                            id: ".$lead['Venue'].",
                            text: '".$venue['Nombre']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(datavenue.text, datavenue.id, true, true);
                        $(newOption).data('data', datavenue); 
                        $('#Venue').append(newOption).trigger('change');                    
                    ";
                }                

                //if ( $lead['Venue'] >0){
                    echo "$('#Surface').val('".$lead['Surface']."');
                          $('#DeliveryType').val('".$lead['Delivery']."');
                    ";

                //}

                //CARGAR DETALLES

                
                $query = "
                    SELECT IdProduct, SUM(Quantity) as Quantity 
                    FROM v_leads_detail 
                    WHERE Status = 'confirmed' 
                    AND (StartDateTime < :DateE AND EndDateTime > :DateS)
                    AND Unlimited = 0
                    GROUP BY IdProduct
                    
                    UNION

                    SELECT
                        relationship_products.Producto_rsp as IdProduct, 
                        count(relationship_products.Producto_rsp) as Quantity
                    FROM
                        v_leads_detail
                        INNER JOIN
                        relationship_products
                        ON 
                            v_leads_detail.IdProduct = relationship_products.Producto_sp
                            
                    WHERE v_leads_detail.Status = 'quoted' 
                    AND (v_leads_detail.StartDateTime < :DateEE AND v_leads_detail.EndDateTime > :DateSS)
                    AND v_leads_detail.Unlimited = 0
                    GROUP BY relationship_products.Producto_rsp		                

                ";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':DateS', $lead['StartDateTime']);
                $stmt->bindParam(':DateE', $lead['EndDateTime']);
                $stmt->bindParam(':DateSS', $lead['StartDateTime']);
                $stmt->bindParam(':DateEE', $lead['EndDateTime']);
                $stmt->execute();                

                $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                

                $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');                        


                if ($lead_details) {
                    foreach ($lead_details as $lead_detail) {

                    $query = "select * FROM products WHERE Id = ". $lead_detail['IdProduct'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);  

                            if (isset($cantidadesOcupadas[$lead_detail['IdProduct']])) {
                                $cantidadOcupada = $cantidadesOcupadas[$lead_detail['IdProduct']];
                                $cantidadOcupada-=$lead_detail['Quantity'];
                            } else {
                                $cantidadOcupada = 0;
                            }

                    $query = "SELECT *  from products_images WHERE Product = ". $lead_detail['IdProduct']." ORDER BY Orden LIMIT 1";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $Images = $stmt->fetch(PDO::FETCH_ASSOC);      
                    $Img ='';                
                    if ($Images)
                        $Img = $Images['Image'];
                        //$IdProd = $lead_detail['IdProduct'];
                        //$IdProdRel = $lead_detail['IdProductRel'];
                        //if ($IdProdRel > 0){
                        //    $IdP =
                        //    $IdPR =
                        //}

                        echo "
                            Row+=1;
                            $('#IdProducto').val('".$lead_detail['IdProduct']."');
                            $('#NombreProducto').val('".$product['Name']."');
                            add_row(Row,".$lead_detail['IdProductRel'].",{
                                id: Row,
                                rel: '".$lead_detail['IdProductRel']."',
                                product: '".$lead_detail['IdProduct']."',
                                name:  '".$product['Name']."',
                                quantity:  '".$product['Quantity']-$cantidadOcupada."',
                                quantityS:  '".$lead_detail['Quantity']."',
                                unlimited:  '".$product['Unlimited']."',
                                price:  '".$lead_detail['OrgPrice']."',
                                taxable:  '".$lead_detail['Tax']."',
                                operationstaff:  '".$product['OperationStaff']."',
                                setupstaff:  '".$product['SetUpStaff']."',
                                volunteer:  '".$product['Volunteer']."',
                                electric:  '".$product['Electric']."',
                                discount:  '".$lead_detail['Discount']."',
                                image:'".$Img."',
                                change_rent_price: '".$product['Change_rent_price']."'
                            },1
                            );
                            ";
//                                priceS:  '".$lead_detail['Price']."',                    
                    }
                }
                //CARGAR DESCUENTOS  
                if ($lead_discounts){
                    $DiscCnt=0;
                    foreach ($lead_discounts as $lead_discount) {
                        $DiscCnt+=1;
                        if ($lead_discount['IdDiscount'] == 0 ){
                            echo "//DESCUENTO APLICADO ".$lead_discount['IdDiscount']." - ".$lead_discount['AmountVal'];
                            //echo "$('#DiscountType').val('Fee');Add_Discount();";
                            //echo "$('#Discount_Desc_$DiscCnt').val('Fee');";
                            //echo "$('#Discount_Amount_$DiscCnt').val('".$lead_discount['AmountVal']."');";
                        }
                        else{
                            if ($lead_discount['Type'] == 'gifcard' ){
                                echo "$('#DiscountType').val('GifCard');Add_Discount();";
                                echo "
                                        var example_$DiscCnt = $('#Discount_Desc_$DiscCnt').select2();

                                        // Datos simulados que vendrían de tu lógica
                                        var data_$DiscCnt = {
                                            id: ".$lead_discount['IdDiscount'].",
                                            text: '".$lead_discount['Descript']."',
                                            amount: '".$lead_discount['AmountVal']."'
                                        };

                                        // Crear la opción si no existe
                                        var newOption_$DiscCnt = new Option(data_$DiscCnt.text, data_$DiscCnt.id, true, true);

                                        // Añadir, disparar cambio y actualizar el input de monto
                                        example_$DiscCnt.append(newOption_$DiscCnt).trigger('change');                                

                                ";
                                //echo "$('#Discount_Desc_$DiscCnt').val('".$lead_discount['IdDiscount']."');";

                                echo "$('#Discount_Amount_$DiscCnt').val('".$lead_discount['AmountVal']."');";
                            }
                            else{
                                //echo "//DESCUENTO APLICADO ".$lead_discount['IdDiscount']." - ".$lead_discount['AmountVal'];
                                echo "$('#DiscountType').val('Cupon');Add_Discount();";
                                echo "$('#Discount_Desc_$DiscCnt').val('".$lead_discount['IdDiscount']."');";
                                echo "$('#Discount_Amount_$DiscCnt').val('".$lead_discount['AmountVal']."');";                            
                            }

                        }
                    }
                }              

            }
        ?>
        //alert();
        recalculate();

        });

//END DOCUMENT READY


        //$(".decimals").keypress(function (e) {
        //    if(e.which == 46){
        //        if($(this).val().indexOf('.') != -1) {
        //            return false;
        //        }
        //    }    
        //    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
        //        return false;
        //    }
        //});          

        //$(".numbers-only").keypress(function (e) {
        //    if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
        //        return false;
        //    }
        //});
        
$(document).on("keypress", ".decimals", function (e) {
    if(e.which == 46){
        if($(this).val().indexOf('.') != -1) {
            return false;
        }
    }    
    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
        return false;
    }
});

$(document).on("keypress", ".numbers-only", function (e) {
            if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
                return false;
            }
});

        //RESET CATEGORIA
        function reset_search(){
            
            $('#ProductSearch').val('');
            $('#IdCategory').val('');
            $('#Category').val('');
            $('#Categories').show();            
            $('#Category_Products').hide();
            $('#IdProducto').val('');
            $('#NombreProducto').val('');
            $('#ProductSelect').hide();
            $('#Products_Elements').hide();
        }
        function reset_cat(){
            $('#IdCategory').val('');
            $('#Category').val('');
            $('#Categories').show();            
            $('#Category_Products').hide();
            $('#IdProducto').val('');
            $('#NombreProducto').val('');
            $('#ProductSelect').hide();
            $('#Products_Elements').hide();
        }
        //RESET PRODUCTO
        function reset_prod(){
            $('#Category_Products').show();
            $('#ProductSelect').hide();
            $('#Products_Elements').hide();
            get_products($('#IdCategory').val());
        }        

        // Seleccion de Category_Products_List
        $('.table-custom-cat tbody tr').on('click', function() {
            $('#IdCategory').val($(this).find('td:nth-child(1)').text());
            $('#Category').val($(this).find('td:nth-child(2)').text());
            $('#Categories').hide();
            //alert('Recupera Productos')
            get_products($(this).find('td:nth-child(1)').text());
            $('#Category_Products').show();
        });

        // Seleccion de Category_Products_List
        $('.table-custom-prd tbody').on('click', 'tr', function() {

            const $primerTd = $(this).find('td:nth-child(1)');
            const todosLosDatos = $primerTd.data();
            //if (todosLosDatos.quantity > 0){

                const idProd = $(this).find('td:nth-child(1)').text();
                const nombreProd = $(this).find('td:nth-child(2)').text();
                $('#IdProducto').val(idProd);
                $('#NombreProducto').val(nombreProd);
                $('#Category_Products').hide();
                $('#ProductSelect').show();
                $('#Products_Elements').show();            

                Row+=1;
                add_row(Row,0,todosLosDatos);
                get_related_products($(this).find('td:nth-child(1)').text());
            //}
            //else{
            //    lanzarMensaje("<?php echo Trd(84)?>", "error", 5000);
            //}
            //alert($(this).find('td:nth-child(1)').data('name'))
            //console.log('Producto seleccionado:', nombreProd);
        });               

        // Seleccion de Category_Products_List
        $('.table-custom-sprd tbody ').on('click','tr', function() {
            const $primerTd = $(this).find('td:nth-child(1)');            
            const todosLosDatos = $primerTd.data();  
            //if (todosLosDatos.quantity > 0){          
                $(this).hide();
                Row+=1;
                add_row(Row,$('#IdProducto').val(),todosLosDatos);
            //}
            //else{
            //    lanzarMensaje("<?php echo Trd(85)?>", "error", 5000);
            //}
        });          


        let searchTimeout = null;

        document.getElementById('ProductSearch').addEventListener('input', function() {
            const query = this.value.trim();

            // Cancelar el temporizador anterior si el usuario sigue escribiendo
            clearTimeout(searchTimeout);

            // Validar mínimo 3 caracteres
            if (query.length >= 3) {
                // Esperar 500ms después de que el usuario deje de escribir para detonar el AJAX
                searchTimeout = setTimeout(() => {
                    
                    buscarProducto(query);
                }, 500);
            }
        });        

        function buscarProducto(query){
            const $tbody = $("#Category_Products_List");
                // 2. Insertamos el spinner de carga (Bootstrap)
                $tbody.html(`
                    <tr id="loading-row">
                        <td colspan="100%" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden"><?= Trd(112) ?></span>
                            </div>
                            <div class="mt-2 text-muted fw-bold"><?= Trd(113) ?></div>
                        </td>
                    </tr>
                `);        

            const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
            const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
            //alert(FHIp[0].replaceAll('-',''))
            //SCat = IdCat;
            $.ajax ({
                url: API_BASE_URL+"get_products_search/?q="+query+"&DateS="+FHI+"&DateE="+FHF, // URL de tu Web Service
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                //data: JSON.stringify({
                //    Id: IdCat
                //}),
                success: function(response) {

            $('#Categories').hide();


            $('#Category_Products').show();                

                    $tbody.empty();
                    if (response && response.products.length >= 0) {
                        response.products.forEach(row => {
                            // VALIDAR SI EXISTE
                            //alert(1)
                            //if (Codes.indexOf(row.Producto) < 0 ){
                            //alert(inventario.hasProduct(row.Producto))
                            //if ( ! inventario.hasProduct(row.Producto) && row.Quantity > 0){
                            if ( ! inventario.hasProduct(row.Producto) ){
                                //alert(2)
                                const rowStyle = row.Quantity <= 0 ? "color: red;" : "";
                                
                                $tbody.append(`<tr>
                                <td style='display: none' 
                                data-product='${row.Producto}' 
                                data-name='${row.ProductName}' 
                                data-quantity='${row.Quantity}' 
                                data-unlimited='${row.Unlimited}' 
                                data-price='${row.Price}' 
                                data-taxable='${row.Taxable}' 
                                data-operationstaff='${row.OperationStaff}' 
                                data-setupstaff='${row.SetUpStaff}' 
                                data-volunteer='${row.Volunteer}' 
                                data-electric='${row.Electric}'
                                data-image='${row.Image}' 
                                >${row.Producto}</td>
                                <td style="${rowStyle}">${row.ProductName}</td>
                                <td style="${rowStyle}">
                                    ${row.Unlimited == 1 ? '<i class="fa-solid fa-infinity"></i>' : row.Quantity }
                                </td>
                                <td style="text-align: right;${rowStyle}">$ ${row.Price}</td>                            
                                </tr>`)
                            }
                        });
                        // Aquí renderizas tus filas con .append()
                        // Ejemplo: response.forEach(item => $tbody.append(...));
                    } else {
                        $tbody.html('<tr><td colspan="100%" class="text-center text-muted">No se encontraron productos</td></tr>');
                    }

                },
                error: function(xhr) {

                },
                cache: true
            });        

        }



        //RECUPERAR PRODUCTOS DE CATERGORIA
        function get_products(IdCat){
            const $tbody = $("#Category_Products_List");
                // 2. Insertamos el spinner de carga (Bootstrap)
                $tbody.html(`
                    <tr id="loading-row">
                        <td colspan="100%" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden"><?= Trd(112) ?></span>
                            </div>
                            <div class="mt-2 text-muted fw-bold"><?= Trd(113) ?></div>
                        </td>
                    </tr>
                `);        

            const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
            const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
            //alert(FHIp[0].replaceAll('-',''))
            SCat = IdCat;
            $.ajax ({
                url: API_BASE_URL+"get_products_categories/?IdCat="+IdCat+"&DateS="+FHI+"&DateE="+FHF, // URL de tu Web Service
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                //data: JSON.stringify({
                //    Id: IdCat
                //}),
                success: function(response) {

                    $tbody.empty();
                    if (response && response.products.length >= 0) {
                        response.products.forEach(row => {
                            // VALIDAR SI EXISTE
                            
                            //if (Codes.indexOf(row.Producto) < 0 ){
                            //alert(inventario.hasProduct(row.Producto))
                            //if ( ! inventario.hasProduct(row.Producto) && row.Quantity > 0){
                            if ( ! inventario.hasProduct(row.Producto) ){
                                
                                const rowStyle = row.Quantity <= 0 ? "color: red;" : "";
                                
                                $tbody.append(`<tr>
                                <td style='display: none' 
                                data-product='${row.Producto}' 
                                data-name='${row.ProductName}' 
                                data-quantity='${row.Quantity}' 
                                data-unlimited='${row.Unlimited}' 
                                data-price='${row.Price}' 
                                data-taxable='${row.Taxable}' 
                                data-operationstaff='${row.OperationStaff}' 
                                data-setupstaff='${row.SetUpStaff}' 
                                data-volunteer='${row.Volunteer}' 
                                data-electric='${row.Electric}'
                                data-image='${row.Image}' 
                                >${row.Producto}</td>
                                <td style="${rowStyle}">${row.ProductName}</td>
                                <td style="${rowStyle}">
                                    ${row.Unlimited == 1 ? '<i class="fa-solid fa-infinity"></i>' : row.Quantity }
                                </td>
                                <td style="text-align: right;${rowStyle}">$ ${row.Price}</td>                            
                                </tr>`)
                            }
                        });
                        // Aquí renderizas tus filas con .append()
                        // Ejemplo: response.forEach(item => $tbody.append(...));
                    } else {
                        $tbody.html('<tr><td colspan="100%" class="text-center text-muted">No se encontraron productos</td></tr>');
                    }

                },
                error: function(xhr) {

                },
                cache: true
            });        

        }

        //RECUPERAR PRODUCTOS RELACIONADOS
        function get_related_products(IdCat){
            const $tbody = $("#Products_Elements_List");
                // 2. Insertamos el spinner de carga (Bootstrap)
                $tbody.html(`
                    <tr id="loading-row">
                        <td colspan="100%" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden"><?= Trd(112) ?></span>
                            </div>
                            <div class="mt-2 text-muted fw-bold"><?= Trd(113) ?></div>
                        </td>
                    </tr>
                `);        

            const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
            const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
            $.ajax ({
                url: API_BASE_URL+"get_related_products/?IdP="+IdCat+"&IdCat="+SCat+"&DateS="+FHI+"&DateE="+FHF, // URL de tu Web Service
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                //data: JSON.stringify({
                //    Id: IdCat
                //}),
                success: function(response) {

                    $tbody.empty();

                    if (response && response.products.length > 0) {
                        response.products.forEach(row => {
                            //alert(inventario.showSummary())
                            row.Quantity = row.Quantity - inventario.getQuantity(row.Producto);
                            if (row.Quantity > 0 || row.Unlimited == 1){
                                if (row.Unlimited == 1)
                                    row.Quantity = 100

                                const rowStyle = row.Quantity <= 0 ? "color: red;" : "";

                                Rltpc+=1;
                                $tbody.append(`<tr>
                                <td style='display: none' 
                                data-product='${row.Producto}' 
                                data-name='${row.ProductName}' 
                                data-quantity='${row.Quantity}' 
                                data-unlimited='${row.Unlimited}' 
                                data-price='${row.Price}' 
                                data-taxable='${row.Taxable}' 
                                data-operationstaff='${row.OperationStaff}' 
                                data-setupstaff='${row.SetUpStaff}' 
                                data-volunteer='${row.Volunteer}' 
                                data-electric='${row.Electric}' 
                                data-image='${row.Image}' 
                                id='Rltpc${Rltpc}'>${row.Producto}</td>
                                <td style="${rowStyle}" >${row.ProductName}</td>
                                <td style="${rowStyle}" id='Rltpc${Rltpc}Cnt' >
                                    ${row.Unlimited == 1 ? '<i class="fa-solid fa-infinity"></i>' : row.Quantity }
                                </td>
                                <td style="text-align: right;${rowStyle}">$ ${row.Price}</td>
                                </tr>`)
                            }
                        });
                        //alert(Rltpc)
                        // Aquí renderizas tus filas con .append()
                        // Ejemplo: response.forEach(item => $tbody.append(...));
                    } else {
                        $tbody.html('<tr><td colspan="100%" class="text-center text-muted"><?= Trd(114) ?></td></tr>');
                    }

                },
                error: function(xhr) {

                },
                cache: true
            });        

        }        

        //REMOVER FILA
        function remove(id){
            const $el = $(`#row_${id}`);
            if ($el.length > 0) {
                product = $el.data('product')
                rel = $el.data('rel')

                if ($el.data('rel') > 0) {
                    cant_rmv = $(`#row_${id}_col_3`).val();
                    inventario.subtract(product, cant_rmv)
                    if (inventario.getQuantity(product) == 0)
                        inventario.remove(product)
                    // Si en 0 borrar de inventario
                    //$el.fadeOut(300, function() {
                    //    $(this).reomve();
                    //});
                    $el.remove();

                }else{
                    inventario.remove(product);
                    $el.remove();
                    //$el.fadeOut(300, function() {
                    //    $(this).remove();
                    //});
                    id+=1;
                    for(var i = id; i <=Row; i++){                
                        const $elr = $(`#row_${i}`);
                        if ($elr.length > 0) {
                            //alert(product + " - "+ $elr.data('rel'))
                            if (product ==  $elr.data('rel')){
                                cant_rmv = $(`#row_${id}_col_3`).val();
                                inventario.subtract($elr.data('product'),cant_rmv);
                                if (inventario.getQuantity(product) == 0)
                                    inventario.remove(product)
                                //$elr.remove();
                                //$elr.fadeOut(300, function() {
                                //    $(this).remove();
                                //});
                                $elr.remove();
                            }
                        }
                    }
                }
            }

            //Actualizar Disponibilidad

            for(var i = 1; i <=Row; i++){  
                const $ell = $(`#row_${i}`);
                if ($ell.length > 0) {
                    //alert(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')) + " prd " + $ell.data('product') +" " +$(`#row_${i}_col_2`).val() )
                    $(`#row_${i}_col_2`).val(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')));
                    $(`#row_${i}_col_2_`).val(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')));
                }            
            }

        autosave_lead()

        }

    //FORMATO CUSTOMER DE SELECT2
    // Función para dibujar las 2 filas en el listado
    function formatResult(repo) {
        if (repo.loading) return "<?php echo Trd(86)?>";
        // Si es un tag nuevo, mostramos un diseño simple o un aviso
        if (repo.newTag) {
            return $("<span><strong><?php echo Trd(87)?> </strong>" + repo.text + "</span>");
        }        
        // Estructura de dos filas con clases de Bootstrap 5
        var $container = $(
            "<div class='d-flex flex-column py-1'>" +
                "<div class='fw-bold text-dark'>" + repo.text + "</div>" +
                "<div class='text-muted small' style='font-size: 0.75rem;'>" + 
                    "<i class='fa-solid fa-location-dot me-1'></i>" + (repo.direccion || '') + 
                "</div>" +
            "</div>"
        );

        return $container;
    }

    function formatResultNoNew(repo) {
        if (repo.loading) return "<?php echo Trd(86)?>";
        // Si es un tag nuevo, mostramos un diseño simple o un aviso
        if (repo.newTag) {
            return $("<span><strong><?php echo Trd(87)?> </strong>" + repo.text + "</span>");
        }        
        // Estructura de dos filas con clases de Bootstrap 5
        var $container = $(
            "<div class='d-flex flex-column py-1'>" +
                "<div class='fw-bold text-dark'>" + repo.text + "</div>" +
                "<div class='text-muted small' style='font-size: 0.75rem;'>" + (repo.nombre || '') + 
                    "<br><i class='fa-solid fa-location-dot me-1'></i>" + (repo.direccion || '') +
                "</div>" +
            "</div>"
        );

        return $container;
    }    


    // Cómo se muestra el ítem seleccionado en el cuadro de búsqueda
    function formatRepo(repo) {
        return repo.text ;
    }


    // LOGIN PARA TOKEN
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
                //console.log('Login exitoso. Token:', jwtToken);
                
                // *** Almacena el token de forma segura (ej: localStorage) ***
                localStorage.setItem('apiToken', jwtToken); 
                
            },
            error: function(xhr, status, error) {
                // Error: Credenciales inválidas (401) o error del servidor
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido.';
                //console.error('Error de login:', errorMessage);
                //alert('Fallo el inicio de sesión: ' + errorMessage);
            }
        });
    }    
*/



document.addEventListener('DOMContentLoaded', function() {

        <?php 
        if ($_SESSION['role_id'] == 'ADMIN'){
        ?>
            // SECCION DE CALENDARIO 
            const fp = flatpickr("#calendarioRango", {
                mode: "range",
                inline: true,
                locale: "es",
                dateFormat: "Y-m-d"
            });
        <?php 
        }else{
        ?>
            // SECCION DE CALENDARIO 
            const fp = flatpickr("#calendarioRango", {
                mode: "range",
                inline: true,
                locale: "es",
                minDate: "today",
                dateFormat: "Y-m-d"
            });
        <?php 
        }
        ?>
    const hInicio = document.getElementById('hInicio');
    const hFin = document.getElementById('hFin');

    // 2. Llenar selectores con etiquetas AM/PM pero valores en 24h
    function llenarHoras() {
        for (let i = 0; i < 24; i++) {
            let valor24 = i.toString().padStart(2, '0') + ":00";
            let ampm = i >= 12 ? 'PM' : 'AM';
            let h12 = i % 12 || 12;
            let label = `${h12}:00 ${ampm}`;
            
            hInicio.innerHTML += `<option value="${valor24}">${label}</option>`;
            hFin.innerHTML += `<option value="${valor24}">${label}</option>`;
        }
    }
    llenarHoras();

    // 3. Sugerencia de +8 horas
    hInicio.addEventListener('change', function() {
        let h = parseInt(this.value.split(':')[0]);
        let nuevaH = (h + 8) % 24;
        hFin.value = nuevaH.toString().padStart(2, '0') + ":00";
    });


<?php if ($IdLead > 0 ){ ?>
    document.getElementById('btnReagendar').addEventListener('click', function() {
        const fechas = fp.selectedDates;
        
        if (fechas.length < 2) {
            lanzarMensaje("<?php echo Trd(88)?>",'alert',4000);
            return;
        }

        // Formato ISO local: YYYY-MM-DD
        const f1 = fechas[0].toLocaleDateString('sv-SE'); // sv-SE devuelve YYYY-MM-DD
        const f2 = fechas[1].toLocaleDateString('sv-SE');

        // Los inputs datetime-local requieren el formato: YYYY-MM-DDTHH:mm
        document.getElementById('fechahorainicio').value = `${f1}T${hInicio.value}`;
        document.getElementById('fechahorafin').value = `${f2}T${hFin.value}`;
        document.getElementById('fechahoraentrega').value = `${f1}T${hInicio.value}`;

        $.ajax({
            url: API_BASE_URL + "reschedule/",
            method: 'PUT',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ Lead: <?= $IdLead ?>,
                                   Start:`${f1}T${hInicio.value}`,
                                   End:`${f2}T${hFin.value}`
             }),
            success: function (response) {
                location.reload(); 
            },
            error: function () {
                //alert("No se pudo guardar la organización.");
                //$('#Organization').val(null).trigger('change');
            }
        });     
        bootstrap.Modal.getInstance(document.getElementById('modalReserva')).hide();
    });    
<?php }else{ ?>

    document.getElementById('btnConfirmar').addEventListener('click', function() {
        const fechas = fp.selectedDates;
        
        if (fechas.length < 2) {
            lanzarMensaje("<?php echo Trd(88)?>",'alerta',4000);
            return;
        }

        // Formato ISO local: YYYY-MM-DD
        const f1 = fechas[0].toLocaleDateString('sv-SE'); // sv-SE devuelve YYYY-MM-DD
        const f2 = fechas[1].toLocaleDateString('sv-SE');

        // Los inputs datetime-local requieren el formato: YYYY-MM-DDTHH:mm
        document.getElementById('fechahorainicio').value = `${f1}T${hInicio.value}`;
        document.getElementById('fechahorafin').value = `${f2}T${hFin.value}`;

        document.getElementById('fechahoraentrega').value = `${f1}T${hInicio.value}`;

        bootstrap.Modal.getInstance(document.getElementById('modalReserva')).hide();
    });

<?php } ?>
    // Valores iniciales
    hInicio.value = "08:00";
    hInicio.dispatchEvent(new Event('change'));


    
    //PARA SINCRONIZAR INPUTS DESKTOP Y MOVIL    
    // 1. Sincronizar campos de texto (Delegación de eventos)
    // Escuchamos el evento 'input' en todo el documento
    document.addEventListener('input', function(event) {
        // Verificamos si el elemento tiene el atributo data-sync
        const element = event.target;
        if (element.hasAttribute('data-sync')) {
            const syncKey = element.getAttribute('data-sync');
            const value = element.value;

            // Actualizar todos los elementos con el mismo data-sync
            document.querySelectorAll('[data-sync="' + syncKey + '"]').forEach(function(el) {
                if (el !== element) {
                    el.value = value;
                }
            });
        }
    });

    // 2. Sincronizar checkboxes (Tax) (Delegación de eventos)
    document.addEventListener('change', function(event) {
        const checkbox = event.target;
        // Verificamos si tiene la clase .sync-tax
        if (checkbox.classList.contains('sync-tax')) {
            const checked = checkbox.checked;

            // Actualizar todos los checkboxes de tax
            document.querySelectorAll('.sync-tax').forEach(function(cb) {
                if (cb !== checkbox) {
                    cb.checked = checked;
                }
            });
        }
    });

});


// AGREGAR PRODUCTO SELECCIONADO
function add_row(id,rel,data,clc=0){
    //data.quantity = data.quantity - 1;
    //if (rel == 0){
        //Codes.push(data.product);
        let Quan=0;
        let Disc=0;
        if (clc == 1){
            inventario.add(data.product, data.name, data.quantityS,data.quantity);
            Quan=data.quantityS;
            Disc=data.discount;
        }
        else{
            inventario.add(data.product, data.name, 1, data.quantity);
            Quan=1;
            Disc=0;
        }
            
    //}
    allow_change_price = 'readonly';
    select_on_focus = '';
    li_edit = '';
    if (data.change_rent_price == 1){
        allow_change_price =  ` onchange=" change_price('row_${id}',this.value); " `;
        select_on_focus = 'select-on-focus';
        li_edit = '<span class="input-group-text bg-transparent border-0 text-muted ps-1"><i class="fa-solid fa-pen fa-sm"></i></span>';
    }
        
        
    row = `
        <div class="row custom-row mx-0" id="row_${id}"
            data-id='${id}'
            data-rel='${rel}'
            data-product='${data.product}' 
            data-name='${data.name}' 
            data-quantity='${data.quantity}' 
            data-unlimited='${data.unlimited}' 
            data-price='${data.price}' 
            data-taxable='${data.taxable}' 
            data-operationstaff='${data.operationstaff}' 
            data-setupstaff='${data.setupstaff}' 
            data-volunteer='${data.volunteer}' 
            data-electric='${data.electric}',
            data-image='${data.image}',
            data-allowchange='${data.change_rent_price}'
        >
            <div class="col-sm-12 col-md-3">
                <div class="mobile-label">Producto</div>
                <div class="d-flex align-items-center">
                    ${rel > 0 ? '&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;':'<i class="fa-solid fa-grip-vertical text-muted me-2 d-none d-md-block"></i>'}
                    <span class="fw-semibold" >${data.name}</span>                    
                    <input type="hidden" id="row_${id}_col_1">
                </div>
            </div>    


            <!-- Contenedor para ícono, cantidad y referencia en móvil -->
            <div class="d-md-none mobile-price-row">
                <div class="icon-col">
                    <i class="fa-solid fa-list"></i>
                </div>            
                <div class="">
                    <div class="mobile-label" >Disp.</div>
                    ${data.unlimited == 1 
                    ? `<i class="fa-solid fa-infinity"></i><input type="hidden" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="" id="row_${id}_col_2" value="${(data.quantity - Quan)}" readonly>`
                    :`<input type="number" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="" id="row_${id}_col_2" value="${(data.quantity - Quan)}" readonly>`}
                    
                </div>
                <div class="">
                    <div class="mobile-label" >Cant.</div>

                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary border-0 bg-light btn-minus" 
                                type="button" 
                                onclick="cambiarCantidad('${id}', -1)">-</button>                    

                                    <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-cant_${id}" data-sync="cant_${id}" placeholder="" id="row_${id}_col_3" value='${Quan}'>

                        <button class="btn btn-outline-secondary border-0 bg-light btn-plus" 
                                type="button" 
                                onclick="cambiarCantidad('${id}', 1)">+</button>
                    </div>                    


                </div>
            </div>
            
            <!-- Versión desktop (se oculta en móvil) -->
            <div class="col-sm-2 col-md-1 d-none d-md-block">
                <i class="fa-solid fa-list"></i>
            </div>               
            <div class="col-sm-2 col-md-1 d-none d-md-block text-center">
                <div class="mobile-label ">Disp.</div>
                    ${data.unlimited == 1 
                    ? `<i class="fa-solid fa-infinity"></i><input type="hidden" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="0.00" id="row_${id}_col_2_" value="${(data.quantity - Quan)}" readonly>`
                    :`<input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="0.00" id="row_${id}_col_2_" value="${(data.quantity - Quan)}" readonly>`}
            </div>        
            <div class="col-sm-2 col-md-1 d-none d-md-block">
                <div class="mobile-label">Cant.</div>


                <div class="input-group input-group-sm">
                    <button class="btn btn-outline-secondary border-0 bg-light btn-minus" 
                            type="button" 
                            onclick="cambiarCantidad('${id}', -1)">-</button>
                    
                    <input type="text" 
                        class="form-control form-control-sm border-0 bg-light text-center p-0 sync-cant_${id}" 
                        data-sync="cant_${id}" 
                        id="row_${id}_col_3_" 
                        value="${Quan}" 
                        readonly>
                    
                    <button class="btn btn-outline-secondary border-0 bg-light btn-plus" 
                            type="button" 
                            onclick="cambiarCantidad('${id}', 1)">+</button>
                </div>

            </div>


            <!-- Contenedor para precio, tax y total en móvil -->
            <div class="d-md-none mobile-price-row">
                <div>
                    <div class="mobile-label">Desc.</div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                        <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-price_${id} decimals" data-sync="price_${id}" placeholder="0.00" id="row_${id}_col_4" value='${Disc}' onfocus="this.select()">
                    </div>
                </div>
                <div class="tax-col">
                    <div class="mobile-label">Tax</div>
                    <div class="text-center">
                        <input class="form-check-input sync-tax_${id}" data-sync="tax_${id}" type="checkbox" id="row_${id}_col_5" ${data.taxable == 1 ? 'checked' : '' }>
                    </div>
                </div>
                <div>
                    <div class="mobile-label">Total</div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                        <input type="text" class="form-control form-control-sm border-0 bg-light fw-bold text-end sync-total_${id} ${select_on_focus}" data-sync="total_${id}" ${allow_change_price} id="row_${id}_col_6" value='${data.price}'>
                        ${li_edit}
                    </div>
                </div>
            </div>
            
            <!-- Versión desktop precio, tax y total (se oculta en móvil) -->
            <div class="col-sm-5 col-md-2 d-none d-md-block">
                <div class="mobile-label">Desc.</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-price_${id} decimals" data-sync="price_${id}" placeholder="0.00" id="row_${id}_col_4_" value='${Disc}' onfocus="this.select()">
                </div>
            </div>
            <div class="col-sm-2 col-md-1 text-center d-none d-md-block">
                <div class="mobile-label">Tax</div>
                <input class="form-check-input sync-tax_${id}" data-sync="tax_${id}" type="checkbox" id="row_${id}_col_5_" ${data.taxable == 1 ? 'checked' : '' }>
            </div>
            <div class="col-sm-5 col-md-2 d-none d-md-block">
                <div class="mobile-label">Total</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-light fw-bold text-end sync-total_${id} ${select_on_focus}" data-sync="total_${id}" ${allow_change_price} id="row_${id}_col_6_" value='${data.price}'>
                    ${li_edit}
                </div>
            </div>

            <div class="col-md-1 text-center">
                <button class="btn btn-sm text-danger border-0 hover-bg-light" onclick="remove(${id});recalculate()">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>`;
        $("#lead_detail").append(row);

        
            for (f=1; f<= Row;f++){
                const $el = $(`#row_${f}`);
                if ($el.length > 0 ) {
                    if ($el.data('product') == data.product && id != f ){
                        const ajusteDisponible = document.getElementById(`row_${f}_col_2_`);
                        const ajusteDisponible_ = document.getElementById(`row_${f}_col_2`);
                        //if (delta > 0) {
                            ajusteDisponible.value = parseInt(ajusteDisponible.value) - 1;
                            ajusteDisponible_.value = ajusteDisponible.value
                        //}
                        //else{
                        //    ajusteDisponible.value = parseInt(ajusteDisponible.value) + 1;
                        //}
                        
                    }
                }
            }
        if (clc==0){                    
            recalculate();
            autosave_lead();
        }
}

//CAMBIO DE CANTIDADES -/+
function cambiarCantidad(id, delta) {
    const fila_afectada = document.getElementById(`row_${id}`);

    const spanDisponible_ = document.getElementById(`row_${id}_col_2`);
    const inputSeleccionado_ = document.getElementById(`row_${id}_col_3`);    

    const spanDisponible = document.getElementById(`row_${id}_col_2_`);
    const inputSeleccionado = document.getElementById(`row_${id}_col_3_`);
    // Convertimos a números
    let cantSeleccionada = parseInt(inputSeleccionado.value) || 0;
    let stockDisponible = parseInt(spanDisponible.value) || 0;

    if ( cantSeleccionada == 1 && delta < 0)
        return

    if (delta > 0) {
        // Lógica para SUMAR
        //if (stockDisponible > 0) {
            cantSeleccionada++;
            stockDisponible--;
            
            // Actualizar la clase (opcional: tu ProductCounter)
            inventario.add(fila_afectada.dataset.product, fila_afectada.dataset.name, 1);
        //} else {
            //alert("¡No hay más stock disponible!");
        //    lanzarMensaje("<?php echo Trd(89)?>", "alert", 5000);
        //}
    } else if (delta < 0) {
        // Lógica para RESTAR
        if (cantSeleccionada > 0) {
            cantSeleccionada--;
            stockDisponible++;
            
            // Actualizar la clase
            inventario.subtract(fila_afectada.dataset.product, 1);
        }
    }


    for (f=1; f<= Row;f++){
        const $el = $(`#row_${f}`);
        if ($el.length > 0 ) {
            if ($el.data('product') == fila_afectada.dataset.product && id != f ){
                const ajusteDisponible = document.getElementById(`row_${f}_col_2_`);
                const ajusteDisponible_ = document.getElementById(`row_${f}_col_2`);
                if (delta > 0) {
                    if (parseInt(ajusteDisponible.value) - 1 < 0)
                        ajusteDisponible.value = 0
                    else
                        ajusteDisponible.value = parseInt(ajusteDisponible.value) - 1;
                }
                else{
                    ajusteDisponible.value = parseInt(ajusteDisponible.value) + 1;
                }
                ajusteDisponible_.value = ajusteDisponible.value
            }
        }
    }
    for (f=1; f<= Rltpc;f++){
        const $el = $(`#Rltpc${f}`);
        if (inventario.hasProduct($el.data('product'))){
            quantity = inventario.getQuantity($el.data('product'));
            inventory= inventario.getInventory($el.data('product'));
            if ($el.data('unlimited') == 0){
                $el.data('quantity',inventory- quantity)
                $(`#Rltpc${f}Cnt`).html(inventory- quantity)
            }
        }
    }

    // Actualizar el DOM
    inputSeleccionado.value = cantSeleccionada;
    spanDisponible.value = stockDisponible;

    inputSeleccionado_.value = cantSeleccionada;
    spanDisponible_.value = stockDisponible;    

    // Feedback visual si se agota
    //spanDisponible.className = stockDisponible === 0 ? "fw-bold text-danger" : "fw-bold text-success";
    recalculate();

    clearTimeout(autoSaveTimerQuant);

    autoSaveTimerQuant = setTimeout(function() {
        autosave_lead();
    }, 5000);    

    
}

//RECCALCULAR POSICIONES
function recalculate(){
    
    let total = 0;
    //alert(total)
    for (f=1; f<= Row;f++){
        const $el = $(`#row_${f}`);
        //alert(f)
        //alert($el.length)
        if ($el.length > 0 ) {
            const Cnt = document.getElementById(`row_${f}_col_3`);
            const Tot = document.getElementById(`row_${f}_col_6`);
            const Tot_ = document.getElementById(`row_${f}_col_6_`);
            Tot.value = Cnt.value *  parseFloat($el.data('price'));
            Tot_.value = Tot.value;
            total = parseFloat(total) + parseFloat(Tot.value);
        }
    }
    //alert(total)
    $('#Item_Totals').val(total.toFixed(2));

    recalculate_totals()    
    
}

//RECALCULAR TOTALES
function recalculate_totals(){
    let total = 0;
    let DCT = 0;
    let SCT = 0;
    let DsCT = 0;
    let ADsCT = 0;
    let SubT = 0;
    let Total = 0;
    let Balance = 0;
    let AmountPaid = 0;
    total = $('#Item_Totals').val() * 1;

    if ($('#Distance_Charges_check').prop('checked'))
        DCT = $('#Distance_Charges_Total').val() * 1;
    if ($('#Staff_Charges_check').prop('checked'))
        SCT = $('#Staff_Charges_Total').val() * 1;
    if ($('#Discount_Charges_check').prop('checked'))
        DsCT = $('#Discount_Charges_Total').val() * 1;

    SubT= ((total + DCT + SCT) - DsCT);

    
    //APLICAR DESCUENTOS 
    for (let i=1; i<= TrDsc; i ++){

        const $el = $(`#Discount_Amount_${i}`);
        if ($el.length > 0 ) {    
            if ($(`#Discount_Charges_check_${i}`).prop('checked')){

                //alert($(`#Discount_Amount_${i}`).data('type'))
            
                if ($(`#Discount_Amount_${i}`).data('type') == 'gifcard'){
                    //alert($(`#Discount_Amount_${i}`).val() * 1)
                    SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                }            
                else if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                    SubT+= $(`#Discount_Amount_${i}`).val() * 1;
                }
                else{
                    if ($(`#Discount_Desc_${i} option:selected`).data('type') == 'amount'){
                        SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                    }
                    else{
                        $(`#Discount_Amount_${i}`).val(SubT *  ( $(`#Discount_Desc_${i} option:selected`).data('amount') / 100) ) * 1;
                        SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                    }
                }
            }
        }
        //Discount_Amount_

    }
    //APLICAR DESCUENTOS 

    $('#SubTotal').val(SubT.toFixed(2));

    //Tax
    if ($('#Tax').prop('checked')){
        $("#Excempt").show();

        $("#NoExcempt1").hide();
        $("#NoExcempt2").hide();
        
        let TaxPc = $('#TaxPc').val();
        let TaxAm = 0;
        $('#TaxAm').val(TaxAm.toFixed(2));
        SubT+= TaxAm;
    }
    else{
        $("#Excempt").hide();

        $("#NoExcempt1").show();
        $("#NoExcempt2").show();        
        let TaxPc = $('#TaxPc').val();
        let TaxAm = SubT * TaxPc;
        $('#TaxAm').val(TaxAm.toFixed(2));
        SubT+= TaxAm;        
    }

    $('#Total').val(SubT.toFixed(2));
    //$('#Depopsit').val('0.00');
    AmountPaid = $('#AmountPaid').val();
    Balance =  SubT - AmountPaid;
    $('#Balance').val(Balance.toFixed(2));
    $('#display-saldo-hoy').html(formatter.format(Balance.toFixed(2)) );
}

//CARGA DATOS DE ORGANIZACION
function load_organization(Id){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };
        $.ajax({
        url: API_BASE_URL + 'organizations/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {

            // 1. Validamos si la opción ya existe. Si no existe, la creamos y la agregamos.
            if ($('#Organization option[value="' + Id + '"]').length === 0) {
                // Creamos una nueva opción con el ID como valor y el Nombre como texto visible
                var newOption = new Option(data.Nombre, Id, true, true);
                // La añadimos al select
                $('#Organization').append(newOption).trigger('change');
            } else {
                // Si la opción ya existía, simplemente seteamos el valor y disparamos el cambio
                $('#Organization').val(Id).trigger('change');
            }        

            $('#Country').val( data.Pais);
            $('#Country').trigger('change');
            $('#State').val( data.Estado);
            $('#Street').val( data.Direccion+' '+data.Direccion2);
            $('#Cell').val( data.TelefonoCelular);
            $('#City').val( data.Ciudad);
            $('#Zip').val( data.CP);
            $('#CustomerEmail').val( data.Correo);
            aplicar_autosave();
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

//CARGA DATOS DE CLIENTE
function load_customer(Id){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'customers/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {

            // 1. Validamos si la opción ya existe. Si no existe, la creamos y la agregamos.
            if ($('#Customer option[value="' + Id + '"]').length === 0) {
                // Creamos una nueva opción con el ID como valor y el Nombre como texto visible
                var newOption = new Option(data.Nombre, Id, true, true);
                // La añadimos al select
                $('#Customer').append(newOption).trigger('change');
            } else {
                // Si la opción ya existía, simplemente seteamos el valor y disparamos el cambio
                $('#Customer').val(Id).trigger('change');
            }           

            $('#Country').val( data.Pais);
            $('#Country').trigger('change');
            $('#State').val( data.Estado);        
            $('#Street').val( data.Direccion+' '+data.Direccion2);
            $('#Cell').val( data.TelefonoCelular);
            $('#City').val( data.Ciudad);
            $('#Zip').val( data.CP);
            $('#CustomerEmail').val( data.Correo); 
            aplicar_autosave();           
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

//CARGA DATOS DE LUGAR DE EVENTO
function load_venue(Id,isFromReady = false){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'venues/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {
            //alert(response.Nombre)
            $('#EventCountry').val( data.Pais);
            $('#EventCountry').trigger('change');
            $('#EventState').val( data.Estado);
            $('#EventStreet').val( data.Direccion);
            //$('#EventStreet').val( data.Direccion+' '+data.Direccion2);
            $('#EventCity').val( data.Ciudad);
            $('#EventZip').val( data.CP);
            $('#EventLat').val( data.Lat);
            $('#EventLng').val( data.Lng);
            //distance_charge(data.CP,data.Pais)
            if (data.Lat == 0 || data.Lat == null){
                //alert('Ubicar Lat Lng')
            }
            if (!isFromReady) {
                distance_charge(data.CP, data.Pais);
            } else {
                console.log('distance_charge omitido por llamada desde document.ready');
            }            

            aplicar_autosave();
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

function get_venue(CusT,IdCus,NameVenue){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        const dataGlobal = {
            CusT: CusT,
            IdCus: IdCus,
            EventStreet: $('#Street').val(),
            EventCity: $('#City').val(),
            EventZip: $('#Zip').val(),
            EventCountry: $('#Country').val(),
            EventState: $('#State').val(),
            EventName: NameVenue
        };

        $.ajax({
        url: API_BASE_URL + 'get_venue/',
        type: 'POST',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        data: JSON.stringify(dataGlobal),        
        success: function(data) {
            venueId = data.Id;
            venueName = data.Nombre;
            // Create the DOM option: new Option(text, value, defaultSelected, selected)
            var newOption = new Option(venueName, venueId, true, true);
            $('#Venue').append(newOption).trigger('change');
            $('#EventCountry').val( data.Pais);
            $('#EventCountry').trigger('change');
            $('#EventState').val( data.Estado);
            $('#EventStreet').val( data.Direccion);
            //$('#EventStreet').val( data.Direccion+' '+data.Direccion2);
            $('#EventCity').val( data.Ciudad);
            $('#EventZip').val( data.CP);
            distance_charge(data.CP,data.Pais)
            autosave_lead();
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

//COPIAR DIRECCION DE ORGANIZACION/CLIENTE
function copy_ad(){

            if ($('#IdOrganization').val() > 0 || $('#Organization').val() >0 ){
                //alert('Organization');
                var venueText = $('#Organization').find(':selected').text();
                if ($('#IdOrganization').val() > 0)
                    get_venue('Org',$('#IdOrganization').val(),venueText)
                else
                    get_venue('Org',$('#Organization').val(),venueText)
            }
            else if ($('#IdCustomer').val() > 0 || $('#Customer').val() >0 ){
                var venueText = $('#Customer').find(':selected').text();
                if ($('#IdCustomer').val() > 0)
                    get_venue('Cus',$('#IdCustomer').val(),venueText)
                else
                    get_venue('Cus',$('#Customer').val(),venueText)

            }
            else{
                lanzarMensaje("<?php echo Trd(90)?>", "error");
            }
            //$('#EventStreet').val($('#Street').val());
            //$('#EventCity').val( $('#City').val());
            //$('#EventZip').val( $('#Zip').val());

            //distance_charge($('#Zip').val())
}

//CARGO POR DISTANCIA
function distance_charge(zip,ctry){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'distance_charge/'+zip,
        type: 'POST',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        data: JSON.stringify({ ZIPO: '<?php echo $account['CP']?>', CONO: '<?php echo $account['Pais']?>', ZIPD: zip, COND: ctry , APPLY: <?= $account['DistanceCharge'] ?>}),

        success: function(data) {
            if (data.cost.status == 'success') {
                let totaldist = data.cost.costo_total;
                $('#Distance_Charges_Total').val(totaldist.toFixed(2))
                $('#Distance_Charges_check').prop('checked', true);
                
                //$('#Tax').prop('checked', true);
                $('#TaxPc').val(data.cost.taxrate);
                
                recalculate_totals()
            }
            else{
                lanzarMensaje("No aplica el envio a ese destino",'error',5000);
            }
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

//ABRIR RUTA EN GOOGLE
function abrirRutaGoogleMaps() {
    // 1. Limpiar y codificar los textos para la URL

    const origenURL = encodeURIComponent('<?= $account['Direccion'].", ". $account['Ciudad'].", ".$account['Estado'].", ".$account['CP'] ?>');
    //const destinoURL = encodeURIComponent('ramon lopez velarde 1023, guadalajara, jalisco, 44840');
    Destiny = $('#EventStreet').val() + ', ' + $('#EventCity').val()+', ' + $('#EventState option:selected').text()+', '+ $('#EventZip').val()
    const destinoURL = encodeURIComponent(Destiny);

    // 2. Construir la URL usando template literals (backticks)
    const url = `https://www.google.com/maps/dir/?api=1&origin=${origenURL}&destination=${destinoURL}&travelmode=driving&layer=traffic`;

    window.open(url, '_blank');
}

//AGREGAR FILAS DE DESCUENTO ** AUN NO FUNCIONA
function Add_Discount(){
    if ($('#DiscountType').val()==''){
        //alert('No selecciono tipo descuento')
        lanzarMensaje("<?php echo Trd(91)?>", "alert", 5000);
    }
    if ($('#DiscountType').val()=='GifCard'){
        TrDsc+=1;
            let Discount = `
                <tr id="tr_discount_${TrDsc}">
                    <td>

                        <select class="form-select select-auto" id="Discount_Desc_${TrDsc}" name="Discount_Desc_${TrDsc}" onchange="" data-idgc="${TrDsc}" placeholder= "Enter Gif Card Code">
                            <option></option>
                        </select>                    

                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="checkbox" id="Discount_Charges_check_${TrDsc}" name="Discount_Charges_check_${TrDsc}" checked onchange="recalculate_totals()">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">$-</span>
                            <input type="text" data-type="gifcard" class="form-control text-end decimals" placeholder="0.00" id="Discount_Amount_${TrDsc}" name="Discount_Amount_${TrDsc}" onchange="ApplyDsc(${TrDsc})" readonly>
                        </div>                            
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#tr_discount_${TrDsc}').remove();recalculate_totals()">
                            <i class="fa-solid fa-trash"></i>
                        </button>    
                    </td>
                </tr>`;

            $('#tr_discount').before(Discount);
            inicializarSelectDescuento(`#Discount_Desc_${TrDsc}`);

    }    
    else if ($('#DiscountType').val()=='Fee'){
        TrDsc+=1;
            let Discount = `
                <tr id="tr_discount_${TrDsc}">
                    <td>
                        <input type="text" class="form-control" id="Discount_Desc_${TrDsc}" name="Discount_Desc_${TrDsc}" value="Fee" >
                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="checkbox" id="Discount_Charges_check_${TrDsc}" name="Discount_Charges_check_${TrDsc}" checked onchange="recalculate_totals()">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">$</span>
                            <input type="text" data-type="fee" class="form-control text-end decimals" placeholder="0.00" id="Discount_Amount_${TrDsc}" name="Discount_Amount_${TrDsc}" onchange="ApplyDsc(${TrDsc})">
                        </div>                            
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#tr_discount_${TrDsc}').remove();recalculate_totals()">
                            <i class="fa-solid fa-trash"></i>
                        </button>    
                    </td>
                </tr>`;

            $('#tr_discount').before(Discount);

    }
    else if ($('#DiscountType').val()=='Cupon'){
        TrDsc+=1;

        <?php 
                $disc='';
                $query = "select * FROM discounts WHERE DateExp > now() AND Active = 1 AND ( Used < Quantity  OR Unlimited = 1) ORDER BY Name";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($discounts) {
                    $disc.='<option value="">...</option>';
                    foreach ($discounts as $discount) {
                        $disc.='<option value="'.$discount['Id'].'" data-Id="'.$discount['Id'].'" data-type="'.$discount['Type'].'" data-amount="'.$discount['Amount'].'" >'.$discount['Code'].'</option>';
                    }
                    
                }   

        ?>

            let Discount = `
                <tr id="tr_discount_${TrDsc}">
                    <td>
                        <select class="form-select" id="Discount_Desc_${TrDsc}" name="Discount_Desc_${TrDsc}" onchange="ApplyDsc(${TrDsc})">
                            <?php echo $disc;?>
                        </select>
                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="checkbox" id="Discount_Charges_check_${TrDsc}" name="Discount_Charges_check_${TrDsc}" checked  onchange="recalculate_totals()">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">$-</span>
                            <input type="text" data-type="cupon" class="form-control text-end decimals" placeholder="0.00" id="Discount_Amount_${TrDsc}" name="Discount_Amount_${TrDsc}" readonly>
                        </div>                            
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#tr_discount_${TrDsc}').remove();recalculate_totals()">
                            <i class="fa-solid fa-trash"></i>
                        </button>    
                    </td>
                </tr>`;

            $('#tr_discount').before(Discount);    

    }
}

//MENSAJES EN LA BARRA INFERIOR
let msgTimer;

function lanzarMensaje(texto, tipo = 'normal', duracion = 4000) {
    const $barra = $('#barra-mensajes');
    const $texto = $('#mensaje-texto');
    const $icono = $('#mensaje-icono');

    // Resetear clases
    $barra.removeClass('msg-minimal-normal msg-minimal-exito msg-minimal-error msg-minimal-alerta d-none');
    
    // Configurar según tipo
    let clase, icono;
    switch(tipo) {
        case 'exito':  clase = 'msg-minimal-exito';  icono = '<i class="fas fa-check-circle"></i>'; break;
        case 'error':  clase = 'msg-minimal-error';  icono = '<i class="fas fa-times-circle"></i>'; break;
        case 'alerta': clase = 'msg-minimal-alerta'; icono = '<i class="fas fa-exclamation-triangle"></i>'; break;
        default:       clase = 'msg-minimal-normal'; icono = '<i class="fas fa-info-circle"></i>';
    }

    $barra.addClass(clase);
    $texto.text(texto);
    $icono.html(icono);

    // Animación de entrada
    $barra.hide().fadeIn(400);

    clearTimeout(msgTimer);
    if (duracion > 0) {
        msgTimer = setTimeout(cerrarBarra, duracion);
    }
}

function cerrarBarra() {
    $('#barra-mensajes').fadeOut(400);
}



function Cancelar(){
    const miModal = new bootstrap.Modal(document.getElementById('modalCancelar'));
    miModal.show();
}


//FUNCION PARA CARGAR CONTRATO 

function LoadDocument(DocumentType){
    if (!StartEvent || !EndEvent){
        lanzarMensaje('<?= Trd(161) ?>','alerta',4000)
        return;
    }

    if (!DeliveryDate) {
        lanzarMensaje('<?= Trd(162) ?>','alerta',4000)
        return;
    }    

    if (!CurrentOrganization && !CurrentCustomer){
        lanzarMensaje('<?= Trd(95) ?>','alerta',4000)
        return;
    }
    if (!CurrentVenue) {
        lanzarMensaje('<?= Trd(96) ?>','alerta',4000)
        return;
    }
    if (!CurrentSurface) {
        lanzarMensaje('<?= Trd(97) ?>','alerta',4000)
        return;
    }    
    

    const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
    const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
    const FHIp = FHI.split('T')
    const FHFp = FHF.split('T')
    TaxPc = 0
    TaxAm = 0

    if ($('#Tax').prop('checked')){
        TaxPc = 0;
        TaxAm = 0;        
    }
    else{
        TaxPc = $('#TaxPc').val() * 1;
        TaxAm = $('#TaxAm').val() * 1;
    }
    <?php


        $query = "select Template FROM document_center WHERE Tipo = 'contract' AND IdTemplate = 2 AND Activo = 1 AND Idioma ='$Idioma'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
        $Contract = $Template['Template'];

        $query = "select Template FROM document_center WHERE Tipo = 'quote' AND IdTemplate = 4 AND Activo = 1 AND Idioma ='$Idioma'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
        $Quote = $Template['Template'];

        $query = "select Template FROM document_center WHERE Tipo = 'picking' AND IdTemplate = 5 AND Activo = 1 AND Idioma ='$Idioma'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
        $Picking = $Template['Template'];        

    ?>
    let PagoAnticipado = $('#DepositAmount').val() ;
    if ($('#AmountPaid').val() * 1 > 0){
        PagoAnticipado = $('#AmountPaid').val() ;
    }
    const datosGenerales = {
        leadid: $('#Folio').val(),
        contractsentdate: "",
        company_logo: "<?=  "https://pub-546a2d9d368f4b87a457962de1d77955.r2.dev/".$account['Id']."/account/originals/".$account['Logo'] ?> ",
        company_name: "<?php echo $account['NombreCompania']?>",
        company_address:"<?php echo $account['Direccion']." ".$account['Direccion2'];?>",
        company_city:"<?php echo $account['Ciudad']." ".$account['CP'];?>",
        company_state:"<?php echo $account['Estado'];?>",
        company_phone:"<?php echo $account['TelefonoCelular']?>",

        organization: $('#Organization option:selected').text(),
        ctfirstname:$('#Customer option:selected').text(),
        ctlastname:"",
        eventstreet:$('#EventStreet').val(),
        eventcity:$('#EventCity').val(),
        eventstate:$('#EventState option:selected').text(),
        eventzip:$('#EventZip').val(),
        phones:$('#Cell').val(),
        startdate: FHIp[0],
        starttime: FHIp[1],
        enddate: FHFp[0],
        endtime: FHFp[1],
        deliverytype:$('#DeliveryType option:selected').text(),

        itemtotals: $('#Item_Totals').val(),
        distanecharges: $('#Distance_Charges_Total').val() * 1,
        staffcosts: $('#Staff_Charges_Total').val() * 1,
        discount: $('#Discount_Charges_Total').val() * 1,

        subtotal: $('#SubTotal').val(),
        taxexcempt: $('#IDTAX').val(),
        taxrate: TaxPc.toFixed(2),
        salestax: TaxAm.toFixed(2),
        tip: 0,
        total: $('#Total').val(),
        apayment: PagoAnticipado,
        ctr_balance_due: $('#Total').val() - PagoAnticipado,
        balancedue: $('#Total').val() - PagoAnticipado,        
        electric:"",
        signature:"",
        signeddate:""
    };
/*
        <?php  if ($account['Deposit'] == 1){?>                
            ctr_balance_due: $('#Total').val() - PagoAnticipado,
            balancedue: $('#Total').val() - PagoAnticipado,
        <?php  } else {?>
            ctr_balance_due: $('#Total').val(),
            balancedue: $('#Total').val(),        
        <?php  }?>
*/


    const productos = [];
    const descuentos = [];
    const extras = [];

    for (let i = 1; i <= Row; i++) {
        const $el = $(`#row_${i}`);
        if ($el.length > 0 ) {
            // Creamos el objeto con las llaves que espera nuestro contrato
            let item = {
                rentalid: '',
                //rentalname_url_photo: 'ajax/tmp/'+$el.data('image'),
                rentalname_url_photo: CFPUBLICURL+'/'+ID_CLIENTE+'/products_images/thumbnails/'+$el.data('image'),
                rentalname: $el.data('name'),
                fullrentaltime: "",
                rentalqty: $(`#row_${i}_col_3`).val(),
                rentaltotalprice: $(`#row_${i}_col_6`).val()
            };
            productos.push(item);
        }
    }    


    for (let i=1; i<= TrDsc; i ++){
        const $el = $(`#Discount_Amount_${i}`);
        if ($el.length > 0 ) {    
            if ($(`#Discount_Charges_check_${i}`).prop('checked')){
                if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                    descuentos.push({
                        concepto:$(`#Discount_Desc_${i}`).val(),
                        monto: $(`#Discount_Amount_${i}`).val()
                    });
                }
                else{
                    descuentos.push({
                        concepto:  $(`#Discount_Desc_${i} option:selected`).text(),
                        monto: $(`#Discount_Amount_${i}`).val()
                    });
                }
            }
        }
        //Discount_Amount_
    }            



    if (DocumentType =='Contract' ){
            $('#modalContratoLabel').html('Visualización de Contrato');
            const htmlRecibido = <?php echo json_encode($Contract); ?>;
            $('#Contract').html(htmlRecibido);
            const $contenedor = $('#contrato-dsj');
            const $cuerpoTabla = $('#lista-productos');
            const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
            ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);
            $('#ShareButton').hide();
            lanzarMensaje("<?php echo Trd(92)?>", "exito");
    }
    else if (DocumentType =='Quote'){
            $('#modalContratoLabel').html('Visualización de Cotización');
            const htmlRecibido = <?php echo json_encode($Quote); ?>;
            $('#Contract').html(htmlRecibido);
            const $contenedor = $('#cotizacion-dsj');
            const $cuerpoTabla = $('#lista-productos');
            const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
            ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);
            $('a[href*="link_to_accept"]').parent().hide();
            $('#ShareButton').show();
            lanzarMensaje("<?php echo Trd(93)?>", "exito");
            <?php 
            if ($account['Tips'] == 0){
            ?>
                    const $div1 = $('#tip-add');
                    $div1.addClass('d-none')
                    const $div2 = $('#tip-remove');
                    $div2.addClass('d-none')
                    const $div3 = $('#tip-adv-pay-pct');
                    $div3.addClass('d-none')            
            <?php
            }
            ?>
            //tip-add
            //tip-remove
            //tip-adv-pay-pct

    }
    else if (DocumentType =='Picking'){
        $('#modalContratoLabel').html('Visualización de Picking');
        const htmlRecibido = <?php echo json_encode($Picking);?>;    
        $('#Contract').html(htmlRecibido);
        const $contenedor = $('#picking-list-dsj');
        const $cuerpoTabla = $('#lista-productos');
        const $extracuerpoTabla = $('#extra-lista-productos');
        const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
        const $extrafilaPlantilla = $extracuerpoTabla.find('.extra-item-fila').first();
                


        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };
        $.ajax({
            url: API_BASE_URL + 'get_packing_list/'+$('#IdLead').val(),
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: misHeaders,
            success: function(data) {
                var listaDeItems = data.items;

                // 2. Recorrer los items (usando un forEach por ejemplo)
                listaDeItems.forEach(function(producto) {

                    extra = {
                        extra_item_name: producto.Item,
                        extra_qty: producto.Quantity
                    };
                    extras.push(extra);
                });

                extra = {
                    extra_item_name: '____________________________________',
                    extra_qty: '____'
                };            
                extras.push(extra);                    


                ejecutarRenderizadoPicking($contenedor, $cuerpoTabla,$extracuerpoTabla, $filaPlantilla,$extrafilaPlantilla,datosGenerales, productos, descuentos, extras);
                $('#ShareButton').hide();
                lanzarMensaje("<?= Trd(107) ?>", "exito");                        

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
    
}

function ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });


    const $contenedorDescuentos = $contenedor.find('#extra_discounts');
    const $filaOriginal = $contenedorDescuentos.find('tr').first();

    if ($filaOriginal.length > 0) {
        const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
        $contenedorDescuentos.empty(); // Limpiamos después de copiar la plantilla

        descuentos.forEach(desc => {

            let gfcrd  ='';
            const codigo = desc.concepto;
            const regex = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
            if (regex.test(codigo)) {
                gfcrd = '<?= Trd(155) ?><br>'
              
            }            

            let filaDescHtml = htmlPlantillaDesc
                .replace('*conceptdiscount*', gfcrd+desc.concepto)
                .replace('*discountconcept*', desc.monto);
            $contenedorDescuentos.append(filaDescHtml);
        });
    }    


    // 3. Lógica para ocultar IDs si el valor es 0
    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });

    <?php
        if ($account['Deposit'] == 0){
    ?>
        $('#tr-apay').hide();    
    <?php   
        }
    ?>    

    const miModal = new bootstrap.Modal(document.getElementById('modalContrato'));
    miModal.show();
}

function ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });


    const $contenedorDescuentos = $contenedor.find('#extra_discounts');
    const $filaOriginal = $contenedorDescuentos.find('tr').first();

    if ($filaOriginal.length > 0) {
        const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
        $contenedorDescuentos.empty(); // Limpiamos después de copiar la plantilla

        descuentos.forEach(desc => {
            let gfcrd  ='';
            const codigo = desc.concepto;
            const regex = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
            if (regex.test(codigo)) {
                gfcrd = '<?= Trd(155) ?><br>'
              
            } 

            let filaDescHtml = htmlPlantillaDesc
                .replace('*conceptdiscount*', gfcrd+desc.concepto)
                .replace('*discountconcept*', desc.monto);
            $contenedorDescuentos.append(filaDescHtml);
        });
    }    


    // 3. Lógica para ocultar IDs si el valor es 0
    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });


        <?php
        if ($account['Deposit'] == 1){
            if ($account['DepositType'] == 'percentage'){
            ?>
                $('#tip-adv-pay-qnt').hide();
            <?php
            }
            else{
            ?>
                $('#tip-adv-pay-pct').hide();
                $('#pay-qnt').html('$'+ $('#DepositAmount').val());
            <?php
            }
        }else{
        ?>
           $('#tr-apay').hide();
           $('#tip-adv-pay-qnt').hide();
           $('#tip-adv-pay-pct').hide();
        <?php                        
        }
        ?>    


    const miModal = new bootstrap.Modal(document.getElementById('modalContrato'));
    miModal.show();
}

function ejecutarRenderizadoPicking($contenedor, $cuerpoTabla,$extracuerpoTabla, $filaPlantilla,$extrafilaPlantilla, datosGenerales, productos, descuentos, extras)
{

    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });

    $extracuerpoTabla.find('tr:not(.extra-item-fila)').remove();

    extras.forEach(extra => {
        let nuevaFilaHtml = $extrafilaPlantilla[0].outerHTML;
        $.each(extra, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('extra-item-fila').css('display', ''); // Quitar display:none
        $extracuerpoTabla.append($nuevaFila);
    });    


    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });

    const miModal = new bootstrap.Modal(document.getElementById('modalContrato'));
    miModal.show();

}


// LLENADO DE ESTADOS POR PAIS

    $('#Country').on('change', function() {

        const selectedCountry = $(this).val();
        const $stateSelect = $('#State');

        // Limpiar selección actual
        $stateSelect.val('');

        // 2. IMPORTANTE: Ocultar y deshabilitar todos primero
        $stateSelect.find('option[data-country]')
            .prop('disabled', true)
            .attr('hidden', true) // Forzamos el atributo HTML
            .hide();

        if (selectedCountry) {
            // 3. Mostrar y habilitar los estados que coinciden
            // Removemos 'hidden' y usamos .show() para forzar la vista
            $stateSelect.find('option[data-country="' + selectedCountry + '"]')
                .prop('disabled', false)
                .removeAttr('hidden') 
                .show();
        }
    });

    $('#EventCountry').on('change', function() {

        const selectedCountry = $(this).val();
        const $stateSelect = $('#EventState');

        // Limpiar selección actual
        $stateSelect.val('');

        // 2. IMPORTANTE: Ocultar y deshabilitar todos primero
        $stateSelect.find('option[data-country]')
            .prop('disabled', true)
            .attr('hidden', true) // Forzamos el atributo HTML
            .hide();

        if (selectedCountry) {
            // 3. Mostrar y habilitar los estados que coinciden
            // Removemos 'hidden' y usamos .show() para forzar la vista
            $stateSelect.find('option[data-country="' + selectedCountry + '"]')
                .prop('disabled', false)
                .removeAttr('hidden') 
                .show();
        }
    });    


//AUTO GUARDADO GRAL

    function autosave_lead(){

        StartEvent  =$('#fechahorainicio').val();
        EndEvent    =$('#fechahorafin').val();
        DeliveryDate = $('#fechahoraentrega').val();        

        CurrentOrganization = $('#IdOrganization').val() || $('#Organization').val();
        CurrentCustomer = $('#IdCustomer').val() || $('#Customer').val();
        CurrentVenue = $('#IdVenue').val() || $('#Venue').val();
        CurrentSurface = $('#Surface').val();       

        <?php 
            //if (isset($lead) AND ( $lead['Status'] == 'confirmed' OR $lead['Status'] == 'canceled'))
            if (isset($lead) AND ( $lead['Status'] == 'canceled'))
                echo "return;";
        ?>

        const headerData = {
            IdLead : $('#IdLead').val(),
            FHI: $('#fechahorainicio').val(),
            FHF: $('#fechahorafin').val(),
            FHD: $('#fechahoraentrega').val(),

            Item_Totals: $('#Item_Totals').val(),

            ChkDstC:    $('#Distance_Charges_check').is(':checked') ? 1 : 0,
            DstC:       $('#Distance_Charges_Total').val(),
            ChkStCs:    $('#Staff_Charges_check').is(':checked') ? 1 : 0,
            StCs:       $('#Staff_Charges_Total').val(),
            ChkDsc:     $('#Discount_Charges_check').is(':checked') ? 1 : 0,
            Dsc:        $('#Discount_Charges_Total').val(),

            SubT:   $('#SubTotal').val(),
            TaxId:  $('#IDTAX').val(),
            TaxPc:  $('#TaxPc').val(),
            TaxAm:  $('#TaxAm').val(),
            Total:  $('#Total').val(),
            Depo:   $('#Deposit').val(),
            DepoA:   $('#DepositAmount').val(),
            BalDue: $('#Balance').val(),

            Referal: $('#Referal').val(),
            Organization:   $('#IdOrganization').val() || $('#Organization').val(),
            Customer:       $('#IdCustomer').val() || $('#Customer').val(),
            OkT:    $('#OkText').is(':checked') ? 1 : 0,
            WA:     $('#WindAlert').is(':checked') ? 1 : 0,
            AE:     $('#AutoEmail').is(':checked') ? 1 : 0,
            ME:     $('#ManualEmail').is(':checked') ? 1 : 0,
            CusNt:  $('#CustomerNote').val(),

            Venue:   $('#IdVenue').val() || $('#Venue').val(),
            EventName:  $('#EventName').val(),
            Surface:    $('#Surface').val(),
            Delivety:   $('#DeliveryType').val(),
            Nt1:        $('#Note_1').val(),
            Nt2:        $('#Note_2').val()
        };


     

        let detalleProductos = [];
        for (f=1; f<= Row;f++){
            const $el = $(`#row_${f}`);
            if ($el.length > 0 ) {
                detalleProductos.push({
                    id_referencia: f,
                    id_prd:     $el.data('product'),
                    id_rel:     $el.data('rel'),
                    nombre_prd: $el.data('name'),
                    price:      $el.data('price'),
                    cant:       $(`#row_${f}_col_3`).val(),
                    descuento:  $(`#row_${f}_col_4`).val(),
                    imp:        $(`#row_${f}_col_5`).is(':checked') ? 1 : 0,
                    precio:     $(`#row_${f}_col_6`).val()
                });
            }
        }

        let discounts = [];

        for (let i=1; i<= TrDsc; i ++){
            const $el = $(`#Discount_Amount_${i}`);
            if ($el.length > 0 ) {    
                if ($(`#Discount_Charges_check_${i}`).prop('checked')){
                    if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                        discounts.push({
                            IdDiscount:0 ,
                            Type:  'fee',
                            Amount: '0',
                            AmountVal: $(`#Discount_Amount_${i}`).val(),
                            Descript:''
                        });
                    }
                    else if ($(`#Discount_Amount_${i}`).data('type') == 'gifcard'){
                        //alert($(`#Discount_Amount_${i}`).data('type'));
                        //alert($(`#Discount_Amount_${i}`).val());
                        //alert($(`#Discount_Desc_${i} option:selected`).text())
                        discounts.push({
                            IdDiscount:$(`#Discount_Desc_${i}`).val() ,
                            Type:  $(`#Discount_Amount_${i}`).data('type'),
                            Amount: '',
                            AmountVal: $(`#Discount_Amount_${i}`).val(),
                            Descript: $(`#Discount_Desc_${i} option:selected`).text()
                        });
                    }                    
                    else{
                        discounts.push({
                            IdDiscount:$(`#Discount_Desc_${i} option:selected`).data('id') ,
                            Type:  $(`#Discount_Desc_${i} option:selected`).data('type'),
                            Amount:  $(`#Discount_Desc_${i} option:selected`).data('amount'),
                            AmountVal: $(`#Discount_Amount_${i}`).val(),
                            Descript: $(`#Discount_Desc_${i} option:selected`).text()
                        });
                    }
                }
            }
            //Discount_Amount_
        }        


        const dataGlobal = {
            header: headerData,
            detalle: detalleProductos,
            descuentos: discounts,
        };

        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
            url: API_BASE_URL + 'lead_auto_save/',
            type: 'PUT',
            contentType: 'application/json',
            headers: misHeaders,
            data: JSON.stringify(dataGlobal),
            success: function(response) {
                //console.log("¡Detalle guardado correctamente!");
                //alert(response.IdLead)
                $('#IdLead').val(response.IdLead);
                $('#token').val(response.IdLead);
                $('#UUID').val(response.UUID);                
                $('#Folio').val(response.Folio);    
                lanzarMensaje("<?php echo Trd(94)?>", tipo = 'exito');
                
            }
        });

    }

// VALIDACIONES DE APLICACION DE DESCUENTO
    $(document).on('input', 'input[id^="row_"]', function() {
        // Obtenemos el ID del input actual
        const currentId = $(this).attr('id');
        // Extraemos el número de fila (ej: de "row_25_col_4" sacamos "25")
        const rowId = currentId.split('_')[1];
        // Referenciamos los campos de esa fila específica
        const $inputDescuento = $(`#row_${rowId}_col_4_`);
        const $inputPrecio = $(`#row_${rowId}_col_6_`);
        // Convertimos a números para operar
        let descuento = parseFloat($inputDescuento.val()) || 0;
        let precio = parseFloat($inputPrecio.val()) || 0;
        // --- Lógica de Validación ---
        // 1. Evitar que sea menor a cero
        if (descuento < 0) {
            $inputDescuento.val(0);
            descuento = 0;
        }
        // 2. Evitar que el descuento sea mayor al precio
        if (descuento > precio) {
            // Si es mayor, lo igualamos al precio máximo permitido
            $inputDescuento.val(precio);
            descuento = precio;
            // Opcional: un toque visual para avisar del error
            $inputDescuento.css('border-color', 'red');
            setTimeout(() => $inputDescuento.css('border-color', ''), 1000);
        }
        // --- Disparar el timer de Autoguardado ---
        clearTimeout(autoSaveTimerQuant);
        autoSaveTimerQuant = setTimeout(function() {
            autosave_lead();
        }, 5000);
    });    

    $(document).on('input', 'input[id^="row_"]', function() {
        // Obtenemos el ID del input actual
        const currentId = $(this).attr('id');
        // Extraemos el número de fila (ej: de "row_25_col_4" sacamos "25")
        const rowId = currentId.split('_')[1];
        // Referenciamos los campos de esa fila específica
        const $inputDescuento = $(`#row_${rowId}_col_4`);
        const $inputPrecio = $(`#row_${rowId}_col_6`);
        // Convertimos a números para operar
        let descuento = parseFloat($inputDescuento.val()) || 0;
        let precio = parseFloat($inputPrecio.val()) || 0;
        // --- Lógica de Validación ---
        // 1. Evitar que sea menor a cero
        if (descuento < 0) {
            $inputDescuento.val(0);
            descuento = 0;
        }
        // 2. Evitar que el descuento sea mayor al precio
        if (descuento > precio) {
            // Si es mayor, lo igualamos al precio máximo permitido
            $inputDescuento.val(precio);
            descuento = precio;
            // Opcional: un toque visual para avisar del error
            $inputDescuento.css('border-color', 'red');
            setTimeout(() => $inputDescuento.css('border-color', ''), 1000);
        }
        // --- Disparar el timer de Autoguardado ---
        clearTimeout(autoSaveTimerQuant);
        autoSaveTimerQuant = setTimeout(function() {
            autosave_lead();
        }, 5000);
    });    


    function aplicar_autosave(){
        if ($('#IdLead').val() > 0){
                clearTimeout(autoSaveTimerQuant);
                autoSaveTimerQuant = setTimeout(function() {
                    autosave_lead();
                }, 5000);
        }        
    }

    function aplicar_autosave_10(){
        if ($('#IdLead').val() > 0){
                clearTimeout(autoSaveTimerQuant);
                autoSaveTimerQuant = setTimeout(function() {
                    autosave_lead();
                }, 10000);
        }        
    }    

    //APLICAR DECUENTOS
    function ApplyDsc(IdDsc){
        //alert(1)
        //alert($(`#Discount_Desc_${IdDsc} option:selected`).data('type'))
        //alert($(`#Discount_Desc_${IdDsc} option:selected`).data('amount'))
        if ($(`#Discount_Desc_${IdDsc} option:selected`).data('type') == 'amount'){
            $(`#Discount_Amount_${IdDsc}`).val($(`#Discount_Desc_${IdDsc} option:selected`).data('amount'))
            
        }
        
        recalculate_totals();
        
    }




    function ProcesarSinPago(){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };
        const dataGlobal = {
            Lead: $('#IdLead').val(),
            From: 'Lead'

        };
        $.ajax({
        url: API_BASE_URL + 'process_operation/',
        type: 'POST',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        data: JSON.stringify(dataGlobal),        
        success: function(data) {    

                lanzarMensaje("<?= Trd(108) ?>", "exito", 5000);
            },
            error: function () {
                lanzarMensaje("<?= Trd(109) ?>",'error',5000);
                $('#Organization').val(null).trigger('change');
            }
        });
    }


    function btnCancelarEvnto(){

        let formData = new FormData();        
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        let TipoC = '';
        let Cargo = '0';
        if ($('#GifCard').prop('checked'))
            TipoC = 'GC'
        else
            TipoC = 'DV'

        if ($('#checkcargo').prop('checked'))
            Cargo = 1;

        // Datos básicos
        formData.append('Lead', $('#IdLead').val());
        formData.append('Type', TipoC);
        formData.append('Cargo',Cargo);
        formData.append('Motivo', $('#MotivoCancelacion').val());  
        formData.append('Usuario', USER);  
          


        // Archivo de imagen (si existe)
        const fileInput = $('#evidence-file')[0].files[0];
        if (fileInput) {
            formData.append('evidence_img', fileInput);
        }




        $.ajax({
        url: API_BASE_URL + 'cancel_lead',
        method: 'POST',
        data: formData,
        headers: { 'Authorization': 'Bearer ' + TOKEN },            
        processData: false, // Vital para FormData
        contentType: false, // Vital para FormData  
        success: function(data) {
                lanzarMensaje("<?= Trd(110) ?>", "exito", 5000);
                location.reload(); 
            },
            error: function () {
                lanzarMensaje("<?= Trd(111) ?>",'error',5000);
                //$('#Organization').val(null).trigger('change');
            }
        });
    }    

    function change_price(id,valor){
        $(`#${id}`).data('price', valor); 
        $(`#r${id}`).attr('data-price', valor);    
        recalculate()
    }

<?php 
if (isset($lead) AND $lead['Status'] == 'confirmed'){
    echo "
    $(window).on('scroll', function() {
        var scrollTop = $(this).scrollTop();
        // Ajustamos la posición vertical basada en el scroll
        $('#watermark').css({
            'margin-top': -(scrollTop * 0.2) + 'px' 
        });
    });
    ";
}

if (isset($lead) AND $lead['Status'] == 'canceled'){

    echo "
    $(window).on('scroll', function() {
        var scrollTop = $(this).scrollTop();
        // Ajustamos la posición vertical basada en el scroll
        $('#watermark_cancel').css({
            'margin-top': -(scrollTop * 0.2) + 'px' 
        });
    });
    ";

}


?>    



const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
});

    </script>

<script>
let map;
let marker;
let geocoder;

// Coordenadas por defecto (Ej. Centro CDMX)
const posicionDefecto = { lat: <?= $account['Lat'] ?>, lng: <?= $account['Lng'] ?> };

function initMap() {
    geocoder = new google.maps.Geocoder();

    map = new google.maps.Map($('#mapa')[0], {
        zoom: 15,
        center: posicionDefecto,
    });

    marker = new google.maps.Marker({
        position: posicionDefecto,
        map: map,
        draggable: true,
        title: "<?= Trd(174) ?>"
    });

    actualizarCoordenadas(posicionDefecto.lat, posicionDefecto.lng);

    // Evento al arrastrar el icono
    marker.addListener("dragend", function (event) {
        actualizarCoordenadas(event.latLng.lat(), event.latLng.lng());
    });
}

function actualizarCoordenadas(lat, lng) {
    $('#temp_lat').val(lat);
    $('#temp_lng').val(lng);
}

function ubicarPorDireccion() {
    const direccion =$('#EventStreet').val() + ', ' + $('#EventCity').val()+', ' + $('#EventState option:selected').text()+', '+ $('#EventZip').val() + $('#EventCountry option:selected').text();
    if (!direccion) return;

    geocoder.geocode({ address: direccion }, function (results, status) {
        if (status === "OK" && results[0]) {
            const location = results[0].geometry.location;
            
            map.setCenter(location);
            marker.setPosition(location);
            actualizarCoordenadas(location.lat(), location.lng());
            if ($('#EventLat').val() == ""){
                $('#EventLat').val(location.lat());
                $('#EventLng').val(location.lng());
            }

        } else {
            lanzarMensaje("<?= Trd(168) ?>",'error',5000);
        }
    });
}

function setUbicacion(lat, lng) {
    // Convertir a número por si los valores vienen como string
    const nLat = parseFloat(lat);
    const nLng = parseFloat(lng);

    // Validar que las coordenadas sean números válidos
    if (isNaN(nLat) || isNaN(nLng)) {
        console.warn("Coordenadas no válidas:", lat, lng);
        return;
    }

    const nuevaPosicion = { lat: nLat, lng: nLng };

    // Actualizar las entradas temporales del modal
    actualizarCoordenadas(nLat, nLng);

    // Si el mapa y el marcador ya están instanciados, los movemos
    if (map && marker) {
        map.setCenter(nuevaPosicion);
        marker.setPosition(nuevaPosicion);
    }
}

$(document).ready(function () {
    
    // Al abrir el modal de Bootstrap, recalculamos el tamaño del mapa para evitar el lienzo gris
    $('#modalMapa').on('shown.bs.modal', function () {
        if (map) {
            google.maps.event.trigger(map, 'resize');

            if ($('#EventLat').val() != "" ){
                setUbicacion($('#EventLat').val(), $('#EventLng').val()) 
            }else{
                
                map.setCenter(marker.getPosition());
                ubicarPorDireccion()
            }
            
        }
    });


    // Pasar coordenadas temporales a los inputs del formulario principal
    $('#btnConfirmarUbicacion').on('click', function () {
        $('#EventLat').val($('#temp_lat').val());
        $('#EventLng').val($('#temp_lng').val());

        //DETONAR EL DUARDADO DE LA DIRECCION, SI NO TIENE VENUE
        if ($('#IdVenue').val() > 0 ){
            triggerAutoSaveVenue();
        }
        else{
            registrarVenue('');
        }
    });
});


</script>


</body>
</html>