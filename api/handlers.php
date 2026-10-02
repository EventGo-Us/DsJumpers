<?php
// api/Handlers.php
// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Aws\S3\S3Client;

// ----------------------------------------------------
// A. LÓGICA DE AUTENTICACIÓN
// ----------------------------------------------------
function handle_login_request($data) {
    
    // --- Esta es la lógica copiada del antiguo login.php ---
    // Simulación de verificación de credenciales
    //if (isset($data->username) && $data->username == "admin" && $data->password == "1234") {
    if (isset($data->username)) {
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60); // 1 hora
        $issuer = URL_BASE & "/api/";     
        //if (isset($data->data_base)){
        //}
        //else{
        //}
        $token_payload = array(
            "iss" => $issuer,
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "data" => array(
                "id" => $data->usuario_id,
                "username" => $data->username,
                "nombre_usuario" => $data->usuario_nombre,
                "base_datos" => $data->data_base,
                "rol" => $data->role_id,
                "id_cliente" => $data->id_cliente,
            )
        );
        try {
            // Usamos la constante SECRET_KEY definida en config/config.php
            $jwt = JWT::encode($token_payload, SECRET_KEY, 'HS256');
            
            http_response_code(200);
            echo json_encode(array(
                "message" => "Inicio de sesión exitoso.",
                "jwt" => $jwt
            ));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => "No se pudo generar el token."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Credenciales inválidas."));
    }
}
// ----------------------------------------------------
// B. LÓGICA CRUD
// ----------------------------------------------------
function handle_generic_crud($table_name,$db, $method, $id, $data) {    
    global $IDS;
    $allowed_tables = [
        'clientes',
        'customers',
        'sale_customers',
        'sale_customer_addresses',
        'categories',
        'scategories',
        'customer_type',
        'wharehouses',
        'gifcard',
        'products',
        'products_categories',
        'products_images',
        'products_images_sale',
        'products_videos',
        'products_files',
        'packing_list',
        'related_products',
        'distance_charges',
        'distance_charges_zip_code',
        'distance_charges_distance',
        'distance_charges_states',
        'distance_charges_totals',
        'account',
        'venues',
        'organizations',
        'surfaces',
        'upselling_products',
        'relationship_products',
        'cost_products',
        'item_prices',
        'products_item_price',
        'customer_addresses',
        'document_center',
        'clone_record',
        'copy_records',
        'price_lists',
        'detail_price_lists',
        'discounts',
        'inventory_stock',
        'operators',
        'referals',
        'vehicles',
        'schedules'
    ];
    if (!in_array($table_name, $allowed_tables)) {
        http_response_code(400);
        echo json_encode(array("message" => "Tabla $table_name no permitida"));
        return;
    }    
    switch ($method) {   
        // ------------------------------------------------------------------
        case 'GET': 
        // ------------------------------------------------------------------

                $FWhere = '';
                $FFWhere = '';
                $FOrder = '';        

                $sortField = isset($_GET['sort_field']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['sort_field']) : '';
                $sortOrder = isset($_GET['sort_order']) ? strtoupper($_GET['sort_order']) : '';

                    // Si mandaron columna y orden válidos, cambiamos el $Order original
                if (!empty($sortField) && !empty($sortOrder)) {
                    $FOrder = "$sortField $sortOrder";
                }




                $sortFieldsString = isset($_GET['sort_fields']) ? $_GET['sort_fields'] : '';
                $likeValue = isset($_GET['like']) ? $_GET['like'] : '';            

           

                if (!empty($sortFieldsString) && !empty($likeValue)) {
                    // Explotamos el string por sus comas
                    $camposAFiltrar = explode(',', $sortFieldsString);
                    $orConditions = [];
                    $orConditions2 = [];
                    $prm_idx = 1;
                    foreach ($camposAFiltrar as $campo) {
                        // Limpieza de seguridad básica para nombres de columnas
                        $campoLimpio = preg_replace('/[^a-zA-Z0-9_]/', '', $campo);
                        if (!empty($campoLimpio)) {
                            $orConditions[] = "$campoLimpio LIKE ? ";
                            $orConditions2[] = "$campoLimpio LIKE :like_$prm_idx ";
                        }
                    }
                    
                    if (!empty($orConditions)) {
                        // Unimos los campos en un bloque ( Campo1 LIKE ... OR Campo2 LIKE ... )
                        $FWhere .= " (" . implode(' OR ', $orConditions) . ") ";
                        $FFWhere .= " (" . implode(' OR ', $orConditions2) . ") ";
                    }
                }    

            if (!isset($_GET['page'])) {

            

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Data' AND Campo <> 'password_c' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Campos = '';
                    unset($Campos);
                    foreach ($resultados as $registro) {
                        $Campos[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Data no creada."));
                }

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Where = '';
                    unset($Where);
                    foreach ($resultados as $registro) {
                        $Where[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Id no creada."));
                }
                $Campos = implode(', ', $Campos);
                $Where = implode(' = ? AND ', $Where);
                // READ ONE
                $query = "SELECT $Campos FROM  $table_name  WHERE $Where = ? LIMIT 0,1";
                if ($table_name == 'products_item_price'){
                    $query = "
                        SELECT
                            products_item_price.Producto, 
                            products_item_price.ItemPrice, 
                            item_prices.JsonPrice, 
                            products_item_price.Taxable
                        FROM
                            products_item_price
                            INNER JOIN
                            item_prices
                            ON 
                                products_item_price.ItemPrice = item_prices.Id
                                WHERE products_item_price.Producto = ?
                    ";
                }
                if ($table_name == 'related_products')
                    $query = "SELECT $Campos FROM  v_related_products  WHERE $Where = ? LIMIT 0,1";
                
                if ($table_name == 'products')
                    $query = "SELECT $Campos FROM  v_products  WHERE $Where = ? LIMIT 0,1";                

                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    http_response_code(200);
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Registro no encontrado."));
                }
            } else {
                // READ ALL CON PAGINACIÓN                 
                // 1. Obtener y validar parámetros de paginación
                // Usamos 10 registros por defecto si no se especifica el límite
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                // Usamos la página 1 por defecto
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $like = isset($_GET['like']) ? $_GET['like'] : '';
                $lang = isset($_GET['lang']) ? $_GET['lang'] : 'es';
                // Asegurar que limit y page sean positivos
                $limit = max(1, $limit); 
                $page = max(1, $page);                
                // Calcular el OFFSET (el punto de partida)
                $offset = ($page - 1) * $limit;



                if ($like!=""){
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Where' ORDER BY Id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados) {
                        $Where = '';
                        unset($Where);
                        foreach ($resultados as $registro) {
                            $Where[]=$registro['Campo'];
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Estructura Where no creada."));
                    }                
                    $Where = " WHERE ".implode(" LIKE ? OR ", $Where)." LIKE ? ";
                }
                else{
                    $Where ='';
                }  
                
                // PARA CONSULTA DE REGISTROS RELACIONADOS!
                    $Where2 = '';
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id2'";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados2) {
                        
                        unset($Where2);
                        foreach ($resultados2 as $registro) {
                            $Where2[]=$registro['Campo'];
                        }
                        $Where2 = implode(" = ? AND ", $Where2).' = ? AND';
                    }    

                    if ($Where2 !="")
                        $Where2 = substr($Where2, 0, -3);

                    if ($Where != "" AND $Where2 != ""){
                        $Where.=" AND ".$Where2;
                    }
                    elseif ($Where == "" AND $Where2 != ""){
                        $Where = ' WHERE '.$Where2;
                    }
                // PARA CONSULTA DE REGISTROS RELACIONADOS!

                //if ($table_name == 'products_categories'){
                //    echo $Where;
                //    die();                    
                //}

                // 2. Consulta para obtener el CONTEO TOTAL de registros
                if ($table_name == 'schedules')
                    $v_table_name = 'v_schedules';                
                elseif ($table_name == 'products')
                    $v_table_name = 'v_products';
                elseif ($table_name == 'gifcard')
                    $v_table_name = 'v_gifcard';
                elseif ($table_name == 'products_categories')
                    $v_table_name = 'v_products_categories'; 
                elseif ($table_name == 'distance_charges')
                    $v_table_name = 'v_distance_charges';
                elseif ($table_name == 'packing_list')
                    $v_table_name = 'v_packing_list';
                elseif ($table_name == 'related_products')
                    $v_table_name = 'v_related_products';
                elseif ($table_name == 'upselling_products')
                    $v_table_name = 'v_upselling_products';       
                elseif ($table_name == 'sale_customer_addresses')
                    $v_table_name = 'v_sale_customer_addresses';                                
                elseif ($table_name == 'distance_charges_distance'){
                    $v_table_name = 'v_distance_charges_distance';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                }
                elseif ($table_name == 'distance_charges_states'){
                    $v_table_name = 'v_distance_charges_states';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                }
                elseif ($table_name == 'relationship_products'){
                    $v_table_name = 'v_relationship_products';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                } 
                elseif ($table_name == 'document_center'){
                    $v_table_name = 'document_center';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                } 
                elseif ($table_name == 'detail_price_lists')
                    $v_table_name = 'v_detail_price_lists';
                elseif ($table_name == 'inventory_stock')
                    $v_table_name = 'v_inventory_stock';                
                else
                    $v_table_name = $table_name;
                
                if ($Where != "" AND $FWhere != "" )
                    $Where=" WHERE ".$FWhere;

                //$count_query = "SELECT COUNT(*) as total FROM $v_table_name $Where";
                $count_query = "SELECT COUNT(*) as total FROM $v_table_name $Where";
                //echo $count_query;
                //if ($page == 2)
                //    echo $count_query ." -- "; 
                $count_stmt = $db->prepare($count_query);
                $p=0;
                if ($like!=""){
                    if ($Where != "" AND $FWhere != "" ){
                        foreach ($camposAFiltrar as $campo) {
                            // Limpieza de seguridad básica para nombres de columnas
                            $campoLimpio = preg_replace('/[^a-zA-Z0-9_]/', '', $campo);
                            if (!empty($campoLimpio)) {
                                $p++;
                                $search_pattern = "%" . $like . "%";
                                $count_stmt->bindValue($p, $search_pattern);                            
                            }
                        }                
                    }
                    else{
                        foreach ($resultados as $registro) {
                            $p++;
                            $search_pattern = "%" . $like . "%";
                            $count_stmt->bindValue($p, $search_pattern);
                        }
                    }
                }
                $idx = 0;
                foreach ($resultados2 as $registro) {
                    $p++;                    
                    $search_pattern = $IDS[$idx];
                    $count_stmt->bindValue($p, $search_pattern);
                    $idx++;
                }                

                if ($table_name == 'distance_charges_distance'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }
                if ($table_name == 'distance_charges_states'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }
                if ($table_name == 'relationship_products'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }                 
                if ($table_name == 'document_center'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }                   

                $count_stmt->execute();
                $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                // Calcular el total de páginas
                $total_pages = ceil($total_rows / $limit);

                //$query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Lst' ORDER BY Id";

                $query = "
                    SELECT
                        la.Campo, 
                        modal_add.TipoCampo
                    FROM
                        listado_ajax AS la
                        INNER JOIN
                        modal_add
                        ON 
                            la.Tabla = modal_add.Tabla AND
                            la.Campo = modal_add.Campo
                    WHERE
                        la.Tabla = ? AND
                        la.Tipo = 'Lst'
                    ORDER BY
                        la.Id ASC                
                ";                

                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $LCampos = '';
                    $LLCampos = '';
                    $TCampos = '';
                    unset($LCampos);
                    unset($LLCampos);
                    unset($TCampos);
                    foreach ($resultados as $registro) {
                        $LCampos[]=$registro['Campo'];
                        if ($registro['TipoCampo'] == 'img' || $registro['TipoCampo'] == 'imglst'){
                            $LLCampos[]=$registro['Campo'];
                            $TCampos[]=$registro['TipoCampo'];
                        }
                            
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Lst no creada."));
                }                
                $Campos = implode(', ', $LCampos);

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Order' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Order = '';
                    unset($Order);
                    foreach ($resultados as $registro) {
                        $Order[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Order no creada."));
                }                
                $Order = implode(',', $Order); 
                $Order.=" ASC ";
                if ($FOrder != "")
                    $Order = $FOrder;

                $where_clauses = [];
                $param_index = 1;
                $Where = '';

                if ($like!=""){
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Where' ORDER BY Id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados) {
                        //$Where = '';
                        //unset($Where);
                        
                        foreach ($resultados as $registro) {
                            //$Where[]=$registro['Campo'];
                        $param_name = ":like_" . $param_index;
                        $where_clauses[] = $registro['Campo'] . " LIKE " . $param_name;
                        $param_index++;                            
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Estructura Where no creada."));
                    }                
                    //$Where = " WHERE ".implode(" LIKE ? OR ", $Where)." LIKE ? ";
                    $Where = " WHERE " . implode(' OR ', $where_clauses);
                }
                else{
                    $Where ='';
                }   

                if ($Where != "" AND $FFWhere != "" )
                    $Where=" WHERE ".$FFWhere;


                
                // PARA CONSULTA DE REGISTROS RELACIONADOS!
                    $Where2 = '';
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id2'";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados2) {
                        
                        unset($Where2);
                        foreach ($resultados2 as $registro) {
                            $param_name = ":id_" . $param_index;
                            $Where2[]=$registro['Campo'] . " = " . $param_name;;
                        }
                        $Where2 = implode(" AND ", $Where2)." AND";
                    }    

                    if ($Where2 !="")
                        $Where2 = substr($Where2, 0, -3);

                    if ($Where != "" AND $Where2 != ""){
                        $Where.=" AND ".$Where2;
                    }
                    elseif ($Where == "" AND $Where2 != ""){
                        $Where = ' WHERE '.$Where2;
                    }
                // PARA CONSULTA DE REGISTROS RELACIONADOS!                
                if ($table_name == 'schedules')
                    $v_table_name = 'v_schedules';                       
                elseif ($table_name == 'products')
                    $v_table_name = 'v_products';
                elseif ($table_name == 'gifcard')
                    $v_table_name = 'v_gifcard';
                elseif ($table_name == 'products_categories')
                    $v_table_name = 'v_products_categories';  
                elseif ($table_name == 'distance_charges')
                    $v_table_name = 'v_distance_charges';
                elseif ($table_name == 'packing_list')
                    $v_table_name = 'v_packing_list';
                elseif ($table_name == 'upselling_products')
                    $v_table_name = 'v_upselling_products';
                elseif ($table_name == 'sale_customer_addresses')
                    $v_table_name = 'v_sale_customer_addresses';
                elseif ($table_name == 'related_products')
                    $v_table_name = 'v_related_products';                
                elseif ($table_name == 'distance_charges_distance'){
                    $v_table_name = 'v_distance_charges_distance';
                    $Where.= ' AND Idioma = :lang ';
                }
                elseif ($table_name == 'distance_charges_states'){
                    $v_table_name = 'v_distance_charges_states';
                    $Where.= ' AND Idioma = :lang ';
                }
                elseif ($table_name == 'relationship_products'){
                    $v_table_name = 'v_relationship_products';
                    $Where.= ' AND Idioma = :lang ';
                }
                elseif ($table_name == 'document_center'){
                    $v_table_name = 'document_center';
                    $Where.= ' AND Idioma = :lang ';
                }                
                elseif ($table_name == 'detail_price_lists')
                    $v_table_name = 'v_detail_price_lists';
                elseif ($table_name == 'inventory_stock')
                    $v_table_name = 'v_inventory_stock';                
                else
                    $v_table_name = $table_name;
                // 3. Consulta para obtener los DATOS PAGINADOS
                $data_query = "SELECT $Campos FROM $v_table_name $Where ORDER BY $Order  LIMIT :limit OFFSET :offset";                
                //echo $data_query;
                $data_stmt = $db->prepare($data_query);
                $param_index=1;
                if ($like!=""){
                    if ($Where != "" AND $FWhere != "" ){

                        foreach ($camposAFiltrar as $campo) {
                            // Limpieza de seguridad básica para nombres de columnas
                            $campoLimpio = preg_replace('/[^a-zA-Z0-9_]/', '', $campo);
                            if (!empty($campoLimpio)) {
                                $search_pattern = "%" . $like . "%";
                                $data_stmt->bindValue(":like_" . $param_index, $search_pattern, PDO::PARAM_STR);
                                $param_index++;                          
                            }
                        }                      

                    }
                    else{
                        foreach ($resultados as $registro) {
                            $search_pattern = "%" . $like . "%";
                            $data_stmt->bindValue(":like_" . $param_index, $search_pattern, PDO::PARAM_STR);
                            $param_index++;
                        }
                    }

                }
                $idx=0;
                foreach ($resultados2 as $registro) {
                    $search_pattern = $IDS[$idx];
                    $data_stmt->bindValue(":id_" . $param_index, $search_pattern, PDO::PARAM_STR);
                    $param_index++;
                    $idx++;
                }                

                if ($table_name == 'distance_charges_distance'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }
                if ($table_name == 'distance_charges_states'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }
                if ($table_name == 'relationship_products'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }
                if ($table_name == 'document_center'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }                

                $data_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $data_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                //if ($page == 2)
                //echo $data_query ." $limit $offset " ;                
                $data_stmt->execute();

                $registros = array();
                while ($row = $data_stmt->fetch(PDO::FETCH_ASSOC)) {

                foreach ($row as $columna => $valor) {
                    if (isset($LLCampos)){
                        // Buscamos si el nombre de la columna existe en el array de nombres de campos
                        $indice = array_search($columna, $LLCampos);
                        
                        // Si existe y el tipo asociado es 'img'
                        if ($indice !== false && ($TCampos[$indice] === 'img' || $TCampos[$indice] === 'imglst')) {
                            // Reemplazamos el valor por el tag HTML (ajusta la ruta según necesites)
                            //$row[$columna] = '<img src="ajax/tmp/' . htmlspecialchars($valor) . '" alt="imagen" style="width:50px;">';
                            $img_folder = $table_name ;
                            if ($table_name == "related_products" OR $table_name == "upselling_products")
                                $img_folder = "products_images";
                            $row[$columna] = '<img src="'.CFPUBLICURL.'/'.ID_CLIENTE.'/'.$img_folder.'/thumbnails/'.htmlspecialchars((string)$valor) . '" alt="imagen" style="width:50px;">';
                        }
                    }
                }                    

                    $registros[] = $row;
                }
                // 4. Reguperar Titulos
                
                $query = "
                    SELECT
                        listado_ajax.Campo,
                        titulos_campos_tablas.Titulo,
                        listado_ajax.Alineacion
                    FROM
                        listado_ajax
                        INNER JOIN
                        titulos_campos_tablas
                        ON 
                            listado_ajax.Tabla = titulos_campos_tablas.Tabla AND
                            listado_ajax.Campo = titulos_campos_tablas.Campo		
                        WHERE 
                            listado_ajax.Tabla = ? AND
                            listado_ajax.Tipo = 'Lst' AND
                            titulos_campos_tablas.Idioma = ?
                        ORDER BY
                            listado_ajax.id            
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindValue(1, $table_name);
                $stmt->bindValue(2, $lang);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $query = "
                    SELECT
                        listado_ajax.Campo,
                        modal_add.TipoCampo
                    FROM
                        listado_ajax
                        INNER JOIN
                        modal_add
                        ON 
                            listado_ajax.Tabla = modal_add.Tabla AND
                            listado_ajax.Campo = modal_add.Campo
                    WHERE 
                        listado_ajax.Tabla = ? AND
                        listado_ajax.Tipo = 'Lst'
                    ORDER BY
                            listado_ajax.id                                        
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindValue(1, $table_name);
                $stmt->execute();
                $resultados_t = $stmt->fetchAll(PDO::FETCH_ASSOC);                

                // 5. Construir la Respuesta
                http_response_code(200);
                echo json_encode(array(
                    "metadata" => array(
                        "total_registros" => (int)$total_rows,
                        "total_paginas" => (int)$total_pages,
                        "pagina_actual" => (int)$page,
                        "registros_por_pagina" => (int)$limit
                    ),
                    "data" => $registros,
                    "titulos" => $resultados,
                    "tipos" => $resultados_t
                ));
            }
            break;

        // ------------------------------------------------------------------
        case 'POST': 
        // ------------------------------------------------------------------
        // INSERTA UN NUEVO REGISTRO
            $query = "SELECT Campo, Requerido,TipoCampo FROM modal_add WHERE Tabla = ? AND TipoCampo <> 'auto' AND TipoCampo <> 'insert' AND TipoCampo <> 'Lst' AND TipoCampo <> 'button' AND TipoCampo <> 'option' AND TipoCampo <> 'titulo' and TipoCampo <> 'imglst' AND Campo <> 'password_c' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $Campos = '';
                $Valores = '';
                unset($Campos);
                unset($Valores);
                foreach ($resultados as $registro) {
                    if ($registro['Requerido'] == 'X' AND empty($data->{$registro['Campo']}) ){
                        http_response_code(404);
                        echo json_encode(array("message" => $registro['Campo']." Requerido."));
                        die();
                    }
                    $Campos[]=$registro['Campo'];
                    $Valores[]=":". strtolower($registro['Campo']);
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Add no creada."));
            }

            $CamposInsert= '';
            $CamposInsertValues = '';
            $query = "SELECT Campo, CampoValor   FROM modal_add WHERE Tabla = ? AND TipoCampo = 'insert' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados2) {
                foreach ($resultados2 as $registro2) {
                    $CamposInsert.= ','.$registro2['Campo'];
                    $CamposInsertValues .= ','.$registro2['CampoValor'];
                }
            }

            $Campos = implode(', ', $Campos);
            $Valores = implode(', ', $Valores);

            $query = "INSERT INTO  $table_name ($Campos $CamposInsert) VALUES($Valores $CamposInsertValues) ";

            $stmt = $db->prepare($query);

            foreach ($resultados as $registro) {

                $campo = strtolower($registro['Campo']);
                $tipocampo = strtolower($registro['TipoCampo']);
                if ($tipocampo == 'password'){
                    $valor = password_hash($data->{$registro['Campo']}, PASSWORD_DEFAULT);
                }
                elseif ($tipocampo == 'html'){
                        $valor = isset($data->{$registro['Campo']}) 
                            ? (($data->{$registro['Campo']}))
                            : null; 

                } 
                else{
                    $valor = isset($data->{$registro['Campo']}) 
                        ? htmlspecialchars((string)strip_tags($data->{$registro['Campo']}))
                        : null;
                }

                
                if ($tipocampo =='checkbox'){
                    if ($valor == 'on'){
                        $valor = 1;
                    }
                    else{
                        $valor = 0;
                    }
                    $stmt->bindValue(":" . $campo, $valor);
                }
                elseif ($tipocampo =='img'){
                    if ($valor!=""){
                        $client = ID_CLIENTE;
                        $gallery = $table_name;
                        $normal = $valor;
                        $miniatura = "thumbnail_".$valor;
                        $miniaturaj = "thumbnail_".$valor;
                        $miniaturaj =  str_replace("avif", "jpg", $miniaturaj);
                        upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);
                        $stmt->bindValue(":" . $campo, $valor);
                    }
                }
                else{
                    $stmt->bindValue(":" . $campo, $valor);
                }
                
                            //echo "$campo -- $valor";                

            }
            
            if ($stmt->execute()) {
                $lastInsertId = $db->lastInsertId();
                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name',$lastInsertId,0,'I','Registro Insertado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();
                http_response_code(201); // Created
                echo json_encode(array("message" => "Registro creado exitosamente."));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("message" => "No se pudo crear el registro."));
            }
            break;

        // ------------------------------------------------------------------
        case 'PUT':
        // ------------------------------------------------------------------

            if ($table_name =='products_item_price' AND $data->{'new'} == 1 ){
                if ($data->{'JsonPrice'} == ""){
                    http_response_code(404);
                    echo json_encode(array("message" => "No tiene precio definido"));
                    die();                    
                }
                $Taxable = 0;
                if (isset($data->{'Taxable'}))
                    $Taxable = 1;

                // 1. Validar si existe, recuperar el Id de item_prices, si no insertar
                $sqlCheck = "SELECT Id FROM item_prices WHERE PriceName = :priceName LIMIT 1";
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->bindValue(":priceName", $data->{'ItemPrice'});
                $stmtCheck->execute();

                $priceRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($priceRow) {
                    // Si ya existe, recuperamos el ID existente
                    $IdRecuperado = $priceRow['Id'];
                } else {
                    // Si no existe, lo insertamos
                    $sqlInsert = "INSERT INTO item_prices (PriceName, Taxable, JsonPrice, FechaCreacion, FechaCambio) 
                                VALUES (:priceName, :taxable, :jsonPrice, NOW(), NOW())";
                    $stmtInsert = $db->prepare($sqlInsert);
                    $stmtInsert->bindValue(":priceName", $data->{'ItemPrice'});
                    $stmtInsert->bindValue(":taxable", $Taxable);
                    $stmtInsert->bindValue(":jsonPrice", $data->{'JsonPrice'});
                    $stmtInsert->execute();
                    
                    // Recuperamos el lastInsertId inmediatamente después del INSERT
                    $IdRecuperado = $db->lastInsertId();
                }

                // 2. Eliminar relaciones anteriores del producto
                $sqlDelete = "DELETE FROM products_item_price WHERE Producto = :producto";
                $stmtDelete = $db->prepare($sqlDelete);
                $stmtDelete->bindValue(":producto", $data->{'Producto'});
                $stmtDelete->execute();                

                // 3. Insertar la nueva relación usando el ID recuperado (ya sea el existente o el nuevo)
                $sqlRelacion = "INSERT INTO products_item_price (Producto, ItemPrice, Taxable) 
                                VALUES (:producto, :itemPrice, :taxable)";
                $stmtRelacion = $db->prepare($sqlRelacion);
                $stmtRelacion->bindValue(":producto", $data->{'Producto'});
                $stmtRelacion->bindValue(":itemPrice", $IdRecuperado);
                $stmtRelacion->bindValue(":taxable", $Taxable);
                $stmtRelacion->execute();

                $sqlDelete = "DELETE FROM detail_price_lists WHERE IdItem = :producto";
                $stmtDelete = $db->prepare($sqlDelete);
                $stmtDelete->bindValue(":producto", $IdRecuperado);
                $stmtDelete->execute();

                $query = "select Id from price_lists where Estatus = 1 AND NOW() BETWEEN FechaHoraInicio AND FechaHoraFin";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    foreach ($resultados as $registro) {
                        $sqlPriceList = "INSERT INTO detail_price_lists (IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio) 
                                        VALUES (:idLista,:idItem,:jsonPrice,1,now(),now())";
                        $sqlPriceList = $db->prepare($sqlPriceList);
                        $sqlPriceList->bindValue(":idLista", $registro['Id']);
                        //$sqlPriceList->bindValue(":idItem", $data->{'Producto'});
                        $sqlPriceList->bindValue(":idItem", $IdRecuperado);
                        $sqlPriceList->bindValue(":jsonPrice", $data->{'JsonPrice'});
                        $sqlPriceList->execute();
                    }
                }                

                http_response_code(200);
                echo json_encode(array("message" => "Registro insertado."));
                die();
            }

            if ($table_name =='products_item_price' AND $data->{'new'} == 2 ){
                if ($data->{'JsonPrice'} == ""){
                    http_response_code(404);
                    echo json_encode(array("message" => "No tiene precio definido"));
                    die();                    
                }
                $Taxable = 0;
                if (isset($data->{'Taxable'}))
                    $Taxable = 1;

                // 1. Validar si existe, recuperar el Id de item_prices, si no insertar
                $sqlCheck = "SELECT Id FROM item_prices WHERE PriceName = :priceName LIMIT 1";
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->bindValue(":priceName", $data->{'ItemPrice'});
                $stmtCheck->execute();

                $priceRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($priceRow) {
                    // Si ya existe, recuperamos el ID existente
                    $IdRecuperado = $priceRow['Id'];
                } else {
                    // Si no existe, lo insertamos
                    $sqlInsert = "INSERT INTO item_prices (PriceName, Taxable, JsonPrice, FechaCreacion, FechaCambio) 
                                VALUES (:priceName, :taxable, :jsonPrice, NOW(), NOW())";
                    $stmtInsert = $db->prepare($sqlInsert);
                    $stmtInsert->bindValue(":priceName", $data->{'price_name'});
                    $stmtInsert->bindValue(":taxable", $Taxable);
                    $stmtInsert->bindValue(":jsonPrice", $data->{'JsonPrice'});
                    $stmtInsert->execute();
                    
                    // Recuperamos el lastInsertId inmediatamente después del INSERT
                    $IdRecuperado = $db->lastInsertId();
                }

                // 2. Eliminar relaciones anteriores del producto
                $sqlDelete = "DELETE FROM products_item_price WHERE Producto = :producto";
                $stmtDelete = $db->prepare($sqlDelete);
                $stmtDelete->bindValue(":producto", $data->{'Producto'});
                $stmtDelete->execute();                

                // 3. Insertar la nueva relación usando el ID recuperado (ya sea el existente o el nuevo)
                $sqlRelacion = "INSERT INTO products_item_price (Producto, ItemPrice, Taxable) 
                                VALUES (:producto, :itemPrice, :taxable)";
                $stmtRelacion = $db->prepare($sqlRelacion);
                $stmtRelacion->bindValue(":producto", $data->{'Producto'});
                $stmtRelacion->bindValue(":itemPrice", $IdRecuperado);
                $stmtRelacion->bindValue(":taxable", $Taxable);
                $stmtRelacion->execute();

                $sqlDelete = "DELETE FROM detail_price_lists WHERE IdItem = :producto";
                $stmtDelete = $db->prepare($sqlDelete);
                $stmtDelete->bindValue(":producto", $IdRecuperado);
                $stmtDelete->execute();

                $query = "select Id from price_lists where Estatus = 1 AND NOW() BETWEEN FechaHoraInicio AND FechaHoraFin";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    foreach ($resultados as $registro) {
                        $sqlPriceList = "INSERT INTO detail_price_lists (IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio) 
                                        VALUES (:idLista,:idItem,:jsonPrice,1,now(),now())";
                        $sqlPriceList = $db->prepare($sqlPriceList);
                        $sqlPriceList->bindValue(":idLista", $registro['Id']);
                        $sqlPriceList->bindValue(":idItem", $IdRecuperado);
                        $sqlPriceList->bindValue(":jsonPrice", $data->{'JsonPrice'});
                        $sqlPriceList->execute();
                    }
                }

                http_response_code(200);
                echo json_encode(array("message" => "Registro insertado."));
                die();
            }            



            // UPDATE (Actualizar un registro existente)
            $query = "SELECT Campo, TipoCampo, Requerido,TipoCampo FROM modal_edit WHERE Tabla = ?  ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $Campos = '';
                unset($Campos);
                foreach ($resultados as $registro) {
                    if ($registro['Requerido'] == 'X' AND empty($data->{$registro['Campo']}) AND $registro['Campo'] != 'IId' ){
                        http_response_code(404);
                        echo json_encode(array("message" => $registro['Campo']." Requerido."));
                    }
                    if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo' AND $registro['TipoCampo']!= 'button')
                        $Campos[]=$registro['Campo']." = :". strtolower($registro['Campo']);
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Edit no creada."));
            }        

            $Campos = implode(', ', $Campos);

            $CamposUpdate= '';
            
            $query = "SELECT Campo,CampoValor FROM modal_edit WHERE Tabla = ? AND TipoCampo = 'update' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados2) {
                foreach ($resultados2 as $registro2) {
                    $CamposUpdate.= ','.$registro2['Campo'].'='.$registro2['CampoValor'];
                }
            }            

            $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $Llaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($Llaves) {
                $Where = '';
                foreach ($Llaves as $llave) {
                    $Where.= $llave['Campo'] ." = :".strtolower($llave['Campo'])." AND";
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Id no creada."));
            }
            $Where=substr($Where, 0, -3);
            $query = "UPDATE $table_name SET $Campos $CamposUpdate WHERE $Where";
            //echo $query;
            //die();
            $stmt = $db->prepare($query);            
            // Sanitizar y enlazar parámetros
            foreach ($resultados as $registro) {
                if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo' AND $registro['TipoCampo']!= 'button')
                {
                    $campo = strtolower($registro['Campo']);
                    $valor = isset($data->{$registro['Campo']}) 
                        ? htmlspecialchars((string)strip_tags($data->{$registro['Campo']}))
                        : null;
                    $tipocampo = strtolower($registro['TipoCampo']);
                    if ($tipocampo =='checkbox'){
                        if ($valor == 'on'){
                            $valor = 1;
                        }
                        else{
                            $valor = 0;
                        }
                        $stmt->bindValue(":" . $campo, $valor);
                    }
                    elseif ($tipocampo =='img'){
                        /*
                        if ($data->{"file_".$registro['Campo']}!=""){
                            $valor = isset($data->{"file_".$registro['Campo']}) 
                                ? htmlspecialchars((string)strip_tags($data->{"file_".$registro['Campo']}))
                                : null;                            
                            $stmt->bindValue(":" . $campo, $valor);
                        }else{
                            $valor = isset($data->{"file_".$registro['Campo']."_1"}) 
                                ? htmlspecialchars((string)strip_tags($data->{"file_".$registro['Campo']."_1"}))
                                : null;                            
                            $stmt->bindValue(":" . $campo, $valor);
                        }
                    */
                        $valor="";
                        if ($data->{"file_".$registro['Campo']}!= $data->{"file_".$registro['Campo']}."_1" AND $data->{"file_".$registro['Campo']}!="" ){
                            $valor = isset($data->{"file_".$registro['Campo']}) 
                                ? htmlspecialchars((string)strip_tags($data->{"file_".$registro['Campo']}))
                                : null;                            
                            $stmt->bindValue(":" . $campo, $valor);
                        }
                        else{
                            $valoro = isset($data->{"file_".$registro['Campo']."_1"}) 
                                ? htmlspecialchars((string)strip_tags($data->{"file_".$registro['Campo']."_1"}))
                                : null;                            
                            $stmt->bindValue(":" . $campo, $valoro);
                        }                        
                        if ($valor!=""){
                            $client = ID_CLIENTE;
                            $gallery = $table_name;
                            $normal = $valor;
                            $miniatura = "thumbnail_".$valor;
                            $miniaturaj = "thumbnail_".$valor;
                            $miniaturaj =  str_replace("avif", "jpg", $miniaturaj);
                            if ($table_name == 'account' ){
                                if ($data->{"file_Logo"}!="")
                                    upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);
                            }else{
                                upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);
                            }
                            
                            //$stmt->bindValue(":" . $campo, $valor);                        
                        }
                    }
                    elseif ($tipocampo =='html'){
                        $valor = isset($data->{$registro['Campo']}) 
                            ? (($data->{$registro['Campo']}))
                            : null;                            
                        $stmt->bindValue(":" . $campo, $valor);
                    }
                    else{
                        $stmt->bindValue(":" . $campo, $valor);
                    }                        


                    //$stmt->bindValue(":" . $campo, $valor);                    
                }

            }
            $Id1 = 0;
            $Id2 = 0;
            $IdC = 0;
            foreach ($Llaves as $llave) {
                $IdC++;
                $campo = strtolower($llave['Campo']);
                $valor = isset($data->{$llave['Campo']}) 
                    ? htmlspecialchars((string)strip_tags($data->{$llave['Campo']}))
                    : null;
                        if ($campo == 'iid'){
                            $valor = $data->{'IId_'.$table_name};
                        }                    

                $stmt->bindValue(":" . $campo, $valor);
                if ($IdC == 1)
                    $Id1 = $valor;
                else
                    $Id2 = $valor;
            }                 

            if ($stmt->execute()) {

                if ($table_name == 'account' ){
                    $sql = "
                        SELECT
                            account.Direccion, 
                            account.Ciudad, 
                            account.CP, 
                            estados_pais.Estado, 
                            pais.Pais
                        FROM
                            account
                            INNER JOIN
                            estados_pais
                            ON 
                                account.Estado = estados_pais.Id
                            INNER JOIN
                            pais
                            ON 
                                account.Pais = pais.Codigo
                        Limit 1                    
                    ";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                    //if ($resultado['Lat'] == null OR $resultado['Lat'] == ''){
                        $miDireccion = $resultado['Direccion']." ".$resultado['Ciudad']." ".$resultado['CP']." ".$resultado['Estado']." ".$resultado['Pais'];
                        $miDireccion = obtenerCoordenadas($miDireccion, GOOGLE_API_KEY);
                        if (isset($miDireccion['error'])) {
                            echo "Hubo un problema: " . $miDireccion['error'];
                        } else {
                            $query = "UPDATE account SET Lat = :lat, Lng = :lng WHERE Id = 1";
                            $stmt = $db->prepare($query);
                            $stmt->bindValue(":lat", $miDireccion['lat']);
                            $stmt->bindValue(":lng", $miDireccion['lng']);
                            $stmt->execute();
                        }                    
                    //}                    

                }

                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name','$Id1','$Id2','U','Registro Actualizado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("message" => "Registro actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el registro."));
            }

            break;

        // ------------------------------------------------------------------
        case 'DELETE':
        // ------------------------------------------------------------------
            // DELETE (Eliminar un registro)
            $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $Llaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($Llaves) {
                $Where = '';
                foreach ($Llaves as $llave) {
                    $Where.= $llave['Campo'] ." = :".strtolower($llave['Campo'])." AND";
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Id no creada."));
            }
            $Where=substr($Where, 0, -3);


            $query = "SELECT Campo FROM modal_delete WHERE Tabla = ?  ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {

            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Delete no creada."));
            }               

            $query = "DELETE FROM $table_name WHERE $Where";
            echo $query;
            $stmt = $db->prepare($query);
            $Id1 = 0;
            $Id2 = 0;
            $IdC = 0;
            foreach ($Llaves as $llave) {
                $IdC++;
                $campo = strtolower($llave['Campo']);
                $valor = isset($data->{$llave['Campo']}) 
                    ? htmlspecialchars((string)strip_tags($data->{$llave['Campo']}))
                    : null;
                $stmt->bindValue(":" . $campo, $valor);
                if ($IdC == 1)
                    $Id1 = $valor;
                else
                    $Id2 = $valor;                
            }               
            if ($stmt->execute()) {

                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name','$Id1','$Id2','D','Registro Borrado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("message" => "Registro eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el Registro."));
            }
            break;

        // ------------------------------------------------------------------
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
            break;
    }
}

