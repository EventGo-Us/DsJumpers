<?php
function add_listado($IdTabla,$collapse = ''){

?>
    <div class="<?= $collapse ?> container-fluid p-4 bg-white border-0 shadow-sm rounded-4" id="listado_<?php echo $IdTabla;?>" style="max-width: 100%;">
        
        <div class="row g-3 align-items-center mb-4">
            
            <div class="col-12 col-md-8 col-lg-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-secondary border rounded-start-3 px-3">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control form-control-sm bg-light border-start-0 px-2 py-2 rounded-end-3 shadow-none text-dark fw-medium" type="search" placeholder="<?php echo Trd(25)?>" id="Search_<?php echo $IdTabla;?>" name="Search_<?php echo $IdTabla;?>" aria-label="Search" onchange="listado('<?php echo $IdTabla;?>')" >
                    <button class="btn btn-primary px-4 py-2 fw-semibold rounded-3 ms-2 shadow-sm d-flex align-items-center gap-2" type="button" id="Srch_<?php echo $IdTabla;?>" onclick="listado('<?php echo $IdTabla;?>');">
                        <i class="fa-solid fa-filter small"></i> <?php echo Trd(25) ? Trd(25) : Trd(73); ?>
                    </button>
                </div>
            </div>
            
            <div class="col-12 col-md-4 col-lg-3 text-md-end">
                <button type="button" class="btn btn-success btn-sm fw-semibold px-3 py-2 rounded-3 shadow-sm w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-2" onclick='AgregarRegistro("<?php echo $IdTabla;?>")'>
                    <i class="fa-solid fa-plus fs-6"></i>
                    <span><?php echo Trd(2)?></span>
                </button>                
            </div>
            
        </div>

<div class="row">
    <div class="col-12">
        <div id="table-container_<?php echo $IdTabla;?>" 
             class="table-responsive rounded-3 border bg-white" 
             style="max-height: 70vh; overflow-y: auto;">
             
             </div>
    </div>
</div>
        
        <div class="row mt-3">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div id="pagination-container_<?php echo $IdTabla;?>" class="pagination-container d-flex align-items-center rounded-3">
                    </div>
            </div>
        </div>
        
    </div>
<?php
}
?>