<!-- SECCION VENUES -->
<b><?php echo Trd(50)?></b>
<form id="venues">    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Venue" class="form-label"><?php echo Trd(51)?></label>
            <input type="hidden" id="IdVenue" name="IdVenue" >
            <select class="form-select select-auto" id="Venue" name="Venue"  onchange="">
                <option></option>
            </select>    
            
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="EventName" class="form-label"><?php echo Trd(52)?></label>
            <input type="text" class="form-control" id="EventName" name="EventName" value = "<?php  echo !empty($lead['EventName']) ? $lead['EventName'] : '';?>" onchange="aplicar_autosave();" >
        </div>        
    </div>
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="EventStreet" class="form-label"><?php echo Trd(53)?></label>

            <div class="grupo-campo">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="EventStreet" name="EventStreet" autocomplete="off">
                    
                    <span class="input-group-text bg-light" style="cursor: pointer;"><i class="fa-solid fa-map-location-dot" onclick="abrirRutaGoogleMaps()"></i></span>
                </div>    
                <div id="lista-sugerencias_2" class="sugerencias"></div>
            </div>               

<!--            
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id= "EventStreet" name="EventStreet" placeholder="">
                <span class="input-group-text bg-light" style="cursor: pointer;"><i class="fa-solid fa-map-location-dot" onclick="abrirRutaGoogleMaps()"></i></span>
            </div>  
-->
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="Surface" class="form-label"><?php echo Trd(54)?></label>
            <input type="hidden" id="IdSurface" name="IdSurface" >
            <select class="form-select" id="Surface" name="Surface"  onchange="aplicar_autosave();">
                <option></option> 

                        <?php
                        if ($Idioma == 'es') 
                            $query = "select Id,Nombre FROM  surfaces ORDER BY Nombre";
                        else
                            $query = "select Id, Name as Nombre FROM  surfaces ORDER BY Name";

                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($resultados) {
                                foreach ($resultados as $registro) {
                                    echo "<option value='".$registro['Id']."'>".$registro['Nombre']."</option>";
                                }
                            }
                        ?>                
            </select>    
        </div>        
    </div>    
    <div class="row">

        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="Country" class="form-label"><?php echo Trd(55)?></label>
            <select class="form-select" id="EventCountry" name="EventCountry">
                <option><?php echo Trd(56)?></option> 
                    <?php
                        $query = "select Codigo,Pais FROM pais WHERE Idioma = 'es' ORDER BY Pais";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($resultados) {
                            foreach ($resultados as $registro) {
                                echo "<option value='".$registro['Codigo']."'>".$registro['Pais']."</option>";
                            }
                        }
                    ?>
            </select>
        </div>
        <div class='col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <label for="State" class="form-label"><?php echo Trd(57)?></label>
            <select class="form-select" id="EventState" name="EventState">
                <option value=""><?php echo Trd(58)?></option> 

                    <?php
                        $query = "select Id,Estado,CodigoPais FROM   estados_pais  WHERE Idioma = 'es' ORDER BY CodigoPais,Estado";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($resultados) {
                            foreach ($resultados as $registro) {
                                echo "<option data-country='".$registro['CodigoPais']."' value='".$registro['Id']."' hidden disabled>".$registro['Estado']."</option>";
                            }
                        }
                    ?>
            </select>
        </div>    

        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="EventCity" class="form-label"><?php echo Trd(59)?></label>
            <input type="text" class="form-control" id="EventCity" name="EventCity" placeholder=""> 
        </div>    
        <div class='col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <label for="EventZip" class="form-label"><?php echo Trd(60)?></label>
            <input type="text" class="form-control" id="EventZip" name="EventZip" placeholder="00000"> 
            <input type="hidden" class="form-control" id="EventLat" name="EventLat" > 
            <input type="hidden" class="form-control" id="EventLng" name="EventLng" > 
        </div>

        <div class='col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMapa">
                <i class="fas fa-map-marker-alt me-1"></i> <?= Trd(167) ?>
            </button>            
        </div>

    </div>     
    <div class="row">        
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="DeliveryType" class="form-label"><?php echo Trd(61)?></label>
            <input type="hidden" id="IdDeliveryType" name="IdDeliveryType" >
            <select class="form-select " id="DeliveryType" name="DeliveryType"  onchange="aplicar_autosave();">
                <option></option> 
                <option value="1">ON SITE</option>
            </select>    
        </div>        
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="Blacklisted" name="BlackListed">
                <label class="form-check-label" for="BlackListed">
                <?php echo Trd(62)?>
                </label>
            </div>        
        </div>        
    </div>   
    <div class="row">
        <div class='col-12'>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="nota1-tab" data-bs-toggle="tab" data-bs-target="#nota1" type="button" role="tab">
                        <?php echo Trd(63)?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nota2-tab" data-bs-toggle="tab" data-bs-target="#nota2" type="button" role="tab">
                        <?php echo Trd(64)?>
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="nota1" role="tabpanel" aria-labelledby="nota1-tab">
                    <label class="form-label text-muted small">Contenido de la Nota 1</label>
                    <textarea class="form-control form-control-minimal" rows="5" placeholder="" id="Note_1" name="Note_1" onchange="aplicar_autosave_10();">