function process_pay($table_name,$db, $method, $id, $data) {
    global $IDS;
    switch ($method) {
        case 'POST': 
            $amount     = $_POST['monto']?? 0;
            $idLead     = $_POST['idLead'] ?? 0;
            $Tipo     = $_POST['tipo']  ?? 0;
            $Usuario     = $_POST['usuario']  ?? 0;
            $Currency   = 'MXN';
            try {


                $query = "SELECT IdBranch FROM lead WHERE Id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $idLead);
                $stmt->execute();
                $lead = $stmt->fetch(PDO::FETCH_ASSOC);

                $Folio = 0;    
                $stmt = $db->prepare("SELECT MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->bindParam(1, $lead['IdBranch']);  // Usa bindParam también aquí
                $stmt->execute();
                $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Payments){
                    $Folio = $Payments['Folio'];
                }
                $Folio += 1;

                if ($Tipo == 'E')
                    $Tipo ='Cash';
                else
                    $Tipo ='Transfer';

                $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                        VALUES  (?,'Pay',?,now(),?,?,?,?,'A',?)";
                $stmtPay = $db->prepare($sqlPay);
                $stmtPay->execute([$idLead,$Folio,$Tipo,$amount,$Currency,'',$Usuario]);    

                $stmt = $db->prepare(" UPDATE folios SET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->execute([$Folio,$lead['IdBranch']]);

                $stmt = $db->prepare(" UPDATE lead SET Balance = Balance - ? WHERE Id = ? ");
                $stmt->execute([$amount,$idLead]);                

                http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'status' => 'success',
                        'message' => '¡Pago realizado con éxito!'
                    ]);    
            } catch (\Exception $e) {
                // Errores generales del sistema
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function get_json_price($table_name,$db, $method, $id, $data) {
    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "            
            SELECT
                item_prices.Id, 
                item_prices.JsonPrice, 
                item_prices.Taxable
            FROM
                item_prices
            WHERE
            item_prices.Id = ?
            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function get_price($table_name,$db, $method, $id, $data) {

    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "
            SELECT
                products_item_price.ItemPrice, 
                item_prices.JsonPrice, 
                item_prices.Taxable
            FROM
                products_item_price
                INNER JOIN
                item_prices
                ON 
                    products_item_price.ItemPrice = item_prices.Id
            WHERE 
            products_item_price.Producto = ?            

            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;

        }
}
function get_template($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "
            SELECT
                Template
            FROM
                templates
            WHERE 
            Id = ?
            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}   

function clone_record($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            if($IDS[0]== 'products'){
                $nuevoId = clonarRegistro($db, $IDS[0], $id, $IDS[1]);
                //clonarRegistrosRelacionados($db, 'products_item_price', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_categories', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_images', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'packing_list', 'Producto_pl', $id, $nuevoId); //Lista de embalaje
                clonarRegistrosRelacionados($db, 'related_products', 'Producto_rp', $id, $nuevoId); //Accesorios, complementos, banners y opciones
                clonarRegistrosRelacionados($db, 'upselling_products', 'Producto_up', $id, $nuevoId); //Venta adicional
                clonarRegistrosRelacionados($db, 'relationship_products', 'Producto_sp', $id, $nuevoId); //Relación
                clonarRegistrosRelacionados($db, 'cost_products', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_files', 'Product', $id, $nuevoId);


                $sql = "UPDATE products SET Name = :name_
                        WHERE Id = :id";

                $stmt = $db->prepare($sql);
                $stmt->execute(['name_' => $IDS[2],'id' => $nuevoId]);

                if ( is_numeric($nuevoId)){
                    http_response_code(200);
                    echo json_encode(array("Id" => $nuevoId));
                }
                else{
                    http_response_code(404);
                    echo json_encode(array("message" => $nuevoId));
                }
            }
            else{
                http_response_code(405);
                echo json_encode(array("message" => "Tabla no permitida"));
            }


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }    
}

function copy_records($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
        $nuevoId = $IDS[2];
        switch ($IDS[0]) {
            case 'packing_list':
                clonarRegistrosRelacionados($db, 'packing_list', 'Producto_pl', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;
            case 'related_products':
                clonarRegistrosRelacionados($db, 'related_products', 'Producto_rp', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'upselling_products':
                clonarRegistrosRelacionados($db, 'upselling_products', 'Producto_up', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'relationship_products':
                clonarRegistrosRelacionados($db, 'relationship_products', 'Producto_sp', $nuevoId, $id);            
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;                                    
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Tabla *".$IDS[0]."* no permitida"));
            break;
        }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}


function clonarRegistro($pdo, $tabla, $id_registro, $columna_id = 'id') {
    try {
        $query_columnas = $pdo->prepare("DESCRIBE $tabla");
        $query_columnas->execute();
        $columnas = $query_columnas->fetchAll(PDO::FETCH_COLUMN);

        $columnas_filtradas = array_diff($columnas, [$columna_id]);
        
        $columnas_select = [];
        foreach ($columnas_filtradas as $col) {
            // Si la columna es de fecha, usamos la función NOW() de MySQL
            if (in_array(strtolower($col), ['fechacreacion', 'fechacambio'])) {
                $columnas_select[] = "NOW()";
            } else {
                $columnas_select[] = $col;
            }
        }

        $lista_columnas_insert = implode(', ', $columnas_filtradas);
        $lista_columnas_select = implode(', ', $columnas_select);

        $sql = "INSERT INTO $tabla ($lista_columnas_insert) 
                SELECT $lista_columnas_select 
                FROM $tabla 
                WHERE $columna_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_registro]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function clonarRegistrosRelacionados($pdo, $tabla, $columna_relacional, $id_antiguo, $id_nuevo) {
    try {
        // 1. Obtener información detallada de las columnas
        $stmt = $pdo->prepare("DESCRIBE $tabla");
        $stmt->execute();
        $detalles_columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $columnas_finales = [];
        $columnas_select = [];

        foreach ($detalles_columnas as $col) {
            $nombre_col = $col['Field'];
            $es_auto_increment = (strpos($col['Extra'], 'auto_increment') !== false);
            $es_primary = ($col['Key'] === 'PRI');

            // EXCLUIR si es autoincrementable o llave primaria (para que MySQL genere el nuevo ID)
            if ($es_auto_increment || $es_primary) {
                continue; 
            }

            $columnas_finales[] = $nombre_col;

            // 2. Lógica de valores para el SELECT
            if ($nombre_col === $columna_relacional) {
                // Reemplazamos el ID padre viejo por el nuevo
                $columnas_select[] = ":id_nuevo";
            } elseif (in_array(strtolower($nombre_col), ['fechacreacion', 'fechacambio'])) {
                // Seteamos timestamp actual
                $columnas_select[] = "NOW()";
            } else {
                // El resto de columnas se copian tal cual
                $columnas_select[] = $nombre_col;
            }
        }

        $lista_insert = implode(', ', $columnas_finales);
        $lista_select = implode(', ', $columnas_select);

        // 3. Ejecutar la inserción masiva
        $sql = "INSERT IGNORE INTO $tabla ($lista_insert) 
                SELECT $lista_select 
                FROM $tabla 
                WHERE $columna_relacional = :id_antiguo";
        //die($sql . "  $id_nuevo  $id_antiguo");
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_nuevo'   => $id_nuevo,
            'id_antiguo' => $id_antiguo
        ]);

        return $stmt->rowCount();

    } catch (PDOException $e) {
        return "Error en clonarRegistrosRelacionados: " . $e->getMessage();
    }
}

function orden($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'PUT': 
        $nuevoId = 'Orden Actualizado';
        //echo $data->{'Idp'};
        //echo $data->{'Id'};  
                $productId = $data->{'Idp'};
                $imageId = $data->{'Id'};

        switch ($data->{'orden'}) {
            case 'I':

                $stmt = $db->prepare("CALL sp_image_move_to_start(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();

                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;
            case 'A':
                
                $stmt = $db->prepare("CALL sp_image_move_up(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'S':
                $stmt = $db->prepare("CALL sp_image_move_down(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                   
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'U':
                $stmt = $db->prepare("CALL sp_image_move_to_end(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                   
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;                                    
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Acción no permitida"));
            break;
        }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function ajustar_precio($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'PUT': 
            $lista = $data->{'lista'};
            $tipo_opcion = $data->{'tipo_opcion'};
            $lista_categoria = $data->{'lista_categoria'};
            $tipo_m_p  = $data->{'tipo_m_p'};
            $montoA  = $data->{'montoA'};
            $montoE  = $data->{'montoE'};
            $montoA = str_replace("$", "", $montoA);
            $montoA = str_replace(",", "", $montoA);
            $montoE = str_replace("$", "", $montoE);
            $montoE = str_replace(",", "", $montoE);            
            if ($tipo_opcion=='C'){

                $query = "INSERT INTO price_lists( Nombre, FechaHoraInicio,FechaHoraFin,Estatus,FechaCreacion,FechaCambio) 
                        SELECT :Nombre, FechaHoraInicio,FechaHoraFin,Estatus,now(),now()
                        FROM price_lists
                        WHERE 
                        Id = :Id";

                $stmt = $db->prepare($query);
                $stmt->bindValue(":Nombre", $lista_categoria);
                $stmt->bindValue(":Id", $lista);
                if ($stmt->execute()) {
                    $lastInsertId = $db->lastInsertId();
                    if ($montoA * 1 == 0 AND $montoE * 1 ==0){
                        $query = "INSERT INTO detail_price_lists( IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio) 
                                SELECT :IdLista,IdItem,JsonPrice,Estatus_price,now(),now()
                                FROM detail_price_lists
                                WHERE 
                                IdLista = :Id";
                        $stmt = $db->prepare($query);
                        $stmt->bindValue(":IdLista", $lastInsertId);
                        $stmt->bindValue(":Id", $lista);                    
                        $stmt->execute();
                    }
                    else{
                    
                        $query = "SELECT IdItem, JsonPrice,Estatus_price FROM detail_price_lists WHERE IdLista = ? ";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(1, $lista);
                        $stmt->execute();
                        $Precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($Precios) {
                            foreach ($Precios as $Precio) {
                                $IdItem = $Precio['IdItem'] ;
                                $JsonPrice = $Precio['JsonPrice'];
                                $Estatus_price = $Precio['Estatus_price'];

                                $json_limpio = html_entity_decode($JsonPrice);
                                $datos = json_decode($json_limpio, true);
                                $id_item =0;
                                foreach ($datos as &$item) {
                                    if ($tipo_m_p == '$'){
                                        if ($id_item == 0)
                                            $item['precio'] = (string)($item['precio'] + $montoA);
                                        else
                                            $item['precio'] = (string)($item['precio'] + $montoE);
                                    }
                                    else{
                                        if ($id_item == 0)
                                            $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoA /100 )));
                                        else
                                            $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoE /100 )));
                                    }
                                    $id_item+1;
                                }
                                unset($item); 
                                $nuevo_json = json_encode($datos);
                                $variable_json = htmlentities($nuevo_json);
                                // INSERT DE PRECIOS CON AJUSTE!!
                                $queryI = "INSERT INTO detail_price_lists( IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio)
                                                        values(:idLista,:idItem,:jsonPrice,:estatus_price,now(),now()) ";
                                $stmtI = $db->prepare($queryI);
                                $stmtI->bindValue(":idLista", $lastInsertId);
                                $stmtI->bindValue(":idItem", $IdItem);
                                $stmtI->bindValue(":jsonPrice", $variable_json);
                                $stmtI->bindValue(":estatus_price", $Estatus_price);
                                $stmtI->execute();
                                // INSERT DE PRECIOS CON AJUSTE!!
                            }
                        }                     

                    }
                    http_response_code(201); // Created
                    echo json_encode(array("message" => "Nueva lista registrada."));
                } else {
                    http_response_code(503); // Service Unavailable
                    echo json_encode(array("message" => "No se encotraron registros."));
                }
            }
            else{
                //Ajustar precio
                //$tipo_m_p -> $ %
                if ($lista_categoria  == 0){
                
                    $query = "SELECT IId, JsonPrice FROM detail_price_lists WHERE IdLista = ? ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $lista);
                }
                else{
                    $query = "
                        SELECT
                            detail_price_lists.IId, 
                            detail_price_lists.JsonPrice
                        FROM
                            detail_price_lists
                            INNER JOIN
                            products_item_price
                            ON 
                                detail_price_lists.IdItem = products_item_price.ItemPrice
                            INNER JOIN
                            products_categories
                            ON 
                                products_item_price.Producto = products_categories.Product
                            WHERE 
                            detail_price_lists.IdLista = ? AND
                            products_categories.Category =?                    
                    ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $lista);
                    $stmt->bindParam(2, $lista_categoria);
                }                    
                    $stmt->execute();
                    $Precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($Precios) {
                        foreach ($Precios as $Precio) {
                            $IId = $Precio['IId'] ;
                            $JsonPrice = $Precio['JsonPrice'];

                            $json_limpio = html_entity_decode($JsonPrice);
                            $datos = json_decode($json_limpio, true);
                            $id_item =0;
                            foreach ($datos as &$item) {
                                if ($tipo_m_p == '$'){
                                    if ($id_item == 0)
                                        $item['precio'] = (string)($item['precio'] + $montoA);
                                    else
                                        $item['precio'] = (string)($item['precio'] + $montoE);
                                }
                                else{
                                    if ($id_item == 0)
                                        $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoA /100 )));
                                    else
                                        $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoE /100 )));
                                }
                                $id_item+1;
                            }
                            unset($item); 
                            $nuevo_json = json_encode($datos);
                            $variable_json = htmlentities($nuevo_json);
                            // UPDATE DE PRECIOS CON AJUSTE!!
                            $queryI = "UPDATE detail_price_lists SET JsonPrice = :jsonPrice, FechaCambio = now()
                                        WHERE IId = :iId";
                            $stmtI = $db->prepare($queryI);
                            $stmtI->bindValue(":iId", $IId);
                            $stmtI->bindValue(":jsonPrice", $variable_json);
                            $stmtI->execute();
                            // UPDATE DE PRECIOS CON AJUSTE!!
                        }
                        http_response_code(201); // Created
                        echo json_encode(array("message" => "Registros actualizados."));
                    }
                    else {
                        http_response_code(503); // Service Unavailable
                        echo json_encode(array("message" => "No se encotraron registros."));
                    }
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}
function get_products_categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $IdCat = isset($_GET['IdCat']) ? (int)$_GET['IdCat'] : 0;

            $DateS_raw = isset($_GET['DateS']) ? $_GET['DateS'] : date('Y-m-d\TH:i');
            $DateE_raw = isset($_GET['DateE']) ? $_GET['DateE'] : date('Y-m-d\TH:i');

            $objDateS = new DateTime($DateS_raw);
            $objDateE = new DateTime($DateE_raw);

            $fechaS = $objDateS->format('Ymd'); // 2026-02-04
            $horaS  = $objDateS->format('H:i');   // 18:00

            $fechaE = $objDateE->format('Ymd'); // 2026-02-05
            $horaE  = $objDateE->format('H:i');   // 02:00

            $DayWeek = date('w', strtotime($fechaS));

            switch ($DayWeek) {
                case '0':
                    $DayWeek =' AND Do = 1 ';
                break;
                case '1':
                    $DayWeek =' AND Lu = 1 ';
                break;
                case '2':
                    $DayWeek =' AND Ma = 1 ';
                break;
                case '3':
                    $DayWeek =' AND Mi = 1 ';
                break;
                case '4':
                    $DayWeek =' AND Ju = 1 ';
                break;
                case '5':
                    $DayWeek =' AND Vi = 1 ';
                break;
                case '6':
                    $DayWeek =' AND Sa = 1 ';
                break;
            }

            //RECUPERAR TODO EL DETALLE DE EVENTOS ACTIVOS DE ESTA FECHA PARA RESTAR LAS CANTIDADES DE LOS PRODUCTOS

            $fechaS_db = $objDateS->format('Y-m-d H:i:s');
            $fechaE_db = $objDateE->format('Y-m-d H:i:s');

            $query = "
                SELECT IdProduct, SUM(Quantity) as Quantity 
                FROM v_leads_detail 
                WHERE Status = 'quoted' OR Status = 'confirmed' 
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
                        
                WHERE v_leads_detail.Status = 'quoted'  OR  v_leads_detail.Status = 'confirmed' 
                AND (v_leads_detail.StartDateTime < :DateEE AND v_leads_detail.EndDateTime > :DateSS)
                AND v_leads_detail.Unlimited = 0
                GROUP BY relationship_products.Producto_rsp		                

            ";

            $query = "
                SELECT 
                    IdProduct, 
                    SUM(Quantity) AS Quantity 
                FROM v_leads_detail 
                WHERE 
                    Status IN ('quoted', 'confirmed')
                    AND Unlimited = 0
                    AND StartDateTime < :DateE
                    AND EndDateTime > :DateS
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
                WHERE 
                        v_leads_detail.Status IN ('quoted', 'confirmed')
                        AND v_leads_detail.Unlimited = 0
                        AND v_leads_detail.StartDateTime < :DateEE 
                        AND v_leads_detail.EndDateTime > :DateSS 

                GROUP BY relationship_products.Producto_rsp	                
            ";             


            $stmt = $db->prepare($query);
            $stmt->bindParam(':DateS', $fechaS_db);
            $stmt->bindParam(':DateE', $fechaE_db);
            $stmt->bindParam(':DateSS', $fechaS_db);
            $stmt->bindParam(':DateEE', $fechaE_db);            
            $stmt->execute();                

            $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                

            $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');                

            $query = "
                SELECT * FROM v_items_prices_lists
                WHERE Category = :idCat  AND 
                            Estatus_price_list = 1 AND
                            Estatus_price = 1 AND 
                :date BETWEEN  FechaHoraInicio AND FechaHoraFin  $DayWeek                                    
            ";                            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idCat', $IdCat, PDO::PARAM_INT);
            $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
            //$stmt->bindValue(1, $IdCat);
            //$stmt->bindValue(2, $Date);
            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados_p) {
                foreach ($resultados_p as $index => $Precio) {
                        $JsonPrice = $Precio['JsonPrice'];
                        $JsonPrice = html_entity_decode($JsonPrice);
                        $ingreso =  $objDateS->format('Y-m-d H:i:00');
                        $salida  = $objDateE->format('Y-m-d H:i:00');
                        // Modificamos directamente el arreglo usando el índice
                        $resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);
                            if (isset($cantidadesOcupadas[$resultados_p[$index]['Producto']])) {
                                $cantidadOcupada = $cantidadesOcupadas[$resultados_p[$index]['Producto']];
                            } else {
                                $cantidadOcupada = 0; // Si no está en el arreglo, nadie lo ha rentado
                            }                            
                        $resultados_p[$index]['Quantity'] = $resultados_p[$index]['Quantity'] - $cantidadOcupada;
                        $query = "SELECT *  from products_images WHERE Product = ".$resultados_p[$index]['Producto']." ORDER BY Orden LIMIT 1";
                        $stmtigm = $db->prepare($query);
                        $stmtigm->execute();
                        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);                             
                        if ($Img)
                            $resultados_p[$index]['Image'] = $Img['Image'];
                        //if ($resultados_p[$index]['Quantity'] <= 0)
                        //    unset($resultados_p[$index]);
                    }
            }

            http_response_code(200);
            echo json_encode(array(
                "products" => $resultados_p
            ));
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function get_products_search($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Search = isset($_GET['q']) ? $_GET['q'] : '';

            $DateS_raw = isset($_GET['DateS']) ? $_GET['DateS'] : date('Y-m-d\TH:i');
            $DateE_raw = isset($_GET['DateE']) ? $_GET['DateE'] : date('Y-m-d\TH:i');

            $objDateS = new DateTime($DateS_raw);
            $objDateE = new DateTime($DateE_raw);

            $fechaS = $objDateS->format('Ymd'); // 2026-02-04
            $horaS  = $objDateS->format('H:i');   // 18:00

            $fechaE = $objDateE->format('Ymd'); // 2026-02-05
            $horaE  = $objDateE->format('H:i');   // 02:00

            $DayWeek = date('w', strtotime($fechaS));

            switch ($DayWeek) {
                case '0':
                    $DayWeek =' AND Do = 1 ';
                break;
                case '1':
                    $DayWeek =' AND Lu = 1 ';
                break;
                case '2':
                    $DayWeek =' AND Ma = 1 ';
                break;
                case '3':
                    $DayWeek =' AND Mi = 1 ';
                break;
                case '4':
                    $DayWeek =' AND Ju = 1 ';
                break;
                case '5':
                    $DayWeek =' AND Vi = 1 ';
                break;
                case '6':
                    $DayWeek =' AND Sa = 1 ';
                break;
            }

            //RECUPERAR TODO EL DETALLE DE EVENTOS ACTIVOS DE ESTA FECHA PARA RESTAR LAS CANTIDADES DE LOS PRODUCTOS

            $fechaS_db = $objDateS->format('Y-m-d H:i:s');
            $fechaE_db = $objDateE->format('Y-m-d H:i:s');

            $query = "
                SELECT IdProduct, SUM(Quantity) as Quantity 
                FROM v_leads_detail 
                WHERE Status = 'quoted' OR Status = 'confirmed' 
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
                        
                WHERE v_leads_detail.Status = 'quoted'  OR  v_leads_detail.Status = 'confirmed' 
                AND (v_leads_detail.StartDateTime < :DateEE AND v_leads_detail.EndDateTime > :DateSS)
                AND v_leads_detail.Unlimited = 0
                GROUP BY relationship_products.Producto_rsp		                

            ";

            $query = "
                SELECT 
                    IdProduct, 
                    SUM(Quantity) AS Quantity 
                FROM v_leads_detail 
                WHERE 
                    Status IN ('quoted', 'confirmed')
                    AND Unlimited = 0
                    AND StartDateTime < :DateE
                    AND EndDateTime > :DateS
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
                WHERE 
                        v_leads_detail.Status IN ('quoted', 'confirmed')
                        AND v_leads_detail.Unlimited = 0
                        AND v_leads_detail.StartDateTime < :DateEE 
                        AND v_leads_detail.EndDateTime > :DateSS 

                GROUP BY relationship_products.Producto_rsp	                
            ";             


            $stmt = $db->prepare($query);
            $stmt->bindParam(':DateS', $fechaS_db);
            $stmt->bindParam(':DateE', $fechaE_db);
            $stmt->bindParam(':DateSS', $fechaS_db);
            $stmt->bindParam(':DateEE', $fechaE_db);            
            $stmt->execute();                

            $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                

            $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');                

            $query = "
                SELECT * FROM v_items_prices_lists
                WHERE ProductName LIKE :search  AND 
                            Estatus_price_list = 1 AND
                            Estatus_price = 1 AND 
                :date BETWEEN  FechaHoraInicio AND FechaHoraFin  $DayWeek                                    
            ";                            
            $stmt = $db->prepare($query);

            $searchParam = '%' . $Search . '%';

            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);          

            //$stmt->bindValue(1, $IdCat);
            //$stmt->bindValue(2, $Date);
            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados_p) {
                foreach ($resultados_p as $index => $Precio) {
                        $JsonPrice = $Precio['JsonPrice'];
                        $JsonPrice = html_entity_decode($JsonPrice);
                        $ingreso =  $objDateS->format('Y-m-d H:i:00');
                        $salida  = $objDateE->format('Y-m-d H:i:00');
                        // Modificamos directamente el arreglo usando el índice
                        $resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);
                            if (isset($cantidadesOcupadas[$resultados_p[$index]['Producto']])) {
                                $cantidadOcupada = $cantidadesOcupadas[$resultados_p[$index]['Producto']];
                            } else {
                                $cantidadOcupada = 0; // Si no está en el arreglo, nadie lo ha rentado
                            }                            
                        $resultados_p[$index]['Quantity'] = $resultados_p[$index]['Quantity'] - $cantidadOcupada;
                        $query = "SELECT *  from products_images WHERE Product = ".$resultados_p[$index]['Producto']." ORDER BY Orden LIMIT 1";
                        $stmtigm = $db->prepare($query);
                        $stmtigm->execute();
                        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);                             
                        if ($Img)
                            $resultados_p[$index]['Image'] = $Img['Image'];
                        //if ($resultados_p[$index]['Quantity'] <= 0)
                        //    unset($resultados_p[$index]);
                    }
            }

            http_response_code(200);
            echo json_encode(array(
                "products" => $resultados_p
            ));
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function get_related_products($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $IdP = isset($_GET['IdP']) ? (int)$_GET['IdP'] : 0;
            $IdCat = isset($_GET['IdCat']) ? (int)$_GET['IdCat'] : 0;
            

            $DateS_raw = isset($_GET['DateS']) ? $_GET['DateS'] : date('Y-m-d\TH:i');
            $DateE_raw = isset($_GET['DateE']) ? $_GET['DateE'] : date('Y-m-d\TH:i');

            $objDateS = new DateTime($DateS_raw);
            $objDateE = new DateTime($DateE_raw);

            $fechaS = $objDateS->format('Ymd'); // 2026-02-04
            $horaS  = $objDateS->format('H:i');   // 18:00

            $fechaE = $objDateE->format('Ymd'); // 2026-02-05
            $horaE  = $objDateE->format('H:i');   // 02:00

            $DayWeek = date('w', strtotime($fechaS));

            switch ($DayWeek) {
                case '0':
                    $DayWeek =' AND Do = 1 ';
                break;
                case '1':
                    $DayWeek =' AND Lu = 1 ';
                break;
                case '2':
                    $DayWeek =' AND Ma = 1 ';
                break;
                case '3':
                    $DayWeek =' AND Mi = 1 ';
                break;
                case '4':
                    $DayWeek =' AND Ju = 1 ';
                break;
                case '5':
                    $DayWeek =' AND Vi = 1 ';
                break;
                case '6':
                    $DayWeek =' AND Sa = 1 ';
                break;
            }                

            //RECUPERAR TODO EL DETALLE DE EVENTOS ACTIVOS DE ESTA FECHA PARA RESTAR LAS CANTIDADES DE LOS PRODUCTOS

            $fechaS_db = $objDateS->format('Y-m-d H:i:s');
            $fechaE_db = $objDateE->format('Y-m-d H:i:s');

            $query = "
                SELECT IdProduct, SUM(Quantity) as Quantity 
                FROM v_leads_detail 
                WHERE Status = 'quoted' 
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

            $query = "
                SELECT 
                    IdProduct, 
                    SUM(Quantity) AS Quantity 
                FROM v_leads_detail 
                WHERE 
                    Status IN ('quoted', 'confirmed')
                    AND Unlimited = 0
                    AND StartDateTime < :DateE
                    AND EndDateTime > :DateS
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
                WHERE 
                        v_leads_detail.Status IN ('quoted', 'confirmed')
                        AND v_leads_detail.Unlimited = 0
                        AND v_leads_detail.StartDateTime < :DateEE 
                        AND v_leads_detail.EndDateTime > :DateSS 

                GROUP BY relationship_products.Producto_rsp	                
            ";             

            $stmt = $db->prepare($query);
            $stmt->bindParam(':DateS', $fechaS_db);
            $stmt->bindParam(':DateE', $fechaE_db);
            $stmt->bindParam(':DateSS', $fechaS_db);
            $stmt->bindParam(':DateEE', $fechaE_db);            
            $stmt->execute();                

            $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                

            $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');             

            $query = "
                SELECT * FROM v_related_products_prices_lists
                WHERE Producto_rp = :idp  AND 
                            Estatus_price_list = 1 AND
                            Estatus_price = 1 AND 
                :date BETWEEN  FechaHoraInicio AND FechaHoraFin $DayWeek  GROUP BY Producto                                 
            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idp', $IdP, PDO::PARAM_INT);
            //$stmt->bindParam(':idcat', $IdCat, PDO::PARAM_INT);//Category = :idcat AND
            $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
            //$stmt->bindValue(1, $IdCat);
            //$stmt->bindValue(2, $Date);
            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($resultados_p) {
                foreach ($resultados_p as $index => $Precio) {
                        $JsonPrice = $Precio['JsonPrice'];
                        $JsonPrice = html_entity_decode($JsonPrice);
                        $ingreso =  $objDateS->format('Y-m-d H:i:00');
                        $salida  = $objDateE->format('Y-m-d H:i:00');
                        // Modificamos directamente el arreglo usando el índice
                        $resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);

                            if (isset($cantidadesOcupadas[$resultados_p[$index]['Producto']])) {
                                $cantidadOcupada = $cantidadesOcupadas[$resultados_p[$index]['Producto']];
                            } else {
                                $cantidadOcupada = 0; // Si no está en el arreglo, nadie lo ha rentado
                            }                            

                        $resultados_p[$index]['Quantity'] = $resultados_p[$index]['Quantity'] - $cantidadOcupada;

                        $query = "SELECT *  from products_images WHERE Product = ".$resultados_p[$index]['Producto']." ORDER BY Orden LIMIT 1";
                        $stmtigm = $db->prepare($query);
                        $stmtigm->execute();
                        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);                             
                        if ($Img)
                            $resultados_p[$index]['Image'] = $Img['Image'];
                        //if ($resultados_p[$index]['Quantity'] <= 0)
                        //    unset($resultados_p[$index]);
                    }
            }                

            http_response_code(200);
            echo json_encode(array(
                "products" => $resultados_p
            ));
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function get_organization($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, Nombre, Direccion FROM organizations
                WHERE Nombre LIKE :q AND Estatus = 'A'
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }  
}

