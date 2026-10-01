<?php
// ----------------------------------------------------
// 1. INCLUSIONES Y DEPENDENCIAS
// ----------------------------------------------------

// Incluye el autoloader de Composer (para JWT)
require '../vendor/autoload.php';

//include_once '../config/config.php'; 

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key     = $_ENV['SECRET_KEY'];
$url_base       = $_ENV['URL_BASE'];
$google_api_key = $_ENV['GOOGLE_API_KEY'];

$host       = $_ENV['host'];
$db_name    = $_ENV['db_name'];
$username   = $_ENV['username'];
$password   = $_ENV['password'];
$port       = $_ENV['port'];

$CFendpoint     = $_ENV['endpoint'];
$CFkey          = $_ENV['key'];
$CFsecret       = $_ENV['secret'];
$CFpublicurl    = $_ENV['publicurl'];


define('SECRET_KEY', $secret_key);
define('URL_BASE', $url_base);
define('GOOGLE_API_KEY', $google_api_key);

define('HOST', $host);
define('USERNAME', $username);
define('PASSWORD', $password);
define('PORT',$port);

define('CFENDPOINT',$CFendpoint);
define('CFKEY',$CFkey);
define('CFSECRET',$CFsecret);
define('CFPUBLICURL',$CFpublicurl);

date_default_timezone_set('America/Mexico_City');

// Incluye la clase de conexión a la BD
include_once '../config/database.php'; 

// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye las funciones de manejo (simuladas)
include_once 'functions.php'; 
include_once 'process_op.php'; 
include_once 'handlers.php'; 


// ----------------------------------------------------
// 2. CONFIGURACIÓN DE ENCABEZADOS (HEADERS)
// ----------------------------------------------------

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Expose-Headers: Authorization-Update");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Incluir OPTIONS para CORS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, ID2,ID3,ID4");

// Manejo de solicitudes OPTIONS (preflight requests de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ----------------------------------------------------
// 3. INICIALIZACIÓN Y LECTURA DE LA SOLICITUD
// ----------------------------------------------------


$method = $_SERVER['REQUEST_METHOD'];

// Obtener y limpiar los segmentos de la URI dinámicamente (funciona en local y producción)
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#/api(?:/(.*))?$#', $request_path, $matches)) {
    $path = $matches[1] ?? '';
} else {
    $path = '';
}
$path = trim($path, '/');
$segments = $path !== '' ? explode('/', $path) : [];

$resource = $segments[0] ?? null; // Ej: 'login', 'clientes', 'productos'
$id = $segments[1] ?? null;       // Ej: ID si existe

if ($resource != 'process_stage_change' )
    $data = json_decode(file_get_contents("php://input"));
else $data= '';
// ----------------------------------------------------
// 4. ENRUTAMIENTO Y AUTENTICACIÓN
// ----------------------------------------------------

// --- A. LOGIN (No requiere Token) ---
if ($resource === 'login' && $method === 'POST') {
    handle_login_request( $data); // Llama a la función de login en Handlers.php
    exit();
} 

if ($resource === 'account_login' && $method === 'POST') {
    //handle_login_request( $data); // Llama a la función de login en Handlers.php

    define('DB_NAME', $data->data_base);  

    $database = new Database();
    $db = $database->getConnection();    

    $stmt = $db->prepare("SELECT * FROM account LIMIT 1");
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account ) {
            http_response_code(200);
            echo json_encode(array(
                "Id" => $account['Id'],
                "WebSite" => $account['WebSite'],
                "Logo" => $account['Logo']
            ));    
    }  
    else{
        http_response_code(401);
        echo json_encode(array("message" => "Credenciales inválidas."));
    }  
    exit();
} 

if ($resource === 'user_login' && $method === 'POST') {
    //handle_login_request( $data); // Llama a la función de login en Handlers.php

    define('DB_NAME', $data->data_base);  

    $database = new Database();
    $db = $database->getConnection();    

    $stmt = $db->prepare("SELECT NombreCompania, ZonaHoraria FROM account ");
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);    

    $stmt = $db->prepare("SELECT * FROM operators WHERE Usuario = ? AND Estatus = 'A' ");
    $stmt->execute([$data->username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['Password'])) {
            http_response_code(200);
            echo json_encode(array(
                "message" => "Inicio de sesión exitoso.",
                "Id" => $user['Id'],
                "Nombre" => $user['Nombres']." " .$user['Apellidos'],
                "Tipo" => $user['Tipo'],
                "ZonaHoraria" => $account['ZonaHoraria'],
                "NombreCompania" => $account['NombreCompania']
            ));    
    }  
    else{
        http_response_code(401);
        echo json_encode(array("message" => "Credenciales inválidas."));
    }  
    exit();
} 


