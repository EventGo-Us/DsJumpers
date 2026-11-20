<?php 
function armar_formulario_edit($tabla,$etiqueta,$idioma){
    global $db;
            $query = "            
                SELECT
                    modal_edit.*, 
                    titulos_campos_tablas.Titulo, 
                    placeholder_campos_tablas.Titulo as PlaceHolder, 
                    placeholder_campos_tablas.Validacion
                FROM
                    modal_edit
                    INNER JOIN
                    titulos_campos_tablas
                    ON 
                        modal_edit.Tabla = titulos_campos_tablas.Tabla AND
                        modal_edit.Campo = titulos_campos_tablas.Campo
                    LEFT JOIN
                    placeholder_campos_tablas
                    ON 
                        titulos_campos_tablas.Tabla = placeholder_campos_tablas.Tabla AND
                        titulos_campos_tablas.Campo = placeholder_campos_tablas.Campo AND
                        titulos_campos_tablas.Idioma = placeholder_campos_tablas.Idioma
                WHERE
                    modal_edit.Tabla = ? AND
                    modal_edit.Etiqueta = ? AND
                    modal_edit.TipoCampo <> 'eval' AND
                    modal_edit.TipoCampo <> 'auto' AND
                    modal_edit.Visible = 1 AND
                    titulos_campos_tablas.Idioma = ? 
                ORDER BY
                    modal_edit.Fila ASC,
                    modal_edit.Id ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $tabla);
            $stmt->bindValue(2, $etiqueta);
            $stmt->bindValue(3, $idioma);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $FilaN = 0;
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
                    $Campo          = "edit_".$row['Campo'];
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
                            echo "<label for='$Campo' class='form-label fw-semibold text-secondary d-block'>$Titulo</label>";
                            break;                            
                        case 'titulo':
                            echo '<h5 class="border-bottom pb-2 mb-3 text-primary fw-bold">'.$Titulo.'</h5>';
                            break;                          
                        case 'hidden':
                            if ( $Campo == "edit_IId")
                                $Campo.="_".$tabla;
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="'.$TipoCampo.'" >';
                            break;                        
                        case 'text':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
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
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" data-tipo="area" style="min-height: 100px;" rows="'.$LargoMin.'" cols="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$SoloLectura.'>'.$ValorCampo.'</textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'html';
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" data-tipo="html" style="min-height: 100px;" rows="'.$LargoMin.'" cols="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$SoloLectura.' ></textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                        case 'range':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" type="'.$TipoCampo.'" class="form-range w-100" min="'.$LargoMin.'" max="'.$LargoMax.'" />';
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
                            if ($SoloLectura!="")
                                $SoloLectura = "disabled";
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            if ($TablaDts2!= "")
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" onChange="change_send3(this.value,'.$aps. str_replace("edit_","",$Campo).$aps.','.$aps.$Receptor.$aps.','.$aps.$tabla.$aps.', $('.$aps. '#'.$Receptor.$aps.').val() )" '.$Requerido.' '.$SoloLectura.'>';
                            else
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" '.$Requerido.' '.$SoloLectura.'>';

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma'  $Filtro ";
                            }
                            else{
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                            }                            
                            if ($TablaDts == "templates")
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo '<option value="" selected> ... </option>';
                            if ($tabla_dts) {
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
                            echo '<label class="form-label fw-semibold text-dark small mb-1" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '<div class="card p-3 border-dashed bg-light shadow-none mb-2">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-control bg-white" type="file"  accept="'.$Filtro.'">';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'"  type="hidden" >';
                            echo '<input name="file_'.$Campo.'_1" id="file_'.$Campo.'_1"  type="hidden" >';
                            
                            if ($Regla != "")
                                echo '<div class="form-text small text-muted mt-1">'.$Regla.'</div>';

                            echo '<div class="mt-3 text-center">';
                            echo "<img src='' id='gallery_file2_$Campo' name ='gallery_file2_$Campo' class='img-thumbnail img-fluid style-preview mb-2' style='max-height: 150px; '>";
                            echo "<div id='gallery_file_".$Campo."' class='row g-2'>";
                            echo "</div>";
                            echo "</div>";                    
                            echo "</div>";                    

                            echo "
                            <script>
                            var el_".$Campo." = document.querySelector('#".$Campo."');
                            el_".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'file_".$Campo."','img'); 
                                    var imgPreview = document.getElementById('gallery_file2_".$Campo."');
                                    if(imgPreview) { imgPreview.style.display='none'; }
                                    previewImage(this.files[i],'file_".$Campo."');
                                }
                            }, false);                    
                            </script>
                            ";
                            break;  

                        case 'file':
                            echo '<label class="form-label fw-semibold text-dark small mb-1" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '<div class="card p-3 border-dashed bg-light shadow-none mb-2">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-control bg-white" type="file"  accept="'.$Filtro.'">';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'"  type="hidden" >';
                            echo '<input name="file_'.$Campo.'_1" id="file_'.$Campo.'_1"  type="hidden" >';
                            
                            if ($Regla != "")
                                echo '<div class="form-text small text-muted mt-1">'.$Regla.'</div>';

                            echo '<div class="mt-3 text-center">';
                            echo "<img src='' id='gallery_file2_$Campo' name ='gallery_file2_$Campo' class='img-thumbnail img-fluid style-preview mb-2' style='max-height: 150px; display: none;'>";
                            echo "<div id='gallery_file_".$Campo."' class='row g-2'>";
                            echo "</div>";
                            echo "</div>";                    
                            echo "</div>";                    

                            echo "
                            <script>
                            var el_".$Campo." = document.querySelector('#".$Campo."');
                            el_".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'file_".$Campo."','file'); 
                                    var imgPreview = document.getElementById('gallery_file2_".$Campo."');
                                    if(imgPreview) { imgPreview.style.display='none'; }
                                }
                            }, false);                    
                            </script>
                            ";
                            break;                      

                        case 'complete':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="selectpicker form-control border rounded" '.$Requerido.' '.$SoloLectura.'>';
                            if ($Campo == "edit_ZonaHoraria"){
                                $zonas = DateTimeZone::listIdentifiers();
                                echo '<option value = "" selected> ... </option>';
                                foreach ($zonas as $zona):
                                    echo '<option value="'.$zona.'">'. ($zona).'</option>';
                                endforeach;
                            }
                            else{
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
                                echo '<option value = "" selected> ... </option>';
                                if ($tabla_dts) {
                                    foreach ($tabla_dts as $tabla_dt) {
                                        $Valor         = $tabla_dt[$CampoValor];
                                        $Descripcion   = $tabla_dt[$CampoDescripcion];
                                        echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                    }
                                }
                            }
                            echo '</select>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                        break;  
                    }
                    echo '</div>'; 
                    echo '</div>'; 
                }
                echo '</div>'; 
            }
}
?>