function save_organization($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $query = "INSERT INTO  organizations (Nombre,Estatus,FechaCreacion,FechaCambio) VALUES(:nombre,'A',now(),now()) ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":nombre", $data->{'nombre'});
            
            if ($stmt->execute()) {

                $lastInsertId = $db->lastInsertId();
                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','organizations',$lastInsertId,0,'I','Registro Insertado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();
                http_response_code(201); // Created
                echo json_encode(   array( "id" => $lastInsertId, "nombre"=> $data->{'nombre'}) );
            }            
        break;
        case 'PUT':
            if ($data->{'IdOrganization'} > 0) {
                $query = "UPDATE  organizations  SET Pais = :pais, Estado = :estado, Direccion = :direccion, Ciudad = :ciudad, CP = :cp, TelefonoCelular = :celular, Correo = :correo, Notas = :notas, FechaCambio = now() WHERE Id = :id ";
                $stmt = $db->prepare($query);
                $stmt->bindValue("pais", $data->{'Country'});
                $stmt->bindValue("estado", $data->{'State'});
                $stmt->bindValue("direccion", $data->{'Street'});
                $stmt->bindValue("ciudad", $data->{'City'});
                $stmt->bindValue("cp", $data->{'Zip'});
                $stmt->bindValue("celular", $data->{'Cell'});
                $stmt->bindValue("correo", $data->{'CustomerEmail'});
                $stmt->bindValue("notas", $data->{'CustomerNote'});
                $stmt->bindValue(":id", $data->{'IdOrganization'});
                if ($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Registro actualizado."));
                }
                else{
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el registro."));
                }
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }  
}

