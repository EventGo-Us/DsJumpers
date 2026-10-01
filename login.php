<?php
ob_start();
session_start(); 
include_once 'config/config.php'; 
include_once 'config/database.php'; 
require 'idioma.php'; 
header('Content-Type: application/json');
$company = $_POST['company'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => $texts['error_vacios']]);
    exit;
}

$database = new DatabaseLogin();
$db = $database->getConnection();

    $stmt = $db->prepare("SELECT Id, nombre_db, estatus, fecha_termino FROM data_bases WHERE company = ?");
    $stmt->execute([$company]);
    $SDB = $stmt->fetch(PDO::FETCH_ASSOC); 
    if ($SDB){

        $fecha_hoy = date('Y-m-d');
        $fecha_termino = $SDB['fecha_termino'];
        if ($fecha_hoy > $fecha_termino) {
            echo json_encode(['status' => 'error', 'message' =>"La cuenta no está vigente. Su acceso expiró el " . $fecha_termino ]);        
            exit(); // Detenemos la ejecución
        }    

        if ($SDB['estatus'] == 'Activo'){
            $_SESSION['nombre_db']    = $SDB['nombre_db'];
            $_SESSION['id_cliente']   = $SDB['Id'];

            
            $loginUrl = URL_BASE."/api/user_login";            

            $data = [
                'username'          => $usuario,
                'password'          => $password,
                'data_base'         => $SDB['nombre_db'],
                'id_cliente'        => $SDB['Id']
            ];

            $payload = json_encode($data);
            $ch = curl_init($loginUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
            curl_setopt($ch, CURLOPT_POST, true);           // Define el método POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Adjunta los datos JSON
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                echo 'Error en cURL: ' . curl_error($ch);
            } else {
                if ($httpCode === 200) {
                    
                    $result = json_decode($response, true);
                    //die(print_r($result));
                    //$jwtToken = $result['jwt'] ?? null;
                    $idusuario = $result['Id'];
                    $rolusuario = $result['Tipo'];
                    $nombreusuario  = $result['Nombre'];
                    $ZonaHoraria  = $result['ZonaHoraria'];
                    $NombreCompania  = $result['NombreCompania'];


                } else {
                    $errorResponse = json_decode($response, true);
                    $errorMessage = $errorResponse['message'] ?? 'Error desconocido.';
                    echo json_encode(['status' => 'error', 'message' => "Fallo el inicio de sesión: " . $errorMessage]);
                    die();
                }
            }

            curl_close($ch);            



            $loginUrl = URL_BASE."/api/login";

            // 1. Preparar los datos
            $data = [
                'username'          => $usuario,
                'password'          => $password,
                'usuario_nombre'    => $nombreusuario,
                'role_id'           => $rolusuario,
                'data_base'         => $SDB['nombre_db'],
                'usuario_id'        => $idusuario,
                'id_cliente'        => $SDB['Id']
            ];

            // 2. Convertir los datos a formato JSON
            $payload = json_encode($data);

            // 3. Inicializar cURL
            $ch = curl_init($loginUrl);

            // 4. Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
            curl_setopt($ch, CURLOPT_POST, true);           // Define el método POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Adjunta los datos JSON
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);

            // 5. Ejecutar la petición
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // 6. Manejo de errores de cURL
            if (curl_errno($ch)) {
                echo 'Error en cURL: ' . curl_error($ch);
            } else {
                // 7. Procesar la respuesta
                if ($httpCode === 200) {
                    $result = json_decode($response, true);
                    $jwtToken = $result['jwt'] ?? null;
                    
                    $_SESSION['usuario_id']     = $idusuario;
                    $_SESSION['user']           = $usuario;
                    $_SESSION['database_id']    = $SDB['Id'];
                    $_SESSION['usuario_nombre'] = $nombreusuario;
                    $_SESSION['role_id']        = $rolusuario;
                    $_SESSION['company']        = $company;
                    $_SESSION['tzone']          = $ZonaHoraria;
                    $_SESSION['NombreCompania'] = $NombreCompania;
                    $_SESSION['apiToken']       = $jwtToken;
                    $_SESSION['saved_company']  = $company;
                    /*
                    // Detectar entorno
                    $is_local = ($_SERVER['SERVER_NAME'] == 'localhost');
                    $cookie_path = $is_local ? '/DsJumpers/' : '/';

                    // Guardar la cookie con parámetros explícitos
                    setcookie("saved_company", $company, [
                        'expires'  => time() + 86400, // 24 horas
                        'path'     => $cookie_path,
                        'domain'   => '',             // Dominio por defecto
                        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // True solo si hay HTTPS
                        'httponly' => true,
                        'samesite' => 'Lax'           // Permite enviar la cookie tras redirecciones
                    ]);
                    */
                    // ... El resto de tu código para iniciar la $_SESSION normal ...                    

                    
                    //echo "Login exitoso. Token guardado en sesión.";
                    echo json_encode(['status' => 'success', 'jwtToken' => $jwtToken, 'rolusuario'=>$rolusuario]);
                } else {
                    $errorResponse = json_decode($response, true);
                    $errorMessage = $errorResponse['message'] ?? 'Error desconocido.';
                    echo json_encode(['status' => 'error', 'message' => "Fallo el inicio de sesión: " . $errorMessage]);
                    
                }
            }
            // 8. Cerrar conexión
            curl_close($ch);
        }
        else{
            echo json_encode(['status' => 'error', 'message' => 'La cuenta no esta activa']);
        }    


    }
    else{
        echo json_encode(['status' => 'error', 'message' => 'No existe compañia registrada.']);
    }

/*
//$stmt = $db->prepare("SELECT id, user, password, database_id, nombre, role_id FROM usuarios WHERE user = ? ");
//$stmt->execute([$usuario]);
//$user = $stmt->fetch(PDO::FETCH_ASSOC);



if ($user && password_verify($password, $user['password'])) {
    $_SESSION['usuario_id']     = $user['id'];
    $_SESSION['user']           = $user['user'];
    $_SESSION['database_id'] = $user['database_id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['role_id']        = $user['role_id'];

    $stmt = $db->prepare("SELECT Id, nombre_db, estatus, fecha_termino FROM data_bases WHERE Id = ?");
    $stmt->execute([$user['database_id']]);
    $SDB = $stmt->fetch(PDO::FETCH_ASSOC); 
    if ($SDB){

    }
    else{
        echo json_encode(['status' => 'error', 'message' => 'No existe cuenta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => $texts['error_login']]);
}
*/
?>