// --- B. MIDDLEWARE DE AUTENTICACIÓN (Para todas las demás rutas) ---
if ($resource !== 'login') {
    //$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $authHeader = $headers['Authorization'] 
    ?? $_SERVER['HTTP_AUTHORIZATION'] 
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
    ?? '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["message" => "$authHeader Acceso denegado. Token no proporcionado o formato incorrecto."]);
        exit();
    }

    $jwt = $matches[1];
    $decoded_token = null;

    try {
        // La validación de la firma y la expiración ocurren aquí
        $decoded_token = JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'));
        // El token es válido. La información del usuario está en $decoded_token        
        // Opcional: Puedes adjuntar los datos del usuario del token a la solicitud si lo necesitas
        // $user_id = $decoded_token->data->id;
        $db_name = $decoded_token->data->base_datos;
        define('ID_CLIENTE', $decoded_token->data->id_cliente);

        $now = time();
        // Si faltan menos de 600 segundos (10 minutos) para que expire
        //echo $decoded_token->exp - $now;
        if (($decoded_token->exp - $now) < 600) {
            // Generar un nuevo token con el mismo payload
            
            //die("NEW:". time() + 3600);
            $decoded_token->exp = time() + 3600;
            
            $nuevoToken = JWT::encode((array)$decoded_token, SECRET_KEY, 'HS256');
            // Enviar el nuevo token en un header para que el cliente lo actualice
            header("Authorization-Update: " . $nuevoToken);
        }        

    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token expirado."]);
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token inválido: " . $e->getMessage()]);
        exit();
    }
    


}

define('DB_NAME', $db_name);  
//define('TZONE', 'America/Mexico_City');//SET TIME ZONE

$database = new Database();
$db = $database->getConnection();




// --- C. ENRUTAMIENTO CRUD PROTEGIDO ---
$IDS='';
unset($IDS);
if (isset($_SERVER['HTTP_ID2']))
    $IDS[] = $_SERVER['HTTP_ID2'] ?? '';
if (isset($_SERVER['HTTP_ID3']))
    $IDS[] = $_SERVER['HTTP_ID3'] ?? '';
if (isset($_SERVER['HTTP_ID4']))
    $IDS[] = $_SERVER['HTTP_ID4'] ?? '';
if (isset($_SERVER['HTTP_ID5']))
    $IDS[] = $_SERVER['HTTP_ID5'] ?? '';