<?php  echo !empty($lead['Note1']) ? $lead['Note1'] : '';?></textarea>
                </div>
                <div class="tab-pane fade" id="nota2" role="tabpanel" aria-labelledby="nota2-tab">
                    <label class="form-label text-muted small">Contenido de la Nota 2</label>
                    <textarea class="form-control form-control-minimal" rows="5" placeholder=""  id="Note_2" name="Note_2"  onchange="aplicar_autosave_10();">
<?php  echo !empty($lead['Note2']) ? $lead['Note2'] : '';?></textarea>
                </div>
            </div>   
        </div>   
    </div> 





</form>





<!-- Cargamos la API de Google Maps (Reemplaza YOUR_API_KEY por tu clave) -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&libraries=places&callback=initMap" async defer></script>



    <script>
        const buscador2 = document.getElementById('EventStreet');
        const listaSugerencias2 = document.getElementById('lista-sugerencias_2');
        let timeout2 = null;

        buscador2.addEventListener('input', function() {
            clearTimeout(timeout2);
            const valor = this.value.trim();

            if (valor.length < 4) {
                listaSugerencias2.style.display = 'none';
                return;
            }

            timeout2 = setTimeout(() => {
                fetch(`ajax/omap.php?buscar=${encodeURIComponent(valor)}`)
                    .then(response => response.json())
                    .then(data => {
                        listaSugerencias2.innerHTML = '';
                        
                        if(data.length === 0 || data.error) {
                            listaSugerencias2.style.display = 'none';
                            return;
                        }

                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'sugerencia-item';
                            div.textContent = item.display_name;
                            
                            div.addEventListener('click', () => {
                                // 1. Ponemos el texto completo en el buscador
                                buscador2.value = item.display_name;
                                listaSugerencias2.style.display = 'none';
                                
                                // 2. Extraemos el objeto address con seguridad
                                const addr = item.address || {};
                                
                                // 3. Rellenamos cada input mapeando las posibles variantes de la API
                                address = addr.road || addr.pedestrian || addr.cycleway || '';
                                housenumber  = addr.house_number;
                                cntry = addr.country ;
                                if (cntry == 'United States')
                                    ctry = 'USA';
                                if (cntry == 'Mexico' || cntry == 'México' )
                                    ctry = 'MX';
                                document.getElementById('EventStreet').value =  housenumber + ' ' +address ;
                                document.getElementById('EventCountry').value = ctry;

                                const countryInput = document.getElementById('EventCountry');
                                countryInput.value = ctry;
                                const event = new Event('change', { bubbles: true });
                                countryInput.dispatchEvent(event);

                                document.getElementById('EventCity').value = addr.city || addr.town || addr.village || addr.suburb || '';
                                //document.getElementById('State').value = addr.state || '';
                                document.getElementById('EventZip').value = addr.postcode || '';

                                document.getElementById('EventLat').value = item.lat || '';
                                document.getElementById('EventLng').value = item.lon || '';

                                const stateSelect = document.getElementById('EventState');
                                const targetState = (addr.state || '').toUpperCase(); // Convertimos el estado buscado a MAYÚSCULAS

                                if (targetState !== '') {
                                    let encontrado = false;

                                    // Recorremos todas las opciones del select
                                    for (let i = 0; i < stateSelect.options.length; i++) {
                                        const optionText = stateSelect.options[i].text.toUpperCase(); // Texto de la opción en MAYÚSCULAS
                                        
                                        if (optionText === targetState) {
                                            stateSelect.selectedIndex = i; // Seleccionamos la opción por su índice
                                            encontrado = true;
                                            break;
                                        }
                                    }

                                    // Si encontramos el estado, detonamos el evento change
                                    if (encontrado) {
                                        stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                } else {
                                    // Si addr.state viene vacío, reseteamos el select al valor por defecto
                                    stateSelect.value = '';
                                    stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                }                                

                                
                            });
                            
                            listaSugerencias2.appendChild(div);
                        });
                        
                        listaSugerencias2.style.display = 'block';
                    })
                    .catch(err => console.error('Error:', err));
            }, 500);
        });

        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!buscador2.contains(e.target) && !listaSugerencias2.contains(e.target)) {
                listaSugerencias2.style.display = 'none';
            }
        });


        document.getElementById('EventZip').addEventListener('input', function() {
            const cp = this.value.trim();
            // Detona automáticamente al alcanzar los 5 caracteres
            if (cp.length === 5) { 

                const urlPHP = 'ajax/omap_cp.php?buscar=' + encodeURIComponent(cp);
                fetch(urlPHP)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // AQUÍ ASIGNAS LOS VALORES A TUS INPUTS DE CIUDAD Y ESTADO
                        // Reemplaza 'id_input_ciudad' e 'id_input_estado' por los IDs reales de tu formulario
                        if (document.getElementById('EventCity')) {
                            document.getElementById('EventCity').value = data.ciudad;
                        }
                        if (document.getElementById('EventCountry')) {
                            document.getElementById('EventCountry').value = data.pais;
                        }                
                        if (document.getElementById('EventState')) {
                            document.getElementById('EventState').value = data.estado;
                        }
                        distance_charge(cp, data.pais);
                        //console.log('Ubicación encontrada:', data.ciudad, ',', data.estado);
                    } else {
                        lanzarMensaje(data.error || 'No se encontraron resultados para este CP.', "alert", 5000);                        
                    }
                })
                .catch(error => {
                    console.error('Error al consultar el CP:', error);
                });    



            }
        });        


    </script>