<?php
    ob_start();
    session_start(); 
    include_once '../config/config.php';     
    include_once '../config/database.php';    
    $database = new Database();
    $conexion = $database->getConnection();

    $action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
    $Respuesta = '¡No existe coincidencia!';
    if($action == 'ajax'){
        $Idioma = $_SESSION['Idioma'];
        $valor    = strip_tags($_REQUEST['valor']);
        $campo    = strip_tags($_REQUEST['campo']);
        $receptor = strip_tags($_REQUEST['receptor']);
        $tabla    = strip_tags($_REQUEST['tabla']);
        $valor2    = strip_tags($_REQUEST['valor2']);

        $sql = "SELECT TablaDts2, CampoFiltro2, CampoValor2, CampoDescripcion2, Filtro2 
                FROM modal_add 
                WHERE Tabla = :tabla AND Campo = :campo";
        //echo $sql;
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':tabla' => $tabla,
            ':campo' => $campo
        ]);

        $myArray = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $TablaDts         = $row['TablaDts2'];
            $CampoFiltro      = $row['CampoFiltro2'];
            $CampoValor       = $row['CampoValor2'];
            $CampoDescripcion = $row['CampoDescripcion2'];
            $Filtro           = $row['Filtro2'];
/*
            $sql2 = "SELECT $CampoValor AS Id, $CampoDescripcion AS Descripcion 
                    FROM $TablaDts 
                    WHERE $Filtro $CampoFiltro = :valor AND (Idioma ='$Idioma' OR Idioma ='')
                    ORDER BY $CampoDescripcion";
            //echo $sql2;
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute([':valor' => $valor]);
*/

            $checkColumn = $conexion->prepare("
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = :tabla 
                AND COLUMN_NAME = 'Idioma'
            ");
            $checkColumn->execute([':tabla' => $TablaDts]);
            $columnaExiste = $checkColumn->fetchColumn() > 0;
            $condicionIdioma = "";
            
            if ($TablaDts != "templates") {
                // 2. Construir el fragmento de la consulta de forma dinámica
                if ($columnaExiste) {
                    $condicionIdioma = "AND (Idioma = '$Idioma' OR Idioma = '')";
                }
            }
            // 3. Tu consulta final modificada
            $sql2 = "SELECT $CampoValor AS Id, $CampoDescripcion AS Descripcion 
                    FROM $TablaDts 
                    WHERE $Filtro $CampoFiltro = :valor $condicionIdioma
                    ORDER BY $CampoDescripcion";

            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute([':valor' => $valor]);

            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $myArray[] = [
                    'Id'          => $row2['Id'],
                    'Descripcion' => $row2['Descripcion']
                ];
            }
        }

    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([ "Registros"  => $myArray,"Valor" => $valor2]);
    exit();    
    //echo $Respuesta;
?>