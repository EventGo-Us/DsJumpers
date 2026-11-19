
    <style>

        .grupo-campo { margin-bottom: 15px; position: relative; }
        /* Estilos del buscador y sugerencias */
        .sugerencias { position: absolute; width: 100%; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; display: none; border-radius: 0 0 4px 4px; }
        .sugerencia-item { padding: 10px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .sugerencia-item:hover { background-color: #f0f0f0; }
        .fila-doble { display: flex; gap: 15px; }
        .fila-doble .grupo-campo { flex: 1; }
    </style>

<b><?php echo Trd(33)?></b>
<form id="customers">
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Organization" class="form-label"><?php echo Trd(34)?></label>
            <input type="hidden" id="IdOrganization" name="IdOrganization" <?php  //if (isset($lead['Organization'])  AND $lead['Organization'] > 0 ) echo "value = '".$lead['Organization']."'" ;?>>
            <select class="form-select select-auto" id="Organization" name="Organization"  onchange="" >
                <option></option>
            </select>    
            
        </div>
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="OkText" name="OkText" 
                <?php  if (isset($lead['OkT']) && $lead['OkT'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="OkText">
                    <?php echo Trd(35)?>
                </label>
            </div>         
        </div>     

        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="WindAlert" name="WindAlert"
                <?php  if (isset($lead['WA']) && $lead['WA'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="WindAlert">
                    <?php echo Trd(36)?>
                </label>
            </div>         
        </div>     
    </div>

    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Customer" class="form-label"><?php echo Trd(37)?></label>
            <input type="hidden" id="IdCustomer" name="IdCustomer" <?php  //if (isset($lead['Customer'])  AND $lead['Customer'] > 0 ) echo "value = '".$lead['Customer']."'" ;?>>
            <select class="form-select select-auto select-custom-template" id="Customer" name="Customer" onchange="">
                <option></option>
            </select>    
        </div>
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="AutoEmail" name="AutoEmail"
                <?php  if (isset($lead['AE']) && $lead['AE'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="AutoEmail">
                    <?php echo Trd(38)?>
                </label>
            </div>         
        </div>     
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="ManualEmail" name="ManualEmail"
                <?php  if (isset($lead['ME']) && $lead['ME'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="ManualEmail">
                    <?php echo Trd(39)?>
                </label>
            </div>         
        </div>     
    </div>    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Street" class="form-label"><?php echo Trd(40)?></label>
            <div class="grupo-campo">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="Street" name="Street" autocomplete="off">
                    
                    <span class="input-group-text bg-light" style="cursor: pointer;" ><i class="fa-solid fa-file-export " onclick="copy_ad()"></i></span>
                </div>    
                <div id="lista-sugerencias" class="sugerencias"></div>                
            </div>              
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="Cell" class="form-label"><?php echo Trd(41)?></label>

            <select class="form-select select-auto select-custom-template" id="Cell" name="Cell" onchange="">
                <option></option>
            </select>              

<!--            
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="" id="Cell" name="Cell">
                <span class="input-group-text bg-light"><i class="fa-solid fa-phone"></i></span>
            </div>  
-->            
        </div>        
    </div>    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="Country" class="form-label"><?php echo Trd(42)?></label>
            <select class="form-select" id="Country" name="Country">
                <option><?php echo Trd(43)?></option> 
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
        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="State" class="form-label"><?php echo Trd(44)?></label>
            <select class="form-select" id="State" name="State">
                <option value=""><?php echo Trd(45)?></option> 

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
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="City" class="form-label"><?php echo Trd(46)?></label>
            <input type="text" class="form-control" id="City" name="City" placeholder=""> 
        </div>    
        
        <div class='col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <label for="Zip" class="form-label"><?php echo Trd(47)?></label>
            <input type="text" class="form-control" id="Zip" name="Zip" placeholder="00000"> 
        </div>
    </div>     
    <div class="row">
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="CustomerEmail" class="form-label"><?php echo Trd(48)?></label>
            <input type="email" class="form-control" id="CustomerEmail" name="CustomerEmail" placeholder=""> 
        </div>
    </div>     
    <div class="row">
        <div class='col-12 col-sm-12 col-md-4 col-lg-12 col-xl-12 col-xxl-12'>
            <label class="form-label text-muted small"><?php echo Trd(49)?></label>
            <textarea class="form-control form-control-minimal" rows="5" placeholder="" id="CustomerNote" name="CustomerNote" onchange="aplicar_autosave_10();" ><?php  echo !empty($lead['CustomerNote']) ? $lead['CustomerNote'] : '';?></textarea>
        </div>
    </div>
</form> 


    <script>
        const buscador = document.getElementById('Street');
        const listaSugerencias = document.getElementById('lista-sugerencias');
        let timeout = null;
        let omapController = null;

        buscador.addEventListener('input', function() {
            clearTimeout(timeout);
            if (omapController) {
                omapController.abort();
            }
            const valor = this.value.trim();

            if (valor.length < 4) {
                listaSugerencias.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                omapController = new AbortController();
                fetch(`ajax/omap.php?buscar=${encodeURIComponent(valor)}`, { signal: omapController.signal })
                    .then(response => response.json())
                    .then(data => {
                        listaSugerencias.innerHTML = '';
                        
                        if(data.length === 0 || data.error) {
                            listaSugerencias.style.display = 'none';
                            return;
                        }

                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'sugerencia-item';
                            div.textContent = item.display_name;
                            
                            div.addEventListener('click', () => {
                                // 1. Ponemos el texto completo en el buscador
                                buscador.value = item.display_name;
                                listaSugerencias.style.display = 'none';
                                
                                // 2. Extraemos el objeto address con seguridad
                                const addr = item.address || {};
                                
                                // 3. Rellenamos cada input mapeando las posibles variantes de la API
                                address = addr.road || addr.pedestrian || addr.cycleway || '';
                                housenumber  = addr.house_number;
                                cntry = addr.country ;
                                if (cntry == 'United States')
                                    ctry = 'USA';
                                else if (cntry == 'Mexico' || cntry == 'México' )
                                    ctry = 'MX';
                                else 
                                    ctry = cntry;
                                document.getElementById('Street').value =  housenumber + ' ' +address ;
                                document.getElementById('Country').value = ctry;

                                const countryInput = document.getElementById('Country');
                                countryInput.value = ctry;
                                const event = new Event('change', { bubbles: true });
                                countryInput.dispatchEvent(event);

                                document.getElementById('City').value = addr.city || addr.town || addr.village || addr.suburb || '';
                                //document.getElementById('State').value = addr.state || '';
                                document.getElementById('Zip').value = addr.postcode || '';

                                const stateSelect = document.getElementById('State');
                                const normalizeState = value => (value || '')
                                    .normalize('NFD')
                                    .replace(/[\u0300-\u036f]/g, '')
                                    .toUpperCase()
                                    .trim();
                                const rawStateName = normalizeState(addr.state);
                                const stateId = String(addr.state_id || '');

                                let encontrado = false;
                                for (let i = 0; i < stateSelect.options.length; i++) {
                                    const option = stateSelect.options[i];
                                    const optionText = normalizeState(option.text);
                                    const optionValue = normalizeState(option.value);

                                    if ((stateId && option.value === stateId) ||
                                        (rawStateName && optionText === rawStateName)) {
                                        stateSelect.selectedIndex = i;
                                        encontrado = true;
                                        break;
                                    }
                                }

                                if (encontrado) {
                                    stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                } else {
                                    stateSelect.value = '';
                                    stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                }

                                
                            });
                            
                            listaSugerencias.appendChild(div);
                        });
                        
                        listaSugerencias.style.display = 'block';
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            console.error('Error:', err);
                        }
                    });
            }, 700);
        });

        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!buscador.contains(e.target) && !listaSugerencias.contains(e.target)) {
                listaSugerencias.style.display = 'none';
            }
        });

        document.getElementById('Zip').addEventListener('input', function() {
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
                        if (document.getElementById('City')) {
                            document.getElementById('City').value = data.ciudad;
                        }
                        if (document.getElementById('Country')) {
                            document.getElementById('Country').value = data.pais;
                        }                
                        if (document.getElementById('State')) {
                            document.getElementById('State').value = data.estado;
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