function get_customers($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, CONCAT(Nombres,' ', Apellidos) as Nombre, Direccion FROM customers
                WHERE ( Nombres LIKE :q  OR Apellidos LIKE :q2)  AND Estatus = 'A'
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':q2', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function save_customer($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            if ($data->{'nombre'} != ""){
                $resultado = dividirNombreCompleto($data->{'nombre'});

                $query = "INSERT INTO  customers (Nombres,Apellidos,Estatus,FechaCreacion,FechaCambio) VALUES(:nombre,:apellidos,'A',now(),now()) ";
                $stmt = $db->prepare($query);
                $stmt->bindValue(":nombre",$resultado['nombres']);
                $stmt->bindValue(":apellidos",$resultado['apellido_paterno']." ".$resultado['apellido_materno'] );                
            }
            else{
                $query = "INSERT INTO  customers (TelefonoCelular,Estatus,FechaCreacion,FechaCambio) VALUES(:telefonocelular,'A',now(),now()) ";
                $stmt = $db->prepare($query);
                $stmt->bindValue(":telefonocelular",$data->{'cell'});

            }
            
            if ($stmt->execute()) {

                $lastInsertId = $db->lastInsertId();
                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','customers',$lastInsertId,0,'I','Registro Insertado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();
                http_response_code(201); // Created
                echo json_encode(   array( "id" => $lastInsertId, "nombre"=> $data->{'nombre'}) );
            }            
        break;
        case 'PUT':
            if ($data->{'IdCustomer'} > 0) {
                $CampoCustomer = "";
                if (isset($data->{'Customer'}) AND $data->{'Customer'} != $data->{'IdCustomer'}){
                    $resultado = dividirNombreCompleto($data->{'Customer'});
                    $CampoCustomer = ", Nombres = :nombres, Apellidos = :apellidos ";
                }
                $query = "UPDATE  customers  SET Pais = :pais, Estado = :estado, Direccion = :direccion, Ciudad = :ciudad, CP = :cp, TelefonoCelular = :celular, Correo = :correo, Notas = :notas, FechaCambio = now() $CampoCustomer WHERE Id = :id ";
                //echo $query;
                $stmt = $db->prepare($query);
                $stmt->bindValue(":pais", $data->{'Country'});
                $stmt->bindValue(":estado", $data->{'State'});
                $stmt->bindValue(":direccion", $data->{'Street'});
                $stmt->bindValue(":ciudad", $data->{'City'});
                $stmt->bindValue(":cp", $data->{'Zip'});
                $stmt->bindValue(":celular", $data->{'Cell'});
                $stmt->bindValue(":correo", $data->{'CustomerEmail'});
                $stmt->bindValue(":notas", $data->{'CustomerNote'});
                $stmt->bindValue(":id", $data->{'IdCustomer'});

                if (isset($data->{'Customer'})  AND $data->{'Customer'} != $data->{'IdCustomer'} ){
                    $stmt->bindValue(":nombres",$resultado['nombres']);
                    $stmt->bindValue(":apellidos",$resultado['apellido_paterno']." ".$resultado['apellido_materno'] );   
                }                

                if ($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Registro actualizado."));
                }
                else{
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el registro."));
                }
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }  
}

function get_customers_cell($table_name, $db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q != "") {
                // Buscamos en ambas tablas agregando la columna virtual 'Tipo'
                $query = "
                    SELECT CONCAT (Id,'-O') as Id, Nombre, Direccion, TelefonoCelular
                    FROM organizations
                    WHERE TelefonoCelular LIKE :q AND Estatus = 'A'
                    
                    UNION ALL
                    
                    SELECT CONCAT (Id,'-C') as Id, CONCAT(Nombres, ' ', Apellidos) AS Nombre, Direccion, TelefonoCelular 
                    FROM customers
                    WHERE TelefonoCelular LIKE :q2 AND Estatus = 'A'
                ";                                            
                
                $stmt = $db->prepare($query);

                // Curingas para la búsqueda parcial
                $searchTerm = "%" . $Q . "%";
                
                // Vinculamos el parámetro para ambas partes del UNION
                $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
                $stmt->bindParam(':q2', $searchTerm, PDO::PARAM_STR);

                $stmt->execute();
                $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

                http_response_code(200);
                echo json_encode(array(
                    "items" => $resultados_p
                ));
            }
            else {
                http_response_code(200);
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
}

function save_venue($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $query = "INSERT INTO  venues (Nombre,FechaCreacion,FechaCambio) VALUES(:nombre,now(),now()) ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":nombre", $data->{'nombre'});
            
            if ($stmt->execute()) {

                $lastInsertId = $db->lastInsertId();
                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','venues',$lastInsertId,0,'I','Registro Insertado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();
                http_response_code(201); // Created
                echo json_encode(   array( "id" => $lastInsertId, "nombre"=> $data->{'nombre'}) );
            }            
        break;
        case 'PUT':
            if ($data->{'IdVenue'} > 0) {

                if ($data ->{'EventLat'} == null OR $data->{'EventLat'} == ''){
                    $miDireccion =$data->{'EventStreet'}." ".$data->{'EventCity'}." ".$data->{'EventZip'}." ".$data->{'EventState'}." ".$data->{'EventCountry'};
                    $miDireccion = obtenerCoordenadas($miDireccion, GOOGLE_API_KEY);
                    if (isset($miDireccion['error'])) {
                        echo "Hubo un problema: " . $miDireccion['error'];
                    } else {
                        $query = "UPDATE venues SET Lat = :lat, Lng = :lng WHERE Id = :venue";
                        $stmt = $db->prepare($query);
                        $stmt->bindValue(":lat", $miDireccion['lat']);
                        $stmt->bindValue(":lng", $miDireccion['lng']);
                        $stmt->bindValue(":venue", $data->{'IdVenue'});
                        $stmt->execute();
                        $data->{'EventLat'} = $miDireccion['lat'];
                        $data->{'EventLng'} = $miDireccion['lng'];
                    }                    
                }

                $query = "UPDATE  venues  SET Pais = :pais, Estado = :estado, Direccion = :direccion, Ciudad = :ciudad, CP = :cp, Lat = :lat, Lng = :lng, FechaCambio = now() WHERE Id = :id ";
                $stmt = $db->prepare($query);
                $stmt->bindValue("pais", $data->{'EventCountry'});
                $stmt->bindValue("estado", $data->{'EventState'});
                $stmt->bindValue("direccion", $data->{'EventStreet'});
                $stmt->bindValue("ciudad", $data->{'EventCity'});
                $stmt->bindValue("cp", $data->{'EventZip'});
                $stmt->bindValue("lat", $data->{'EventLat'});
                $stmt->bindValue("lng", $data->{'EventLng'});
                $stmt->bindValue(":id", $data->{'IdVenue'});
                if ($stmt->execute()) {
                    http_response_code(200);
                    if ($data->{'EventLat'} == ""){
                        echo json_encode(array("message" => "Registro actualizado.","GEO" => false));
                    }
                    else{
                        echo json_encode(array("message" => "Registro actualizado.","GEO" => true));
                    }
                    
                }
                else{
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el registro."));
                }
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }  
}

function get_referals($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, CONCAT(Nombres,' ', Apellidos) as Nombre, Direccion FROM referals
                WHERE ( Nombres LIKE :q  OR Apellidos LIKE :q2)  AND Estatus = 'A'
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':q2', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}


function get_venues($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
                $query = "
                    SELECT Id, Nombre, Direccion FROM venues
                    WHERE Nombre LIKE :q 
                ";                            
                $stmt = $db->prepare($query);

                // Adiciona os curingas para busca parcial
                $searchTerm = "%" . $Q . "%";
                $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);

                $stmt->execute();
                $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

                http_response_code(200);
                echo json_encode(array(
                    "items" => $resultados_p
                ));
                }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function get_venue($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $Adr = $data->EventStreet;
            $EvC = $data->EventCity;
            $EvZ = $data->EventZip;
            $Ctry = $data->EventCountry;
            $State = $data->EventState;
            $EName = $data->EventName;
            $query = "
                SELECT Id FROM venues
                WHERE Direccion = :q1 AND  
                      Ciudad = :q2 AND
                      Cp = :q3";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial            
            $stmt->bindParam(':q1', $Adr, PDO::PARAM_STR);
            $stmt->bindParam(':q2', $EvC, PDO::PARAM_STR);
            $stmt->bindParam(':q3', $EvZ, PDO::PARAM_STR);

            $stmt->execute();
            $venue = $stmt->fetch(PDO::FETCH_ASSOC);            

            if ($venue){
                $query = "
                    SELECT * FROM venues
                    WHERE Id = :q";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':q', $venue['Id'], PDO::PARAM_STR);
                $stmt->execute();
                $venue = $stmt->fetch(PDO::FETCH_ASSOC);                            
            }
            else{
            
                $sqlLead = "INSERT INTO venues (Nombre,Direccion,Ciudad,CP,Estado,Pais,FechaCreacion,FechaCambio) VALUES (?,?,?,?,?,?,now(),now())";

                $stmtLead = $db->prepare($sqlLead);
                $stmtLead->execute([$EName,$Adr,$EvC,$EvZ,$State,$Ctry]);
                $idLead = $db->lastInsertId();            

                $query = "
                    SELECT * FROM venues
                    WHERE Id = :q";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':q', $idLead, PDO::PARAM_STR);
                $stmt->execute();
                $venue = $stmt->fetch(PDO::FETCH_ASSOC);                     

            }

            http_response_code(200);
            echo json_encode($venue);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function distance_charge($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $ZIPO = $data->{'ZIPO'};
            $CONO = $data->{'CONO'};
            $ZIPD = $data->{'ZIPD'};
            $COND = $data->{'COND'};
            $APPLY = $data->{'APPLY'};
            if ($ZIPD!="" AND $ZIPD != $ZIPO AND $APPLY == 1){
                    //RECUPERAMOS EL COSTO EXTRA POR MILLA
                    $query = "SELECT Rate, Zip, Distance, State, Total, Restriction FROM distance_charges  LIMIT 1";
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    //$costo_extra = $stmt->fetchColumn();
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($data) {
                        // Ahora accedes a cada valor por su nombre
                        $costo_extra =  $data['Rate'];
                        $Zip =  $data['Zip'];
                        $Distance =  $data['Distance'];
                        
                    }           
                    //echo " ** $Distance **";
                    $total_millas = 0;
                    if ($Distance==1){
                        //die("$ZIPO,$CONO $ZIPD,$COND");
                        $total_millas = get_distance("$ZIPO,$CONO","$ZIPD,$COND");
                        if (str_starts_with($total_millas, 'Error')) {
                            echo "Se detectó un error.";
                        }
                        else{
                            $total_millas = str_replace(" mi", "", $total_millas);
                            $total_millas = str_replace(",", "", $total_millas);
                            $total_millas = $total_millas * 1;
                        }
                        //$total_millas = 35; //AQUI VA LA FUNCION DE GOOGLE MAPS PARA SABER LAS MILLAS
                        //die( $total_millas);

                    // 1. Consultamos los rangos ordenados
                        $query = "SELECT MinM, MaxM, ChargeD, ChargeType 
                                FROM distance_charges_distance 
                                ORDER BY MinM ASC";
                    //echo $query;    
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $rangos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $costo_total = 0;
                        $max_milla_cubierta = 0;

                        // 2. Procesamos cada tramo
                        foreach ($rangos as $rango) {
                            $min = (float)$rango['MinM'];
                            $max = (float)$rango['MaxM'];
                            $cargo = (float)$rango['ChargeD'];
                            $tipo = strtoupper($rango['ChargeType']);

                            // Si el viaje no llega ni al inicio de este rango, lo ignoramos
                            if ($total_millas < $min) {
                                continue;
                            }

                            // Determinamos el final del tramo actual
                            $milla_final_en_tramo = min($total_millas, $max);
                            
                            if ($tipo === 'F') {
                                // Cargo FIJO: Se suma el monto completo si el envío toca este rango
                                $costo_total += $cargo;
                            } elseif ($tipo === 'M') {
                                // Cargo POR MILLA: Calculamos cuántas millas del total caen en este rango
                                $millas_a_cobrar = $milla_final_en_tramo - ($min - 1); 
                                $costo_total += ($millas_a_cobrar * $cargo);
                            }

                            // Guardamos hasta dónde llega la cobertura de la tabla
                            $max_milla_cubierta = max($max_milla_cubierta, $max);
                        }

                        // 3. Si hay millas excedentes fuera de la tabla, aplicamos el costo extra
                        if ($total_millas > $max_milla_cubierta) {
                            $millas_excedentes = $total_millas - $max_milla_cubierta;
                            $costo_total += ($millas_excedentes * $costo_extra);
                        }
                        //$costo_total;
                    }

                    if ($CONO =='MX'){
                        $TaxRate = '0.16';
                    }else{

                        $TaxRate = 0;
                        $query = "SELECT EstimatedCombineRate FROM taxrates_zip WHERE Zip = :zip";
                        
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':zip', $ZIPD, PDO::PARAM_STR);
                        $stmt->execute();
                        //$costo_extra = $stmt->fetchColumn();
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($data) {
                            // Ahora accedes a cada valor por su nombre
                            $TaxRate =  $data['EstimatedCombineRate'];
                        }                    

                    }


                    $respuesta = [
                        "status" => "success",
                        "total_millas" => $total_millas,
                        "costo_total" => round($costo_total, 2),
                        "taxrate" => $TaxRate
                    ];

                    echo json_encode(array(
                        "cost" => $respuesta
                    ));  
                }
                else{

                    if ($CONO =='MX'){
                        $TaxRate = '0.16';
                    }else{

                        $TaxRate = 0;
                        $query = "SELECT EstimatedCombineRate FROM taxrates_zip WHERE Zip = :zip";
                        
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':zip', $ZIPD, PDO::PARAM_STR);
                        $stmt->execute();
                        //$costo_extra = $stmt->fetchColumn();
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($data) {
                            // Ahora accedes a cada valor por su nombre
                            $TaxRate =  $data['EstimatedCombineRate'];
                        }                    

                    }

                    $respuesta = [
                        "status" => "success",
                        "total_millas" => 0,
                        "costo_total" => 0,
                        "taxrate" => $TaxRate
                    ];

                    echo json_encode(array(
                        "cost" => $respuesta
                    ));                 

                }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function calcularCostoEstanciaPHP($jsonConfig, $inicio, $fin) {
    $configTotal = is_string($jsonConfig) ? json_decode($jsonConfig, true) : $jsonConfig;
    if (empty($configTotal)) return 0;

    $fechaInicio = new DateTime($inicio);
    $fechaFin = new DateTime($fin);
    $intervalo = $fechaInicio->diff($fechaFin);
    
    // Si la fecha fin es menor, costo 0
    if ($fechaFin < $fechaInicio) return 0;

    // Calcular horas totales (equivalente a Math.ceil)
    // Convertimos diferencia a segundos y dividimos por 3600
    $segundos = $fechaFin->getTimestamp() - $fechaInicio->getTimestamp();
    $horasReales = ceil($segundos / 3600);
    
    // REGLA: Mínimo 8 horas
    $h = max($horasReales, 8);

    $conv = [
        "hora" => 1, "horas" => 1, 
        "dia" => 24, "dias" => 24, 
        "semana" => 168, "semanas" => 168
    ];

    // Buscar configuraciones (sustituye al .find de JS)
    $f1 = null; $f2 = null; $f3 = null;
    foreach ($configTotal as $c) {
        if ($c['funcion'] === 'f1') $f1 = $c;
        if ($c['funcion'] === 'f2') $f2 = $c;
        if ($c['funcion'] === 'f3') $f3 = $c;
    }

    if (!$f1) return 0;

    $costoTotal = 0;
    $p1 = floatval($f1['precio'] ?? 0);

    // --- LÓGICA DE CÁLCULO ---
    if ($f1['tipo'] === "Indefinido") {
        $costoTotal = $p1;
    } 
    elseif ($f1['tipo'] === "Cada") {
        $t1 = (floatval($f1['tiempo'] ?? 1)) * $conv[$f1['unidad']];
        $costoTotal = $p1; // Cobro inicial

        $limiteF2 = ($f2 && $f2['tipo'] === "Hasta") 
            ? (floatval($f2['tiempo'] ?? 1)) * $conv[$f2['unidad']] 
            : PHP_INT_MAX;

        if ($h > 0) {
            if ($h < $limiteF2) {
                $costoTotal += floor($h / $t1) * $p1;
            } else {
                // Se cobra F1 hasta el límite definido por F2
                $costoTotal += floor(($limiteF2 - 1) / $t1) * $p1;
            }

            // Aplicar F3 si sobrepasa o iguala el límite de F2
            if ($h >= $limiteF2 && $f3 && $f3['tipo'] === "Cada") {
                $t3 = (floatval($f3['tiempo'] ?? 1)) * $conv[$f3['unidad']];
                $p3 = floatval($f3['precio'] ?? 0);
                
                // Cálculo de ciclos de F3 desde el punto de corte
                $costoTotal += (floor(($h - $limiteF2) / $t3) + 1) * $p3;
            }
        }
    } 
    elseif ($f1['tipo'] === "Hasta") {
        $t1 = (floatval($f1['tiempo'] ?? 1)) * $conv[$f1['unidad']];
        if ($h <= $t1) {
            $costoTotal = $p1;
        } elseif ($f3) {
            $p3 = floatval($f3['precio'] ?? 0);
            $t3 = (floatval($f3['tiempo'] ?? 1)) * $conv[$f3['unidad']];
            
            if ($f3['tipo'] === "Hasta") {
                $costoTotal = $p3;
            } elseif ($f3['tipo'] === "Cada") {
                $costoTotal = $p1 + (floor(($h - $t1) / $t3) * $p3);
            }
        } else {
            $costoTotal = $p1;
        }
    }

    return $costoTotal;
}
function lead_auto_save($table_name,$db, $method, $id, $data){

    if (!$data || !isset($data->header) || !isset($data->detalle)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        exit;
    }

    $sql = "SELECT * FROM account";
    $stmt = $db->prepare($sql);
    //$stmt->bindValue(":name", $data->Product); 
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);    

    $h = $data->header;

    $idLead = (!empty($h->IdLead)) ? $h->IdLead : null;
    


    if ($idLead) {
        // --- MODO UPDATE ---
        $sqlLead = "UPDATE lead SET 
            StartDateTime=?, EndDateTime=?, DeliveryDateTime=?,Organization=?, Customer=?, Referal=?, 
            OkT=?, WA=?, AE=?, ME=?, CustomerNote=?, Venue=?, EventName=?, Surface=?, 
            Delivery=?, Note1=?, Note2=?, ItemTotals=?, ChkDstC=?, DistanceCharges=?, ChkStCs=?, 
            StafCost=?, ChkDsc=?, Discount=?, SubTotal=?, TaxId=?, TaxPc=?, 
            TaxAmount=?, Total=?, Deposit=?,DepositAmount=?, Balance=?,FechaCambio=now(),TotalBT=?
            WHERE Id = ?";
        
        $stmtLead = $db->prepare($sqlLead);
        $stmtLead->execute([
            $h->FHI, $h->FHF, $h->FHD, $h->Organization, $h->Customer, $h->Referal,
            $h->OkT, $h->WA, $h->AE, $h->ME, $h->CusNt, $h->Venue, $h->EventName, $h->Surface,
            $h->Delivety, $h->Nt1, $h->Nt2, $h->Item_Totals, $h->ChkDstC, $h->DstC, $h->ChkStCs, 
            $h->StCs, $h->ChkDsc, $h->Dsc, $h->SubT, $h->TaxId, $h->TaxPc, 
            $h->TaxAm, $h->Total, $h->Depo,$h->DepoA, $h->BalDue, $h->Total, $idLead
        ]);

        // Limpiar detalles anteriores para evitar duplicados
        $db->prepare("DELETE FROM lead_detail WHERE IdLead = ?")->execute([$idLead]);

        $db->prepare("DELETE FROM lead_discounts WHERE IdLead = ?")->execute([$idLead]);

    } else {


        $Status = '';

        if ($h->Organization == "" AND  $h->Customer == "" AND $h->Venue == "" ){
            $Status = 'draft';
        }
        elseif (($h->Organization > 0 OR  $h->Customer > 0) AND $h->Venue == "" ){
            $Status = 'draft';
        }
        elseif ( ($h->Organization > 0 OR  $h->Customer > 0) AND $h->Venue > 0 ){
            $Status = 'quoted';
        }      

        $Folio = 0;    
        $IdBranch = 1;
        $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Lead'");
        $stmt->execute([$IdBranch]);
        $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Payments){
            $Folio = $Payments['Folio'];
        }
        $Folio+=1;


        
        if ($account['DepositType'] == 'percentage'){
            $h->Depo = $account['DepositAmount'];
            $h->DepoA = ($h->Total * ($h->Depo / 100)); 
        }else{
            $h->Depo = 0;
            $h->DepoA = $account['DepositAmount']; 
        }        

        // --- MODO INSERT --
        $sqlLead = "INSERT INTO lead (
            StartDateTime, EndDateTime,DeliveryDateTime, Organization, Customer, Referal, 
            OkT, WA, AE, ME, CustomerNote, Venue, EventName, Surface, 
            Delivery, Note1, Note2, ItemTotals, ChkDstC, DistanceCharges, ChkStCs, 
            StafCost, ChkDsc, Discount, SubTotal, TaxId, TaxPc, 
            TaxAmount, Total, Deposit,DepositAmount, Balance, Status,FechaCreacion,FechaCambio,IdBranch,Folio,TotalBT
        ) VALUES (?,?,?,?,?,?,
                  ?,?,?,?,?,?,?,?,
                  ?,?,?,?,?,?,?,
                  ?,?,?,?,?,?,
                  ?,?,?,?,?,?,now(),now(),?,?,?)";

        $stmtLead = $db->prepare($sqlLead);
        $stmtLead->execute([
            $h->FHI, $h->FHF, $h->FHD, $h->Organization, $h->Customer, $h->Referal,
            $h->OkT, $h->WA, $h->AE, $h->ME, $h->CusNt, $h->Venue, $h->EventName, $h->Surface,
            $h->Delivety, $h->Nt1, $h->Nt2, $h->Item_Totals, $h->ChkDstC, $h->DstC, $h->ChkStCs, 
            $h->StCs, $h->ChkDsc, $h->Dsc, $h->SubT, $h->TaxId, $h->TaxPc, 
            $h->TaxAm, $h->Total, $h->Depo,$h->DepoA, $h->BalDue, $Status,$IdBranch,$Folio,$h->Total
        ]);
        $idLead = $db->lastInsertId();

        $stmt = $db->prepare(" UPDATE folios SET Folio = ? WHERE IdBranch = ? AND Type = 'Lead'");
        $stmt->execute([$Folio,$IdBranch]);
        $UUID = generar_uuid_v4();
        //$stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?,NOW() + INTERVAL '2 days',?)");
        $stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?, NOW() + INTERVAL 2 DAY, ?)");
        $stmt->execute([$UUID,$idLead,'A']);

    }

    // --- 3. INSERTAR DETALLES (detalle es un array de objetos) ---
    $sqlDetail = "INSERT INTO lead_detail (IdLead, IdProduct, IdProductRel, Quantity, Discount, Tax, Price,OrgPrice) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtDetail = $db->prepare($sqlDetail);

    foreach ($data->detalle as $item) {
        $stmtDetail->execute([
            $idLead, 
            $item->id_prd, 
            $item->id_rel, 
            $item->cant, 
            $item->descuento, 
            $item->imp, 
            $item->precio,
            $item->price
        ]);
    }

    // --- 4. INSERTAR DESCUENTOS (detall,e es un array de objetos) ---
    // --- 4. INSERTAR DESCUENTOS (detall,e es un array de objetos) Descr
    $sqlDiscounts = "INSERT INTO lead_discounts (IdLead, IdDiscount, Type, Amount,AmountVal,Descript) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmtDiscounts = $db->prepare($sqlDiscounts);

    foreach ($data->descuentos as $item) {
        $stmtDiscounts->execute([
            $idLead, 
            $item->IdDiscount,
            $item->Type, 
            $item->Amount,
            $item->AmountVal,
            $item->Descript
        ]);
    }    

        $stmt = $db->prepare("select UUID FROM quotes WHERE IdQuote = ?");
        $stmt->execute([$idLead]);
        $Quotes = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Quotes){
            $UUID = $Quotes['UUID'];
        }

        $stmt = $db->prepare("select Folio FROM lead WHERE Id = ?");
        $stmt->execute([$idLead]);
        $Lead = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Lead){
            $Folio = $Lead['Folio'];
        }        

    echo json_encode(["status" => "success", "IdLead" => $idLead,"Folio" => $Folio, "UUID" => $UUID]);



}
function leads($table_name, $db, $method, $id, $data) {
    global $IDS;
    switch ($method) {
        case 'GET': 
            $limit = 15;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            
            // Recoger parámetros de filtrado estándar
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
            $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

            // Decodificar los presets recibidos del frontend
            $presets = isset($_GET['presets']) ? json_decode($_GET['presets'], true) : [];

            // Consulta base
            $sql = "SELECT l.*, 
                    CASE 
                        WHEN l.Organization > 0 THEN l.NombreOrganizacion 
                        WHEN l.Customer > 0 THEN CONCAT(l.NombreCliente, ' ', l.ApellidosCliente)
                        ELSE 'Sin identificar'
                    END AS NombreMostrar
                    FROM v_leads l
                    LEFT JOIN v_leads_detail ld ON l.Id = ld.Id";

            $conditions = [];
            $params = [];

            // Excluir leads con Status 'deleted' (soft delete)
            $conditions[] = "l.Status != 'deleted'";

            // 1. Filtro por texto estándar libre
            if ($search != '') {
                $conditions[] = "(l.NombreOrganizacion LIKE :search 
                                 OR l.NombreCliente LIKE :search 
                                 OR l.ApellidosCliente LIKE :search 
                                 OR ld.Name LIKE :search)";
                $params[':search'] = "%$search%";
            }

            // 2. Procesar los filtros rápidos predeterminados (Presets)
            if (!empty($presets) && is_array($presets)) {
                $status_conditions = [];
                $product_conditions = [];

                foreach ($presets as $index => $preset) {
                    $field = $preset['field'];
                    $value = $preset['value'];
                    $param_key = ":preset_" . $field . "_" . $index;

                    if ($field === 'Status') {
                        // Agrupamos filtros de estado con "OR" por si seleccionan varios estados a la vez
                        $status_conditions[] = "l.Status = {$param_key}";
                        $params[$param_key] = $value;
                    } elseif ($field === 'Product') {
                        // Filtro para buscar productos específicos seleccionados
                        $product_conditions[] = "ld.Name LIKE {$param_key}";
                        $params[$param_key] = "%{$value}%";
                    }
                }

                if (!empty($status_conditions)) {
                    $conditions[] = "(" . implode(' OR ', $status_conditions) . ")";
                    //echo "111";
                }
                if (!empty($product_conditions)) {
                    $conditions[] = "(" . implode(' OR ', $product_conditions) . ")";
                    //echo "222";
                }
            }

            // 3. Filtros de rango de fecha
            if ($date_from != '' && $date_to != '') {
                $conditions[] = "l.StartDateTime BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $date_from . " 00:00:00";
                $params[':date_to'] = $date_to . " 23:59:59";
            } elseif ($date_from != '') {
                $conditions[] = "l.StartDateTime >= :date_from";
                $params[':date_from'] = $date_from . " 00:00:00";
            } elseif ($date_to != '') {
                $conditions[] = "l.StartDateTime <= :date_to";
                $params[':date_to'] = $date_to . " 23:59:59";
            }

            // Unir condiciones a la query
            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= " GROUP BY l.Id ORDER BY l.Id DESC LIMIT :limit OFFSET :offset";

            
            $stmt = $db->prepare($sql);

            // Bindeo de todos los parámetros acumulados
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();            

            if ($stmt) {
                http_response_code(200);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        case 'DELETE':
            // Soft-delete: cambiar Status a 'deleted' para uno o varios leads
            $ids = isset($data->ids) ? $data->ids : [];
            if (empty($ids) || !is_array($ids)) {
                http_response_code(400);
                echo json_encode(array("status" => "error", "message" => "No se proporcionaron IDs válidos."));
                break;
            }

            // Sanitizar: solo enteros
            $ids = array_map('intval', $ids);

            // Verificar cuáles leads tienen pagos activos (Estatus = 'A')
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sqlCheck = "SELECT p.IdLead, l.Folio 
                         FROM payments p 
                         INNER JOIN lead l ON l.Id = p.IdLead 
                         WHERE p.IdLead IN ($placeholders) AND p.Estatus = 'A' 
                         GROUP BY p.IdLead";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute($ids);
            $leadsConPagos = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

            $idsConPagos = array_column($leadsConPagos, 'IdLead');
            $foliosConPagos = array_column($leadsConPagos, 'Folio');

            // Filtrar los IDs que SÍ se pueden eliminar (sin pagos activos)
            $idsBorrables = array_diff($ids, $idsConPagos);

            $deleted = 0;
            if (!empty($idsBorrables)) {
                $placeholdersBorrar = implode(',', array_fill(0, count($idsBorrables), '?'));
                $sql = "UPDATE lead SET Status = 'deleted' WHERE Id IN ($placeholdersBorrar)";
                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($idsBorrables));
                $deleted = $stmt->rowCount();
            }

            // Responder con resultado mixto si algunos no se pudieron borrar
            if (!empty($idsConPagos)) {
                $foliosList = implode(', ', array_map(function($f) { return '#' . $f; }, $foliosConPagos));
                http_response_code(200);
                echo json_encode(array(
                    "status" => "partial",
                    "message" => "No se pudieron eliminar los leads con pagos aplicados: " . $foliosList,
                    "deleted" => $deleted,
                    "blocked_ids" => array_map('intval', $idsConPagos),
                    "blocked_folios" => $foliosConPagos
                ));
            } else {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Leads eliminados correctamente.", "count" => $deleted));
            }
        break;
        default:
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function sales($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 


            $limit = 15;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            if ($search != ''){
                $sql = "SELECT *
                        FROM v_sales
                        WHERE (NombreCliente LIKE :s OR ApellidosCliente LIKE :s)
                        ORDER BY Id DESC 
                        LIMIT :limit OFFSET :offset";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':s', "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            else{
                // Consulta con lógica de negocio integrada
                $sql = "SELECT *
                        FROM v_sales 
                        ORDER BY Id DESC 
                        LIMIT :limit OFFSET :offset";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();            


            if ($stmt) {
                http_response_code(200);
                echo json_encode($stmt->fetchAll());
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 


function comments_admin($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 


            $limit = 15;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            if ($search != ''){
                $sql = "SELECT *
                        FROM sale_reviews
                        WHERE (author_name LIKE :s OR author_meta LIKE :s)
                        ORDER BY Id DESC 
                        LIMIT :limit OFFSET :offset";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':s', "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            else{
                // Consulta con lógica de negocio integrada
                $sql = "SELECT *
                        FROM sale_reviews 
                        ORDER BY Id DESC 
                        LIMIT :limit OFFSET :offset";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();            


            if ($stmt) {
                http_response_code(200);
                echo json_encode($stmt->fetchAll());
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 


function comments_admin_update($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $queryI= "UPDATE sale_reviews SET status  = :status, is_featured = :is_featured WHERE id = :id";
            
            $stmt = $db->prepare($queryI);
            $stmt->bindValue(":status", $data->status);
            $stmt->bindValue(":is_featured", $data->is_featured);
            $stmt->bindValue(":id", $data->id);
            $stmt->execute();            


            if ($stmt) {
                http_response_code(200);
                echo json_encode(array("message" => "Registro actualizado."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 


function pending_payments($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 

$fechaInicio = $_GET['fInicio'] ?? date('Y-m-d');
$fechaFin    = $_GET['fFin'] ?? date('Y-m-d'); 

// Rango completo de tiempo para el día
$inicioFull = $fechaInicio . " 00:00:00";
$finFull    = $fechaFin . " 23:59:59";

$limit  = 15;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Aseguramos que la página nunca sea menor a 1
$page   = max(1, $page); 
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Consulta SQL optimizada
$sql = "SELECT *, 
            CASE 
                WHEN Organization > 0 THEN NombreOrganizacion 
                WHEN Customer > 0 THEN CONCAT(NombreCliente, ' ', ApellidosCliente)
                ELSE 'Sin identificar'
            END AS NombreMostrar
        FROM v_leads 
        WHERE (NombreOrganizacion LIKE :s OR NombreCliente LIKE :s OR ApellidosCliente LIKE :s)
          AND Balance > 0
          AND StartDateTime BETWEEN :inicio AND :fin
        ORDER BY StartDateTime DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);

$searchTerm = "%{$search}%";
$stmt->bindValue(':s', $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(':inicio', $inicioFull, PDO::PARAM_STR);
$stmt->bindValue(':fin', $finFull, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

            if ($stmt) {
                http_response_code(200);
                echo json_encode($stmt->fetchAll());
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}  

function operation($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $limit = 15;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
            $usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
            $id = isset($_GET['id']) ? $_GET['id'] : '';
            $Filtro = '';
            if ($tipo == 'ADMIN' OR $tipo == 'LOGISTICS' ){
                $Filtro = " `Status` <> 'ALMACENADO' AND `Status` <> 'FINALIZADO' OR ISNULL(Status) ";
            }

            if ($tipo == 'DRIVER'){
                $Filtro = " `Status` <> 'ALMACENADO' AND `Status` <> 'BODEGA' AND `Status` <> 'FINALIZADO' AND id_driver = $id  ";
            }            
            
            $sql = "
                SELECT
                    v_operations.Id_operation, 
                    v_operations.Id, 
                    v_operations.StartDateTime, 
                    v_operations.EndDateTime, 
                    v_operations.DeliveryDateTime, 
                    v_operations.Organization, 
                    v_operations.Customer, 
                    v_operations.Venue, 
                    v_operations.Total, 
                    v_operations.IdBranch, 
                    v_operations.Folio, 
                    v_operations.NombreOrganizacion, 
                    v_operations.OPhone, 
                    v_operations.NombreCliente, 
                    v_operations.ApellidosCliente, 
                    v_operations.CPhone, 
                    v_operations.Lugar, 
                    v_operations.Ciudad, 
                    v_operations.Estado, 
                    v_operations.id_vehicle, 
                    v_operations.vehiculo, 
                    v_operations.placas, 
                    v_operations.id_driver, 
                    v_operations.NombresChofer, 
                    v_operations.ApellidosChofer, 
                    v_operations.Lat, 
                    v_operations.Lng, 
                    v_operations.orden, 
                    v_operations.id_route, 
                    v_operations.Note1, 
                    v_operations.Note2,
                    v_operations.Balance, 
                    CASE 
                            WHEN Organization > 0 THEN NombreOrganizacion 
                            WHEN Customer > 0 THEN CONCAT(NombreCliente, ' ', ApellidosCliente)
                            ELSE 'Sin identificar'
                    END AS NombreMostrar,
                    
                    CASE 
                            WHEN ISNULL(Status) THEN 'EVENTO'
                            ELSE Status
                    END AS Status,	
                    v_operations.id_event, 	
                    extra_event.titulo, 
                    extra_event.descripcion, 
                    extra_event.gasto, 
                    extra_event.imagen,
                    extra_event.fechahora
                FROM
                    v_operations
                    left JOIN
                    extra_event
                    ON 
                        v_operations.id_event = extra_event.id_event
                WHERE
                    id_vehicle > 0 AND
                    (
                        $Filtro
                    )            

                    ORDER BY  id_vehicle, orden  ASC"; 
            $stmt = $db->prepare($sql);
            $stmt->execute();            
            $v_operations = $stmt->fetchAll();

            $client = ID_CLIENTE;
            $gallery = 'events';

            //$URLBASE = CFPUBLICURL . "/".$client."/".$gallery."/originals/";            

            foreach ($v_operations as &$op) {
                // Ejemplo: Si quieres añadir la ruta completa a la imagen
                if ($op['imagen'] != "")
                    $op['imagen'] = CFPUBLICURL . "/".$client."/".$gallery."/originals/" . $op['imagen'];
            }
            unset($op);            

            $all_ids = array_column($v_operations, 'id_route');
            $unique_ids = array_unique($all_ids);

            // Si el array está vacío, evitamos la consulta
            if (empty($unique_ids)) {
                $extra_events = [];
            } else {
                // Paso 2: Consulta única con WHERE IN
                // Creamos una cadena de placeholders (?,?,?) según la cantidad de IDs
                $placeholders = implode(',', array_fill(0, count($unique_ids), '?'));
                
                $sql = "SELECT * FROM extra_event WHERE id_route IN ($placeholders)";
                $stmt_extra = $db->prepare($sql);
                
                // Ejecutamos pasando los valores únicos
                $stmt_extra->execute(array_values($unique_ids));
                $extra_events = $stmt_extra->fetchAll();
}            

            if ($stmt) {
                http_response_code(200);
                $response = [
                    "status" => "success",
                    "operations" => $v_operations,
                    "extra_events" => $extra_events
                ];                
                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}  

function get_packing_list($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            // Consulta con lógica de negocio integrada
            $sql = "SELECT
                        products.`Name` as Item, 
                        sum(packing_list.Quantity_pl) as Quantity
                    FROM
                        lead
                        INNER JOIN
                        lead_detail
                        ON 
                            lead.Id = lead_detail.IdLead
                        INNER JOIN
                        packing_list
                        ON 
                            lead_detail.IdProduct = packing_list.Producto_pl AND
                            lead.Surface = packing_list.Surface
                        INNER JOIN
                        products
                        ON 
                            packing_list.Producto_rpl = products.Id
                            WHERE lead.Id = :id
                            GROUP BY packing_list.Producto_rpl";
            //die ($sql);
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt) {
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);                
                http_response_code(200);
                echo json_encode(array(
                    "items" => $items
                ));            


            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}  

function process_stage_change($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Recibir datos básicos
            $id_op     = $_POST['id_op'];
            $currentStage = $_POST['currentStage'];
            $nextStage = $_POST['next_stage'] ?? '';
            $coords    = $_POST['coords'];
            $notes     = $_POST['notes'];
            $items     = json_decode($_POST['items'], true);

            // 2. Manejar la imagen (opcional)
            $fileName = '';
            $imagePath = null;
            if (isset($_FILES['evidence_img']) && $_FILES['evidence_img']['error'] === UPLOAD_ERR_OK) {

                $ext = pathinfo($_FILES['evidence_img']['name'], PATHINFO_EXTENSION);
                $fileName = "evidencia_" . time() . "_" . uniqid();
                $origen = "../ajax/tmp/evidencias/" . $fileName. "." . $ext;
                //echo $fileName;
                if (move_uploaded_file($_FILES['evidence_img']['tmp_name'], $origen)) {
                    $destinot  = "../ajax/tmp/thumbnail_" . $fileName . ".avif";
                    $destino   = "../ajax/tmp/" . $fileName . ".avif";
                    $destinot2 = "../ajax/tmp/thumbnail_" . $fileName . ".jpg";
                    
                    $normal   =  $fileName . ".avif";
                    $miniatura  = "thumbnail_" . $fileName . ".avif";
                    $miniaturaj = "thumbnail_" . $fileName . ".jpg";

                    $imgOriginal = cargarImagen($origen);

                    if ($imgOriginal) {
                        generarThumbnailAVIF($imgOriginal, $destinot, 150);
                        generarNormalAVIF($imgOriginal, $destino, 1200);
                        generarThumbnailJPG($imgOriginal, $destinot2, 150);

                        imagedestroy($imgOriginal);
                        unlink($origen);

                        $client = ID_CLIENTE;
                        $gallery = 'evidence';

                        $fileName = CFPUBLICURL . "/".$client."/".$gallery."/originals/". $fileName. ".avif";

                        upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);

                    } else {
                        //echo "Formato no soportado.";
                    }
                }                
            }            
            //die();
            if  ($currentStage == 'ENTREGA'){
                $sign     = $_POST['sign'];
            }

            if  ($currentStage == 'ACONDICIONAMIENTO'){
                $stages = json_decode($_POST['STAGES'], true);

                foreach ($stages as $item) {
                    $id = $item['id'];
                    $limpios = $item['cleaning'];
                    $lavados = $item['washing'];
                    $reparar = $item['repair'];


                    $queryI ="SELECT * FROM operation_checklist WHERE id_operation = :id_operation AND id_checklist = :id";

                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_operation", $id_op);
                    $stmtI->bindValue(":id", $id);
                    $stmtI->execute();      
                    $registro = $stmtI->fetch(PDO::FETCH_ASSOC);                    

                    $queryI= "INSERT INTO operation_checklist (id_operation,id_product,id_accesory_base,id_accesory,requested_quantity,assorted_quantity,stage) VALUES(:id_operation,:id_product,:id_accesory_base,:id_accesory,:requested_quantity,:assorted_quantity,:stage)";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_operation", $registro['id_operation']);
                    $stmtI->bindValue(":id_product", $registro['id_product']);
                    $stmtI->bindValue(":id_accesory_base", $registro['id_accesory_base']);
                    $stmtI->bindValue(":id_accesory", $registro['id_accesory']);
                    $stmtI->bindValue(":requested_quantity", $registro['requested_quantity']);

                    $stmtI->bindValue(":assorted_quantity", $limpios);
                    $stmtI->bindValue(":stage", 'LIMPIEZA');
                    $stmtI->execute();

                    $stmtI->bindValue(":assorted_quantity", $lavados);
                    $stmtI->bindValue(":stage", 'LAVADO');
                    $stmtI->execute();

                    $stmtI->bindValue(":assorted_quantity", $reparar);
                    $stmtI->bindValue(":stage", 'REPARACION');
                    $stmtI->execute();                    

                    }                
                    $nextStage = 'ALMACENADO';
            }            


            $items = json_decode($_POST['items'], true);

            foreach ($items as $item) {
                $id = $item['id'];
                //$qty = $item['qty'];
                $chk = $item['chk'];            
                if ($chk)
                    $chk = 1;
                else
                    $chk = 0;

                $queryI= "UPDATE operation_checklist SET assorted_quantity = requested_quantity, verification_stage = :verification_stage WHERE id_checklist = :id_checklist AND id_operation = :id_operation AND stage = :operation_type ";
                
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":verification_stage", $chk);
                $stmtI->bindValue(":id_checklist", $id);
                $stmtI->bindValue(":id_operation", $id_op);
                $stmtI->bindValue(":operation_type", $currentStage);
                $stmtI->execute();                
            }
            if ($nextStage != ''){
                $queryI ="SELECT * FROM operation_checklist WHERE    id_operation = :id_operation AND stage = :operation_type";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_operation", $id_op);
                $stmtI->bindValue(":operation_type", $currentStage);
                $stmtI->execute();      
                $resultados = $stmtI->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    foreach ($resultados as $registro) {
                        $queryI= "INSERT INTO operation_checklist (id_operation,id_product,id_accesory_base,id_accesory,requested_quantity,assorted_quantity,stage,verification_stage) VALUES(:id_operation,:id_product,:id_accesory_base,:id_accesory,:requested_quantity,:assorted_quantity,:stage,:verification_stage)";
                        $stmtI = $db->prepare($queryI);
                        $stmtI->bindValue(":id_operation", $registro['id_operation']);
                        $stmtI->bindValue(":id_product", $registro['id_product']);
                        $stmtI->bindValue(":id_accesory_base", $registro['id_accesory_base']);
                        $stmtI->bindValue(":id_accesory", $registro['id_accesory']);
                        $stmtI->bindValue(":requested_quantity", $registro['requested_quantity']);
                        $stmtI->bindValue(":assorted_quantity", 0);
                        $stmtI->bindValue(":stage", $nextStage);
                        $stmtI->bindValue(":verification_stage", $registro['verification_stage']);
                        $stmtI->execute();                  
                    }
                }


                if  ($currentStage == 'ENTREGA'){
                    $sign     = $_POST['sign'];
                    $queryI= "INSERT INTO operation_evidence (id_operation,operation_type,url_photo,geolocation,notes,sign,datetime) VALUES(:id_operation,:operation_type,:url_photo,:geolocation,:notes,:sign,NOW())";                
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_operation", $id_op);
                    $stmtI->bindValue(":operation_type", $currentStage);
                    $stmtI->bindValue(":url_photo", $fileName);
                    $stmtI->bindValue(":geolocation", $coords);
                    $stmtI->bindValue(":notes", $notes);
                    $stmtI->bindValue(":sign", $sign);
                    $stmtI->execute();
                }
                else{
                    $queryI= "INSERT INTO operation_evidence (id_operation,operation_type,url_photo,geolocation,notes,datetime) VALUES(:id_operation,:operation_type,:url_photo,:geolocation,:notes,NOW())";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_operation", $id_op);
                    $stmtI->bindValue(":operation_type", $currentStage);
                    $stmtI->bindValue(":url_photo", $fileName);
                    $stmtI->bindValue(":geolocation", $coords);
                    $stmtI->bindValue(":notes", $notes);
                    $stmtI->execute();                
                }


            


                $queryI= "UPDATE operation_master SET status = :operation_type  WHERE id_operation = :id_operation ";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_operation", $id_op);
                $stmtI->bindValue(":operation_type", $currentStage);
                $stmtI->execute();               
            }

            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}  



function process_stage_change_em($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Recibir datos básicos
            $id_route     = $_POST['id_route'];
            $currentStage = $_POST['currentStage'];
            $nextStage = $_POST['next_stage'];
            $coords    = '';
            $notes     = '';            
            $filename = '';

            $queryI ="SELECT id_operation FROM route_stops WHERE id_route = :id_route";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_route", $id_route);
            $stmtI->execute();             
            $operaciones = $stmtI->fetchAll(PDO::FETCH_ASSOC);       

    if ($operaciones) {            
        foreach ($operaciones as $operacion) {
            $id_op     = $operacion['id_operation'];
            $queryI ="SELECT * FROM operation_checklist WHERE id_operation = :id_operation AND stage = :operation_type";

            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_operation", $id_op);
            $stmtI->bindValue(":operation_type", $currentStage);
            $stmtI->execute();      
            $resultados = $stmtI->fetchAll(PDO::FETCH_ASSOC);       
            if ($resultados) {
                foreach ($resultados as $registro) {
                    $queryI= "INSERT INTO operation_checklist (id_operation,id_product,id_accesory_base,id_accesory,requested_quantity,assorted_quantity,stage) VALUES(:id_operation,:id_product,:id_accesory_base,:id_accesory,:requested_quantity,:assorted_quantity,:stage)";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_operation", $registro['id_operation']);
                    $stmtI->bindValue(":id_product", $registro['id_product']);
                    $stmtI->bindValue(":id_accesory_base", $registro['id_accesory_base']);
                    $stmtI->bindValue(":id_accesory", $registro['id_accesory']);
                    $stmtI->bindValue(":requested_quantity", $registro['requested_quantity']);
                    $stmtI->bindValue(":assorted_quantity", 0);
                    //$stmtI->bindValue(":assorted_quantity", $registro['assorted_quantity']);
                    $stmtI->bindValue(":stage", $nextStage);
                    $stmtI->execute();                  
                }
            }

            $queryI= "INSERT INTO operation_evidence (id_operation,operation_type,url_photo,geolocation,datetime) VALUES(:id_operation,:operation_type,:url_photo,:geolocation,NOW())";

            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_operation", $id_op);
            $stmtI->bindValue(":operation_type", $currentStage);
            $stmtI->bindValue(":url_photo", $filename);
            $stmtI->bindValue(":geolocation", $coords);
            $stmtI->execute();            

            $queryI= "UPDATE operation_checklist SET assorted_quantity = requested_quantity WHERE id_operation = :id_operation AND stage = :operation_type ";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_operation", $id_op);
            $stmtI->bindValue(":operation_type", $currentStage);
            $stmtI->execute();            


            $queryI= "UPDATE operation_master SET status = :operation_type  WHERE id_operation = :id_operation ";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_operation", $id_op);
            $stmtI->bindValue(":operation_type", $currentStage);
            $stmtI->execute();   
        }            
    }

            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function assign_operator($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Recibir datos básicos
            $vehiculoId     = $_POST['vehiculoId'];
            $fecha          = $_POST['fecha'];
            $operadorId     = $_POST['operadorId'];

            $queryI ="SELECT id_route FROM daily_route WHERE date = :date AND id_vehicle = :id_vehicle";

            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":date", $fecha);
            $stmtI->bindValue(":id_vehicle", $vehiculoId);
            $stmtI->execute();      
            $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);       
            if ($resultado) {
                $db->prepare("UPDATE daily_route SET  id_driver=? WHERE id_route = ?")->execute([$operadorId,$resultado['id_route']]);

                $query ="SELECT id_operation FROM route_stops WHERE id_route = :id_route";
                $stmt = $db->prepare($query);
                $stmt->bindValue(":id_route", $resultado['id_route']);
                $stmt->execute();      
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);       
                if ($resultados) {
                    foreach ($resultados as $registro) {
                        $db->prepare("UPDATE operation_master SET  id_driver=? WHERE id_operation = ?")->execute([$operadorId,$registro['id_operation']]);
                    }
                }
            }            

            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}


function delete_route($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Recibir datos básicos
            $vehiculoId     = $_POST['vehiculoId'];
            $fecha          = $_POST['fecha'];

            $queryI ="SELECT id_route FROM daily_route WHERE date = :date AND id_vehicle = :id_vehicle";

            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":date", $fecha);
            $stmtI->bindValue(":id_vehicle", $vehiculoId);
            $stmtI->execute();      
            $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);       
            if ($resultado) {
                $query ="SELECT id_operation FROM route_stops WHERE id_route = :id_route";
                $stmt = $db->prepare($query);
                $stmt->bindValue(":id_route", $resultado['id_route']);
                $stmt->execute();      
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);       
                if ($resultados) {
                    foreach ($resultados as $registro) {

                        $db->prepare("UPDATE operation_master SET status = 'BODEGA', id_vehicle=0, id_driver=0 WHERE id_operation = ?")->execute([$registro['id_operation']]);

                        $db->prepare("DELETE FROM operation_checklist WHERE id_operation = ? AND stage <> 'SURTIDO'")->execute([$registro['id_operation']]);
                        
                        $db->prepare("UPDATE operation_checklist SET stage = 'SURTIDO', assorted_quantity=0 WHERE id_operation = ?")->execute([$registro['id_operation']]);                    

                        $db->prepare("DELETE FROM operation_evidence WHERE id_operation = ?")->execute([$registro['id_operation']]);                        
                    }
                }
            }            

            $db->prepare("DELETE FROM daily_route WHERE id_route = ?")->execute([$resultado['id_route']]);
            $db->prepare("DELETE FROM route_stops WHERE id_route = ?")->execute([$resultado['id_route']]);


            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function data_monitor($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

$sql = "
SELECT
	daily_route.id_route, 
	daily_route.id_route as Ruta, 
	daily_route.date, 	
	v_operations.NombreOrganizacion, 
    v_operations.OPhone, 
	CONCAT(v_operations.NombreCliente, ' ' , v_operations.ApellidosCliente) as NombreCliente,
    v_operations.CPhone, 
	CONCAT(NombresChofer, ' ',	v_operations.ApellidosChofer ) as Operador, 
	v_operations.vehiculo as Vehiculo, 
	route_stops.id_operation as id_operacion, 		
	operation_evidence.operation_type, 
	operation_evidence.url_photo, 
	operation_evidence.geolocation, 
	operation_evidence.notes, 
	operation_evidence.sign as firma, 
	operation_evidence.datetime as fechahora
FROM
	daily_route
	INNER JOIN
	route_stops
	ON 
		daily_route.id_route = route_stops.id_route
	INNER JOIN
	v_operations
	ON 
		route_stops.id_operation = v_operations.Id_operation
	INNER JOIN
	operation_evidence
	ON 
		v_operations.Id_operation = operation_evidence.id_operation
    WHERE daily_route.date = :date and operation_evidence.operation_type IN ('SURTIDO','CARGA','INSTALACION','PRUEBA FUNCIONAMIENTO','ENTREGA','RECOLECCION','ACONDICIONAMIENTO','ALMACENADO')
		ORDER BY id_route, id_evidence
";

            $stmtI = $db->prepare($sql);
            $stmtI->bindValue(":date", $_POST['date']);
            $stmtI->execute();      
            $SAMPLE_DATA = $stmtI->fetchAll(PDO::FETCH_ASSOC);       
            if ($SAMPLE_DATA) {

                $OPERATION_ORDER = [
                    'SURTIDO','CARGA','INSTALACION','PRUEBA FUNCIONAMIENTO',
                    'ENTREGA','RECOLECCION','ACONDICIONAMIENTO','ALMACENADO'
                ];
                //'DOBLADO'

                // ── Agrupar ───────────────────────────────────────────────────
                $rutas = [];
                foreach ($SAMPLE_DATA as $row) {
                    $r  = $row['Ruta'];
                    $op = $row['id_operacion'];
                    
                    // Extraer cliente y teléfono por fila
                    $client = $row['NombreOrganizacion'] ?: $row['NombreCliente'];
                    $phone  = !empty($row['NombreOrganizacion']) ? $row['OPhone'] : $row['CPhone'];

                    if (!isset($rutas[$r])) {
                        $rutas[$r] = [
                            'ruta' => $r,
                            'operador' => $row['Operador'],
                            'vehiculo' => $row['Vehiculo'],
                            'operaciones' => []
                        ];
                    }
                    
                    if (!isset($rutas[$r]['operaciones'][$op])) {
                        $rutas[$r]['operaciones'][$op] = [
                            'client' => $client, // Guardamos el cliente correspondiente a ESTA operación
                            'phone'  => $phone,  // Guardamos el teléfono correspondiente a ESTA operación
                            'rows'   => []
                        ];
                    }
                    
                    $rutas[$r]['operaciones'][$op]['rows'][] = $row;
                }

                // ── Calcular ──────────────────────────────────────────────────
                $result = [];
                foreach ($rutas as $rutaKey => $rutaData) {
                    $totalOps   = count($OPERATION_ORDER);
                    $totalItems = count($rutaData['operaciones']);
                    $totalPasos = $totalItems * $totalOps;
                    $pasosHechos = 0;

                    $itemsDetail = [];
                    foreach ($rutaData['operaciones'] as $opId => $opData) {
                        $rows      = $opData['rows'];
                        $opClient  = $opData['client']; // Obtenemos el cliente correcto para esta operación
                        $opPhone   = $opData['phone'];  // Obtenemos el teléfono correcto para esta operación

                        $pasosItem = count($rows);
                        $pasosHechos += $pasosItem;
                        $lastOp = end($rows);
                        $pctItem = round(($pasosItem / $totalOps) * 100);

                        // Pasos con detalle completo
                        $stepsDetail = [];
                        $doneTypes = array_map(fn($r) => $r['operation_type'], $rows);
                        
                        foreach ($OPERATION_ORDER as $i => $opType) {
                            $done = in_array($opType, $doneTypes);
                            $stepData = null;
                            if ($done) {
                                foreach ($rows as $r2) {
                                    if ($r2['operation_type'] === $opType) { 
                                        $stepData = $r2; 
                                        break; 
                                    }
                                }
                            }
                            $stepsDetail[] = [
                                'step'           => $i + 1,
                                'operation_type' => $opType,
                                'done'           => $done,
                                'url_photo'      => $done ? ($stepData['url_photo'] ?? '') : '',
                                'geolocation'    => $done ? ($stepData['geolocation'] ?? '') : '',
                                'notas'          => $done ? ($stepData['notas'] ?? '') : '',
                                'firma'          => $done ? ($stepData['firma'] ?? '') : '',
                                'fechahora'      => $done ? ($stepData['fechahora'] ?? '') : '',
                            ];
                        }

                        $itemsDetail[] = [
                            'id_operacion'     => $opId,
                            'client'           => $opClient, // Ahora es único e independiente por cada operación
                            'phone'            => $opPhone,  // Ahora es único e independiente por cada operación
                            'completados'      => $pasosItem,
                            'total'            => $totalOps,
                            'pct'              => $pctItem,
                            'ultima_operacion' => $lastOp['operation_type'],
                            'ultima_hora'      => $lastOp['fechahora'],
                            'completo'         => ($pasosItem >= $totalOps),
                            'steps'            => $stepsDetail,
                        ];
                    }

                    $pctRuta = $totalPasos > 0 ? round(($pasosHechos / $totalPasos) * 100) : 0;

                    $result[] = [
                        'ruta'                 => $rutaKey,
                        'operador'             => $rutaData['operador'],
                        'vehiculo'             => $rutaData['vehiculo'],
                        'pct_global'           => $pctRuta,
                        'total_items'          => $totalItems,
                        'items'                => $itemsDetail,
                        'ultima_actualizacion' => date('Y-m-d H:i:s'),
                    ];
                }

                http_response_code(200);
                echo json_encode([
                    'success'   => true,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'rutas'     => $result,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);            

            }
            else{

                http_response_code(200);
                echo json_encode([
                    'success'   => false,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'rutas'     => '',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);               

            }

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function process_operation($table_name,$db, $method, $id, $data){

    global $IDS;
    switch ($method) {
        case 'POST': 
            $Lead     = $data->{'Lead'};
            process_op($Lead,$db);

            if($data->{'From'} == 'Lead'){
                $queryI ="UPDATE lead SET Status = 'confirmed' WHERE Id = :lead";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":lead", $Lead);
                $stmtI->execute();
            }            

            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function payment_report($table_name,$db, $method, $id, $data){

    global $IDS;
    switch ($method) {
        case 'POST': 

            $fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
            $fechaFin = $_POST['fecha_fin'] ?? date('Y-m-d');

            $usuario = $_POST['usuario'] ?? '';
            $sql = "										SELECT payments.Folio, payments.DateTime as FechadePago, payments.Platform, 
                        payments.Amount, payments.Currency, payments.TransactionId, payments.Estatus,
                    CASE WHEN v_leads.NombreOrganizacion IS NULL THEN CONCAT(v_leads.NombreCliente, ' ', v_leads.ApellidosCliente) 
                        ELSE v_leads.NombreOrganizacion END AS Cliente,
                    CASE WHEN operators.Nombres IS NULL THEN 'Link Pago' ELSE operators.Usuario END AS Usuario,
                    v_leads.Id
                    FROM payments
                    LEFT JOIN operators ON payments.Usuario = operators.Usuario
                    INNER JOIN v_leads ON payments.IdLead = v_leads.Id
                    WHERE DATE(payments.DateTime) BETWEEN :inicio AND :fin";

            if ($usuario !== '') {
                $sql .= " AND usuarios.nombre = :usuario";
            }

            $stmt = $db->prepare($sql);

            // Bind de parámetros con PDO
            $stmt->bindValue(':inicio', $fechaInicio);
            $stmt->bindValue(':fin', $fechaFin);

            if ($usuario !== '') {
                $stmt->bindValue(':usuario', $usuario);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);    


            http_response_code(200);
            echo json_encode(array("data" => $data));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function reassign_route($table_name,$db, $method, $id, $data){

    global $IDS;
    switch ($method) {
        case 'POST': 
            $idOperation = $_POST['idOperation'] ?? null;
            $isNewRoute  = isset($_POST['isNewRoute']) && ($_POST['isNewRoute'] == '1' || $_POST['isNewRoute'] === 1);
            $idRoute     = $_POST['idRoute'] ?? null;
            $date        = $_POST['date'] ?? null;
            $idVehicle   = $_POST['idVehicle'] ?? null;
            $idDriver    = $_POST['idDriver'] ?? null;

            if (!$idOperation) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Falta idOperation."]);
                return;
            }

            try {
                $db->beginTransaction();

                // 1. Determinar o crear la ruta destino
                if ($isNewRoute || empty($idRoute)) {
                    if (!$date || !$idVehicle) {
                        throw new Exception("Fecha y Vehículo son requeridos para crear una nueva ruta.");
                    }
                    $idDriver = !empty($idDriver) ? intval($idDriver) : 0;
                    $idVehicle = intval($idVehicle);

                    // Verificar si ya existe una ruta para ese vehículo y fecha
                    $stmtRuta = $db->prepare("SELECT id_route FROM daily_route WHERE date = :date AND id_vehicle = :id_vehicle");
                    $stmtRuta->bindValue(":date", $date);
                    $stmtRuta->bindValue(":id_vehicle", $idVehicle);
                    $stmtRuta->execute();
                    $rutaExistente = $stmtRuta->fetch(PDO::FETCH_ASSOC);

                    if ($rutaExistente) {
                        $idRoute = $rutaExistente['id_route'];
                        if ($idDriver > 0) {
                            $db->prepare("UPDATE daily_route SET id_driver = ? WHERE id_route = ?")->execute([$idDriver, $idRoute]);
                        }
                    } else {
                        $stmtIns = $db->prepare("INSERT INTO daily_route (date, id_vehicle, id_driver, polyline, status) VALUES (?, ?, ?, '', 1)");
                        $stmtIns->execute([$date, $idVehicle, $idDriver]);
                        $idRoute = $db->lastInsertId();
                    }
                } else {
                    $stmtR = $db->prepare("SELECT id_vehicle, id_driver FROM daily_route WHERE id_route = :idRoute");
                    $stmtR->bindValue(":idRoute", $idRoute);
                    $stmtR->execute();
                    $resultado = $stmtR->fetch(PDO::FETCH_ASSOC);       
                    if (!$resultado) {
                        throw new Exception("La ruta especificada no existe.");
                    }
                    $idDriver  = $resultado['id_driver'];
                    $idVehicle = $resultado['id_vehicle'];
                }

                // 2. Obtener la ruta anterior (para limpieza posterior si queda vacía)
                $stmtOldRoute = $db->prepare("SELECT id_route FROM route_stops WHERE id_operation = ?");
                $stmtOldRoute->execute([$idOperation]);
                $oldRoute = $stmtOldRoute->fetchColumn();

                // 3. Obtener el siguiente orden de visita en la ruta destino
                $stmtMaxOrder = $db->prepare("SELECT COALESCE(MAX(visit_order), 0) + 1 FROM route_stops WHERE id_route = ?");
                $stmtMaxOrder->execute([$idRoute]);
                $nextOrder = $stmtMaxOrder->fetchColumn();

                // 4. Actualizar o insertar route_stops
                $stmtStop = $db->prepare("UPDATE route_stops SET id_route = :idRoute, visit_order = :orden WHERE id_operation = :idOperation");
                $stmtStop->bindValue(":idRoute", $idRoute);
                $stmtStop->bindValue(":orden", $nextOrder);
                $stmtStop->bindValue(":idOperation", $idOperation);
                $stmtStop->execute();

                if ($stmtStop->rowCount() === 0) {
                    $stmtInsStop = $db->prepare("INSERT INTO route_stops (id_route, id_operation, visit_order) VALUES (:idRoute, :idOperation, :orden)");
                    $stmtInsStop->bindValue(":idRoute", $idRoute);
                    $stmtInsStop->bindValue(":idOperation", $idOperation);
                    $stmtInsStop->bindValue(":orden", $nextOrder);
                    $stmtInsStop->execute();
                }

                // 5. Actualizar operation_master
                $queryOp = "UPDATE operation_master SET id_vehicle = :idVehicle, id_driver = :idDriver, orden = :orden WHERE id_operation = :idOperation";
                $stmtOp = $db->prepare($queryOp);
                $stmtOp->bindValue(":idVehicle", $idVehicle);
                $stmtOp->bindValue(":idDriver", $idDriver);
                $stmtOp->bindValue(":orden", $nextOrder);
                $stmtOp->bindValue(":idOperation", $idOperation);
                $stmtOp->execute();                

                // 6. Registrar en el histórico (operation_evidence) la reasignación
                $notaAuditoria = "Operación reasignada a Vehículo #{$idVehicle}, Chofer #{$idDriver}, Ruta #{$idRoute}";
                $stmtLog = $db->prepare("INSERT INTO operation_evidence (id_operation, operation_type, notes, datetime) VALUES (?, 'REASIGNACION', ?, NOW())");
                $stmtLog->execute([$idOperation, $notaAuditoria]);

                // 7. Si la ruta anterior era distinta y quedó sin paradas, eliminarla
                if ($oldRoute && $oldRoute != $idRoute) {
                    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM route_stops WHERE id_route = ?");
                    $stmtCheck->execute([$oldRoute]);
                    if ($stmtCheck->fetchColumn() == 0) {
                        $db->prepare("DELETE FROM daily_route WHERE id_route = ?")->execute([$oldRoute]);
                    }
                }

                $db->commit();
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Registro actualizado.", "id_route" => $idRoute));

            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                echo json_encode(array("status" => "error", "message" => "Error al reasignar: " . $e->getMessage()));
            }

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}
function reschedule($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'PUT': 
                
            
            $DateS_raw = $data->Start;
            $DateE_raw = $data->End;

            $objDateS = new DateTime($DateS_raw);
            $objDateE = new DateTime($DateE_raw);

            $fechaS = $objDateS->format('Ymd'); // 2026-02-04
            $horaS  = $objDateS->format('H:i');   // 18:00

            $fechaE = $objDateE->format('Ymd'); // 2026-02-05
            $horaE  = $objDateE->format('H:i');   // 02:00

            $DayWeek = date('w', strtotime($fechaS));

            switch ($DayWeek) {
                case '0':
                    $DayWeek =' AND Do = 1 ';
                break;
                case '1':
                    $DayWeek =' AND Lu = 1 ';
                break;
                case '2':
                    $DayWeek =' AND Ma = 1 ';
                break;
                case '3':
                    $DayWeek =' AND Mi = 1 ';
                break;
                case '4':
                    $DayWeek =' AND Ju = 1 ';
                break;
                case '5':
                    $DayWeek =' AND Vi = 1 ';
                break;
                case '6':
                    $DayWeek =' AND Sa = 1 ';
                break;
            }                



                $data->Start = str_replace("T", " ", $data->Start);
                $data->End = str_replace("T", " ", $data->End);

                //ACTUALIZAR FECHA
                $queryI ="UPDATE lead SET StartDateTime = :startdatetime, EndDateTime = :enddatetime, DeliveryDateTime = :deliverydatetime, FechaCreacion = now()  WHERE Id = :lead";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":startdatetime", $data->Start);
                $stmtI->bindValue(":enddatetime", $data->End);
                $stmtI->bindValue(":deliverydatetime", $data->Start);
                $stmtI->bindValue(":lead", $data->Lead);
                $stmtI->execute();

                $queryI ="SELECT * FROM lead_detail WHERE IdLead = :lead";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":lead", $data->Lead);
                $stmtI->execute();
                $resultados = $stmtI->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultados as $registro) {
                    //echo 'Product:'. $registro['IdProduct'];
                    $query = "
                        SELECT * FROM v_items_prices_lists
                        WHERE Producto = :idp  AND 
                                    Estatus_price_list = 1 AND
                                    Estatus_price = 1 AND 
                        :date BETWEEN  FechaHoraInicio AND FechaHoraFin $DayWeek  GROUP BY Producto                                 
                    ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':idp', $registro['IdProduct'], PDO::PARAM_INT);
                    $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
                    $stmt->execute();

                    $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);                

                    if ($resultados_p) {
                        foreach ($resultados_p as  $Precio) {
                                $JsonPrice = $Precio['JsonPrice'];
                                $JsonPrice = html_entity_decode($JsonPrice);
                                $ingreso =  $objDateS->format('Y-m-d H:i:00');
                                $salida  = $objDateE->format('Y-m-d H:i:00');
                                // Modificamos directamente el arreglo usando el índice
                                //$resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);
                                $orgprice = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida).' - '.$registro['IdProduct'];
                                $queryI ="UPDATE lead_detail SET OrgPrice = :orgprice , Price = :orgpricep * Quantity WHERE IdLead = :lead AND IdProduct = :idproduct";
                                $stmtI = $db->prepare($queryI);
                                $stmtI->bindValue(":orgprice", $orgprice);
                                $stmtI->bindValue(":orgpricep", $orgprice);
                                $stmtI->bindValue(":lead", $data->Lead);
                                $stmtI->bindValue(":idproduct", $registro['IdProduct']);
                                $stmtI->execute();
                        }
                    }
                }
            http_response_code(200);
            echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}

function swap_order($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $queryI ="UPDATE operation_master SET orden = :orden WHERE Id_operation = :id_operation";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":orden", $data->orden_1);
            $stmtI->bindValue(":id_operation", $data->id_1);
            $stmtI->execute();        
            $queryI ="UPDATE operation_master SET orden = :orden WHERE Id_operation = :id_operation";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":orden", $data->orden_2);
            $stmtI->bindValue(":id_operation", $data->id_2);
            $stmtI->execute();     
            
            operation($table_name,$db, "GET", $id, $data);          
            
            //http_response_code(200);
            //echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}            
            
function save_route($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $Tipo = $_POST['tipo'] ?? null;
            if (!$Tipo) {

                http_response_code(200);
                echo json_encode(array("message" => "No se recibieron datos."));            
                exit;
            }    
            $fecha = $_POST['fecha'];
            $stmtRuta = $db->prepare("INSERT INTO daily_route (date,id_vehicle,id_driver,polyline,status) 
                                        VALUES (?, ?, ?, ?, ?)");

            $stmtDetalle = $db->prepare("INSERT INTO route_stops (id_route, id_operation, visit_order) 
                                            VALUES (?, ?, ?)");    

            $updtOP = $db->prepare("UPDATE operation_master SET id_vehicle = ?, orden = ? WHERE Id_operation = ? ");

            if ($Tipo == 'optima'){

                $json_data = $_POST['todas_las_rutas'] ?? null;
                $rutas = json_decode($json_data, true);

                foreach ($rutas as $item) {
                    $v = $item['vehiculo'];
                    $dr = $item['datosRuta'];
                    $envios = $item['envios'];
                    //echo "Vehiculo: ".$v['id']." Ruta:".$dr['polyline'];
                    $V = str_replace("V", "", $v['id']);
                    $stmtRuta->execute([
                        $fecha, 
                        $V,
                        0, 
                        $dr['polyline'],
                        1
                    ]);
                    
                    $idRutaInsertada = $db->lastInsertId();

                    foreach ($envios as $index => $envio) {
                        //echo "Envio ".$envio['id']."</br>"; 
                        $E = str_replace("E", "", $envio['id']);
                        $orden = $index + 1;
                        $stmtDetalle->execute([
                            $idRutaInsertada,
                            $E,
                            $orden
                        ]);                

                        $updtOP->execute([$V,$orden,$E]);
                    }
                }
            }
            else{

                //echo "Vehiculo: ".$_POST['id_vehiculo']." Ruta:".$_POST['polyline'];
                $puntos_envio = $_POST['puntos_envio'] ?? null;
                $envios = json_decode($puntos_envio, true);        
                $V = str_replace("V", "", $_POST['id_vehiculo']);
                $stmtRuta->execute([
                    $fecha, 
                    $V,
                    0, 
                    $_POST['polyline'],
                    1
                ]);
                
                $idRutaInsertada = $db->lastInsertId();        
                
                foreach ($envios as $index => $envio) {
                    //echo "Envio ".$envio['id']."</br>"; 
                    $E = str_replace("E", "", $envio['id']);
                    $orden = $index + 1;
                    $stmtDetalle->execute([
                        $idRutaInsertada,
                        $E,
                        $orden
                    ]);

                    $updtOP->execute([$V,$orden,$E]);
                }

            }            



            
            http_response_code(200);
            echo json_encode(array("message" => "Ruta registrada."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}            

function acondicionamiento($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 


            // Parámetros de la solicitud
            $groupBy = $_GET['group_by'] ?? 'operation'; // 'operation' o 'product'

            // 1. Obtener operaciones base
            $opsQuery = $db->query("SELECT * FROM v_operations WHERE `status` = 'ACONDICIONAMIENTO' ORDER BY id_operation")->fetchAll();

            $resultado = [];

            foreach ($opsQuery as $op) {
                // Lógica "Evento en Puerta": Por ejemplo, si la fecha de entrega es en los próximos 2 días
                // Ajusta 'FechaEntrega' al nombre real de tu columna
                //$esEventoEnPuerta = (strtotime($op['FechaEntrega']) <= strtotime('+2 days'));
                
                $rows = $db->query("SELECT * FROM v_operation_checklist WHERE `stage` IN ('LAVADO', 'LIMPIEZA', 'REPARACION') AND assorted_quantity > '0' AND id_operation = ".$op['Id_operation'])->fetchAll();

                $rows = array_filter($rows, function($row) {
                    // We keep the row ONLY if it DOES NOT meet your removal criteria
                    return !($row['id_accesory'] > 0 && $row['load_accesory'] == 0);
                });                

                foreach ($rows as $row) {
                    

                    $itemIdForImg = $row['id_product'];
                    if ($row['id_accesory_base']) $itemIdForImg = $row['id_accesory_base'];
                    if ($row['id_accesory']) $itemIdForImg = $row['id_accesory'];

                    $query = "SELECT Image from products_images WHERE Product = ? ORDER BY Orden LIMIT 1";
                    $stmtigm = $db->prepare($query);
                    $stmtigm->execute([$itemIdForImg]);
                    $img = $stmtigm->fetchColumn();
                    $img = str_replace("avif", "jpg", $img);

                    $tipo = $row['id_accesory_base'] ? 'BASE' : ($row['id_accesory'] ? 'ACCESORIO' : 'PRODUCTO');

                    $esEventoEnPuerta = false;
                    if ($tipo == "ACCESORIO" || $tipo == "PRODUCTO") {
                        // Buscamos si este producto tiene eventos confirmados en los próximos 2 días
                        $queryEvento = "SELECT COUNT(*) FROM v_leads_detail 
                                        WHERE Status = 'confirmed' 
                                        AND IdProduct = ? 
                                        AND StartDateTime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)";
                        
                        $stmtEv = $db->prepare($queryEvento);
                        $stmtEv->execute([$itemIdForImg]);
                        $count = $stmtEv->fetchColumn();
                        
                        if ($count > 0) {
                            $esEventoEnPuerta = true;
                        }
                    }
                    //echo $tipo."**";
                    if ($tipo != 'BASE'){
                        $itemData = [
                            'name'      => $row['id_accesory_base'] || $row['id_accesory'] ? ($row['Base'] ?? $row['Accesory']) : $row['Product'],
                            'image'     => CFPUBLICURL.'/'.ID_CLIENTE.'/products_images/thumbnails/'.$img,
                            'stage'     => $row['stage'],
                            'assorted'  => $row['assorted_quantity'],
                            'tipo'      => $tipo,
                            'IdOp'     => $op['Id_operation'],
                            'folio'     => $op['Folio'],
                            'cliente'   => $op['NombreOrganizacion'] ?: ($op['NombreCliente'] . " " . $op['ApellidosCliente']),
                            'urgente'   => $esEventoEnPuerta 
                        ];

                    if ($groupBy === 'product') {
                        // Agrupar por Nombre de Producto
                        $key = $itemData['name'];
                        if (!isset($resultado[$key])) {
                            $resultado[$key] = ['label' => $key, 'items' => []];
                        }
                        $resultado[$key]['items'][] = $itemData;
                    } else {
                        // Agrupar por Operación (Folio)
                        $key = $op['Id_operation'];
                        if (!isset($resultado[$key])) {
                            $resultado[$key] = ['label' => "Folio: #".$op['Folio'] . " - " . $itemData['cliente'], 'items' => []];
                        }
                        $resultado[$key]['items'][] = $itemData;
                    }


                    }
                }
            }
            http_response_code(200);
            echo json_encode(array_values($resultado));            

            
            //http_response_code(200);
            //echo json_encode(array("message" => "Registro actualizado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}   

function cancel_lead($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $Id     = $_POST['Lead'];
            $Type = $_POST['Type'];
            $Cargo = $_POST['Cargo'];      
            $Motivo = $_POST['Motivo'];      
            $Usuario     = $_POST['usuario']  ?? 0;

            //echo $Id;

            $query = "SELECT * FROM account";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);            


            $query = "SELECT * FROM lead WHERE Id = :q";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':q', $Id, PDO::PARAM_INT);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $query = "SELECT SUM(Amount) as Amount FROM payments WHERE IdLead = :q AND Type = 'Pay'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':q', $Id, PDO::PARAM_INT);
            $stmt->execute();
            $payments = $stmt->fetch(PDO::FETCH_ASSOC);            

            $MontoPagado = $payments['Amount'];
            $MontoCargo = 0;



            // 2. Manejar la imagen (opcional)
            $fileName = '';
            $imagePath = null;
            if (isset($_FILES['evidence_img']) && $_FILES['evidence_img']['error'] === UPLOAD_ERR_OK) {

                $ext = pathinfo($_FILES['evidence_img']['name'], PATHINFO_EXTENSION);
                $fileName = "evidencia_" . time() . "_" . uniqid();
                $origen = "../ajax/tmp/evidencias/" . $fileName. "." . $ext;
                //echo $fileName;
                if (move_uploaded_file($_FILES['evidence_img']['tmp_name'], $origen)) {
                    $destinot  = "../ajax/tmp/thumbnail_" . $fileName . ".avif";
                    $destino   = "../ajax/tmp/" . $fileName . ".avif";
                    $destinot2 = "../ajax/tmp/thumbnail_" . $fileName . ".jpg";
                    
                    $normal   =  $fileName . ".avif";
                    $miniatura  = "thumbnail_" . $fileName . ".avif";
                    $miniaturaj = "thumbnail_" . $fileName . ".jpg";

                    $imgOriginal = cargarImagen($origen);

                    if ($imgOriginal) {
                        generarThumbnailAVIF($imgOriginal, $destinot, 150);
                        generarNormalAVIF($imgOriginal, $destino, 1200);
                        generarThumbnailJPG($imgOriginal, $destinot2, 150);

                        imagedestroy($imgOriginal);
                        unlink($origen);

                        $client = ID_CLIENTE;
                        $gallery = 'evidence';

                        $fileName = CFPUBLICURL . "/".$client."/".$gallery."/originals/". $fileName. ".avif";

                        upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);

                    } else {
                        //echo "Formato no soportado.";
                    }
                }                
            }                 



            if ($Cargo==1){
                if ($account['DepositType'] == 'amount'){
                    $MontoCargo = $account['DepositAmount'];
                }
                else{
                    $Total = $lead['SubTotal'] + $lead['TaxAmount'] + $lead['Tip'];
                    $MontoCargo = $Total *  ($account['DepositAmount'] / 100);
                }
            }

            $MontoDev = $MontoPagado  - $MontoCargo;
            $GifCard = '';
            if ($Type == 'GC'){
                //GIFCARD
                $CusType = '';
                $Customer = '';
                if ($lead['Customer'] > 0){
                    $CusType = 'C';
                    $Customer = $lead['Customer'];
                }
                else{
                    $CusType = 'O';
                    $Customer = $lead['Organization'];
                }
                $GifCard = generarGifCarf();
                
                $stmt = $db->prepare("INSERT INTO gifcard (Code,CusType,Customer,Amount,FechaExpiracion,Estatus,FechaCreacion,FechaCambio) VALUES (?,?,?,?, NOW() + INTERVAL 30 DAY ,1,now(),now())");
                $stmt->execute([$GifCard,$CusType,$Customer,$MontoDev]);                
                $Tipo = 'GifCard';
            }
            else{
                //DEVOLUCION
                $Tipo = 'Cash';
            }            

            $MontoDev= $MontoDev * -1;

            $Folio = 0;    
            $stmt = $db->prepare("SELECT MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Dev'");
            $stmt->bindParam(1, $lead['IdBranch']);  // Usa bindParam también aquí
            $stmt->execute();
            $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($Payments){
                $Folio = $Payments['Folio'];
            }
            $Folio += 1;

            $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,evidencia,Usuario) 
                                    VALUES  (     ?,'Dev',   ?,   now(),       ?,     ?,       ?,            ?,    'A',        ?,      ?)";
            $stmtPay = $db->prepare($sqlPay);
            $stmtPay->execute([$Id,$Folio,$Tipo,$MontoDev,$account['Currency'],$GifCard,$fileName,$Usuario]);    

            $stmt = $db->prepare(" UPDATE folios SET Folio = ? WHERE IdBranch = ? AND Type = 'Dev'");
            $stmt->execute([$Folio,$lead['IdBranch']]);

            //$queryI ="UPDATE lead SET Status = 'canceled', Balance = SubTotal + TaxAmout + Tip , FechaCambio = now()  WHERE Id = :id";
            $queryI ="UPDATE lead SET Status = 'canceled', CancellationReason = :motivo,  FechaCambio = now()  WHERE Id = :id";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":motivo", $Motivo);
            $stmtI->bindValue(":id", $Id);
            $stmtI->execute();

            //$stmt = $db->prepare("DELETE FROM lead_discounts WHERE IdLead = ? ");
            //$stmt->execute([$Id]);

            $query = "SELECT * FROM operation_master WHERE id_lead = :q";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':q', $Id, PDO::PARAM_INT);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lead){
                $id_op = $lead['Id_operation'];

                //OPERATION
                $queryI ="DELETE from operation_master WHERE Id_operation = :id_op";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_op", $id_op);
                $stmtI->execute();
                
                $queryI ="DELETE from operation_master WHERE Id_operation = :id_op";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_op", $id_op);
                $stmtI->execute();
                
                $queryI ="DELETE from operation_checklist WHERE id_operation = :id_op";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_op", $id_op);
                $stmtI->execute();
                
                $queryI ="DELETE from operation_evidence WHERE id_operation = :id_op";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":id_op", $id_op);
                $stmtI->execute();
            }



            http_response_code(200);
            echo json_encode(array("message" => "Evento cancelado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}     


function extra_event($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $vehiculoId = $_POST['vehiculo'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $titulo = $_POST['eventTitulo'] ?? '';
            $desc   = $_POST['eventDesc'] ?? '';
            $gasto  = $_POST['eventGasto'] ?? 0;
            
            $nombreFoto = "";

            $fileName = '';
            $imagePath = null;
            if (isset($_FILES['eventFoto']) && $_FILES['eventFoto']['error'] === UPLOAD_ERR_OK) {

                $ext = pathinfo($_FILES['eventFoto']['name'], PATHINFO_EXTENSION);
                $fileName = "event_" . time() . "_" . uniqid();
                $origen = "../ajax/tmp/events/" . $fileName. "." . $ext;
                //echo $fileName;
                if (move_uploaded_file($_FILES['eventFoto']['tmp_name'], $origen)) {
                    $destinot  = "../ajax/tmp/thumbnail_" . $fileName . ".avif";
                    $destino   = "../ajax/tmp/" . $fileName . ".avif";
                    $destinot2 = "../ajax/tmp/thumbnail_" . $fileName . ".jpg";
                    
                    $normal   =  $fileName . ".avif";
                    $miniatura  = "thumbnail_" . $fileName . ".avif";
                    $miniaturaj = "thumbnail_" . $fileName . ".jpg";

                    $imgOriginal = cargarImagen($origen);

                    if ($imgOriginal) {
                        generarThumbnailAVIF($imgOriginal, $destinot, 150);
                        generarNormalAVIF($imgOriginal, $destino, 1200);
                        generarThumbnailJPG($imgOriginal, $destinot2, 150);

                        imagedestroy($imgOriginal);
                        unlink($origen);

                        $client = ID_CLIENTE;
                        $gallery = 'events';

                        //$fileName = CFPUBLICURL . "/".$client."/".$gallery."/originals/". $fileName. ".avif";
                        $fileName =  $fileName. ".avif";

                        upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);

                    } else {
                        //echo "Formato no soportado.";
                    }
                }                
            }   

                $queryI = "SELECT id_route, id_vehicle, id_driver FROM daily_route WHERE date = :date AND id_vehicle = :id_vehicle";

                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":date", $fecha);
                $stmtI->bindValue(":id_vehicle", $vehiculoId);
                $stmtI->execute();      
                $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);       
                if ($resultado) {
                    $id_route   = $resultado['id_route'];
                    $id_vehicle = $resultado['id_vehicle'];
                    $id_driver  = $resultado['id_driver'];
                    $queryI ="INSERT INTO extra_event (id_route,titulo,descripcion,gasto,imagen,fechahora) VALUES(:id_route,:titulo,:descripcion,:gasto,:imagen,now())";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_route", $id_route);
                    $stmtI->bindValue(":titulo", $titulo);
                    $stmtI->bindValue(":descripcion", $desc);
                    $stmtI->bindValue(":gasto", $gasto);
                    $stmtI->bindValue(":imagen", $fileName);
                    $stmtI->execute();                
                    $Id_Event = $db->lastInsertId();

                    $queryI = "SELECT Id as Lead, MAX(orden) + 1 as orden FROM v_operations WHERE id_route = :id_route";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_route", $id_route);                
                    $stmtI->execute();      
                    $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);
                    if ($resultado) {
                        $Lead   = $resultado['Lead'];
                        $orden = $resultado['orden'];
                    }

                    $queryI ="INSERT INTO operation_master (id_lead,id_vehicle,id_driver,orden,id_event) VALUES(:id_lead,:id_vehicle,:id_driver,:orden,:id_event)";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_lead", $Lead);
                    $stmtI->bindValue(":id_vehicle", $id_vehicle);
                    $stmtI->bindValue(":id_driver", $id_driver);
                    $stmtI->bindValue(":orden", $orden);
                    $stmtI->bindValue(":id_event", $Id_Event);
                    $stmtI->execute(); 
                    $id_operation = $db->lastInsertId(); 

                    $queryI ="INSERT INTO route_stops (id_route,id_operation,visit_order) VALUES(:id_route,:id_operation,:orden)";
                    $stmtI = $db->prepare($queryI);
                    $stmtI->bindValue(":id_route", $id_route);
                    $stmtI->bindValue(":id_operation", $id_operation);
                    $stmtI->bindValue(":orden", $orden);
                    $stmtI->execute();                     

                }

            http_response_code(200);
            echo json_encode(array("status" => "success", "message" => "Evento agregado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}     

function extra_event_delete($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $id_event = $_POST['id'];

            $stmt = $db->prepare("SELECT id_route FROM extra_event WHERE id_event = ?");
            $stmt->execute([$id_event]);
            $id_route = $stmt->fetchColumn();            

            $stmt = $db->prepare("SELECT id_operation FROM operation_master WHERE id_event = ?");
            $stmt->execute([$id_event]);
            $id_operation = $stmt->fetchColumn();            

            $stmt = $db->prepare("SELECT imagen FROM extra_event WHERE id_event = ?");
            $stmt->execute([$id_event]);
            $imagen = $stmt->fetchColumn();             

            $queryI ="DELETE FROM extra_event WHERE id_event = :id";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id", $id_event);
            $stmtI->execute();

            $queryI ="DELETE FROM operation_master WHERE id_event = :id";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id", $id_event);
            $stmtI->execute();

            $queryI ="DELETE FROM route_stops WHERE id_route = :id_route AND id_operation = :id_operation";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":id_route", $id_route);
            $stmtI->bindValue(":id_operation", $id_operation);
            $stmtI->execute();  

            delete_Aws(ID_CLIENTE,'events',$imagen);

            http_response_code(200);
            echo json_encode(array("status" => "success", "message" => "Evento borrado."));

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}     

function get_pay_platform($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 

            // Obtenemos la plataforma activa
            $acc = $db->query("SELECT pay_platform FROM account LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            // Obtenemos datos de OPAY
            $opay = $db->query("SELECT Id, SecretKey, PublicKey FROM opay_account LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            // Obtenemos datos de OPAY
            $paypal = $db->query("SELECT Id, SecretKey, Active FROM paypal_account LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            
            // Obtenemos datos de SQUARE
            $square = $db->query("SELECT Id, LocalId, Token FROM square_account LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                'pay_platform' => $acc['pay_platform'] ?? '',
                'opay' => $opay ?: ['Id'=>'', 'SecretKey'=>'', 'PublicKey'=>''],
                'square' => $square ?: ['Id'=>'', 'LocalId'=>'', 'Token'=>''],
                'paypal' => $paypal ?: ['Id'=>'', 'SecretKey'=>'', 'Active'=>'']
            ]);            


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 

function update_pay_platform($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $platform = $_POST['pay_platform'] ?? '';

            if (empty($platform)) {
                echo json_encode(['status' => 'error', 'message' => 'Plataforma no seleccionada']);
                exit;
            }            
            if ($platform != "PAYPAL") {
                $stmt1 = $db->prepare("UPDATE account SET pay_platform = ? LIMIT 1");
                $stmt1->execute([$platform]);
            }


            // 2. Actualizar la tabla específica
            if ($platform === 'OPAY') {
                $stmt2 = $db->prepare("UPDATE opay_account SET Id = ?, SecretKey = ?, PublicKey = ? LIMIT 1");
                $stmt2->execute([
                    $_POST['opay_id'],
                    $_POST['opay_secret'],
                    $_POST['opay_public']
                ]);
            } else if ($platform === 'SQUARE') {
                $stmt2 = $db->prepare("UPDATE square_account SET Id = ?, LocalId = ?, Token = ? LIMIT 1");
                $stmt2->execute([
                    $_POST['square_id'],
                    $_POST['square_local'],
                    $_POST['square_token']
                ]);
            }
            //else if ($platform === 'PAYPAL') {
                if (isset($_POST['paypal_active'])){
                    $active = 1;
                }else{
                    $active = 0;
                }
                $stmt2 = $db->prepare("UPDATE paypal_account SET Id = ?, SecretKey = ?, Active = ? LIMIT 1");
                $stmt2->execute([
                    $_POST['paypal_id'],
                    $_POST['paypal_secret'],
                    $active
                ]);
            //}
            http_response_code(200);
            echo json_encode(['status' => 'success']);

    
    

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 

function get_gif_card($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $q = $_GET['q'] ;
            $Tp = $_GET['Tp'] ;
            $Id = $_GET['Id'] ;

            $queryI = "SELECT Id, Code, Amount FROM gifcard WHERE Code LIKE :q  AND CusType = :tp AND Customer = :id AND Estatus = 1 AND now()  <= FechaExpiracion";
            $stmt = $db->prepare($queryI);
            $searchTerm = "%" . $q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(":tp", $Tp);
            $stmt->bindValue(":id", $Id);
            $stmt->execute();      
            $resultados_p = $stmt->fetch(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 
function asistencias($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fechaFin    = $_GET['fecha_fin'] ?? date('Y-m-d');
    $operatorId  = $_GET['operator_id'] ?? '';

    // Array para mapear los días de la semana en español
    $diasEspañol = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    // Construimos la consulta base
    // Buscamos en el calendario de horarios asignados y cruzamos con lo que realmente se ponchó
    $sql = "SELECT 
                o.Id AS OperadorId,
                CONCAT(o.Nombres, ' ', o.Apellidos) AS NombreCompleto,
                l.Nombre as NombreUbicacion,
                 WEEKDAY(a.HoraEntradaReal) as DiaSemana,
                s.HoraEntrada AS HoraRequerida,
                s.HoraSalida AS HoraRequeridaS,
                a.Fecha,
                a.HoraEntradaReal,
                a.HoraSalidaReal,
                a.EstatusEntrada,
                a.LatitudEntrada,
                a.LongitudEntrada,
                a.FueraAreaEntrada,
                a.LatitudSalida,
                a.LongitudSalida,
                a.FueraAreaSalida,
                IF(a.HoraSalidaReal > s.HoraSalida, TIMESTAMPDIFF(MINUTE, s.HoraSalida, a.HoraSalidaReal) / 60.0, 0) AS HorasExtras
            FROM schedules s
            INNER JOIN operators o ON s.OperatorId = o.Id
            INNER JOIN wharehouses l ON s.LocationId = l.Id
            LEFT JOIN attendance a ON s.OperatorId = a.OperatorId 
                AND a.Fecha BETWEEN :fechaInicio AND :fechaFin
                
            WHERE (o.Estatus = 'A' OR o.Estatus IS NULL)";

    if (!empty($operatorId)) {
        $sql .= " AND o.Id = :operatorId";
    }

    $sql .= " ORDER BY a.Fecha DESC, NombreCompleto ASC";

    $stmt = $db->prepare($sql);
    $params = [':fechaInicio' => $fechaInicio, ':fechaFin' => $fechaFin];
    if (!empty($operatorId)) { $params[':operatorId'] = $operatorId; }

    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 1. Si no hay registros, metemos el mensaje vacío en la estructura HTML del JSON
if (count($resultados) == 0) {
    $htmlOutput = '<tr><td colspan="7" class="text-center text-muted py-4">No hay registros de asistencia en el rango de fechas seleccionado.</td></tr>';
    echo json_encode(['tabla' => $htmlOutput]);
    exit;
}

$htmlOutput = ""; // Variable para acumular las filas HTML

foreach ($resultados as $row) {
    $fechaFormateada = $row['Fecha'] ? date('d/m/Y', strtotime($row['Fecha'])) : 'Sin Registro';
    $diaNombre = $row['Fecha'] ? $diasEspañol[date('w', strtotime($row['Fecha']))] : $diasEspañol[$row['DiaSemana']];
    
    $badgeClass = 'bg-light text-dark';
    $estatusFinal = 'Falta';

    if ($row['HoraEntradaReal']) {
        if ($row['EstatusEntrada'] == 'A tiempo') {
            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
            $estatusFinal = 'A Tiempo';
        } else {
            $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            $estatusFinal = 'Retardo';
        }
    } else {
        $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
    }

    $entrada = $row['HoraEntradaReal'] ? date('g:i a', strtotime($row['HoraEntradaReal'])) : '---';
    $salida = $row['HoraSalidaReal'] ? date('g:i a', strtotime($row['HoraSalidaReal'])) : '---';
    $horarioTeorico = date('g:i a', strtotime($row['HoraRequerida']));
    $horarioTeoricoS = date('g:i a', strtotime($row['HoraRequeridaS']));

    $mapaLink = '---';
    if ($row['LatitudEntrada'] && $row['LongitudEntrada']) {
        $mapaLink = "<a href='https://maps.google.com/?q={$row['LatitudEntrada']},{$row['LongitudEntrada']}' target='_blank' class='btn btn-link btn-sm text-decoration-none p-0'><i class='fa-solid fa-map-pin text-danger'></i> Ver Mapa</a>";
    }

    $mapaLinkS = '---';
    if ($row['LatitudSalida'] && $row['LongitudSalida']) {
        $mapaLinkS = "<a href='https://maps.google.com/?q={$row['LatitudSalida']},{$row['LongitudSalida']}' target='_blank' class='btn btn-link btn-sm text-decoration-none p-0'><i class='fa-solid fa-map-pin text-danger'></i> Ver Mapa</a>";
    }    

    if ($row['FueraAreaEntrada'] == 1)
        $row['FueraAreaEntrada'] = "<i class='fa-solid fa-flag text-danger'></i>";
    else
        $row['FueraAreaEntrada'] = "";

    if ($row['FueraAreaSalida'] == 1)
        $row['FueraAreaSalida'] = "<i class='fa-solid fa-flag text-danger'></i>";
    else
        $row['FueraAreaSalida'] = "";    

    // Concatenamos la fila completa en la variable
    $htmlOutput .= "<tr>
            <td>
                <span class='fw-semibold text-dark d-block'>{$row['NombreCompleto']}</span>
                <small class='text-muted' style='font-size:0.75rem;'>{$row['NombreUbicacion']}</small>
            </td>
            <td>
                <span class='d-block text-dark'>$fechaFormateada</span>
                <small class='text-muted' style='font-size:0.75rem;'>$diaNombre</small>
            </td>
            <td class='text-secondary'>$horarioTeorico / $horarioTeoricoS</td>
            <td class='fw-medium text-dark'>$entrada ". $row['FueraAreaEntrada'] ." <br> $mapaLink </td>
            <td class='fw-medium text-dark'>$salida ".$row['FueraAreaSalida']." <br> $mapaLinkS </td>
            <td><span class='badge px-2.5 py-1.5 rounded-3 $badgeClass' style='font-size:0.8rem; font-weight:500;'>$estatusFinal</span></td>
            <td class='fw-medium text-dark'>".number_format($row['HorasExtras'], 2) ." </td>
          </tr>";
}

// 2. Enviamos el encabezado JSON correcto y el objeto codificado
header('Content-Type: application/json');
echo json_encode(['tabla' => $htmlOutput]);
exit;

    break;
    }
}


function attendance($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 


    $operatorId = intval($_POST['operator_id']);
    $latCliente = floatval($_POST['latitud']);
    $lngCliente = floatval($_POST['longitud']);
    $accion     = $_POST['accion']; // 'entrada' o 'salida'

    $fechaActual = date('Y-m-d');
    $horaActual  = date('H:i:s');
    $diaSemana   = date('w'); // 0 = Domingo, 1 = Lunes, etc.

    switch ($diaSemana) {
        case '0':
            $diaSemana = " s.D = 1 ";
        break;
        case '1':
            $diaSemana = " s.L = 1 ";
        break;
        case '2':
            $diaSemana = " s.M = 1 ";
        break;
        case '3':
            $diaSemana = " s.MI = 1 ";
        break;
        case '4':
            $diaSemana = " s.J = 1 ";
        break;
        case '5':
            $diaSemana = " s.V = 1 ";
        break;
        case '6':
            $diaSemana = " s.S = 1 ";
        break;                       
    }    

    $sqlSch = "SELECT *
               FROM operators
               WHERE Id = ? ";    
    $stmt = $db->prepare($sqlSch);
    $stmt->execute([$operatorId]);
    $Operator = $stmt->fetch(PDO::FETCH_ASSOC);   

    
    $sqlSch = "SELECT s.*, l.Lat, l.Lng , l.RadioMetros 
               FROM schedules s 
               INNER JOIN wharehouses l ON s.LocationId = l.Id 
               WHERE s.OperatorId = ? AND $diaSemana ";    
    $stmt = $db->prepare($sqlSch);
    $stmt->execute([$operatorId]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        echo json_error("No tienes horario asignado para el día de hoy.");
        exit;
    }
    
    $FueraPerimetro=0;
    // 2. Validar Distancia Geográfica (Geofence)
    $distancia = calcularDistancia($latCliente, $lngCliente, $horario['Lat'], $horario['Lng']);
    if ($distancia > $horario['RadioMetros'] ) {
        if ( $Operator['RegistroExterno'] == 1){
            $FueraPerimetro=1;
        }
        else{
            echo json_error("Fuera de rango. Estás a " . round($distancia) . " metros de la ubicación permitida.");
            exit;
        }

    }

    // 3. Procesar Entrada o Salida
    if ($accion == 'entrada') {
        // Verificar si ya registró entrada hoy (Usando prepare para evitar inyección SQL)
        $check = $db->prepare("SELECT Id FROM attendance WHERE OperatorId = ? AND Fecha = ?");
        $check->execute([$operatorId, $fechaActual]);
        
        if ($check->fetch()) {
            echo json_error("Ya registraste tu entrada el día de hoy.");
            exit;
        }

        // Validar tolerancia de entrada
        $horaPermitida = strtotime($horario['HoraEntrada']);
        $toleranciaSec = $horario['ToleranciaMinutos'] * 60;
        $horaMaxIn     = $horaPermitida + $toleranciaSec;
        $horaRealSec   = strtotime($horaActual);

        $estatusEntrada = ($horaRealSec <= $horaMaxIn) ? 'A tiempo' : 'Retardo';

        $ins = $db->prepare("INSERT INTO attendance (OperatorId, Fecha, HoraEntradaReal, LatitudEntrada, LongitudEntrada, EstatusEntrada,FueraAreaEntrada) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($ins->execute([$operatorId, $fechaActual, $horaActual, $latCliente, $lngCliente, $estatusEntrada, $FueraPerimetro])) {
            echo json_success("Entrada registrada con éxito ($estatusEntrada) a las $horaActual.");
        } else {
            echo json_error("Error al registrar entrada.");
        }

    } elseif ($accion == 'salida') {
        // Verificar si ya registró entrada primero
        $check = $db->prepare("SELECT Id, HoraSalidaReal FROM attendance WHERE OperatorId = ? AND Fecha = ?");
        $check->execute([$operatorId, $fechaActual]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            echo json_error("Primero debes registrar una entrada para el día de hoy.");
            exit;
        }
        
        if (!is_null($row['HoraSalidaReal'])) {
            echo json_error("Ya registraste tu salida el día de hoy.");
            exit;
        }

        $upd = $db->prepare("UPDATE attendance SET HoraSalidaReal = ?, LatitudSalida = ?, LongitudSalida = ?, FueraAreaSalida = ? WHERE OperatorId = ? AND Fecha = ?");
        
        if ($upd->execute([$horaActual, $latCliente, $lngCliente, $operatorId, $fechaActual, $FueraPerimetro])) {
            echo json_success("Salida registrada con éxito a las $horaActual.");
        } else {
            echo json_error("Error al registrar salida.");
        }
    }    
    



            break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
} 

function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $radioTierra = 6371000; // Radio de la Tierra en metros

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
         
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $radioTierra * $c; // Devuelve metros
}

function json_error($msg) { return json_encode(['status' => 'error', 'message' => $msg]); }
function json_success($msg) { return json_encode(['status' => 'success', 'message' => $msg]);}

function generar_uuid_v4() {
    // Generamos 16 bytes de datos aleatorios
    $data = random_bytes(16);

    // Configuramos el bit de versión a 4 (0100)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Configuramos los bits de variante (10xx)
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Formateamos en el estándar 8-4-4-4-12
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function generarGifCarf($bloques = 4, $longitudBloque = 4) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $resultado = [];

    for ($i = 0; $i < $bloques; $i++) {
        $segmento = '';
        for ($j = 0; $j < $longitudBloque; $j++) {
            // Usamos random_int para mayor seguridad
            $indice = random_int(0, strlen($caracteres) - 1);
            $segmento .= $caracteres[$indice];
        }
        $resultado[] = $segmento;
    }

    return implode('-', $resultado);
}



function sendmail($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
        try{
            $contenidoBinario = base64_decode($data->archivo_base64);
            $nombreArchivo = $data->nombre_archivo;

            $sql = "SELECT * FROM account";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            $datosConexion = [
                'host'             => $account['ServidorS'],
                'username'         => $account['UsuarioS'],
                'password'         => $account['PasswordS'],
                'port'             => $account['PortS'],
                'encryption'       => PHPMailer::ENCRYPTION_SMTPS,
                'nombre_remitente' => $account['NombreCompania']
            ];
            $archivos = [];

            $resultado = enviarEmail(
                $datosConexion, 
                $data->correo, 
                $data->Subject,
                $data->Body,
                $archivos,
                $contenidoBinario,
                $nombreArchivo
            );            

            http_response_code(200);
            echo json_encode([
                "status" => $resultado['status'],
                "message"=>$resultado['message']." ".$data->correo
            ]);
        } catch (PDOException $e) {
            http_response_code(405);
            echo json_encode([
                "status" => 'fail',
                "message"=>$e->getMessage()
            ]);
        }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
}

function upload_Aws($client, $gallery, $normal, $miniatura, $miniaturaj) {
    $r2_config = [
        'region' => 'auto',
        'endpoint' => CFENDPOINT,
        'credentials' => [
            'key'    => CFKEY,
            'secret' => CFSECRET,
        ],
    ];

    $s3Client = new S3Client($r2_config);
    $bucket_name = 'eventgo';

    try {
        // --- 1. Imagen Normal (AVIF) ---
        $keyNormal = "$client/$gallery/originals/$normal";
        $fileNormal = '../ajax/tmp/' . $normal;

        $s3Client->putObject([
            'Bucket'      => $bucket_name,
            'Key'         => $keyNormal,
            'SourceFile'  => $fileNormal,
            'ContentType' => 'image/avif'
        ]);

        // Validación de existencia en el bucket
        if (!$s3Client->doesObjectExist($bucket_name, $keyNormal)) {
            throw new Exception("Error al verificar $keyNormal en R2/S3.");
        }
        unlink($fileNormal);

        // --- 2. Miniatura AVIF ---
        $miniatura_avif = str_replace("thumbnail_", "", $miniatura);
        $fileMiniAvif = '../ajax/tmp/' . $miniatura_avif;
        rename('../ajax/tmp/' . $miniatura, $fileMiniAvif);

        $keyMiniAvif = "$client/$gallery/thumbnails/$miniatura_avif";

        $s3Client->putObject([
            'Bucket'      => $bucket_name,
            'Key'         => $keyMiniAvif,
            'SourceFile'  => $fileMiniAvif,
            'ContentType' => 'image/avif'
        ]);

        // Validación de existencia en el bucket
        if (!$s3Client->doesObjectExist($bucket_name, $keyMiniAvif)) {
            throw new Exception("Error al verificar $keyMiniAvif en R2/S3.");
        }
        unlink($fileMiniAvif);

        // --- 3. Miniatura JPG ---
        $miniatura_jpg = str_replace("thumbnail_", "", $miniaturaj);
        $fileMiniJpg = '../ajax/tmp/' . $miniatura_jpg;
        rename('../ajax/tmp/' . $miniaturaj, $fileMiniJpg);

        $keyMiniJpg = "$client/$gallery/thumbnails/$miniatura_jpg";

        $s3Client->putObject([
            'Bucket'      => $bucket_name,
            'Key'         => $keyMiniJpg,
            'SourceFile'  => $fileMiniJpg,
            'ContentType' => 'image/jpeg'
        ]);

        // Validación de existencia en el bucket
        if (!$s3Client->doesObjectExist($bucket_name, $keyMiniJpg)) {
            throw new Exception("Error al verificar $keyMiniJpg en R2/S3.");
        }
        unlink($fileMiniJpg);

        return true;

    } catch (Aws\S3\Exception\S3Exception $e) {
        error_log("Error de AWS/R2 al subir: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Error de validación: " . $e->getMessage());
        return false;
    }
}


function delete_Aws($client,$gallery,$file){
    $r2_config = [
        'region' => 'auto',
        'endpoint' => CFENDPOINT,
        'credentials' => [
            'key'    => CFKEY,
            'secret' => CFSECRET,
        ],
    ];
    $s3Client    = new S3Client($r2_config);
    try{
        $s3Client->deleteObject([
            'Bucket' => 'eventgo',
            'Key'    => "$client/$gallery/originals/$file"
        ]);

        $s3Client->deleteObject([
            'Bucket' => 'eventgo',
            'Key'    => "$client/$gallery/thumbnails/$file"
        ]);        

        $file =  str_replace("avif", "jpg", $file);

        $s3Client->deleteObject([
            'Bucket' => 'eventgo',
            'Key'    => "$client/$gallery/thumbnails/$file"
        ]);               

        return true;
    } catch (Aws\S3\Exception\S3Exception $e) {
        error_log("Error al subir a S3: " . $e->getMessage());
        return $e->getMessage();
    }  
}
?>