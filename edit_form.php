<?php
// Mantenemos el parámetro opcional $BotonesFijos por defecto en false
function edit_form($IdTabla, $Idioma, $Tp,  $BotonesFijos = false,$visible = "display: none"){
    global $db;
    global $t;
?>   
    <?php
        if ($Tp == 'I' || $Tp == 'M'){
            echo '<div class="container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="edit_form_'.$IdTabla.'" style="'.$visible.'; max-width: 100%;">';
        }
        elseif($Tp == 'D'){
            echo '<div class="container-fluid p-0 mb-4" id="edit_form_'.$IdTabla.'" style="display: none; max-width: 100%;">';
        }
    ?> 
    
        <h4 class="mb-4 text-dark fw-bold border-bottom pb-2"><i class="fa-solid fa-pen-to-square me-2 text-primary small"></i><?php echo Trd(5)?></h4>
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation mb-5 pb-5" novalidate>
        <?php 
        $query = "SELECT COUNT(DISTINCT(modal_edit.Etiqueta)) as NoEtiquetas FROM modal_edit WHERE Tabla = ? ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $IdTabla);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($resultados) {
            foreach ($resultados as $registro) {
                $NoEtiquetas = $registro['NoEtiquetas'];
            }
        }

        if ($NoEtiquetas == 1){
            armar_formulario_edit($IdTabla, "General", $Idioma);
        }
        else {
            $query = "            
            SELECT
                DISTINCT( modal_edit.Etiqueta ) as Etiqueta,
                etiquetas_tablas.Titulo
            FROM
                modal_edit
                INNER JOIN
                etiquetas_tablas
                ON 
                    modal_edit.Tabla = etiquetas_tablas.Tabla AND
                    modal_edit.Etiqueta = etiquetas_tablas.Etiqueta
                    WHERE 
                    modal_edit.Tabla = ? AND 
                    etiquetas_tablas.Idioma = ?
                ORDER BY modal_edit.Id
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $IdTabla);
            $stmt->bindValue(2, $Idioma);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $Tab = 0;
            foreach ($resultados as $registro) {
                $Tab++;
                $Etiqueta = $registro['Etiqueta'];
                
                echo '<div class="mt-4 mb-3">';
                echo '<h5 class="text-secondary fw-semibold border-bottom pb-2 mb-3"><i class="fa-solid fa-layer-group me-2 small text-muted"></i>'.$registro['Titulo'].'</h5>';
                armar_formulario_edit($IdTabla, $Etiqueta, $Idioma);
                echo '</div>';
            }
        }        
        if ($IdTabla == 'item_prices'){
            ?>
            <div class="container-fluid px-0 mt-4">
                <h5 class="text-dark fw-bold mb-3"><?php Trd_2(2)?></h5>
                <div id="edit_contenedor-funciones" class="p-3 bg-light rounded-3 border">
  <div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0"><?= $t['funcion_precios_edit'] ?></h1>
      <button type="button" id="edit_btnLimpiar" class="btn btn-outline-danger btn-sm"><?= $t['limpiar'] ?></button>
    </div>
    <div id="edit_casos" class="vstack gap-2 mb-4"></div>
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['precio_vs_tiempo_edit'] ?></h2>
            <select id="edit_eje" class="form-select form-select-sm w-auto">
              <option value="1"><?= $t['horas'] ?></option>
              <option value="24"><?= $t['dias'] ?></option>
              <option value="168"><?= $t['semanas'] ?></option>
            </select>
          </div>
          <div style="height:320px"><canvas id="edit_grafica"></canvas></div>
        </div></div>
      </div>
    </div>
    <div class="row g-3" style="display: none;">
      <div class="col-lg-12">
        <div class="card h-100"><div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['json_precio_edit'] ?></h2>
            <button type="button" id="edit_btnAplicar" class="btn btn-sm btn-outline-primary"><?= $t['aplicar_json'] ?></button>
          </div>
          <textarea id="edit_json" class="form-control font-monospace flex-grow-1" rows="10" spellcheck="false"></textarea>
          <div id="edit_jsonErr" class="text-danger small mt-1"></div>
        </div></div>
      </div>
    </div>  
  </div>
                </div>
            </div>
            <?php 
        }        
        ?>

        <?php if ($BotonesFijos): ?>
            <div class="position-fixed bottom-0 start-50 translate-middle-x bg-white z-3 shadow-lg p-3 d-flex justify-content-end gap-2 border-top workflow-actions" style="width: 100%; max-width: inherit; box-shadow: 0 -10px 25px rgba(0,0,0,0.1) !important;">
        <?php else: ?>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <?php endif; ?>
                <button class="btn btn-light border fw-semibold px-4 rounded-3 shadow-none text-secondary" type="button" onclick='Cancel_edit("<?php echo $IdTabla;?>");'>
                    <i class="fa-solid fa-xmark me-1"></i><?php echo Trd(6)?>
                </button>
                <button class="btn btn-primary fw-semibold px-4 rounded-3 shadow-sm" type="submit">
                    <i class="fa-solid fa-floppy-disk me-1"></i><?php echo Trd(7)?>
                </button>
            </div>
        </form>
        
    <?php
        if ($Tp == 'F' || $Tp == 'M' || $Tp == 'D'){
            echo "</div>";
        }
    ?>   
    <script>
        $('#edit_<?php echo $IdTabla?>').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('was-validated');
                return false;
            }        

            e.preventDefault(); 
            updateRecord($(this), '<?php echo $IdTabla?>'); // Pasamos el formulario a la función
        });   

        $(document).ready(function() {
        <?php
            $query = "SELECT Campo FROM modal_edit WHERE Tabla = ? AND TipoCampo ='html'";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $IdTabla);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                foreach ($resultados as $registro) {
                    $Campo = $registro['Campo'];
                    echo "$('#edit_$Campo').summernote({
                        height: 320,
                        styleWithSpan: false,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ]
                    });";
                }
            }    
        ?>
        });
    </script>
<?php } ?>