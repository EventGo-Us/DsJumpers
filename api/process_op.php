<?php
function process_op($Lead,$db){
        $queryI ="SELECT Id_operation FROM operation_master WHERE id_lead = :lead";
        $stmtI = $db->prepare($queryI);
        $stmtI->bindValue(":lead", $Lead);
        $stmtI->execute();      
        $operation = $stmtI->fetch(PDO::FETCH_ASSOC);

        if (!$operation){

            $queryI ="UPDATE lead SET Status = 'confirmed' WHERE Id = :lead";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":lead", $Lead);
            $stmtI->execute();        

            $queryI ="SELECT Venue, Surface FROM lead WHERE Id = :lead";
            $stmtI = $db->prepare($queryI);
            $stmtI->bindValue(":lead", $Lead);
            $stmtI->execute();      
            $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                $Surface = $resultado['Surface'];
                $IdVenue = $resultado['Venue'];

                $queryI ="SELECT Direccion,Ciudad,CP,Estado,Pais,Lat,Lng FROM venues WHERE Id = :venue";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":venue", $IdVenue);
                $stmtI->execute();      
                $resultado = $stmtI->fetch(PDO::FETCH_ASSOC);

                if ($resultado) {
                    if ($resultado['Lat'] == null OR $resultado['Lat'] == ''){
                        $miDireccion = $resultado['Direccion']." ".$resultado['Ciudad']." ".$resultado['CP']." ".$resultado['Estado']." ".$resultado['Pais'];
                        
                        $sql = "
                            SELECT                       
                                google_api
                            FROM
                                account
                            Limit 1                    
                        ";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);   

                        $miDireccion = obtenerCoordenadas($miDireccion, $resultado['google_api']);
                        if (isset($miDireccion['error'])) {
                            echo "Hubo un problema: " . $miDireccion['error'];
                        } else {
                            $query = "UPDATE venues SET Lat = :lat, Lng = :lng WHERE Id = :venue";
                            $stmt = $db->prepare($query);
                            $stmt->bindValue(":lat", $miDireccion['lat']);
                            $stmt->bindValue(":lng", $miDireccion['lng']);
                            $stmt->bindValue(":venue", $IdVenue);
                            $stmt->execute();
                        }                    
                    }
                }

                $query = "INSERT INTO operation_master(id_lead,status,id_vehicle,id_driver,orden)  VALUES(:idlead,'BODEGA',0,0,0)";
                $stmt = $db->prepare($query);
                $stmt->bindValue(":idlead", $Lead);
                if ($stmt->execute()) {
                    $lastInsertId = $db->lastInsertId();
                }

                $queryI ="SELECT IdProduct,IdProductRel,Quantity FROM lead_detail WHERE IdLead = :lead";
                $stmtI = $db->prepare($queryI);
                $stmtI->bindValue(":lead", $Lead);
                $stmtI->execute();      
                $resultados = $stmtI->fetchAll(PDO::FETCH_ASSOC);            

                if ($resultados) {
                    
                    $query = "INSERT INTO operation_checklist(id_operation,id_product,id_accesory_base,id_accesory,requested_quantity,assorted_quantity,stage)  
                                                        VALUES(:id_operation,:id_product,:id_accesory_base,:id_accesory,:requested_quantity,:assorted_quantity,:stage)";
                    $stmt = $db->prepare($query);
                    foreach ($resultados as $registro) {
                        
                        if ($registro['IdProductRel'] == 0){
                            // PRODUCTO
                            $IdProduct= $registro['IdProduct'];
                            $stmt->bindValue(":id_operation", $lastInsertId);
                            $stmt->bindValue(":id_product", $registro['IdProduct']);
                            $stmt->bindValue(":id_accesory_base", 0);
                            $stmt->bindValue(":id_accesory", 0);
                            $stmt->bindValue(":requested_quantity", $registro['Quantity']);
                            $stmt->bindValue(":assorted_quantity", 0);
                            $stmt->bindValue(":stage", 'SURTIDO');
                            $stmt->execute();
                            
                            //INSERTAR los packing_list de cada producto en id_accesory_base
                            $queryI ="SELECT Producto_rpl, Quantity_pl FROM packing_list WHERE Producto_pl = :producto_pl AND Surface = :surface";
                            $stmtI = $db->prepare($queryI);
                            $stmtI->bindValue(":producto_pl", $registro['IdProduct']);
                            $stmtI->bindValue(":surface", $Surface);
                            $stmtI->execute();      
                            $packing_list = $stmtI->fetchAll(PDO::FETCH_ASSOC);
                            if ($packing_list) {
                                foreach ($packing_list as $element_pl) {
                                    $stmt->bindValue(":id_operation", $lastInsertId);
                                    $stmt->bindValue(":id_product", $registro['IdProduct']);
                                    $stmt->bindValue(":id_accesory_base", $element_pl['Producto_rpl']);
                                    $stmt->bindValue(":id_accesory", 0);
                                    $stmt->bindValue(":requested_quantity", $element_pl['Quantity_pl']);
                                    $stmt->bindValue(":assorted_quantity", 0);
                                    $stmt->bindValue(":stage", 'SURTIDO');
                                    $stmt->execute();
                                }
                            }
                        }
                        else{
                            if (@$IdProduct == $registro['IdProductRel'] ){
                                //INSERTAR los accesorios de cada producto en id_accesory
                                $stmt->bindValue(":id_operation", $lastInsertId);
                                $stmt->bindValue(":id_product", $IdProduct);
                                $stmt->bindValue(":id_accesory_base", 0);
                                $stmt->bindValue(":id_accesory", $registro['IdProduct']);
                                $stmt->bindValue(":requested_quantity", $registro['Quantity']);
                                $stmt->bindValue(":assorted_quantity", 0);
                                $stmt->bindValue(":stage", 'SURTIDO');
                                $stmt->execute();
                            }
                        }
                    }
                }
            }
        }
}

function obtenerCoordenadas($direccion, $apiKey) {
    // 1. Codificar la dirección para la URL
    $direccionCodificada = urlencode($direccion);
    
    // 2. Construir la URL de la API
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$direccionCodificada}&key={$apiKey}";

    // 3. Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    
    // Manejo de errores de conexión
    if(curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }
    
    curl_close($ch);

    // 4. Decodificar el JSON resultante
    $data = json_decode($response, true);

    // 5. Verificar el estado de la respuesta
    if ($data['status'] === 'OK') {
        $lat = $data['results'][0]['geometry']['location']['lat'];
        $lng = $data['results'][0]['geometry']['location']['lng'];
        
        return [
            'lat' => $lat,
            'lng' => $lng,
            'direccion_formateada' => $data['results'][0]['formatted_address']
        ];
    } else {
        return ['error' => 'Error de la API: ' . $data['status']];
    }
}

?>