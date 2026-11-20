<?php 
function armar_formulario_add($tabla,$etiqueta,$idioma){
    global $db;
            $query = "            
                SELECT
                    modal_add.*, 
                    titulos_campos_tablas.Titulo, 
                    placeholder_campos_tablas.Titulo as PlaceHolder, 
                    placeholder_campos_tablas.Validacion
                FROM
                    modal_add
                    INNER JOIN
                    titulos_campos_tablas
                    ON 
                        modal_add.Tabla = titulos_campos_tablas.Tabla AND
                        modal_add.Campo = titulos_campos_tablas.Campo
                    LEFT JOIN
                    placeholder_campos_tablas
                    ON 
                        titulos_campos_tablas.Tabla = placeholder_campos_tablas.Tabla AND
                        titulos_campos_tablas.Campo = placeholder_campos_tablas.Campo AND
                        titulos_campos_tablas.Idioma = placeholder_campos_tablas.Idioma
                WHERE
                    modal_add.Tabla = ? AND
                    modal_add.Etiqueta = ? AND
                    modal_add.TipoCampo <> 'eval' AND
                    modal_add.TipoCampo <> 'auto' AND
                    modal_add.Visible = 1 AND
                    titulos_campos_tablas.Idioma = ?
                ORDER BY
                    modal_add.Fila ASC,
                    modal_add.Id ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $tabla);
            $stmt->bindValue(2, $etiqueta);
            $stmt->bindValue(3, $idioma);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $FilaN=0;
                // g-4 para una separación horizontal y vertical más limpia y profesional
                echo '<div class="row g-4">';
                foreach ($resultados as $row) {

                    $Fila           = $row['Fila'];
                    $ColXS          = $row['ColXS'];
                    $ColSM          = $row['ColSM'];
                    $ColMD          = $row['ColMD'];
                    $ColLG          = $row['ColLG'];
                    $ColXL          = $row['ColXL'];
                    $ColXXL         = $row['ColXXL'];
                    $Titulo         = $row['Titulo'];
                    $Campo          = $row['Campo'];
                    $TipoCampo      = $row['TipoCampo'];
                    $Requerido      = $row['Requerido'];
                    $LargoMin       = $row['LargoMin'];
                    $LargoMax       = $row['LargoMax'];
                    $PlaceHolder    = $row['PlaceHolder'];
                    $Validacion     = $row['Validacion'];
                    $Patron         = $row['Patron'];
                    $SoloLectura    = $row['SoloLectura'];
                    $Alineacion     = $row['Alineacion'];
                    $AnchoPX        = $row['AnchoPX'];
                    $TablaDts       = $row['TablaDts'];
                    $CampoValor     = $row['CampoValor'];
                    $CampoDescripcion = $row['CampoDescripcion'];
                    $Filtro         = $row['Filtro'];
                    $Regla          = $row['Regla'];
                    $TablaDts2      = $row['TablaDts2'];
                    $CampoFiltro2   = $row['CampoFiltro2'];
                    $CampoValor2    = $row['CampoValor2'];
                    $CampoDescripcion2  = $row['CampoDescripcion2'];
                    $Receptor       = $row['Receptor'];                    
                    $Filtro2        = $row['Filtro2'];
                    $ValorCampo     = $row['Valor'];

                    // Clases estándar de Bootstrap 5 (sin el -sm para mejor consistencia visual)
                    $form_control = "form-control";
                    $form_select  = "form-select";

                    if ($FilaN != $Fila AND $FilaN != 0){
                        echo '</div>';
                        echo '<div class="row g-4">';
                    }   
                    $FilaN = $Fila;

                    $aps="'";
                    if ($Patron!="")
                        $Patron = ' pattern="'.$Patron.'" ';            
                                                
                    if ($Requerido=='X')
                        $Requerido = 'required=""';
                    else
                        $Requerido = '';
                    if ($SoloLectura=='X')
                        $SoloLectura = 'readonly="readonly"';
                    else
                        $SoloLectura = '';                    

                    echo "<div class='col-$ColXS col-sm-$ColSM col-md-$ColMD col-lg-$ColLG col-xl-$ColXL col-xxl-$ColXXL'>";

                    // Contenedor por campo para controlar márgenes de forma segura
                    echo '<div class="mb-1">';

                    switch ($TipoCampo) {
                        case 'option':
                                $option= substr($Campo, 0, -2);
                                echo '<div class="form-check my-2">';
                                echo '<input class="form-check-input" type="radio" name="'.$option.'" id="'.$Campo.'" value = "'.$Alineacion.'">';
                                echo '<label class="form-check-label fw-medium text-dark" for="'.$Campo.'">';
                                echo $Titulo;
                                echo '</label>';
                                echo '</div>';
                            break;                        
                        case 'label':
                            echo "<label class='form-label fw-semibold text-secondary d-block'>$Titulo</label>";
                            break;                        
                        case 'titulo':
                            echo '<h5 class="border-bottom pb-2 mb-3 text-primary fw-bold">'.$Titulo.'</h5>';
                            break;                          
                        case 'hidden':
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="'.$TipoCampo.'" >';
                            break;                          
                        case 'text':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'random':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" value="'.generarCodigoAlfanumerico().'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                        case 'number';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' numbers-only w-100" type="number" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'decimal';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' decimals w-100" type="text" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'currency';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' decimals currency w-100" type="text" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';                            
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;   
                        case 'area';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" style="min-height: 100px;" rows="'.$LargoMin.'" cols="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$SoloLectura.'>'.$ValorCampo.'</textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'html';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" style="min-height: 100px;" rows="'.$LargoMin.'" cols="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$SoloLectura.'></textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                        case 'range':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" type="'.$TipoCampo.'" class="form-range w-100" style="" min="'.$LargoMin.'" max="'.$LargoMax.'" />';
                            break;
                        case 'date':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'time':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'tel':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'url':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'password':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'color':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="form-control form-control-color w-100" type="'.$TipoCampo.'" >';
                            break;
                        case 'email':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'select':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            if ($TablaDts2!= "")
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" style=""  onChange="change_send2(this.value,'.$aps.$Campo.$aps.','.$aps.$Receptor.$aps.','.$aps.$tabla.$aps.','.$aps.$aps.')" '.$Requerido.' '.$SoloLectura.'>';
                            else
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" style="" '.$Requerido.' '.$SoloLectura.'>';

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                    $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
                                    echo "QRY Idioma $query";
                            }
                            else{
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                            }                             
                            if ($TablaDts == "templates")
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";

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
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'checkbox':
                            echo '<div class="form-check form-switch pt-4">';
                                echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-check-input" type="checkbox" '.$Requerido.' '.$SoloLectura.'>';
                                    echo '<label class="form-check-label fw-semibold text-dark small" for="'.$Campo.'">';
                                        echo $Titulo;
                                    echo '</label>';
                            echo '</div>';
                            break;
                        case 'button':
                                echo "<button type='button' class='btn btn-primary w-100 mt-4 shadow-sm' name='$Campo' id='$Campo'>$Titulo</button>";
                            break;                            

                        case 'img':

echo '<label class="form-label fw-bold text-dark small mb-2" for="file_'.$Campo.'">'.$Titulo.'</label>';

// CONTENEDOR PRINCIPAL: Funciona como botón y como contenedor del preview
echo '<div id="dropzone_'.$Campo.'" class="position-relative border-dashed bg-light d-flex flex-column align-items-center justify-content-center text-center" style="width: 200px; height: 150px; border-radius: 12px; transition: all 0.2s ease-in-out; overflow: hidden;">';
    
    // 1. ESTADO INICIAL: Texto e Icono para cargar
    echo '<div id="prompt_'.$Campo.'" class="p-2 pointer-events-none d-flex flex-column align-items-center justify-content-center h-100">';
        echo '<i class="bi bi-cloud-arrow-up text-secondary fs-3 mb-1"></i>';
        echo '<span class="fw-semibold text-secondary small">Cargar imagen</span>';
        if ($Regla != "") {
            echo '<div class="text-muted" style="font-size: 10px; max-width: 180px;">'.$Regla.'</div>';
        }
    echo '</div>';

    // 2. ESTADO CARGADO: Contenedor del Preview (Oculto por defecto)
    echo '<div id="gallery_'.$Campo.'" class="w-100 h-100 d-none position-relative" style="z-index: 3;">';
        echo '<img id="img_preview_'.$Campo.'" class="w-100 h-100" style="object-fit: cover;" alt="Preview">';
        // Botón Borrar flotante
        echo '<button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-2 d-flex align-items-center justify-content-center p-0 shadow" style="width: 26px; height: 26px; z-index: 4;" onclick="removePreview(\''.$Campo.'\', event)" title="Eliminar">';
            echo '<i class="fa-solid fa-trash-can" style="font-size: 11px;"></i>'; // Icono cambiado a Font Awesome
        echo '</button>';
    echo '</div>';

    // 3. INPUT REAL (Invisible pero cubre todo el recuadro para detectar clics y drag&drop)
    echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" type="file" accept="'.$Filtro.'" style="cursor: pointer; z-index: 2;">';
    echo '<input name="'.$Campo.'" id="'.$Campo.'" type="text" style="display: none;">';

echo '</div>';

// JAVASCRIPT INTEGRADO
echo "
<script>
(function() {
    var fileInput = document.querySelector('#file_".$Campo."');
    
    fileInput.addEventListener('change', function () {
        var files = this.files;
        if(files.length > 0){
            // Ejecuta tu lógica de subida
            uploadFile(files[0], '".$Campo."', 'img'); 
            // Ejecuta el cambio visual del preview
            previewImageInline(files[0], '".$Campo."');
        }
    }, false);  
})();

if (typeof window.previewImageInline !== 'function') {
    window.previewImageInline = function(file, field) {
        if (!file.type.match(/image.*/)) {
            alert('Por favor, selecciona un archivo de imagen válido.');
            return;
        }
    
        var reader = new FileReader();
        reader.onload = function(e) {
            // Asignar la imagen al src del preview interno
            document.getElementById('img_preview_' + field).src = e.target.result;
            
            // Alternar visibilidades dentro del mismo recuadro
            document.getElementById('prompt_' + field).classList.add('d-none');
            document.getElementById('gallery_' + field).classList.remove('d-none');
            
            // Desactivar temporalmente el input file para que no estorbe el botón de borrar
            document.getElementById('file_' + field).style.pointerEvents = 'none';
        };
        reader.readAsDataURL(file);
    };
}

if (typeof window.removePreview !== 'function') {
    window.removePreview = function(field, event) {
        // Evitamos que el clic en borrar active el input de abajo
        event.stopPropagation();
        event.preventDefault();
        
        // Limpiar valores
        document.getElementById('file_' + field).value = '';
        document.getElementById('img_preview_' + field).src = '';
        
        // Volver a mostrar el estado inicial Cargar imagen
        document.getElementById('gallery_' + field).classList.add('d-none');
        document.getElementById('prompt_' + field).classList.remove('d-none');
        
        // Reactivar el input file para permitir una nueva carga
        document.getElementById('file_' + field).style.pointerEvents = 'auto';
    };
}
</script>
";

                            break;

                        case 'file':
                            echo '<label class="form-label fw-semibold text-dark small mb-1" for="file_'.$Campo.'">'.$Titulo.'</label>';
                            echo '<div class="card p-3 border-dashed bg-light shadow-none">';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'" class="form-control bg-white" type="file"  accept="'.$Filtro.'">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="hidden" >';
                            
                            if ($Regla != "")
                                echo '<div class="form-text small text-muted mt-1">'.$Regla.'</div>';

                            echo '<div id="gallery_'.$Campo.'" class="row g-2 mt-2"></div>';
                            echo '</div>';               

                            echo "
                            <script>
                            var ".$Campo." = document.querySelector('#file_".$Campo."');
                            ".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'".$Campo."','file'); 
                                }
                            }, false);                    
                            </script>
                            ";
                            break;                            

                            case 'complete':
                                echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="selectpicker form-control border rounded shadow-sm" style="" '.$Requerido.' '.$SoloLectura.'>';

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                    $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
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
                                echo '<div class="invalid-feedback">'.$Validacion.'</div>';

                            break;                            
                    }
                    echo '</div>'; // Cierra mb-1
                    echo '</div>'; // Cierra col-##
                }
                echo '</div>'; // Cierra row final
            }
}

function generarCodigoAlfanumerico() {
    $timestamp = base_convert(str_replace('.', '', microtime(true)), 10, 36);
    $caracteres = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomPart = "";
    for ($i = 0; $i < 16; $i++) {
        $randomPart .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    $combinado = strtoupper(substr($timestamp . $randomPart, 0, 16));
    return implode("-", str_split($combinado, 4));
}
?>