//print_r($IDS);
//die();
switch ($resource) {
    case'image_actions':

        header('Content-Type: application/json');

        $imageTable = $_POST['table'] ?? 'products_images';
        if (!in_array($imageTable, ['products_images', 'products_images_sale'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tabla de imágenes no válida']);
            exit;
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {

            case 'delete':
                $id = intval($_POST['id'] ?? 0);

                $stmt = $db->prepare("SELECT Image FROM $imageTable WHERE IId = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $imageFile = $row['Image'];
                    //$baseName  = str_replace('.avif', '', $imageFile);

                    // Borrar archivos físicos
                    //@unlink("tmp/" . $imageFile);
                    //@unlink("tmp/thumbnail_" . $baseName . ".avif");
                    //@unlink("tmp/thumbnail_" . $baseName . ".jpg");
                    @delete_Aws(ID_CLIENTE, $imageTable, $imageFile);

                    $stmt = $db->prepare("DELETE FROM $imageTable WHERE IId = ?");
                    $stmt->execute([$id]);

                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No encontrado']);
                }
                break;

            case 'reorder':
                $order = json_decode($_POST['order'] ?? '[]', true); // array de IDs en el nuevo orden

                if (!is_array($order) || empty($order)) {
                    echo json_encode(['success' => false, 'message' => 'Orden inválido']);
                    break;
                }

                //$db->beginTransaction();
                foreach ($order as $index => $id) {
                    $stmt = $db->prepare("UPDATE $imageTable SET Orden = ?, FechaCambio = NOW() WHERE IId = ?");
                    $stmt->execute([$index + 1, intval($id)]);
                }
                //$db->commit();

                echo json_encode(['success' => true]);
                break;

            case 'list':
                $product_id = intval($_POST['product_id'] ?? 0);

                $stmt = $db->prepare("SELECT IId, Orden, Image FROM $imageTable WHERE Product = ? ORDER BY Orden ASC");
                $stmt->execute([$product_id]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'data' => $rows]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
        exit;        


    break;
    case 'save_route':
        save_route($resource,$db, $method, $id, $data);
        break;

    case 'swap_order':
        swap_order($resource,$db, $method, $id, $data);
        break;

    case 'reschedule':
        reschedule($resource,$db, $method, $id, $data);
        break;

    case 'payment_report':
        payment_report($resource,$db, $method, $id, $data);
        break;

    case 'process_pay':
        process_pay($resource,$db, $method, $id, $data);
        break;
    case 'reassign_route';
        reassign_route($resource,$db, $method, $id, $data);
    break;      
    case 'process_operation';
        process_operation($resource,$db, $method, $id, $data);
    break;  
    case 'data_monitor':
        data_monitor($resource,$db, $method, $id, $data);
    break;  

    case 'assign_operator':
        assign_operator($resource,$db, $method, $id, $data);
    break;        

    case 'delete_route':
        delete_route($resource,$db, $method, $id, $data);
    break;        
    case 'process_stage_change_em':
        process_stage_change_em($resource,$db, $method, $id, $data);
    break;    
    case 'process_stage_change':
        process_stage_change($resource,$db, $method, $id, $data);
    break;
    case 'inventory_stock':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;            
    case 'discounts':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;    
    case 'clientes':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'operators':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'referals':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;      
    case 'schedules':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;          
    case 'vehicles':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;                  
    case 'customers':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;

    case 'sale_customers':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        

    case 'sale_customer_addresses':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;                
    case 'categories':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'scategories':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;         
    case 'customer_type':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'wharehouses':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'gifcard':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_categories':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_images':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_images_sale':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'products_videos':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'products_files':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'packing_list':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'related_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;                
    case 'distance_charges':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_zip_code':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_distance':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_states':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'distance_charges_totals':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'account':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'venues':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'organizations':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'surfaces':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'upselling_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;    
    case 'relationship_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'cost_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'item_prices':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_item_price':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'customer_addresses':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'document_center':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        

    case 'price_lists':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;

    case 'detail_price_lists':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;

    case 'get_price':
        get_price($resource,$db, $method, $id, $data);
        break;
    case 'get_json_price':
        get_json_price($resource,$db, $method, $id, $data);
        break;
    case 'clone_record':
        clone_record($resource,$db, $method, $id, $data);
        break;
    case 'copy_records':
        copy_records($resource,$db, $method, $id, $data);
        break;  
    case 'orden':
        orden($resource,$db, $method, $id, $data);
        break;
    case 'ajustar_precio':
        ajustar_precio($resource,$db, $method, $id, $data);
        break;                        
    case 'get_products_categories':
        get_products_categories($resource,$db, $method, $id, $data);
        break; 
    case 'get_products_search':
        get_products_search($resource,$db, $method, $id, $data);
        break;         
    case 'get_related_products':
        get_related_products($resource,$db, $method, $id, $data);
        break;
    case 'get_organization':
        get_organization($resource,$db, $method, $id, $data);
        break;
    case 'save_organization':
        save_organization($resource,$db, $method, $id, $data);
        break;        
    case 'get_customers':
        get_customers($resource,$db, $method, $id, $data);
        break;
    case 'save_customer':
        save_customer($resource,$db, $method, $id, $data);
        break;        
    case 'get_customers_cell':
        get_customers_cell($resource,$db, $method, $id, $data);
        break;
        

    case 'save_venue':
        save_venue($resource,$db, $method, $id, $data);
        break;                

    case 'get_referals':
        get_referals($resource,$db, $method, $id, $data);
        break;        
    case 'get_venues':
        get_venues($resource,$db, $method, $id, $data);
        break;
    case 'get_venue':
        get_venue($resource,$db, $method, $id, $data);
        break;        
    case 'distance_charge':
        distance_charge($resource,$db, $method, $id, $data);
        break;
    case 'lead_auto_save':
        lead_auto_save($resource,$db, $method, $id, $data);
        break;
    case 'template':
        get_template($resource,$db, $method, $id, $data);
        break;
    case 'leads':
        leads($resource,$db, $method, $id, $data);
        break;

    case 'sales':
        sales($resource,$db, $method, $id, $data);
        break;        

    case 'comments_admin':
        comments_admin($resource,$db, $method, $id, $data);
        break;                

    case 'comments_admin_update':
        comments_admin_update($resource,$db, $method, $id, $data);
        break;                        

        case 'pending_payments':
        pending_payments($resource,$db, $method, $id, $data);
        break;
    case 'operation':
        operation($resource,$db, $method, $id, $data);
        break;        
    case 'get_packing_list':
        get_packing_list($resource,$db, $method, $id, $data);
        break;        
    case 'sendmail':
        sendmail($resource,$db, $method, $id, $data);
    break;
    case 'acondicionamiento':
        acondicionamiento($resource,$db, $method, $id, $data);
    break;    
    case 'cancel_lead':
        cancel_lead($resource,$db, $method, $id, $data);
    break;    
    case 'extra_event':
        extra_event($resource,$db, $method, $id, $data);
    break;   
    case 'extra_event_delete':
        extra_event_delete($resource,$db, $method, $id, $data);
    break;        
    case 'get_pay_platform':
        get_pay_platform($resource,$db, $method, $id, $data);
    break;            
    case 'update_pay_platform':
        update_pay_platform($resource,$db, $method, $id, $data);
    break;  
    case 'get_gif_card':
        get_gif_card($resource,$db, $method, $id, $data);
    break;                    

    case 'attendance':
        attendance($resource,$db, $method, $id, $data);
    break;                    

    case 'asistencias':
        asistencias($resource,$db, $method, $id, $data);
    break; 


    default:
        // Manejar rutas no definidas
        http_response_code(404);
        echo json_encode(["message" => "Recurso '" . $resource . "' no encontrado."]);
        break;
    
}

$db = null;

?>