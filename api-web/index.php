<?php
// ----------------------------------------------------
// 1. INCLUSIONES Y DEPENDENCIAS
// ----------------------------------------------------
// Incluye el autoloader de Composer (para JWT)
require '../vendor/autoload.php';
// Incluye las configuraciones globales (SECRET_KEY, DB_USER, etc.)
//include_once '../config/config.php'; 
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$secret_key     = $_ENV['SECRET_KEY'];
$url_base       = $_ENV['URL_BASE'];
$url_base_sale  = $_ENV['URL_BASE_SALE'];
$google_api_key = $_ENV['GOOGLE_API_KEY'];
$host       = $_ENV['host'];
$db_name    = $_ENV['db_name'];
$username   = $_ENV['username'];
$password   = $_ENV['password'];
$port       = $_ENV['port'];
$CFpublicurl    = $_ENV['publicurl'];
define('SECRET_KEY', $secret_key);
define('URL_BASE', $url_base);
define('URL_BASE_SALE', $url_base_sale);
define('GOOGLE_API_KEY', $google_api_key);
define('HOST', $host);
define('USERNAME', $username);
define('DB_NAMEP', $db_name);
define('PASSWORD', $password);
define('PORT',$port);
date_default_timezone_set('America/Mexico_City');
define('CFPUBLICURL',$CFpublicurl);
// Incluye la clase de conexión a la BD
include_once '../config/database.php'; 
// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiAuthError;
use Square\SquareClient;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
use Square\Exceptions\SquareApiException;
use Square\Exceptions\SquareException;
use Square\Payments\Requests\ListPaymentsRequest;
include_once '../api/functions.php'; 
include_once '../api/process_op.php'; 
// ----------------------------------------------------
// 2. CONFIGURACIÓN DE ENCABEZADOS (HEADERS)
// ----------------------------------------------------
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Incluir OPTIONS para CORS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, ID2,ID3,ID4,X-ID-CLIENT,LNG");
// Manejo de solicitudes OPTIONS (preflight requests de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
$headers = getallheaders();
//$authHeader = $headers['Authorization'] ?? '';
// Buscar en getallheaders(), $_SERVER o $_ENV
$authHeader = $headers['Authorization'] 
    ?? $_SERVER['HTTP_AUTHORIZATION'] 
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
    ?? '';

$token = str_replace('Bearer ', '', $authHeader);
$clienteId = $_SERVER['HTTP_X_ID_CLIENT'] ?? null;
$lng = $_SERVER['HTTP_LNG'] ?? 'es';
//$clienteId = $headers['X-ID-CLIENT'] ?? '';
$database = new DatabaseLogin();
$db = $database->getConnection();
$sql = "SELECT nombre_db, token_key, fecha_termino FROM data_bases WHERE Id = :id AND estatus = 'Activo'";
$stmt = $db->prepare($sql);
$stmt->bindValue(":id", $clienteId); 
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);
//$lng = 'es';
$Traducciones = Traducciones('index',$lng,$db);
if ($account){
    $fecha_hoy = date('Y-m-d');
    $fecha_termino = $account['fecha_termino'];
    if ($fecha_hoy > $fecha_termino) {
        echo json_encode(['status'=>'error','error' => Trd(1) . $fecha_termino ]);        
        die();
    }  
    $key = $account['token_key'];
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    if ($decoded->cliente_id == $clienteId){
        define('DB_NAME', $account['nombre_db']);
    }
    else{
        http_response_code(401);
        echo json_encode(['status'=>'error',"error" => Trd(2)]);
        die();        
    }
}
else{
    http_response_code(401);
    echo json_encode(['status'=>'error',"error" => Trd(3)]);
    die();
}
$db = null;


// ----------------------------------------------------
// 4. INICIALIZACIÓN Y LECTURA DE LA SOLICITUD
// ----------------------------------------------------

//define('TZONE', 'America/Mexico_City');//SET TIME ZONE

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));


// Obtener y limpiar los segmentos de la URI dinámicamente (funciona en local y producción)
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#/api-web(?:/(.*))?$#', $request_path, $matches)) {
    $path = $matches[1] ?? '';
} else {
    $path = '';
}
$path = trim($path, '/');
$segments = $path !== '' ? explode('/', $path) : [];

$resource = $segments[0] ?? null; // Ej: 'login', 'clientes', 'productos'
$id = $segments[1] ?? null;       // Ej: ID si existe
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





switch ($resource) {
    case 'Traducciones_web':
	    $Traducciones = Traducciones_web($data->program,$lng,$db);     
        http_response_code(200);
        echo json_encode($Traducciones);        
    break;  
    case 'Traducciones_web_sales':
	    $Traducciones = Traducciones_web_sales($data->program,$lng,$db);     
        http_response_code(200);
        echo json_encode($Traducciones);        
    break;      

    case 'discounts':
	    $Traducciones = Traducciones('get_discounts',$lng,$db);        
        get_discounts($resource,$db, $method, $id, $data);
    break;    
    case 'products':
	    $Traducciones = Traducciones('products',$lng,$db);            
        products($resource,$db, $method, $id, $data);
    break;

    case 'products_sale':
	    $Traducciones = Traducciones('products_sale',$lng,$db);            
        products_sale($resource,$db, $method, $id, $data);
    break;    

    

    case 'newsletter':
	    $Traducciones = Traducciones('newsletter',$lng,$db);            
        newsletter($resource,$db, $method, $id, $data);
    break;

    case 'contact':
	    $Traducciones = Traducciones('contact',$lng,$db);            
        contact($resource,$db, $method, $id, $data);
    break;    

    case 'sale_comments':
	    $Traducciones = Traducciones('sale_comments',$lng,$db);            
        sale_comments($resource,$db, $method, $id, $data);
    break;

    case 'validate_token_comment':
	    $Traducciones = Traducciones('validate_token_comment',$lng,$db);            
        validate_token_comment($resource,$db, $method, $id, $data);
    break;    

    case 'set_comment':
	    $Traducciones = Traducciones('set_comment',$lng,$db);            
        set_comment($resource,$db, $method, $id, $data);
    break;      

    case 'products_sale_stock':
	    $Traducciones = Traducciones('products_sale_stock',$lng,$db);            
        products_sale_stock($resource,$db, $method, $id, $data);
    break;

    case 'products_sale_hero':
	    $Traducciones = Traducciones('products_sale_hero',$lng,$db);            
        products_sale_hero($resource,$db, $method, $id, $data);
    break;  
    
    case 'sales_customer_register':
	    $Traducciones = Traducciones('sales_customer_register',$lng,$db);            
        sales_customer_register($resource,$db, $method, $id, $data);
    break;      
    
    case 'sales_customer_login':
	    $Traducciones = Traducciones('sales_customer_login',$lng,$db);            
        sales_customer_login($resource,$db, $method, $id, $data);
    break;      

    case 'update_profile':
	    $Traducciones = Traducciones('update_profile',$lng,$db);            
        update_profile($resource,$db, $method, $id, $data);
    break;   

    case 'get_orders':
	    $Traducciones = Traducciones('get_orders',$lng,$db);            
        get_orders($resource,$db, $method, $id, $data);
    break;   
    
    case 'get_addresses':
	    $Traducciones = Traducciones('get_addresses',$lng,$db);            
        get_addresses($resource,$db, $method, $id, $data);
    break;   
    
    case 'create_address':
	    $Traducciones = Traducciones('create_address',$lng,$db);            
        create_address($resource,$db, $method, $id, $data);
    break;       

    case 'creatdelete_addresse_address':
	    $Traducciones = Traducciones('delete_address',$lng,$db);            
        delete_address($resource,$db, $method, $id, $data);
    break;     

    case 'get_all_sales':
	    //$Traducciones = Traducciones('get_all_sales',$lng,$db);            
        get_all_sales($resource,$db, $method, $id, $data);
    break;    

    case 'get_sale':
	    //$Traducciones = Traducciones('get_all_sales',$lng,$db);            
        get_sale($resource,$db, $method, $id, $data);
    break;    

    case 'get_sale_contract':
	    //$Traducciones = Traducciones('get_all_sales',$lng,$db);            
        get_sale_contract($resource,$db, $method, $id, $data);
    break;      

    case 'save_sale_contract':
	    //$Traducciones = Traducciones('get_all_sales',$lng,$db);            
        save_sale_contract($resource,$db, $method, $id, $data);
    break;     

    case 'sendsale':
        sendsale($resource,$db, $method, $id, $data);
    break;    

    case 'categories':
	    $Traducciones = Traducciones('categories',$lng,$db);            
        categories($resource,$db, $method, $id, $data);
    break;

    case 'categories_sale':
	    $Traducciones = Traducciones('categories_sale',$lng,$db);            
        categories_sale($resource,$db, $method, $id, $data);
    break;    

    case 'scategories_sale':
	    $Traducciones = Traducciones('scategories_sale',$lng,$db);            
        scategories_sale($resource,$db, $method, $id, $data);
    break;      

    case 'password_recover':
        password_recover($resource,$db, $method, $id, $data);
    break;        

    case 'validate_token':
        validate_token($resource,$db, $method, $id, $data);
    break;     

    case 'password_resets':
        password_resets($resource,$db, $method, $id, $data);
    break;     

    case 'surfaces':
	    $Traducciones = Traducciones('surfaces',$lng,$db);        
        surfaces($resource,$db, $method, $id, $data);
    break;    
    case 'products_categories':
	    $Traducciones = Traducciones('products_categories',$lng,$db);            
        products_categories($resource,$db, $method, $id, $data);
    break;
    case 'get_accesories':
	    $Traducciones = Traducciones('get_accesories',$lng,$db);            
        get_accesories($resource,$db, $method, $id, $data);
    break;    
    case 'process_quote':
	    $Traducciones = Traducciones('process_quote',$lng,$db);            
        process_quote($resource,$db, $method, $id, $data);
    break;
    case 'account':
	    $Traducciones = Traducciones('account',$lng,$db);            
        account($resource,$db, $method, $id, $data);
    break;   
    case 'sendmail':
	    $Traducciones = Traducciones('sendmail',$lng,$db);        
        sendmail($resource,$db, $method, $id, $data);
    break;
    case 'sendbook':
        sendbook($resource,$db, $method, $id, $data);
    break;
    case 'cart_update':
        cart_update($resource,$db, $method, $id, $data);
    break;
    case 'quotes':
	    $Traducciones = Traducciones('quotes',$lng,$db);            
        quotes($resource,$db, $method, $id, $data);
    break;    
    case 'document_center':
	    $Traducciones = Traducciones('document_center',$lng,$db);        
        document_center($resource,$db, $method, $id, $data);
    break;   
    case 'quote_account':
	    $Traducciones = Traducciones('quote_account',$lng,$db);            
        quote_account($resource,$db, $method, $id, $data);
    break;        
    case 'tip_deposit':
	    $Traducciones = Traducciones('tip_deposit',$lng,$db);            
        tip_deposit($resource,$db, $method, $id, $data);
    break; 
    case 'quote_data':
	    $Traducciones = Traducciones('quote_data',$lng,$db);            
        quote_data($resource,$db, $method, $id, $data);
    break;     
    case 'processpayment':
	    $Traducciones = Traducciones('processpayment',$lng,$db);            
        processpayment($resource,$db, $method, $id, $data);
    break;  
    case 'processpayment_square':
        $Traducciones = Traducciones('processpayment_square',$lng,$db);
        processpayment_square($resource,$db, $method, $id, $data);
    break;

    case 'processpayment_paypal':
        $Traducciones = Traducciones('processpayment_paypal',$lng,$db);
        processpayment_paypal($resource,$db, $method, $id, $data);
    break;    

    case 'processpayment_sale':
	    $Traducciones = Traducciones('processpayment_sale',$lng,$db);            
        processpayment_sale($resource,$db, $method, $id, $data);
    break;  
    case 'processpayment_square_sale':
        $Traducciones = Traducciones('processpayment_square_sale',$lng,$db);
        processpayment_square_sale($resource,$db, $method, $id, $data);
    break;

    case 'processpayment_paypal_sale':
        $Traducciones = Traducciones('processpayment_paypal_sale',$lng,$db);
        processpayment_paypal_sale($resource,$db, $method, $id, $data);
    break;      

    case 'gifcard_pay':
        $Traducciones = Traducciones('gifcard_pay',$lng,$db);         
        gifcard_pay($resource,$db, $method, $id, $data);
    break;    
    case 'OPAY':
        $Traducciones = Traducciones('OPAY',$lng,$db);        
        OPAY($resource,$db, $method, $id, $data);
    break;
    case 'SQUARE':
        $Traducciones = Traducciones('SQUARE',$lng,$db);        
        SQUARE($resource,$db, $method, $id, $data);
    break;    
    case 'PAYPAL':
        $Traducciones = Traducciones('PAYPAL',$lng,$db);
        PAYPAL($resource,$db, $method, $id, $data);
    break;        
    case 'tnks':
	    $Traducciones = Traducciones('tnks',$lng,$db);           
        tnks($resource,$db, $method, $id, $data);
    break;    
    case 'tnks_square':
	    $Traducciones = Traducciones('tnks_square',$lng,$db);            
        tnks_square($resource,$db, $method, $id, $data);
    break;        
    case 'distance_charge':
	    $Traducciones = Traducciones('distance_charge',$lng,$db);           
        distance_charge($resource,$db, $method, $id, $data);
    break;
    case 'distance':
	    $Traducciones = Traducciones('distance_charge',$lng,$db);           
        distance($resource,$db, $method, $id, $data);
    break;        
    case 'validate_gifcard':
	    $Traducciones = Traducciones('validate_gifcard',$lng,$db);        
        validate_gifcard($resource,$db, $method, $id, $data);
    break;   
    case 'validate_coupon':
	    $Traducciones = Traducciones('validate_coupon',$lng,$db);        
        validate_coupon($resource,$db, $method, $id, $data);
    break;                
    default:
        // Manejar rutas no definidas
        http_response_code(404);
        echo json_encode(["message" => "Recurso '" . $resource . "' no encontrado."]);
        break;
}

$db = null;
function OPAY($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "select Id,PublicKey FROM opay_account";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($account);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
} 
function SQUARE($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "select Id, LocalId FROM square_account";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($account);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function PAYPAL($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "select Id, Active FROM paypal_account";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($account);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function gifcard_pay($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
            $token = $data->token ?? null;
            $ahora = date("Y-m-d H:i:s");
            $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
            $stmt->execute([$token]);
            $cotizacion = $stmt->fetch();
            if ($cotizacion) {
                // Verificar si la fecha actual es mayor a la de expiración
                if ($ahora > $cotizacion['ExpDate']) {
                    echo Trd(1) . $cotizacion['ExpDate']." $ahora";
                    die();
                }
            } else {
                echo Trd(2);
                die();
            }        
            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                
            $stmt = $db->prepare("SELECT * FROM lead WHERE Id = ? ");
            $stmt->execute([$cotizacion['IdQuote']]);
            $lead = $stmt->fetch();    
            //TOTAL GENERAL
            $TotalG = $lead['SubTotal'] + $lead['TaxAmount'] + $lead['Tip'];            
            //RECUPERAMOS LOS DATOS DEL GIFCARD
            $stmt = $db->prepare("SELECT * FROM lead_discounts WHERE IdLead = ? AND Type = 'gifcard'");
            $stmt->execute([$lead['Id']]);
            $lead_discounts = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($lead_discounts){
                $stmt = $db->prepare("SELECT * FROM gifcard WHERE Code = ? ");
                $stmt->execute([$lead_discounts['Descript']]);
                $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);
                //ACTUALIZAMOS El descuento del gifcard en lead_discounts
                $stmt = $db->prepare(" UPDATE lead_discounts SET AmountVal = ? WHERE Id = ?");
                $stmt->execute([$TotalG,$lead_discounts['Id']]);               
            }
            // MONTO DE GIFCARD
            $GCAmount = $gifcard['Amount'];
            // SALDO DE GIFCARD
            $GCRemain = $GCAmount - $TotalG;
            //RECUPERAMOS EL FOLIO DEL EVENTO
            $Folio = 0;    
            $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
            $stmt->execute([$lead['IdBranch']]);
            $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($Payments){
                $Folio = $Payments['Folio'];
            }
            $Folio+=1;
            $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
            $stmt->execute([$Folio,$lead['IdBranch']]);              
            //INSERTAMOS EL PAGO
            $paymentid = $lead_discounts['Descript'];
            $currency = $account['Currency'];
            $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                    VALUES  (?,'Pay',?,now(),'GifCard',?,?,?,'A','Web')";
            $stmtPay = $db->prepare($sqlPay);
            $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$TotalG,$currency,$paymentid]);    
            //ACTUALIAMOS EL ESTATUS DEL EVENTO
            $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
            $stmt->execute(['confirmed', $cotizacion['IdQuote']]);
            //ACTUALIZAMOS EL SALDO DEL GIFCARD
            $stmt = $db->prepare(" UPDATE gifcard SET Amount = ? WHERE Id = ?");
            $stmt->execute([$GCRemain,$gifcard['Id']]);              
            //METEMOS EL EVENTO A PROCESO
            process_op($cotizacion['IdQuote'],$db);            
            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '8' AND Idioma = '$lng'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);             
                    //RECUPERAR Customer
                    $sql = "SELECT * FROM customers WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Customer']); 
                    $stmt->execute();
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);            
                    //RECUPERAR venue
                    $sql = "SELECT * FROM venues WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Venue']); 
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);       
                    $header = "";
                    //$header = "MIME-Version: 1.0\r\n";
                    //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $header .= $Template['Nombre']."\r\n";            
                                // Incluimos el teléfono en el cuerpo del correo
                    $cuerpo = "<html>".$Template['Template']."</html>";
                    if ($customer){
                        $nombreCliente = $customer['Nombres'];
                        $correoCliente =$customer['Correo'];
                    }
                    else{
                        $nombreCliente = $organization['Nombre'];
                        $correoCliente =$organization['Correo'];
                    }    
                    $aviso_gifcard0 = '';
                    $aviso_gifcard = '';
                    if ($GCRemain > 0){
                        $aviso_gifcard0 = 'display: none';
                    }
                    else{
                        $aviso_gifcard = 'display: none';
                    }
                    //'company_logo'      => $account['Logo'],
                    $valores = [                        
                        'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                        'company_name' => $account['NombreCompania'],
                        'ctfirstname'  => $nombreCliente,
                        'leadid'       => $lead['Folio'],
                        'total'  => $TotalG,
                        'apayment'  => $TotalG,
                        'balancedue'  => '0.00',
                        'link_to_accept'  => '',
                        'eventstreet' => $venue['Direccion'],
                        'eventcity'    => $venue['Ciudad'],
                        'startdate'  => $lead['StartDateTime'],
                        'company_name'  => $account['NombreCompania'],
                        'company_phone'  => $account['TelefonoOficina'],
                        'company_city'  => $account['Ciudad'],
                        'aviso_gifcard0' => $aviso_gifcard0,
                        'aviso_gifcard' => $aviso_gifcard,
                        'gifcard' => $lead_discounts['Descript'],
                        'balancegifcard' => $GCRemain,
                        'fechagifcard' => $gifcard['FechaExpiracion'], 
                        'aviso_saldo' => 'display: none'
                    ];       
                    $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
                    $datosConexion = [
                        'host'             => $account['ServidorS'],
                        'username'         => $account['UsuarioS'],
                        'password'         => $account['PasswordS'],
                        'port'             => $account['PortS'],
                        'encryption'       => '',
                        'nombre_remitente' => $account['NombreCompania']
                    ];
                    $archivos = [];
                    $resultado = enviarEmail(
                        $datosConexion, 
                        $correoCliente, 
                        $header,
                        $cuerpo,
                        $archivos,
                        $cotizacion['Contrato'],
                        $cotizacion['UUID'].".PDF"
                    );                        
                //
                echo json_encode([
                    'success'    => true,
                    'payment_id' => "",
                    'status'     => 'success',
                    'amount'     => $TotalG,
                    'currency'   => $currency,
                    'status' => 'success',
                    'message' => Trd(3),
                    'transaction_id' => '',
                    'url' => 'successpayment.php?Id='.$token
                ]);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(4)));
        break;
    }      
}

function process_sale($db,$data,$pay_id,$platform){

    $sqlSale = "INSERT INTO sales (
                    customer_id, address_id, total_amount, cart_notes, 
                    payer_name, payer_lastname, payer_email, 
                    payment_method, gateway_token, device_fingerprint, payment_status,cart_json
                ) VALUES (
                    :customer_id, :address_id, :total_amount, :cart_notes, 
                    :payer_name, :payer_lastname, :payer_email, 
                    :payment_method, :gateway_token, :device_fingerprint, 'completed', :cart_json
                )";

    $stmtSale = $db->prepare($sqlSale);
    
    $stmtSale->execute([
        ':customer_id'       => $data->id_client,
        ':address_id'        => $data->id_address,
        ':total_amount'      => $data->amount,
        ':cart_notes'        => $data->cartNote_final ?? null,
        ':payer_name'        => $data->name,
        ':payer_lastname'    => $data->last_name,
        ':payer_email'       => $data->email,
        ':payment_method'    => $data->payment_method,
        ':gateway_token'     => $data->token_id,
        ':device_fingerprint'=> $data->deviceIdHiddenFieldName ?? null,
        ':cart_json' =>$data->cart_json
    ]);

    // Obtener el ID autogenerado de la venta recién creada
    $saleId = $db->lastInsertId();

    // --- PASO B: Decodificar e Insertar Artículos del Carrito ---
    if (!isset($data->cart_json)) {
        throw new Exception("El elemento 'cart-json' es obligatorio en la petición.");
    }

    $cartItems = json_decode($data->cart_json, true);
    
    if (!is_array($cartItems)) {
        throw new Exception("El formato interno de 'cart-json' no pudo ser resuelto a una matriz.");
    }

    $sqlItem = "INSERT INTO sale_items (
                    sale_id, product_id, product_name_snapshot, price, quantity, is_special_production
                ) VALUES (
                    :sale_id, :product_id, :product_name, :price, :quantity, :is_special_production
                )";
                
    $stmtItem = $db->prepare($sqlItem);

    foreach ($cartItems as $item) {
        $stmtItem->execute([
            ':sale_id'               => $saleId,
            ':product_id'            => $item['id'],
            ':product_name'          => $item['name'],
            ':price'                 => $item['price'],
            ':quantity'              => $item['quantity'],
            ':is_special_production' => $item['onlyRequest'] ? 1 : 0
        ]);
    }

        $sql = "SELECT * FROM account ";
        $stmt = $db->prepare($sql);
        $stmt->execute();                
        $account = $stmt->fetch(PDO::FETCH_ASSOC);                

        $Currency = $account['Currency'];        

        $Suc = 1;
        $Folio = 0;    
        $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'PaySale'");
        $stmt->execute([$Suc]);
        $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Payments){
            $Folio = $Payments['Folio'];
        }
        $Folio+=1;
        $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'PaySale'");
        $stmt->execute([$Folio,$Suc]);           


        $sqlPay = "INSERT INTO payments_sale (IdSale,     Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                VALUES       (?     ,'PaySale',    ?,   now(),       ?,     ?,       ?,            ?,    'A', 'Web')";
        $stmtPay = $db->prepare($sqlPay);        
        $stmtPay->execute([$saleId,$Folio,$platform,$data->amount,$Currency,$pay_id]);    


        //DESCUENTO DE EXISTENCIAS

        //PROCESO DE GENERACION DE CONTRATO

        //PROCESO DE ENVIO DE CORREO

        return $Folio;

    

}

function processpayment_paypal_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST':

                $Folio = process_sale($db,$data,$data->orderID,'paypal');

                $sql = "SELECT Currency FROM account ";
                $stmt = $db->prepare($sql);
                $stmt->execute();                
                $account = $stmt->fetch(PDO::FETCH_ASSOC);                

                $Currency = $account['Currency'];              

                echo json_encode([
                    'success'    => true,
                    'payment_id' => 'PaySale - '.$Folio,
                    'status'     => 'success',
                    'amount'     => $data->amount,
                    'currency'   => $Currency,
                    'message' => '¡Pago realizado con éxito!',
                    'transaction_id' => $data->orderID,
                    'url' => "tnks?Sale=$Folio&Id=".$data->orderID
                ]);            


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(4)));
        break;
    }               
}
function processpayment_square_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST':

            $stmt = $db->prepare("SELECT * FROM  square_account");
            $stmt->execute();
            $square_account = $stmt->fetch();       
            $accessToken = $square_account['Token'];
            $locationId  = $square_account['LocalId'];
            // ── Recibir token del frontend ─────────────────────────────────────────────────
            //$input = json_decode(file_get_contents('php://input'), true);
            $token_id = $data->token_id ?? null;
            if (!$token_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => Trd(1)]);
                exit;
            }
                // 2. Recibir datos del formulario
                $tokenId    = $data->token_id ?? null;
                $token      = $data->token ?? null;
                $deviceId   = $data->deviceIdHiddenFieldName ?? null;
                $amount     = $data->amount ?? 0;
                $amount = $amount * 100;
                $ahora = date("Y-m-d H:i:s");            

                // Datos del cliente
                $customerData = [
                    'name' => $data->name,
                    'last_name' => $data->last_name,
                    'email' => $data->email,
                    'phone_number' => $data->phone ?? '5500000000'
                ];
            // ── Inicializar cliente Square (v45) ───────────────────────────────────────────
            $square = new SquareClient(
                token: $accessToken,
                options: ['baseUrl' => 'https://connect.squareupsandbox.com'] // sandbox
                // Para producción: omitir baseUrl o usar 'https://connect.squareup.com'
            );
            // ── Crear el pago ──────────────────────────────────────────────────────────────
            try {
                $response = $square->payments->create(
                    request: new CreatePaymentRequest([
                        'idempotencyKey' => uniqid('Pago_', true), // clave única por transacción
                        'sourceId'       => $token_id,
                        'locationId'     => $locationId,
                        'amountMoney'    => new Money([
                            'amount'   => $amount,           // en centavos: 1000 = $10.00 USD
                            'currency' => Currency::Usd->value,
                        ]),
                        'note' => 'Pago total de producto - ' . $customerData['name'],
                    ])
                );
                $payment = $response->getPayment();     

                $Folio= process_sale($db,$data,$payment->getId(),'square');

                $sql = "SELECT Currency FROM account ";
                $stmt = $db->prepare($sql);
                $stmt->execute();                
                $account = $stmt->fetch(PDO::FETCH_ASSOC);                

                $Currency = $account['Currency'];              

                echo json_encode([
                    'success'    => true,
                    'payment_id' => 'PaySale - '.$Folio,
                    'status'     => 'success',
                    'amount'     => $data->amount,
                    'currency'   => $Currency,
                    'message' => '¡Pago realizado con éxito!',
                    'transaction_id' => $payment->getId(),
                    'url' => "tnks?Sale=$Folio&Id=".$payment->getId()
                ]);                          

                
            } catch (SquareApiException $e) {
                // Error de la API de Square (4xx / 5xx)
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'body'    => json_decode($e->getBody(), true),
                    'status' => 'error',
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()        
                ]);
            } catch (SquareException $e) {
                // Error de red u otro error del SDK
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Error intero: ' . $e->getMessage(),
                    'status' => 'error',
                    'description' => $e->getMessage()
                ]);
            }    


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(4)));
        break;
    }
}
function processpayment_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST':




        $stmt = $db->prepare("SELECT * FROM  opay_account");
        $stmt->execute();
        $opay_account = $stmt->fetch();       
        $merchantId = $opay_account['Id'];
        $privateKey = $opay_account['SecretKey'];            
        $countryCode = 'MX';
        $clientIp = $_SERVER['REMOTE_ADDR'];
        $isSandbox = true;
        try {
            $openpay = Openpay::getInstance($merchantId, $privateKey,$countryCode,$clientIp);
            Openpay::setProductionMode(!$isSandbox);
            // 2. Recibir datos del formulario
            $tokenId    = $data->token_id;
            $token      = $data->token;
            $deviceId   = $data->deviceIdHiddenFieldName;
            $amount     = $data->amount;
            $ahora = date("Y-m-d H:i:s");


            // Datos del cliente
            $customerData = [
                'name' => $data->name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'phone_number' => $data->phone ?? '5500000000'
            ];
            if (!$tokenId || !$deviceId) {
                throw new Exception('Faltan identificadores de seguridad (Token/Device ID).');
            }
            $Currency = 'MXN';
            // 3. Preparar el objeto del cargo
            $chargeRequest = [
                'method' => 'card',
                'source_id' => $tokenId,
                'amount' => (float)$amount,
                'currency' => $Currency,
                'description' => 'Pago total de producto - ' . $customerData['name'],
                'device_session_id' => $deviceId, // Vital para el sistema antifraude
                'customer' => $customerData,
                // Si quieres habilitar 3D Secure para mayor seguridad:
                // 'use_3d_secure' => true,
                // 'redirect_url' => 'https://tu-sitio.com/pago-completado',
            ];
            // 4. Realizar el cargo
            $charge = $openpay->charges->create($chargeRequest);
            // 5. Respuesta según el estado del pago
            if ($charge->status == 'completed') {
                $Folio = process_sale($db,$data,$charge->id,'opay');

                $sql = "SELECT Currency FROM account ";
                $stmt = $db->prepare($sql);
                $stmt->execute();                
                $account = $stmt->fetch(PDO::FETCH_ASSOC);                

                $Currency = $account['Currency'];              

                echo json_encode([
                    'success'    => true,
                    'payment_id' => 'PaySale - '.$Folio,
                    'status'     => 'success',
                    'amount'     => $data->amount,
                    'currency'   => $Currency,
                    'message' => '¡Pago realizado con éxito!',
                    'transaction_id' => $charge->id,
                    'url' => "tnks?Sale=$Folio&Id=".$charge->id
                ]);                   

            } else {
                // En caso de pagos pendientes (como 3D Secure)
                echo json_encode([
                    'status' => 'pending',
                    'url' => $charge->payment_method->url
                ]);
            }            



        } catch (OpenpayApiTransactionError $e) {
            // Errores específicos de la transacción (ej. fondos insuficientes)
            http_response_code(402);
            echo json_encode([
                'status' => 'error',
                'error_code' => $e->getErrorCode(),
                'description' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Errores generales del sistema
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'description' => $e->getMessage()
            ]);
        }  



        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(4)));
        break;
    }
}
function processpayment_paypal($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 

                $token      = $data->token ?? null;
                $amount     = $data->amount ?? 0;
                $OrdeId      = $data->orderID ?? '';
                $ahora = date("Y-m-d H:i:s");
                $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
                $stmt->execute([$token]);
                $cotizacion = $stmt->fetch();
                if ($cotizacion) {
                    // Verificar si la fecha actual es mayor a la de expiración
                    if ($ahora > $cotizacion['ExpDate']) {
                        echo Trd(2) . $cotizacion['ExpDate']." $ahora";
                        die();
                    }
                } else {
                    echo Trd(3);
                    die();
                }        

                $sql = "SELECT * FROM account ";
                $stmt = $db->prepare($sql);
                $stmt->execute();                
                $account = $stmt->fetch(PDO::FETCH_ASSOC);                

                $Currency = $account['Currency'];

                $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
                $stmt->execute([$cotizacion['IdQuote']]);
                $lead = $stmt->fetch();    
                $Folio = 0;    
                $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->execute([$lead['IdBranch']]);
                $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Payments){
                    $Folio = $Payments['Folio'];
                }
                $Folio+=1;
                $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->execute([$Folio,$lead['IdBranch']]);                


            try{
                $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                        VALUES  (?,'Pay',?,now(),'paypal',?,?,?,'A','Web')";
                $stmtPay = $db->prepare($sqlPay);
                $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$amount,$Currency,$OrdeId]);    
                $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
                $stmt->execute(['confirmed', $cotizacion['IdQuote']]);
                //RECUPERAMOS LOS DATOS DEL GIFCARD
                $stmt = $db->prepare("SELECT * FROM lead_discounts WHERE IdLead = ? AND Type = 'gifcard'");
                $stmt->execute([$cotizacion['IdQuote']]);
                $lead_discounts = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($lead_discounts){
                    $stmt = $db->prepare("SELECT * FROM gifcard WHERE Code = ? ");
                    $stmt->execute([$lead_discounts['Descript']]);
                    $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);
                    //ACTUALIZAMOS EL SALDO DEL GIFCARD
                    $stmt = $db->prepare(" UPDATE gifcard SET Amount = 0, Estatus = 0 WHERE Id = ?");
                    $stmt->execute([$gifcard['Id']]);
                    //METER PAGO DE GIFCARD                    
                    $Folio = 0;    
                    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$lead['IdBranch']]);
                    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($Payments){
                        $Folio = $Payments['Folio'];
                    }
                    $Folio+=1;
                    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$Folio,$lead['IdBranch']]);                        
                    $paymentid = $lead_discounts['Descript'];                    
                    $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                            VALUES  (?,'Pay',?,now(),'GifCard',?,?,?,'A','Web')";
                    $stmtPay = $db->prepare($sqlPay);
                    $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$lead_discounts['AmountVal'],$Currency,$paymentid]);                        
                }
                    process_op($cotizacion['IdQuote'],$db);
                //ENVIO DE CORREO                    
                    $sql = "SELECT * FROM account ";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $account = $stmt->fetch(PDO::FETCH_ASSOC);                
                    //RECUPERAR PLANTILLA
                    $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '8' AND Idioma = '$lng'";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $Template = $stmt->fetch(PDO::FETCH_ASSOC);    
                    //RECUPERAR Lead
                    $sql = "SELECT * FROM lead WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $cotizacion['IdQuote']); 
                    $stmt->execute();
                    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
                    //RECUPERAR Customer
                    $sql = "SELECT * FROM customers WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Customer']); 
                    $stmt->execute();
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);            
                    //RECUPERAR venue
                    $sql = "SELECT * FROM venues WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Venue']); 
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);       
                    $header = "";
                    //$header = "MIME-Version: 1.0\r\n";
                    //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $header .= $Template['Nombre']."\r\n";            
                                // Incluimos el teléfono en el cuerpo del correo
                    $cuerpo = "<html>".$Template['Template']."</html>";
                            if ($customer){
                                $nombreCliente = $customer['Nombres'];
                                $correoCliente =$customer['Correo'];
                            }
                            else{
                                $nombreCliente = $organization['Nombre'];
                                $correoCliente =$organization['Correo'];
                            }    
                    $valores = [
                        'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                        'company_name' => $account['NombreCompania'],
                        'ctfirstname'  => $nombreCliente,
                        'leadid'       => $lead['Folio'],
                        'total'  => $lead['Total'],
                        'apayment'  => $lead['DepositAmount'],
                        'balancedue'  => $lead['Balance'],
                        'link_to_accept'  => '',
                        'eventstreet' => $venue['Direccion'],
                        'eventcity'    => $venue['Ciudad'],
                        'startdate'  => $lead['StartDateTime'],
                        'company_name'  => $account['NombreCompania'],
                        'company_phone'  => $account['TelefonoOficina'],
                        'company_city'  => $account['Ciudad'],
                    ];       
                    $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
                    $datosConexion = [
                        'host'             => $account['ServidorS'],
                        'username'         => $account['UsuarioS'],
                        'password'         => $account['PasswordS'],
                        'port'             => $account['PortS'],
                        'encryption'       => '',
                        'nombre_remitente' => $account['NombreCompania']
                    ];
                    $archivos = [];
                    $resultado = enviarEmail(
                        $datosConexion, 
                        $correoCliente, 
                        $header,
                        $cuerpo,
                        $archivos,
                        $cotizacion['Contrato'],
                        $cotizacion['UUID'].".PDF"
                    );                        
                //
                echo json_encode([
                    'success'    => true,
                    'payment_id' => 'Pay - '.$Folio,
                    'status'     => 'Success',
                    'amount'     => $amount,
                    'currency'   => $Currency,
                    'status' => 'success',
                    'message' => Trd(6),
                    'transaction_id' => $OrdeId,
                    'url' => 'successpayment.php?Id='.$token.'&TId='.$OrdeId
                ]);
            } catch (SquareApiException $e) {
                // Error de la API de Square (4xx / 5xx)
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'body'    => json_decode($e->getBody(), true),
                    'status' => 'error',
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()        
                ]);
            } catch (SquareException $e) {
                // Error de red u otro error del SDK
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error'   => Trd(5) . $e->getMessage(),
                    'status' => 'error',
                    'description' => $e->getMessage()
                ]);
            }                              


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(7)));
        break;
    }      
} 
function processpayment_square($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
            $stmt = $db->prepare("SELECT * FROM  square_account");
            $stmt->execute();
            $square_account = $stmt->fetch();       
            $accessToken = $square_account['Token'];
            $locationId  = $square_account['LocalId'];
            // ── Recibir token del frontend ─────────────────────────────────────────────────
            //$input = json_decode(file_get_contents('php://input'), true);
            $token_id = $data->token_id ?? null;
            if (!$token_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => Trd(1)]);
                exit;
            }
                // 2. Recibir datos del formulario
                $tokenId    = $data->token_id ?? null;
                $token      = $data->token ?? null;
                $deviceId   = $data->deviceIdHiddenFieldName ?? null;
                $amount     = $data->amount ?? 0;
                $amount = $amount * 100;
                $ahora = date("Y-m-d H:i:s");
                $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
                $stmt->execute([$token]);
                $cotizacion = $stmt->fetch();
                if ($cotizacion) {
                    // Verificar si la fecha actual es mayor a la de expiración
                    if ($ahora > $cotizacion['ExpDate']) {
                        echo Trd(2) . $cotizacion['ExpDate']." $ahora";
                        die();
                    }
                } else {
                    echo Trd(3);
                    die();
                }        
                $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
                $stmt->execute([$cotizacion['IdQuote']]);
                $lead = $stmt->fetch();    
                $Folio = 0;    
                $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->execute([$lead['IdBranch']]);
                $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Payments){
                    $Folio = $Payments['Folio'];
                }
                $Folio+=1;
                $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                $stmt->execute([$Folio,$lead['IdBranch']]);                      
                // Datos del cliente
                $customerData = [
                    'name' => $data->name,
                    'last_name' => $data->last_name,
                    'email' => $data->email,
                    'phone_number' => $data->phone ?? '5500000000'
                ];
            // ── Inicializar cliente Square (v45) ───────────────────────────────────────────
            $square = new SquareClient(
                token: $accessToken,
                options: ['baseUrl' => 'https://connect.squareupsandbox.com'] // sandbox
                // Para producción: omitir baseUrl o usar 'https://connect.squareup.com'
            );
            // ── Crear el pago ──────────────────────────────────────────────────────────────
            try {
                $response = $square->payments->create(
                    request: new CreatePaymentRequest([
                        'idempotencyKey' => uniqid('Pago_', true), // clave única por transacción
                        'sourceId'       => $token_id,
                        'locationId'     => $locationId,
                        'amountMoney'    => new Money([
                            'amount'   => $amount,           // en centavos: 1000 = $10.00 USD
                            'currency' => Currency::Usd->value,
                        ]),
                        'note' => Trd(4) . $customerData['name'],
                    ])
                );
                $payment = $response->getPayment();
                $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                        VALUES  (?,'Pay',?,now(),'Square',?,?,?,'A','Web')";
                $stmtPay = $db->prepare($sqlPay);
                $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$amount/100,Currency::Usd->value,$payment->getId()]);    
                $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
                $stmt->execute(['confirmed', $cotizacion['IdQuote']]);
                //RECUPERAMOS LOS DATOS DEL GIFCARD
                $stmt = $db->prepare("SELECT * FROM lead_discounts WHERE IdLead = ? AND Type = 'gifcard'");
                $stmt->execute([$cotizacion['IdQuote']]);
                $lead_discounts = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($lead_discounts){
                    $stmt = $db->prepare("SELECT * FROM gifcard WHERE Code = ? ");
                    $stmt->execute([$lead_discounts['Descript']]);
                    $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);
                    //ACTUALIZAMOS EL SALDO DEL GIFCARD
                    $stmt = $db->prepare(" UPDATE gifcard SET Amount = 0, Estatus = 0 WHERE Id = ?");
                    $stmt->execute([$gifcard['Id']]);
                    //METER PAGO DE GIFCARD                    
                    $Folio = 0;    
                    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$lead['IdBranch']]);
                    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($Payments){
                        $Folio = $Payments['Folio'];
                    }
                    $Folio+=1;
                    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$Folio,$lead['IdBranch']]);                        
                    $paymentid = $lead_discounts['Descript'];                    
                    $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                            VALUES  (?,'Pay',?,now(),'GifCard',?,?,?,'A','Web')";
                    $stmtPay = $db->prepare($sqlPay);
                    $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$lead_discounts['AmountVal'],Currency::Usd->value,$paymentid]);                        
                }
                    process_op($cotizacion['IdQuote'],$db);
                //ENVIO DE CORREO                    
                    $sql = "SELECT * FROM account ";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $account = $stmt->fetch(PDO::FETCH_ASSOC);                
                    //RECUPERAR PLANTILLA
                    $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '8' AND Idioma = '$lng'";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $Template = $stmt->fetch(PDO::FETCH_ASSOC);    
                    //RECUPERAR Lead
                    $sql = "SELECT * FROM lead WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $cotizacion['IdQuote']); 
                    $stmt->execute();
                    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
                    //RECUPERAR Customer
                    $sql = "SELECT * FROM customers WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Customer']); 
                    $stmt->execute();
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);            
                    //RECUPERAR venue
                    $sql = "SELECT * FROM venues WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Venue']); 
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);       
                    $header = "";
                    //$header = "MIME-Version: 1.0\r\n";
                    //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $header .= $Template['Nombre']."\r\n";            
                                // Incluimos el teléfono en el cuerpo del correo
                    $cuerpo = "<html>".$Template['Template']."</html>";
                            if ($customer){
                                $nombreCliente = $customer['Nombres'];
                                $correoCliente =$customer['Correo'];
                            }
                            else{
                                $nombreCliente = $organization['Nombre'];
                                $correoCliente =$organization['Correo'];
                            }    
                    $valores = [
                        'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                        'company_name' => $account['NombreCompania'],
                        'ctfirstname'  => $nombreCliente,
                        'leadid'       => $lead['Folio'],
                        'total'  => $lead['Total'],
                        'apayment'  => $lead['DepositAmount'],
                        'balancedue'  => $lead['Balance'],
                        'link_to_accept'  => '',
                        'eventstreet' => $venue['Direccion'],
                        'eventcity'    => $venue['Ciudad'],
                        'startdate'  => $lead['StartDateTime'],
                        'company_name'  => $account['NombreCompania'],
                        'company_phone'  => $account['TelefonoOficina'],
                        'company_city'  => $account['Ciudad'],
                    ];       
                    $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
                    $datosConexion = [
                        'host'             => $account['ServidorS'],
                        'username'         => $account['UsuarioS'],
                        'password'         => $account['PasswordS'],
                        'port'             => $account['PortS'],
                        'encryption'       => '',
                        'nombre_remitente' => $account['NombreCompania']
                    ];
                    $archivos = [];
                    $resultado = enviarEmail(
                        $datosConexion, 
                        $correoCliente, 
                        $header,
                        $cuerpo,
                        $archivos,
                        $cotizacion['Contrato'],
                        $cotizacion['UUID'].".PDF"
                    );                        
                //
                echo json_encode([
                    'success'    => true,
                    'payment_id' => $payment->getId(),
                    'status'     => $payment->getStatus(),
                    'amount'     => $payment->getAmountMoney()->getAmount() / 100,
                    'currency'   => $payment->getAmountMoney()->getCurrency(),
                    'status' => 'success',
                    'message' => Trd(6),
                    'transaction_id' => $payment->getId(),
                    'url' => 'successpayment.php?Id='.$token.'&TId='.$payment->getId()
                ]);
            } catch (SquareApiException $e) {
                // Error de la API de Square (4xx / 5xx)
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'body'    => json_decode($e->getBody(), true),
                    'status' => 'error',
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()        
                ]);
            } catch (SquareException $e) {
                // Error de red u otro error del SDK
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error'   => Trd(5) . $e->getMessage(),
                    'status' => 'error',
                    'description' => $e->getMessage()
                ]);
            }            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(7)));
        break;
    }      
}               
function processpayment($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
        $stmt = $db->prepare("SELECT * FROM  opay_account");
        $stmt->execute();
        $opay_account = $stmt->fetch();       
        $merchantId = $opay_account['Id'];
        $privateKey = $opay_account['SecretKey'];            
        $countryCode = 'MX';
        $clientIp = $_SERVER['REMOTE_ADDR'];
        $isSandbox = true;
        try {
            $openpay = Openpay::getInstance($merchantId, $privateKey,$countryCode,$clientIp);
            Openpay::setProductionMode(!$isSandbox);
            // 2. Recibir datos del formulario
            $tokenId    = $data->token_id;
            $token      = $data->token;
            $deviceId   = $data->deviceIdHiddenFieldName;
            $amount     = $data->amount;
            $ahora = date("Y-m-d H:i:s");
            $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
            $stmt->execute([$token]);
            $cotizacion = $stmt->fetch();
            if ($cotizacion) {
                // Verificar si la fecha actual es mayor a la de expiración
                if ($ahora > $cotizacion['ExpDate']) {
                    echo Trd(1) . $cotizacion['ExpDate']." $ahora";
                    die();
                }
            } else {
                echo Trd(2);
                die();
            }        
            $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
            $stmt->execute([$cotizacion['IdQuote']]);
            $lead = $stmt->fetch();    
            $Folio = 0;    
            $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
            $stmt->execute([$lead['IdBranch']]);
            $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($Payments){
                $Folio = $Payments['Folio'];
            }
            $Folio+=1;
            $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
            $stmt->execute([$Folio,$lead['IdBranch']]);                
            // Datos del cliente
            $customerData = [
                'name' => $data->name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'phone_number' => $data->phone ?? '5500000000'
            ];
            if (!$tokenId || !$deviceId) {
                throw new Exception(Trd(3));
            }
            $Currency = 'MXN';
            // 3. Preparar el objeto del cargo
            $chargeRequest = [
                'method' => 'card',
                'source_id' => $tokenId,
                'amount' => (float)$amount,
                'currency' => $Currency,
                'description' => Trd(4) . $customerData['name'],
                'device_session_id' => $deviceId, // Vital para el sistema antifraude
                'customer' => $customerData,
                // Si quieres habilitar 3D Secure para mayor seguridad:
                // 'use_3d_secure' => true,
                // 'redirect_url' => 'https://tu-sitio.com/pago-completado',
            ];
            // 4. Realizar el cargo
            $charge = $openpay->charges->create($chargeRequest);
            // 5. Respuesta según el estado del pago
            if ($charge->status == 'completed') {
                $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                        VALUES  (?,'Pay',?,now(),'OpenPay',?,?,?,'A','Web')";
                $stmtPay = $db->prepare($sqlPay);
                $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$amount,$Currency,$charge->id]);    
                $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
                $stmt->execute(['confirmed', $cotizacion['IdQuote']]);
                //RECUPERAMOS LOS DATOS DEL GIFCARD
                $stmt = $db->prepare("SELECT * FROM lead_discounts WHERE IdLead = ? AND Type = 'gifcard'");
                $stmt->execute([$cotizacion['IdQuote']]);
                $lead_discounts = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($lead_discounts){
                    $stmt = $db->prepare("SELECT * FROM gifcard WHERE Code = ? ");
                    $stmt->execute([$lead_discounts['Descript']]);
                    $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);
                    //ACTUALIZAMOS EL SALDO DEL GIFCARD
                    $stmt = $db->prepare(" UPDATE gifcard SET Amount = 0, Estatus = 0 WHERE Id = ?");
                    $stmt->execute([$gifcard['Id']]);     
                   //METER PAGO DE GIFCARD                    
                    $Folio = 0;    
                    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$lead['IdBranch']]);
                    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($Payments){
                        $Folio = $Payments['Folio'];
                    }
                    $Folio+=1;
                    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$Folio,$lead['IdBranch']]);                        
                    $paymentid = $lead_discounts['Descript'];                    
                    $sqlPay = "INSERT INTO payments (IdLead,Type,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                            VALUES  (?,'Pay',?,now(),'GifCard',?,?,?,'A','Web')";
                    $stmtPay = $db->prepare($sqlPay);
                    $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$lead_discounts['AmountVal'],$Currency,$paymentid]);                        
                }                
                process_op($cotizacion['IdQuote'],$db);
                //ENVIO DE CORREO                    
                    $sql = "SELECT * FROM account ";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $account = $stmt->fetch(PDO::FETCH_ASSOC);                
                    //RECUPERAR PLANTILLA
                    $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '8' AND Idioma = '$lng'";
                    $stmt = $db->prepare($sql);
                    //$stmt->bindValue(":name", $data->Product); 
                    $stmt->execute();
                    $Template = $stmt->fetch(PDO::FETCH_ASSOC);    
                    //RECUPERAR Lead
                    $sql = "SELECT * FROM lead WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $cotizacion['IdQuote']); 
                    $stmt->execute();
                    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
                    //RECUPERAR Customer
                    $sql = "SELECT * FROM customers WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Customer']); 
                    $stmt->execute();
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);            
                    //RECUPERAR venue
                    $sql = "SELECT * FROM venues WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", $lead['Venue']); 
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);       
                    $header = "";
                    //$header = "MIME-Version: 1.0\r\n";
                    //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $header .= $Template['Nombre']."\r\n";            
                                // Incluimos el teléfono en el cuerpo del correo
                    $cuerpo = "<html>".$Template['Template']."</html>";
                            if ($customer){
                                $nombreCliente = $customer['Nombres'];
                                $correoCliente =$customer['Correo'];
                            }
                            else{
                                $nombreCliente = $organization['Nombre'];
                                $correoCliente =$organization['Correo'];
                            }    
                    $valores = [
                        'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                        'company_name' => $account['NombreCompania'],
                        'ctfirstname'  => $nombreCliente,
                        'leadid'       => $lead['Folio'],
                        'total'  => $lead['Total'],
                        'apayment'  => $lead['DepositAmount'],
                        'balancedue'  => $lead['Balance'],
                        'link_to_accept'  => '',
                        'eventstreet' => $venue['Direccion'],
                        'eventcity'    => $venue['Ciudad'],
                        'startdate'  => $lead['StartDateTime'],
                        'company_name'  => $account['NombreCompania'],
                        'company_phone'  => $account['TelefonoOficina'],
                        'company_city'  => $account['Ciudad'],
                    ];       
                    $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
                    $datosConexion = [
                        'host'             => $account['ServidorS'],
                        'username'         => $account['UsuarioS'],
                        'password'         => $account['PasswordS'],
                        'port'             => $account['PortS'],
                        'encryption'       => '',
                        'nombre_remitente' => $account['NombreCompania']
                    ];
                    $archivos = [];
                    $resultado = enviarEmail(
                        $datosConexion, 
                        $correoCliente, 
                        $header,
                        $cuerpo,
                        $archivos,
                        $cotizacion['Contrato'],
                        $cotizacion['UUID'].".PDF"
                    );                        
                echo json_encode([
                    'status' => 'success',
                    'message' => Trd(5),
                    'transaction_id' => $charge->id,
                    'url' => 'successpayment.php?Id='.$token.'&TId='.$charge->id
                ]);
            } else {
                // En caso de pagos pendientes (como 3D Secure)
                echo json_encode([
                    'status' => 'pending',
                    'url' => $charge->payment_method->url
                ]);
            }
        } catch (OpenpayApiTransactionError $e) {
            // Errores específicos de la transacción (ej. fondos insuficientes)
            http_response_code(402);
            echo json_encode([
                'status' => 'error',
                'error_code' => $e->getErrorCode(),
                'description' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Errores generales del sistema
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'description' => $e->getMessage()
            ]);
        }            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(6)));
        break;
    }      
}   
function quote_data($table_name,$db, $method, $id, $data){
    global $IDS;
    global $clienteId;
    switch ($method) {
        case 'GET': 
            $query = "select * FROM lead WHERE Id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->lead);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            $query = "select * FROM lead_detail WHERE IdLead = ".$lead['Id']." ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $lead_detailss = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $query = "select * FROM customers WHERE Id = ".$lead['Customer'];
            $stmt = $db->prepare($query);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);        
            $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
            $stmt = $db->prepare($query);
            $stmt->execute();
            $organization = $stmt->fetch(PDO::FETCH_ASSOC);
            $query = "select * FROM venues WHERE Id = ".$lead['Venue'];
            $stmt = $db->prepare($query);
            $stmt->execute();
            $venue = $stmt->fetch(PDO::FETCH_ASSOC);               
/*            
            $query = "
                SELECT 
                    ld.*, 
                    p.Name AS ProductName, 
                    pi.Image AS ProductImage
                FROM lead_detail ld
                LEFT JOIN products p ON p.Id = (CASE WHEN ld.IdProductRel > 0 THEN ld.IdProductRel ELSE ld.IdProduct END)
                LEFT JOIN products_images pi ON pi.Product = (CASE WHEN ld.IdProductRel > 0 THEN ld.IdProductRel ELSE ld.IdProduct END)
                WHERE ld.IdLead = :id_lead
                GROUP BY ld.Id
            ";
*/
            $query = "
                SELECT 
                    ld.*, 
                    p.Name AS ProductName, 
                    pi.Image AS ProductImage
                FROM lead_detail ld
                LEFT JOIN products p ON p.Id =  ld.IdProduct 
                LEFT JOIN products_images pi ON pi.Product = ld.IdProduct
                WHERE ld.IdLead = :id_lead
                GROUP BY ld.Id
            ";
            $stmt = $db->prepare($query);
            $stmt->execute(['id_lead' => $lead['Id']]);
            $lead_details = $stmt->fetchAll(PDO::FETCH_ASSOC);            
            $push ='';
            if ($lead_details) {
                foreach ($lead_details as $row) {
                    // Escapamos los datos para evitar errores de sintaxis JS
                    $urlImage = CFPUBLICURL."/".$clienteId."/products_images/thumbnails/". ($row['ProductImage'] ?? 'default.jpg');
                    $name     = addslashes($row['ProductName']);
                    $push.="
                        productos.push({
                            rentalname_url_photo: '{$urlImage}',
                            rentalname: '{$name}',
                            fullrentaltime: '',
                            rentalqty: '{$row['Quantity']}',
                            rentaltotalprice: '{$row['Price']}'
                        });
                    ";
                }
            }            
            $query = "
            SELECT
                lead_discounts.Id, 
                lead_discounts.IdLead, 
                lead_discounts.IdDiscount, 
                discounts.`Name`, 
                lead_discounts.Type, 
                lead_discounts.Amount, 
                lead_discounts.AmountVal
            FROM
                lead_discounts
                INNER JOIN
                discounts
                ON 
                    lead_discounts.IdDiscount = discounts.Id        
            WHERE lead_discounts.IdLead = ".$lead['Id']." ORDER BY lead_discounts.Id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);                
            //$query = "SELECT * FROM lead_discounts WHERE IdLead = ".$lead['Id']." AND Type = 'gifcard'";
            $query= "            
                SELECT
                    lead_discounts.Id, 
                    lead_discounts.IdLead, 
                    lead_discounts.IdDiscount, 
                    lead_discounts.Type, 
                    lead_discounts.Amount, 
                    lead_discounts.AmountVal, 
                    lead_discounts.Descript, 
                    gifcard.Amount as gifcardAmount
                FROM
                    lead_discounts
                    INNER JOIN
                    gifcard
                    ON 
                        lead_discounts.Descript = gifcard.`Code`
                WHERE  lead_discounts.IdLead = ".$lead['Id']." AND lead_discounts.Type = 'gifcard'
            ";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $gifcrds = $stmt->fetchAll(PDO::FETCH_ASSOC);               
            $respuesta = [
                'lead'         => $lead,
                'lead_details'      => $lead_detailss,
                'customer'     => $customer,
                'organization' => $organization,
                'venue'        => $venue,
                'script_push'  => $push,
                'discounts' => $discounts,
                'gifcrds' => $gifcrds
            ];            
            http_response_code(200);
            echo json_encode($respuesta);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}   
function tip_deposit($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $Tip = $data->tip;
            $APay = $data->apay;
            $Cotizacion = $data->quote;
            $Pq = $data->pq;
            $sql = "select * FROM account";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            $DiscuountVal = 0;
            $sql = "select SUM(AmountVal) as AmountVal FROM lead_discounts WHERE IdLead = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Cotizacion); 
            $stmt->execute();
            $AmountVal = $stmt->fetch(PDO::FETCH_ASSOC);   
            if ($AmountVal)
                $DiscuountVal = $AmountVal['AmountVal'];
            $sql = "select (Subtotal + TaxAmount) as Total, DepositAmount, Balance FROM lead WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Cotizacion); 
            $stmt->execute();
            $Lead = $stmt->fetch(PDO::FETCH_ASSOC);
            $Total = $Lead['Total'];
            $Total+= $Tip;
            if ($DiscuountVal > $Total)
                $Total = 0;
            else
                $Total = $Total - $DiscuountVal;
            //RECALCULAR EL DEPOSIT AMOUNT
            $DepoA = $Lead['DepositAmount'];;
            $BalDue = $Lead['Balance'];;
            if ($Total > 0){
                if ($account['DepositType'] == 'percentage'){
                    $Depo = $account['DepositAmount'];
                    $DepoA = ($Total * ($Depo / 100)); 
                }else{
                    $Depo = 0;
                    $DepoA = $account['DepositAmount']; 
                }
                if ($DepoA > $Total)
                    $DepoA = $Total;
                $BalDue = $Total - $DepoA;
            }            
            $query = "UPDATE lead SET Tip = ?, Total = ?, DepositAmount = ?, Balance = ? WHERE Id = ? ";        
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $Tip);
            $stmt->bindParam(2, $Total);
            $stmt->bindParam(3, $DepoA);
            $stmt->bindParam(4, $BalDue);
            $stmt->bindParam(5, $Cotizacion);
            $stmt->execute();
            if ($account['DepositType'] == 'percentage'){
                if ($APay > 0){
                    $query = "UPDATE lead SET Deposit = ?, DepositAmount = Total * ( ? / 100) WHERE Id = ? ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $APay);
                    $stmt->bindParam(2, $APay);
                    $stmt->bindParam(3, $Cotizacion);
                    $stmt->execute();
                    $query = "UPDATE lead SET BalanceQ = Total - DepositAmount  WHERE Id = ? ";        
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $Cotizacion);
                    $stmt->execute();
                }
            }else{
                if ($Pq > 0){
                    $query = "UPDATE lead SET Deposit = 0, DepositAmount = ? WHERE Id = ? ";
                    $stmt = $db->prepare($query);
                    //$stmt->bindParam(1, 0);
                    $stmt->bindParam(1, $Pq);
                    $stmt->bindParam(2, $Cotizacion);
                    $stmt->execute();
                    $query = "UPDATE lead SET BalanceQ = Total - DepositAmount  WHERE Id = ? ";        
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $Cotizacion);
                    $stmt->execute();
                }            
            }
            http_response_code(200);
            //echo json_encode($account);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}     
function quote_account($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "select * FROM account";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":uuid", $data->token); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($account);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}     
function document_center($table_name,$db, $method, $id, $data){
    global $IDS;    
    switch ($method) {
        case 'GET': 
            $sql = "select Template FROM document_center WHERE Tipo = :tipo AND IdTemplate = :template AND Activo = 1 AND Idioma = :idioma";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":tipo", $data->Tipo); 
            $stmt->bindValue(":template", $data->IdTemplate); 
            $stmt->bindValue(":idioma", $data->Idioma); 
            $stmt->execute();
            $document_center = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($document_center);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}      
function quotes($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "SELECT UUID,IdQuote,ExpDate,Status,Contrato FROM quotes WHERE UUID = :uuid AND Status = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":uuid", $data->token); 
            $stmt->execute();
            $quotes = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($quotes) {
                // Convertimos el contenido a base64 antes de devolverlo
                $quotes['Contrato'] = base64_encode($quotes['Contrato']);
            }            
            http_response_code(200);
            echo json_encode($quotes);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}      
function account($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "SELECT * FROM account";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "account"=>$account
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}            
function sendbook($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                
            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '7' AND Idioma = '$lng'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);
            //RECUPERAR Quote
            $sql = "SELECT IdQuote FROM quotes WHERE UUID = :uuid";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":uuid", $data->UUID); 
            $stmt->execute();
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);
            $pdfBinary = base64_decode($data->PDF);
            $stmt = $db->prepare("UPDATE quotes SET Contrato = ? WHERE UUID = ?");
            $stmt->execute([$pdfBinary,$data->UUID]);
            //RECUPERAR Lead
            $sql = "SELECT * FROM lead WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $quote['IdQuote']); 
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            //RECUPERAR Customer
            $sql = "SELECT * FROM customers WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $lead['Customer']); 
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            //RECUPERAR venue
            $sql = "SELECT * FROM venues WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $lead['Venue']); 
            $stmt->execute();
            $venue = $stmt->fetch(PDO::FETCH_ASSOC);            
            $header = "";
            //$header = "MIME-Version: 1.0\r\n";
            //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
            $header .= $Template['Nombre']."\r\n";            
                        // Incluimos el teléfono en el cuerpo del correo
            $cuerpo = "<html>".$Template['Template']."</html>";
            $valores = [
                'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $customer['Nombres'],
                'leadid'       => $lead['Folio'],
                'total'  => $lead['Total'],
                'apayment'  => $lead['DepositAmount'],
                'balancedue'  => $lead['Balance'],
                'link_to_accept'  => $account['WebSite']."makepayment.php?Id=".$data->UUID,
                'eventstreet' => $venue['Direccion'],
                'eventcity'    => $venue['Ciudad'],
                'startdate'  => $lead['StartDateTime'],
                'company_name'  => $account['NombreCompania'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],
            ];            
            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
            $mail['correo'] = $customer['Correo'];
            $mail['archivo_base64'] = $data->PDF;
            $mail['nombre_archivo'] = $data->UUID.'.pdf';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';
            $mail = (object) $mail;            
            sendmail('',$db, 'POST', '', $mail);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
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
            if (isset($data->echo))
                return;
            http_response_code(200);
            echo json_encode([
                "send" => true,
                "status" => $resultado['status'],
                "message"=>$resultado['message']." ".$data->correo
            ]);
        } catch (PDOException $e) {
            http_response_code(405);
            echo json_encode([
                "send" => false,
                "status" => 'fail',
                "message"=>$e->getMessage()
            ]);
        }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}
function products($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            /*
            $sql = "SELECT * FROM products WHERE Name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            */
            $sql = "SELECT * FROM products WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $data->IdP); 
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);            
            foreach ($productos as $product) {
                $sql = "SELECT Image FROM products_images WHERE Product = :idproduct AND Orden = 1";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct", $product['Id']); 
                $stmt->execute();
                $Image = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            foreach ($productos as $product) {
                $sql = "SELECT Image FROM products_images WHERE Product = :idproduct ORDER BY Orden";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }   
            
            foreach ($productos as $product) {
                $sql = "SELECT * FROM products_videos WHERE Product = :idproduct ORDER BY Title";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }               
            
            foreach ($productos as $product) {
                $sql = "SELECT Producto_rp, Producto_r, Image, 'Nombre producto extra' as Name, '122' as Price, '0' as Quantity  FROM v_related_products WHERE Producto_rp = :idproduct ";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Accesories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($Accesories) {
                foreach ($Accesories as $index =>$Acc ) {
                    $resultados_p = precios($data,$Accesories[$index]['Producto_r']);
                    if ($resultados_p) {
                        $Accesories[$index]['Price'] = $resultados_p[0]['Price'];
                        $Accesories[$index]['Name'] = $resultados_p[0]['ProductName'];
                        $Accesories[$index]['Quantity'] = $resultados_p[0]['Quantity'];
                    }
                }   
            }         
            foreach ($productos as $product) {
                $sql = "
                    SELECT
                        v_upselling_products.Producto_up, 
                        v_upselling_products.Producto_rup, 
                        v_upselling_products.`Name`, 
                        products_images.Orden, 
                        products_images.Image,
                        '0' as Price,
                        '0' as Quantity 
                    FROM
                        v_upselling_products
                        left outer JOIN
                        products_images
                        ON 
                            v_upselling_products.Producto_rup = products_images.Product
                        WHERE Orden = 1 AND Producto_up = :idproduct";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $UpSelling = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }                 
            if ($UpSelling) {
                foreach ($UpSelling as $index =>$Ups ) {
                    $resultados_p = precios($data,$UpSelling[$index]['Producto_rup']);
                    if ($resultados_p) {
                        $UpSelling[$index]['Price'] = $resultados_p[0]['Price'];
                        $UpSelling[$index]['Name'] = $resultados_p[0]['ProductName'];
                        $UpSelling[$index]['Quantity'] = $resultados_p[0]['Quantity'];
                    }
                }   
            }   
            //$DateS_raw = isset($_GET['DateS']) ? $_GET['DateS'] : date('Y-m-d\TH:i');
            //$DateE_raw = isset($_GET['DateE']) ? $_GET['DateE'] : date('Y-m-d\TH:i');
            $resultados_p = precios($data,$product['Id']);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos,
                "Image" => $Image,
                "Images" => $Images,
                "Videos" => $Videos,
                "Accesories" => $Accesories,
                "UpSelling"=>$UpSelling,
                "Resultadosp"=>$resultados_p
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function products_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            /*
            $sql = "SELECT * FROM products WHERE Name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            */
            $sql = "SELECT * FROM v_products WHERE Id = :id AND Active = 1 AND For_Sale = 1 GROUP BY Id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $data->IdP); 
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);            
            foreach ($productos as $product) {
                $sql = "SELECT Image FROM  products_images_sale WHERE Product = :idproduct AND Orden = 1";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct", $product['Id']); 
                $stmt->execute();
                $Image = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            foreach ($productos as $product) {
                $sql = "SELECT Image FROM products_images_sale WHERE Product = :idproduct ORDER BY Orden";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }   
            
            foreach ($productos as $product) {
                $sql = "SELECT * FROM products_videos WHERE Product = :idproduct ORDER BY Title";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }               
            
            foreach ($productos as $product) {
                $sql = "SELECT Sum(Quantity_for_sale) as Quantity FROM inventory_stock WHERE Id_product = :idproduct AND Active = 1";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }            


            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos,
                "Image" => $Image,
                "Images" => $Images,
                "Videos" => $Videos,
                "Stock" => $Stock,
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function newsletter($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            
                //echo $data->email;
                //print_r($data) ;
                // 1. Obtener el valor de $_POST de forma segura
                $email_raw = isset($data->email) ? trim($data->email) : '';

                // 2. Decodificar por si acaso llegó con caracteres de URL (como %40)
                $email_decoded = urldecode($email_raw);

                // 3. Validar el correo limpio
                $email = filter_var($email_decoded, FILTER_VALIDATE_EMAIL);

                if (!$email) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'El correo electrónico no es válido.'
                    ]);
                    exit;
                }          

                try {
                    // 1. Verificar si el correo ya existe
                    $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
                    $stmt->execute([$data->email]);
                    
                    if ($stmt->fetch()) {
                        // El correo ya está registrado
                        echo json_encode(['status' => 'exists', 'message' => 'Ya registrado']);
                        exit;
                    }

                    // 2. Si no existe, lo insertamos
                    $insert = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
                    $insert->execute([$data->email]);

                    echo json_encode(['status' => 'success', 'message' => 'Registrado correctamente']);

                } catch (\PDOException $e) {
                    // Error del servidor o base de datos
                    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
                }            

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}

function contact($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

        define('SHARED_KEY', 'TuClaveSecretaSuperSegura123!'); // Exactamente la misma clave

        //if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        //    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
        //    exit;
        //}
        //echo $data->captcha ." - ".$data->captcha_token;
        $captcha_input = isset($data->captcha) ? strtoupper(trim($data->captcha)) : '';
        $captcha_token = isset($data->captcha_token) ? trim($data->captcha_token) : '';

        // ... (Recibir y sanitizar el resto de campos: name, phone, email, message) ...

        if (empty($captcha_input) || empty($captcha_token)) {
            echo json_encode(['status' => 'captcha_error', 'message' => 'Falta la verificación de seguridad.']);
            exit;
        }

        // --- DESCRIFRAR EL TOKEN DEL CAPTCHA ---
        try {
            $token_decoded = base64_decode($captcha_token);
            list($iv, $encrypted_data) = explode('::', $token_decoded, 2);
            
            $decrypted = openssl_decrypt($encrypted_data, 'aes-128-cbc', SHARED_KEY, 0, $iv);
            $payload = json_decode($decrypted, true);
            
            if (!$payload || !isset($payload['code']) || !isset($payload['expires'])) {
                throw new Exception("Token inválido o corrupto.");
            }

            // 1. Validar expiración del tiempo
            if (time() > $payload['expires']) {
                echo json_encode(['status' => 'captcha_error', 'message' => 'El código captcha ha expirado. Por favor, intenta con uno nuevo.']);
                exit;
            }

            // 2. Validar que el texto coincida
            if ($captcha_input !== $payload['code']) {
                echo json_encode(['status' => 'captcha_error', 'message' => 'El código de verificación es incorrecto.']);
                exit;
            }

        } catch (Exception $e) {
            echo json_encode(['status' => 'captcha_error', 'message' => 'Error de validación de seguridad de datos.']);
            exit;
        }


        // 1. Recibir y limpiar campos primarios
        $name    = isset($data->name) ? trim(urldecode($data->name)) : '';
        $phone   = isset($data->phone) ? trim(urldecode($data->phone)) : '';
        $subject = isset($data->subject) ? trim(urldecode($data->subject)) : '';
        $message = isset($data->message) ? trim(urldecode($data->message)) : '';
        $captcha = isset($data->captcha) ? strtoupper(trim($data->captcha)) : '';

        // Validar correo de forma segura contra encoding de URL
        $email_raw = isset($data->email) ? trim($data->email) : '';
        $email     = filter_var(urldecode($email_raw), FILTER_VALIDATE_EMAIL);

        // 2. Validaciones estrictas
        if (empty($name) || empty($phone) || empty($subject) || empty($message) || empty($captcha)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        if (!$email) {
            echo json_encode(['status' => 'error', 'message' => 'El correo electrónico no es válido.']);
            exit;
        }

        // --- PROCESAR E INSERTAR EN LA BASE DE DATOS REMOTA ---
        try {
            // 4. Guardar en la base de datos
            $sql = "INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $phone, $email, $subject, $message]);



            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            $stmt->execute();		
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            $datosConexion = [
                'host'             => $account['ServidorS'],
                'username'         => $account['UsuarioS'],
                'password'         => $account['PasswordS'],
                'port'             => $account['PortS'],
                'encryption'       => '',
                'nombre_remitente' => $account['NombreCompania']
            ];
            $archivos = [];

            // Armamos el cuerpo del correo en HTML
            $body = "
            <html>
            <head>
            <title>Nuevo mensaje de contacto</title>
            </head>
            <body>
            <h2>Detalles del mensaje recibido:</h2>
            <table style='border-collapse: collapse; width: 100%; max-width: 600px;'>
                <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Nombre:</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>$name</td>
                </tr>
                <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Teléfono:</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>$phone</td>
                </tr>
                <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Email:</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>
                    <a href='mailto:$email?subject=Re: " . urlencode($subject) . "' style='color: #0066cc; text-decoration: underline;'>
                    $email
                    </a> <em>(Haz clic para responder)</em>
                </td>
                </tr>
                <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Asunto:</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>$subject</td>
                </tr>
                <tr>
                <td style='padding: 8px; valign: top;'><strong>Mensaje:</strong></td>
                <td style='padding: 8px;'>$message</td>
                </tr>
            </table>
            </body>
            </html>
            ";

            // Recuerda configurar las cabeceras (Headers) para que el correo se envíe como HTML
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';
            $headers[] = "From: Formulario Web <no-reply@tudominio.com>";            


            $resultado = enviarEmail(
                $datosConexion, 
                $account['CorreoS'], 
                implode("\r\n", $headers),
                $body,
                $archivos,
                '',
                ''
            );             



            echo json_encode(['status' => 'success', 'message' => '¡Tu mensaje ha sido enviado exitosamente!']);

        } catch (\PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el mensaje: ' . $e->getMessage()]);
        }


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}


function sale_comments($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $sort = isset($data->sort) ? $data->sort : 'relevant';
            $limit = isset($data->limit) ? intval($data->limit) : 10;

            if ($limit <= 0 || $limit > 50) $limit = 10;

            // Construir la consulta según el filtro solicitado
            if ($sort === 'recent') {
                // Más recientes primero
                $sql = "SELECT id, author_name, author_meta, rating, review_text, is_featured, created_at 
                        FROM sale_reviews 
                        WHERE status = 'approved' 
                        ORDER BY created_at DESC 
                        LIMIT :limit";
            } else {
                // Más relevantes: Primero destacados, luego mejores puntuaciones y finalmente fechas recientes
                $sql = "SELECT id, author_name, author_meta, rating, review_text, is_featured, created_at 
                        FROM sale_reviews 
                        WHERE status = 'approved' 
                        ORDER BY is_featured DESC, rating DESC, created_at DESC 
                        LIMIT :limit";
            }
            try {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();                
                $reviews = $stmt->fetchAll();
                
                // Añadimos una propiedad para calcular las iniciales del avatar de forma dinámica
                foreach ($reviews as &$review) {
                    $words = explode(' ', $review['author_name']);
                    $initials = '';
                    foreach ($words as $w) {
                        $initials .= mb_substr($w, 0, 1, 'UTF-8');
                    }
                    $review['avatar_initials'] = strtoupper(mb_substr($initials, 0, 2, 'UTF-8'));
                }
                $sql = "SELECT avg(rating) as AVER FROM sale_reviews WHERE status = 'approved'";
                $stmt = $db->prepare($sql);                
                $stmt->execute();                
                $avg = $stmt->fetch();

                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "count" => count($reviews),
                    "avg" => intval($avg['AVER']),
                    "data" => $reviews
                ]);

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error al procesar la consulta."]);
            }            


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}



function validate_token_comment($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            try {
                $stmt = $db->prepare("SELECT id, author_name, author_meta FROM sale_reviews WHERE id = ? AND access_token = ? AND token_used = 0");
                $stmt->execute([$data->id, $data->token]);
                $reviewData = $stmt->fetch(); 

                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "data" => $reviewData
                ]);

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error al procesar la consulta."]);
            }            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}


function set_comment($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            try {

                $stmt = $db->prepare("UPDATE sale_reviews SET rating = ?, review_text = ?, token_used = 1, status = 'pending' WHERE id = ?");
                $stmt->execute([$data->rating, $data->review_text, $data->id]);

                http_response_code(200);
                echo json_encode([
                    "status" => "success"
                ]);

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error al procesar la consulta."]);
            }            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}


function products_sale_stock($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "
            
                    SELECT
                        products.Id, 
                        products.`Name`, 
                        products.SalePrice, 
                        products.Discount, 
                        products_images_sale.Image, 
                        sum(inventory_stock.Quantity_for_sale) as Quantity
                    FROM
                        products
                        INNER JOIN
                        products_images_sale
                        ON 
                            products.Id = products_images_sale.Product
                        INNER JOIN
                        inventory_stock
                        ON 
                            products.Id = inventory_stock.Id_product
                    WHERE
                        products.Active = 1 AND
                        products.For_Sale = 1 AND
                        products_images_sale.Orden = 1 AND
                        inventory_stock.Quantity_for_sale > 0 AND 
                        inventory_stock.Active = 1
                        GROUP BY inventory_stock.Id_product
                        ORDER BY 
                        RAND()
                    LIMIT 4           

            ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}

function products_sale_hero($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "
                    SELECT
                        products.Id, 
                        products.`Name`, 
                        products.SalePrice, 
                        products.Discount, 
                        products_images_sale.Image, 
                        sum(inventory_stock.Quantity_for_sale) as Quantity
                    FROM
                        products
                        INNER JOIN
                        products_images_sale
                        ON 
                            products.Id = products_images_sale.Product
                        INNER JOIN
                        inventory_stock
                        ON 
                            products.Id = inventory_stock.Id_product
                    WHERE
                        products.Active = 1 AND
                        products.For_Sale = 1 AND
                        products_images_sale.Orden = 1 AND
                        inventory_stock.Quantity_for_sale > 0 AND 
                        inventory_stock.Active = 1
                        GROUP BY inventory_stock.Id_product
                        ORDER BY 
                        RAND()
                    LIMIT 1            

            ";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $producto = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($producto as $prd) {
                $sql = "SELECT Image FROM products_images_sale WHERE Product = '".$prd['Id']."' AND ORden > 1 ORDER BY Orden LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($producto),
                "data" => $producto,
                "images" => $images
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}

function sales_customer_register($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

        $firstname = isset($data->firstname) ? trim($data->firstname) : '';
        $lastname  = isset($data->lastname) ? trim($data->lastname) : '';
        $email     = isset($data->email) ? filter_var(trim($data->email), FILTER_VALIDATE_EMAIL) : false;
        $phone     = isset($data->phone) ? preg_replace('/\s+/', '', $data->phone) : '';
        $state     = isset($data->state) ? trim($data->state) : '';
        $password  = isset($data->password) ? $data->password : '';
        $password_confirm = isset($data->password_confirm) ? $data->password_confirm : '';

        // 2. Validaciones estrictas del Backend
        if (empty($firstname) || empty($lastname) || empty($phone) || empty($state) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'La dirección de correo electrónico no es válida.']);
            exit;
        }

        if (strlen($phone) !== 10 || !ctype_digit($phone)) {
            echo json_encode(['success' => false, 'message' => 'El número de teléfono debe contener exactamente 10 dígitos numéricos.']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
            exit;
        }

        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas enviadas no coinciden.']);
            exit;
        }

        try {


            $stmtCheck = $db->prepare("SELECT id FROM sale_customers WHERE email = :email LIMIT 1");
            $stmtCheck->execute([':email' => $email]);
            
            if ($stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado con otra cuenta.']);
                exit;
            }


            $passwordHash = password_hash($password, PASSWORD_DEFAULT);


            $sqlInsert = "INSERT INTO sale_customers (firstname, lastname, email, phone, state, password) 
                        VALUES (:firstname, :lastname, :email, :phone, :state, :password)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $result = $stmtInsert->execute([
                ':firstname' => $firstname,
                ':lastname'  => $lastname,
                ':email'     => $email,
                ':phone'     => $phone,
                ':state'     => $state,
                ':password'  => $passwordHash
            ]);

            if ($result) {
                // Obtener el ID generado para el cliente recién creado
                $customerId = $db->lastInsertId();

                $sql = "SELECT id,firstname,lastname,email,phone,state FROM sale_customers WHERE id  = $customerId";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $sale_customer = $stmt->fetch(PDO::FETCH_ASSOC);

                // Respuesta exitosa para el Frontend
                echo json_encode([
                    'success' => true,
                    'sale_customer' => $sale_customer,
                    'message' => 'Tu perfil de distribuidor ha sido activado e iniciaste sesión con éxito.'
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudieron guardar los datos. Inténtalo de nuevo.']);
                exit;
            }

        } catch (PDOException $e) {
            // Log interno del error para depuración en desarrollo (no exponer detalles crudos al cliente)
            error_log("Error de Registro PDO: " . $e->getMessage());
            
            echo json_encode([
                'success' => false, 
                'message' => 'Ocurrió un error inesperado en el servidor de base de datos. Por favor, inténtalo más tarde.'
            ]);
            exit;
        }            




        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}            

function sales_customer_login($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 


$loginUser = isset($data->loginUser) ? trim($data->loginUser) : '';
$loginPass = isset($data->loginPass) ? $data->loginPass : '';            


try {


    // Buscar al cliente por correo electrónico
    $sql = "SELECT id, firstname, lastname, email, password FROM sale_customers WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $loginUser]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe y la contraseña coincide con el hash almacenado
    if ($user && password_verify($loginPass, $user['password'])) {

        $sql = "SELECT id,firstname,lastname,email,phone,state FROM sale_customers WHERE id  = ". $user['id'];
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $sale_customer = $stmt->fetch(PDO::FETCH_ASSOC);        


        echo json_encode([
            'success' => true,
            'sale_customer' => $sale_customer,
            'message' => 'Sesión iniciada correctamente.'
        ]);
        exit;
    } else {
        // Mensaje genérico para no dar pistas a atacantes si el correo existe o no
        echo json_encode([
            'success' => false,
            'message' => 'El correo electrónico o la contraseña son incorrectos.'
        ]);
        exit;
    }

} catch (PDOException $e) {
    error_log("Error de Login PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno en el servidor de accesos. Inténtalo más tarde.'
    ]);
    exit;
}            

            

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}    



function update_profile($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $firstname = $data->firstname;
            $lastname = $data->lastname;
            $phone = $data->phone;
            $customerId = $data->customerId;

            $stmt = $db->prepare("UPDATE sale_customers SET firstname = :f, lastname = :l, phone = :p WHERE id = :id");
            $stmt->execute([':f' => $firstname, ':l' => $lastname, ':p' => $phone, ':id' => $customerId]);            

            echo json_encode([
                'success' => true,
                'message' => 'Información de perfil actualizada con éxito.'
            ]);            

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}                

function get_orders($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            //$type = $data->type;
            //$statusCondition = ($type === 'process') ? "NOT IN ('Entregado', 'Cancelado')" : "= 'Entregado'";            
            $status_p = $data->type;
            $customerId = $data->customerId;
            
            $sql = "SELECT FechaCreacion, Ciudad, Estado, Balance, Total, Note1, Status, Status_p,Cart_json FROM v_sales  WHERE customer_id = :cid AND status_p = :status_p ORDER BY id DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':cid' => $customerId,':status_p'=>$status_p]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            //$orders = [];
            echo json_encode(['success' => true, 'data' => $orders]);

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}    

function get_addresses($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $customerId = $data->customerId;

            $stmt = $db->prepare("SELECT 
            a.id, 
            a.alias, 
            a.country, 
            a.state, 
            a.city, 
            a.street, 
            a.colonia, 
            a.zip, 
            a.reference, 
            a.is_default,
            CASE 
                WHEN UPPER(a.country) = 'MX' THEN 0.16
                ELSE COALESCE(t.StateRate, 0)
            END AS tax
        FROM sale_customer_addresses a
        LEFT JOIN taxrates_zip t 
            ON a.state = t.State 
           AND a.zip = t.Zip
        WHERE a.customer_id = :cid 
        ORDER BY a.is_default DESC, a.id DESC");
            $stmt->execute([':cid' => $customerId]);
            $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);          

            echo json_encode([
                'success' => true, 
                'data' => $addresses
            ]);            
            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}    

function create_address($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $customerId = $data->customerId;
            $action  = $data->action;
            $alias   = $data->alias;
            $country   = $data->country;
            $state   = $data->state;
            $city    = $data->city;
            $street  = $data->street;
            $colonia = $data->colonia;
            $zip     = $data->zip;
            $refs    = $data->refs;
            $addrId  = $data->addrId;

            // Validar si el cliente ya cuenta con direcciones previas (si es la primera, marcar como por defecto)
            $stmtCount = $db->prepare("SELECT COUNT(*) FROM sale_customer_addresses WHERE customer_id = :cid");
            $stmtCount->execute([':cid' => $customerId]);
            $hasAddresses = (int)$stmtCount->fetchColumn() > 0;
            $isDefault = $hasAddresses ? 0 : 1;

            if ($action === 'create_address') {
                $sql = "INSERT INTO sale_customer_addresses (customer_id, alias, country, state, city, street, colonia, zip, reference, is_default) 
                        VALUES (:cid, :alias, :country, :state, :city, :street, :colonia, :zip, :refs, :is_default)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':cid' => $customerId, ':alias' => $alias, ':country' => $country ,':state' => $state, ':city' => $city,
                    ':street' => $street, ':colonia' => $colonia, ':zip' => $zip, ':refs' => $refs, ':is_default' => $isDefault
                ]);
                $msg = "Nueva dirección agregada con éxito.";
            } else {
                // Verificar pertenencia por seguridad antes de actualizar
                $sql = "UPDATE sale_customer_addresses SET alias = :alias, country = :country, state = :state, city = :city, street = :street, 
                        colonia = :colonia, zip = :zip, reference = :refs 
                        WHERE id = :aid AND customer_id = :cid";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':alias' => $alias, ':country' => $country, ':state' => $state, ':city' => $city, ':street' => $street,
                    ':colonia' => $colonia, ':zip' => $zip, ':refs' => $refs, ':aid' => $addrId, ':cid' => $customerId
                ]);
                $msg = "Dirección de envío actualizada.";
            }

            echo json_encode(['success' => true, 'message' => $msg]);

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}    

function delete_address($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            
            $customerId = $data->customerId;
            $addrId  = $data->addrId;            

            // 1. REQUISITO DE VALIDACIÓN CRÍTICO: Contar cuántas direcciones tiene actualmente el usuario
            $stmtCount = $db->prepare("SELECT COUNT(*) FROM customer_addresses WHERE customer_id = :cid");
            $stmtCount->execute([':cid' => $customerId]);
            $totalAddresses = (int)$stmtCount->fetchColumn();

            if ($totalAddresses <= 1) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No puedes eliminar esta dirección. Debes mantener al menos una dirección registrada para tus entregas.'
                ]);
                exit;
            }

            // 2. Verificar si la dirección a borrar es la "Principal" (is_default) para delegar el rol a otra
            $stmtCheckDefault = $db->prepare("SELECT is_default FROM customer_addresses WHERE id = :aid AND customer_id = :cid");
            $stmtCheckDefault->execute([':aid' => $addrId, ':cid' => $customerId]);
            $isDefault = (int)$stmtCheckDefault->fetchColumn();

            // 3. Ejecutar borrado seguro asegurando propiedad del id
            $stmtDel = $db->prepare("DELETE FROM customer_addresses WHERE id = :aid AND customer_id = :cid");
            $stmtDel->execute([':aid' => $addrId, ':cid' => $customerId]);

            // 4. Si borramos la principal, convertimos de manera automática otra dirección aleatoria en predeterminada
            if ($isDefault === 1) {
                $stmtSetDefault = $db->prepare("UPDATE customer_addresses SET is_default = 1 WHERE customer_id = :cid LIMIT 1");
                $stmtSetDefault->execute([':cid' => $customerId]);
            }

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }       
}    

function get_discounts($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "select * FROM discounts WHERE DateExp > now() AND Active = 1 AND (Used < Quantity OR Unlimited = 1) AND IntExt = '1' ORDER BY Name";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $discounts
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function get_all_sales($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            // 1. Recibir y sanitizar los parámetros
            $pagina = isset($data->pagina) ? (int)$data->pagina : 1;
            $registros_por_pagina = isset($data->registros_por_pagina) ? (int)$data->registros_por_pagina : 10;
            $categoria = isset($data->categoria) ? $data->categoria : 'all';
            $scategoria = isset($data->scategoria) ? $data->scategoria : '';
            $orden = isset($data->orden) ? $data->orden : 'precio_menor';
            $search = isset($data->search) ? $data->search : '';

            // Calcular el desplazamiento (OFFSET) para el paginado
            if ($pagina < 1) $pagina = 1;
            $offset = ($pagina - 1) * $registros_por_pagina;

            // 2. Construir condiciones dinámicas (Filtro de categoría)
            $where_categoria = "";
            $params = [];

            if ($categoria !== 'all' && !empty($categoria)) {
                // Filtramos por el nombre de la categoría (o el ID si lo prefieres cambiar)
                if ($categoria == 'stock'){
                    $where_categoria = " AND inventory_stock.Quantity_for_sale > 0 ";
                    if ($scategoria !=""){
                        $where_categoria.= " AND v_products.Nombre = :categoria ";
                        $params[':categoria'] = $scategoria;                        
                    }    
                }
                elseif ($categoria == 'byrequest'){
                    $where_categoria = " AND v_products.OnlyRequest = 1 ";
                }                 
                elseif ($categoria == 'newdesign'){
                    $where_categoria = " AND v_products.NewDesign = 1 ";
                }                
                else{
                    $where_categoria = " AND v_products.Nombre = :categoria ";
                    $params[':categoria'] = $categoria;

                    if ($scategoria !=""){
                        $where_categoria.= " AND v_products.Nombre_sc = :scategoria ";
                        $params[':scategoria'] = $scategoria;                        
                    }
                        
                }
            }

            if ($search !== '' && !empty($search)) {
                // 1. Dejamos el marcador limpio en el SQL
                $where_categoria.= " AND v_products.`Name` LIKE :search ";
                
                // 2. Concatenamos los comodines '%' al valor real en PHP
                $params[':search'] = '%' . $search . '%';
            }         

//                    

            // 3. Determinar el ordenamiento basado en el precio neto (SalePrice - Discount)
            $order_by = " ORDER BY precio_neto ASC "; // Por defecto precio menor
            if ($orden === 'precio_menor') {
                $order_by = " ORDER BY precio_neto ASC ";
            }
            elseif ($orden === 'precio_mayor') {
                $order_by = " ORDER BY precio_neto DESC ";
            }
            elseif ($orden === 'destacados') {
                $order_by = " ORDER BY v_products.Featured DESC ";
            }
            elseif ($orden === 'nuevos') {
                $order_by = " ORDER BY v_products.NewDesign DESC ";
            }


            // 4. Consulta SQL principal con paginado y cálculo de precio neto
            $sql = "
                SELECT
                    v_products.Id, 
                    v_products.`Name`,
                    v_products.Nombre as Categoria, 
                    v_products.SalePrice, 
                    v_products.Discount,
                    (v_products.SalePrice - v_products.Discount) as precio_neto,
                    products_images_sale.Image, 
                    SUM(inventory_stock.Quantity_for_sale) as Quantity,
                    v_products.Featured,
                    v_products.NewDesign,
                    v_products.OnlyRequest
                FROM
                    v_products
                    INNER JOIN products_images_sale ON v_products.Id = products_images_sale.Product
                    INNER JOIN inventory_stock ON v_products.Id = inventory_stock.Id_product
                WHERE
                    v_products.Active = 1 AND
                    v_products.For_Sale = 1 AND
                    products_images_sale.Orden = 1 AND
                    inventory_stock.Active = 1
                    $where_categoria
                GROUP BY 
                    v_products.Id, 
                    v_products.`Name`,
                    v_products.Nombre, 
                    v_products.SalePrice, 
                    v_products.Discount,
                    products_images_sale.Image
                $order_by
                LIMIT :limit OFFSET :offset
            ";
            //echo $sql;
            $stmt = $db->prepare($sql);

            // Asignar parámetros del filtro si existen
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }

            // Los parámetros de LIMIT y OFFSET deben ser tratados estrictamente como enteros en PDO
            $stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 5. (Opcional pero recomendado) Obtener el total real de registros para que el frontend calcule las páginas totales
            $sql_total = "
                SELECT COUNT(DISTINCT v_products.Id) as total 
                FROM v_products
                INNER JOIN products_images_sale ON v_products.Id = products_images_sale.Product
                INNER JOIN inventory_stock ON v_products.Id = inventory_stock.Id_product
                WHERE v_products.Active = 1 AND v_products.For_Sale = 1 AND products_images_sale.Orden = 1 AND inventory_stock.Quantity_for_sale > 0 AND inventory_stock.Active = 1
                $where_categoria
            ";
            //echo $sql_total;
            $stmt_total = $db->prepare($sql_total);
            if ($categoria !== 'all' && !empty($categoria)) {
                if ($categoria == 'stock'){
                    
                    if ($scategoria !=""){
                        $stmt_total->bindValue(':categoria', $categoria);                 
                    }                   
                }
                elseif ($categoria == 'byrequest'){
                
                }                 
                elseif ($categoria == 'newdesign'){
                
                }   
                else{
                    $stmt_total->bindValue(':categoria', $categoria);
                    if ($scategoria !=""){
                        $stmt_total->bindValue(':scategoria', $scategoria);                    
                    }                    
                }                
                
            }

            if ($search !== '' && !empty($search)) {
                $stmt_total->bindValue(':search', '%' . $search . '%');
            }                   

            $stmt_total->execute();
            $total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "pagina_actual" => $pagina,
                "registros_por_pagina" => $registros_por_pagina,
                "total_registros" => (int)$total_registros,
                "total_paginas" => ceil($total_registros / $registros_por_pagina),
                "data" => $productos
            ]);
        break;    
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function get_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $sql = "SELECT * FROM payments_sale WHERE Folio = :folio AND TransactionId = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":folio", $data->Sale);
            $stmt->bindValue(":id", $data->Id);
            $stmt->execute();
            $Pay = $stmt->fetch(PDO::FETCH_ASSOC);               

            $sql = "SELECT 
                id,
                customer_id,
                address_id,
                balance,
                total_amount,
                cart_notes,
                payer_name,
                payer_lastname,
                payer_email,
                payment_method,
                gateway_token,
                device_fingerprint,
                cart_json,
                payment_status,
                status,
                IF(Contrato IS NULL, 0, 1) AS Contrato
             FROM sales WHERE id = :sale ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":sale", $Pay['IdSale']);
            $stmt->execute();
            $Sale = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT * FROM sale_customers WHERE id = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Sale['customer_id']);
            $stmt->execute();
            $Client = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT * FROM sale_customer_addresses WHERE id = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Sale['address_id']);
            $stmt->execute();
            $Address = $stmt->fetch(PDO::FETCH_ASSOC);            




            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "sale" => $Sale,
                "client" => $Client,
                "address" => $Address,
                "pay" => $Pay
                
            ]);
        break;    
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }                  
}



function get_sale_contract($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $sql = "SELECT Template FROM document_center WHERE Tipo ='contract' AND IdTemplate = '13' and Idioma = :lng ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":lng", $data->lng);
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);               
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "template" => $Template['Template']                
            ]);
        break;    
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }                  
}

function save_sale_contract($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $sql = "SELECT * FROM payments_sale WHERE Folio = :folio AND TransactionId = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":folio", $data->IdSale);
            $stmt->bindValue(":id", $data->TransactionId);
            $stmt->execute();
            $Pay = $stmt->fetch(PDO::FETCH_ASSOC);                           

            $pdfBinary = base64_decode($data->Contract);
            $stmt = $db->prepare("UPDATE sales SET Contrato = ? WHERE id = ?");
            $stmt->execute([$pdfBinary,$Pay['IdSale']]); 

            
            http_response_code(200);
            echo json_encode([
                "status" => "success"
            ]);
        break;    
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }                  
}


function sendsale($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 

            $sql = "SELECT * FROM payments_sale WHERE Folio = :folio AND TransactionId = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":folio", $data->IdSale);
            $stmt->bindValue(":id", $data->TransactionId);
            $stmt->execute();
            $Pay = $stmt->fetch(PDO::FETCH_ASSOC);               

            $sql = "SELECT 
                id,
                customer_id,
                address_id,
                balance,
                total_amount,
                cart_notes,
                payer_name,
                payer_lastname,
                payer_email,
                payment_method,
                gateway_token,
                device_fingerprint,
                cart_json,
                payment_status,
                status,
                Created_at
            FROM sales WHERE id = :sale ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":sale", $Pay['IdSale']);
            $stmt->execute();
            $Sale = $stmt->fetch(PDO::FETCH_ASSOC);


            $sql = "SELECT * FROM sale_customers WHERE id = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Sale['customer_id']);
            $stmt->execute();
            $Client = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT * FROM sale_customer_addresses WHERE id = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $Sale['address_id']);
            $stmt->execute();
            $Address = $stmt->fetch(PDO::FETCH_ASSOC);            



            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);            
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                
            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '12' AND Idioma = '$lng'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);


            //$header = "MIME-Version: 1.0\r\n";
            //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
            $header = $Template['Nombre']."\r\n";            
                        // Incluimos el teléfono en el cuerpo del correo
            $cuerpo = "<html>".$Template['Template']."</html>";
            
            $valores = [
                'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $Client['firstname']." ".$Client['lastname'],
                'leadid'       => $Sale['id'],
                'total'  => $Sale['total_amount'],
                'apayment'  => $Sale['total_amount'],
                'balancedue'  => $Sale['balance'],
                'link_to_accept'  => $account['WebSite']."/profile.php?tab=process",
                'eventstreet' => $Address['Street'],
                'eventcity'    => $Address['City'],
                'startdate'  => $Sale['Created_at'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],
            ];            
            
            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
            
            $mail['correo'] = $Client['email'];
            $mail['archivo_base64'] = $data->PDF;
            $mail['nombre_archivo'] = $data->TransactionId.'.pdf';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';
            $mail = (object) $mail;            
            sendmail('',$db, 'POST', '', $mail);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
}       


function categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT Id, Nombre, Imagen FROM categories WHERE IntExt = 1 ORDER BY Nombre ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function categories_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT Id, Nombre, Imagen FROM categories WHERE WebSale = 1 ORDER BY Nombre ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}

function scategories_sale($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT
                        categories.Id, 
                        categories.Nombre, 
                        categories.Imagen, 
                        scategories.IId, 
                        scategories.Nombre_sc	
                    FROM
                        categories
                        INNER JOIN
                        scategories
                        ON 
                            categories.Id = scategories.Id_Categoria
                    WHERE categories.WebSale = 1 AND scategories.WebSale_sc = 1
                    ORDER BY
                    categories.Nombre, 
                    scategories.Nombre_sc	 ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}



function password_recover($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
            try{
                $email= $data->email;
                $stmtUser = $db->prepare("SELECT id FROM sale_customers WHERE email = ?");
                $stmtUser->execute([$email]);
                if (!$stmtUser->fetch()) {
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'message' => 'Si el correo está registrado, recibirás un enlace.']);
                    exit;
                }
                
                $token = bin2hex(random_bytes(32));

                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmtDelete->execute([$email]);
                $stmtInsert = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmtInsert->execute([$email, $token, $expires_at]);


                $sql = "SELECT * FROM account ";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $account = $stmt->fetch(PDO::FETCH_ASSOC);  

                $datosConexion = [
                    'host'             => $account['ServidorS'],
                    'username'         => $account['UsuarioS'],
                    'password'         => $account['PasswordS'],
                    'port'             => $account['PortS'],
                    'encryption'       => '',
                    'nombre_remitente' => $account['NombreCompania']
                ];		            


                $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '10' AND Idioma = '$lng'";
                $stmt = $db->prepare($sql);

                $stmt->execute();
                $Template = $stmt->fetch(PDO::FETCH_ASSOC); 			
              
                $cuerpo = "<html>".$Template['Template']."</html>";            

                
                $recovery_link = URL_BASE_SALE . "/reset?email=$email&token=$token";

                $sql = "SELECT id, firstname, lastname, email, password FROM sale_customers WHERE email = :email LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([':email' => $email]);
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);            

                $header= $Template['Nombre']."\r\n";            

                $valores = [
                'ctfirstname' => $user['firstname']. ' ' .$user['lastname'],
                'user_email' => $email,
                'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                'company_name' => $account['NombreCompania'],
                'company_phone' =>  $account['TelefonoOficina'],
                'reset_password_link' => $recovery_link
                ];       
                $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
    
                $resultado = enviarEmail(
                    $datosConexion, 
                    $email, 
                    $header,
                    $cuerpo,
                    [],
                    '',
                    ''
                );
    
                if ($resultado['status']) {
                //if (1==1) {
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'message' => 'El enlace de recuperación ha sido generado con éxito.']);

                } else {
                    http_response_code(200);
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo electrónico. Inténtalo más tarde.']);
                }   
            } catch (PDOException $e) {
                die(Trd(3) . $e->getMessage());
            }                     
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}


function validate_token($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $email= $data->email;
            $token= $data->token;
            $stmt = $db->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
            $stmt->execute([$email, $token]);
            if (!$stmt->fetch()) {
                http_response_code(200);
                echo json_encode(['status' => 'error', 'message' => "No existe o no vigente."]);
                exit;
            }
            else{
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Existe y vigente.']);
                exit;
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}


function password_resets($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            $email = $data->email;
            $token = $data->token;
            $password = $data->password;

            $stmtCheck = $db->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
            $stmtCheck->execute([$email, $token]);
            
            if (!$stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'status' => 'error', 'message' => 'El enlace de recuperación ha expirado o ya no es válido.']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmtUpdate = $db->prepare("UPDATE sale_customers SET password = ? WHERE email = ?");
            $stmtUpdate->execute([$hashedPassword, $email]);

            $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmtDelete->execute([$email]);

            http_response_code(200);
            echo json_encode(['success' => true, 'status' => 'success', 'message' => "¡Tu contraseña ha sido actualizada con éxito! Ahora puedes iniciar sesión."]);
            exit;
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}





function surfaces($table_name,$db, $method, $id, $data){
    global $IDS;    
    switch ($method) {
        case 'POST': 
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT Id, Nombre FROM surfaces ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $surfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($surfaces),
                "data" => $surfaces
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}
function products_categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
/*
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "            
                SELECT
                    v_products_categories.Category, 
                    products_images.Product as IdProduct, 
                    products.`Name` as ProductName,
                    products_images.Image
                FROM
                    v_products_categories
                    INNER JOIN
                    products_images
                    ON 
                        v_products_categories.Product = products_images.Product
                    INNER JOIN
                    products
                    ON 
                        products_images.Product = products.Id
                WHERE
                    v_products_categories.Nombre = :name AND
                    products_images.Orden = 1            
            ";
            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);
            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            $stmt->bindValue(":name", $data->Category); 
            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();
            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/
            $sql = 'SELECT Id FROM categories WHERE Nombre = :name';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":name", $data->Category);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            $IdCat = $category['Id'];
            $DateS_raw = isset($data->SD) ? $data->SD."T".$data->SH : date('Y-m-d\TH:i');
            $DateE_raw = isset($data->ED) ? $data->ED."T".$data->EH  : date('Y-m-d\TH:i');
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
            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($resultados_p),
                "data" => $resultados_p
            ]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}
function get_accesories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $productoId = $_GET['producto_id'] ?? 0;
                $sql = "SELECT Producto_rp, Producto_r, Image, 'Nombre producto extra' as Name, '122' as Price  FROM v_related_products WHERE Producto_rp = :idproduct ";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$productoId); 
                $stmt->execute();
                $Accesories = $stmt->fetchAll(PDO::FETCH_ASSOC);            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "Accesories" => $Accesories
            ]);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(1)));
        break;
    }      
}
function process_quote($table_name,$db, $method, $id, $data){
    global $IDS;
    global $lng;
    switch ($method) {
        case 'POST': 
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => Trd(1)]);
                exit;
            }            
    $stmtCheck = $db->prepare("SELECT * FROM account");
    $stmtCheck->execute();
    $account = $stmtCheck->fetch(PDO::FETCH_ASSOC);    
    if ($data->cliente->Id == ""){
        try {
            $stmtCheck = $db->prepare("SELECT Id FROM customers WHERE Correo = ? LIMIT 1");
            $stmtCheck->execute([$data->cliente->correo]);
            $clienteExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if ($clienteExistente) {
                // --- ACTUALIZAR CLIENTE ---
                $idCliente = $clienteExistente['Id'];
                $sqlUpd = "UPDATE customers SET 
                            Nombres = ?, 
                            Apellidos = ?, 
                            NombreEmpresa = ?, 
                            TelefonoCelular = ?, 
                            Direccion = ?, 
                            Direccion2 = ?, 
                            Ciudad = ?, 
                            CP = ?,
                            FechaCambio = now() 
                        WHERE Id = ?";
                $stmtUpd = $db->prepare($sqlUpd);
                $stmtUpd->execute([
                    $data->cliente->nombre,
                    $data->cliente->apellidos ?? '', // Evitar nulls
                    $data->cliente->organizacion,
                    $data->cliente->telefono,
                    $data->cliente->direccion,
                    $data->cliente->colonia ?? '', // Usado como Direccion2
                    $data->cliente->ciudad,
                    $data->cliente->cp,
                    $idCliente
                ]);
                $mensaje = Trd(2);
                $Organization = ''; 
                $Customer = $idCliente;                    
            } else {
                // --- INSERTAR CLIENTE ---
                $sqlIns = "INSERT INTO customers (
                            Nombres, Apellidos, NombreEmpresa, Correo, TelefonoCelular, 
                            Direccion, Direccion2, Ciudad, CP, Pais,Estado, Lenguaje,Estatus,FechaCreacion,FechaCambio
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, '$lng', 'A',now(),now())";
                $stmtIns = $db->prepare($sqlIns);
                $stmtIns->execute([
                    $data->cliente->nombre,
                    $data->cliente->apellidos ?? '',
                    $data->cliente->organizacion,
                    $data->cliente->correo,
                    $data->cliente->telefono,
                    $data->cliente->direccion,
                    $data->cliente->colonia ?? '',
                    $data->cliente->ciudad,
                    $data->cliente->cp,
                    $account['Estado'],
                    $account['Pais']
                ]);
                $idCliente = $db->lastInsertId();
                $Organization = ''; 
                $Customer = $idCliente;                 
            }
            // Ahora ya tienes el $idCliente listo para usarlo en la tabla de reservaciones
            //echo "ID del cliente: " . $idCliente;
        } catch (PDOException $e) {
            die(Trd(3) . $e->getMessage());
        }
    }
    else{
        $idCliente = $data->cliente->Id;
        if ($data->cliente->Type == 'O' ){
            $Organization = $idCliente;
            $Customer = 0; 
        }
        else{
            $Organization = 0; 
            $Customer = $idCliente;        
        }
    }

try {
    // 1. Verificar si el lugar ya existe por Dirección y CP
    // Esto evita duplicar el mismo domicilio en la base de datos
    $stmtCheckVenue = $db->prepare("SELECT Id FROM venues WHERE Direccion = ? AND CP = ? LIMIT 1");
    $stmtCheckVenue->execute([
        $data->lugar->direccion,
        $data->lugar->cp
    ]);
    $venueExistente = $stmtCheckVenue->fetch(PDO::FETCH_ASSOC);
    if ($venueExistente) {
        // --- ACTUALIZAR LUGAR ---
        $idVenue = $venueExistente['Id'];
        $sqlUpdVenue = "UPDATE venues SET 
                        Direccion2 = ?, 
                        Ciudad = ?, 
                        Estado = ?, 
                        Pais = ? ,
                        FechaCambio = now()
                        WHERE Id = ?";
        $stmtUpdVenue = $db->prepare($sqlUpdVenue);
        $stmtUpdVenue->execute([
            $data->lugar->colonia, // Mapeado a Direccion2
            $data->lugar->ciudad , // Valor por defecto o del array
            $account['Estado'],
            $account['Pais'],
            $idVenue
        ]);
    } else {
        // --- INSERTAR LUGAR ---
        $sqlInsVenue = "INSERT INTO venues (
                            Nombre, Direccion, Direccion2, Ciudad, CP, Estado, Pais,FechaCreacion,FechaCambio
                        ) VALUES (?, ?, ?, ?, ?, ?, ?,now(),now())";
        $stmtInsVenue = $db->prepare($sqlInsVenue);
        $stmtInsVenue->execute([
            Trd(5) . $data->cliente->nombre, // Nombre descriptivo temporal
            $data->lugar->direccion,
            $data->lugar->colonia,
            $data->lugar->ciudad,
            $data->lugar->cp,
            $account['Estado'],
            $account['Pais']
        ]);
        $idVenue = $db->lastInsertId();
    }
    // El $idVenue ahora está listo para guardarse en la tabla 'reservaciones'
    // Ejemplo: $stmtReserva->execute([$idCliente, $idVenue, ...]);
} catch (PDOException $e) {
    die(Trd(4) . $e->getMessage());
}
            //{"ZIPO":"45640","CONO":"MX","ZIPD":"44840","COND":"MX"}
            //$data_dst =json_encode(["ZIPO" =>$account['CP'],"CONO" =>$account['Pais'],"ZIPD" => $data->lugar->cp,"COND" => $account['Pais']]);
            $arreglo = [
                "ZIPO" => $account['CP'],
                "CONO" => $account['Pais'],
                "ZIPD" => $data->lugar->cp, // (asumiendo que $data venía de otro lado)
                "COND" => $account['Pais']
            ];
            // Convertimos el arreglo a objeto
            $data_dst = (object) $arreglo;    
            $Costo_Distancia = 0;
            $Distance = '';
            $Distance = distance_charge($table_name,$db, $method, $id, $data_dst);
            $Distance = json_decode($Distance);
            //$Nt1 =  $data->lugar->cp." ".$Distance->cost->costo_total." ".$Distance->cost->total_millas." ".$Distance->cost->taxrate;
            // Acceder al valor
            $Costo_Distancia = $Distance->cost->costo_total;
            $TaxPc = $Distance->cost->taxrate; 
            $TaxAm = 0; 
            //TOTAL DE ITEMS
            $TotalI = 0;
            //TOTAL QUE APLICA A PROPINA
            $TotalTip = 0;
            //Costo de Operacion!
            $StaffCost = 0;
            $stmt = $db->prepare("SELECT OperationCost, TipApply FROM products WHERE id = :id LIMIT 1");
            foreach ($data->reserva->items as $item) {
                $TotalI+= $item->cantidad * $item->precio;
                $stmt->execute(['id' => $item->id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product && $product['TipApply'] == 1) {
                    $TotalTip+= $item->cantidad * $item->precio;
                }
                if (!empty($item->adicionales)) {
                    foreach ($item->adicionales as $extra) {
                        $TotalI+= $extra->precio * $item->cantidad ;
                        $stmt->execute(['id' => $extra->id]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($product && $product['TipApply'] == 1) {
                            $TotalTip+= $extra->precio * $item->cantidad ;
                        }
                    }
                }
            }
            //FOLIO DE RESERVA
            $Folio = 0;    
            $IdBranch = 1;
            $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Lead'");
            $stmt->execute([$IdBranch]);
            $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($Payments){
                $Folio = $Payments['Folio'];
            }
            $Folio+=1;            
            $stmt = $db->prepare(" UPDATE folios SET Folio = ? WHERE IdBranch = ? AND Type = 'Lead'");
            $stmt->execute([$Folio,$IdBranch]);            
            $SubtotalGral = ($TotalI + $Costo_Distancia + $StaffCost);
            //SI APLICA ALGUN CUPON
            $Cupon = $data->cupon->cupon;
            $TipoCupon = $data->cupon->tipocupon;
            $DescuentoCupon = $data->cupon->descuento;
            $MontoCupon= 0;
            if ($Cupon != ""){
                if ($TipoCupon == 'percentage' ){
                    $MontoCupon = $SubtotalGral * ($DescuentoCupon / 100);
                }
                else{
                    $MontoCupon = $DescuentoCupon;
                }
            }                
            $Subtotal = $SubtotalGral - $MontoCupon;
            //RECUPERA EL GIFCARD
            $MontoGC= 0;
            if ($data->cupon->idgfc != ""){
                $MontoGC = $data->cupon->agfc;
            }
            if ($MontoGC > $Subtotal ){
                $Subtotal = 0;
            }
            else{
                $Subtotal = $Subtotal - $MontoGC;
            }
            //IMPUESTOS APLICABLES!!
            $TaxId = ''; 
            //$TaxPc = 0; 
            //$TaxAm = 0; 
            //$Total = 0;
            $fechas = explode(' to ', $data->reserva->fecha);
            $FHI = $fechas[0] .' '. $data->reserva->hInicio; 
            $FHF = $fechas[1] .' '. $data->reserva->hFin;                 
            $Referal = '';
            $OkT = 0; 
            $WA = 0; 
            $AE = 0; 
            $ME = 0; 
            $CusNt = ''; 
            $Venue = $idVenue; 
            $EventName = ''; 
            $Surface = $data->lugar->superficie;
            $Delivety = $data->lugar->tipo_entrega; 
            $Nt1 = ''; 
            $Nt2 = ''; 
            $Item_Totals = $TotalI; 
            $ChkDstC = 1; 
            $DstC = $Costo_Distancia; 
            $ChkStCs = 1; 
            $StCs = $StaffCost; 
            $ChkDsc = 0; 
            $Dsc = 0;
            $SubT = $SubtotalGral;
            $TaxAm = $SubT * $TaxPc;
            $Total = $Subtotal + $TaxAm; 
            $STTAm = $Subtotal + $TaxAm;
            //SECCION PARA ANTICIPO
            $BalDue = 0;
            $DepoA = 0;            
            if ($Total > 0){
                if ($account['DepositType'] == 'percentage'){
                    $Depo = $account['DepositAmount'];
                    $DepoA = ($STTAm * ($Depo / 100)); 
                }else{
                    $Depo = 0;
                    $DepoA = $account['DepositAmount']; 
                }
                if ($DepoA > $STTAm)
                    $DepoA = $STTAm;
                $BalDue = $STTAm - $DepoA;
            }
            $Status = 'Pending';
            $IdBranch = 1;
            $sqlLead = "INSERT INTO lead (
                StartDateTime, EndDateTime, DeliveryDateTime, Organization, Customer, 
                Referal, OkT, WA, AE, ME, 
                CustomerNote, Venue, EventName, Surface, Delivery, 
                Note1, Note2, ItemTotals, ChkDstC, DistanceCharges, 
                ChkStCs, StafCost, ChkDsc, Discount, SubTotal, 
                TaxId, TaxPc, TaxAmount, Total, Deposit, 
                DepositAmount, Balance, Status, FechaCreacion, FechaCambio, 
                IdBranch, Folio, TotalBT
            ) VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, now(), now(), 
                ?, ?, ?
            )";
            $stmtLead = $db->prepare($sqlLead);
            // El array debe tener exactamente 36 elementos (los ? en el SQL)
            $stmtLead->execute([
                $FHI, $FHF, $FHI, $Organization, $Customer,        // 1-5
                $Referal, $OkT, $WA, $AE, $ME,                     // 6-10
                $CusNt, $Venue, $EventName, $Surface, $Delivety,   // 11-15
                $Nt1, $Nt2, $Item_Totals, $ChkDstC, $DstC,         // 16-20
                $ChkStCs, $StCs, $ChkDsc, $Dsc, $SubT,             // 21-25
                $TaxId, $TaxPc, $TaxAm, $Total, $Depo,             // 26-30
                $DepoA, $BalDue, $Status,                          // 31-33
                $IdBranch, $Folio, $TotalTip                          // 34-36
            ]);
            $idLead = $db->lastInsertId();
            $UUID = generar_uuid_v4();
            //$stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?,NOW() + INTERVAL '2 days',?)");
            $stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?, NOW() + INTERVAL 2 DAY, ?)");
            $stmt->execute([$UUID,$idLead,'A']);
            $sqlDetail = "INSERT INTO lead_detail (IdLead, IdProduct, IdProductRel, Quantity, Discount, Tax, Price, OrgPrice) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDetail = $db->prepare($sqlDetail);
            foreach ($data->reserva->items as $item) {
                $stmtDetail->execute([
                    $idLead, 
                    $item->id, 
                    '0', 
                    $item->cantidad, 
                    '0', 
                    '0', 
                    $item->precio * $item->cantidad,
                    $item->precio
                ]);                
                if (!empty($item->adicionales)) {
                    foreach ($item->adicionales as $extra) {
                        $Total+= $extra->precio * $item->cantidad ;
                        $stmtDetail->execute([
                            $idLead,
                            $extra->id,
                            $item->id,
                            $item->cantidad,
                            '0',
                            '0',
                            $extra->precio * $item->cantidad,
                            $extra->precio
                        ]);
                    }
                }
            }
        if ($Cupon!=''){
            $stmt = $db->prepare("select Id FROM discounts WHERE Code = ?");
            $stmt->execute([$Cupon]);
            $IdCupon = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $db->prepare("UPDATE discounts SET Used = Used + 1 WHERE Code = ?");
            $stmt->execute([$Cupon]);
            $UpdateCupon = $stmt->fetch(PDO::FETCH_ASSOC);            
            // --- 4. INSERTAR DESCUENTOS (detalle es un array de objetos) ---
            $sqlDiscounts = "INSERT INTO lead_discounts (IdLead, IdDiscount, Type, Amount,AmountVal) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmtDiscounts = $db->prepare($sqlDiscounts);
            $stmtDiscounts->execute([
                $idLead, 
                $IdCupon['Id'],
                $TipoCupon, 
                $DescuentoCupon,
                $MontoCupon
            ]);
        }
        if ($data->cupon->idgfc != ""){
            $sqlDiscounts = "INSERT INTO lead_discounts (IdLead, IdDiscount, Type, Amount,AmountVal,Descript) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtDiscounts = $db->prepare($sqlDiscounts);
            $stmtDiscounts->execute([
                $idLead, 
                $data->cupon->idgfc,
                'gifcard', 
                '0',
                $data->cupon->agfc,
                $data->cupon->gfc
            ]);        
        }
        $stmt = $db->prepare("select UUID FROM quotes WHERE IdQuote = ?");
        $stmt->execute([$idLead]);
        $Quotes = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Quotes){
            $UUID = $Quotes['UUID'];
        }
            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                
            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '6' AND Idioma = '$lng'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);        
            $header = "";
            //$header = "MIME-Version: 1.0\r\n";
            //$header .= "Content-Type: text/html; charset=UTF-8\r\n";
            $header .= $Template['Nombre']."\r\n";            
                        // Incluimos el teléfono en el cuerpo del correo
            $cuerpo = "<html>".$Template['Template']."</html>";
            $valores = [
                'company_logo'      => CFPUBLICURL."/".$account['Id']."/account/originals/".$account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $data->cliente->nombre,
                'eventcity'    => $data->lugar->ciudad,
                'leadid'       => $Folio,
                'startdate'  => $FHI,
                'total'  => $Total,
                'link_to_accept'  => $account['WebSite'].'quote.php?Id='.$UUID,
                'company_name'  => $account['NombreCompania'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],
            ];            
            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);
            $mail['correo'] = $data->cliente->correo;
            $mail['archivo_base64'] = '';
            $mail['nombre_archivo'] = '';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';
            $mail = (object) $mail;            
            sendmail('',$db, 'POST', '', $mail);
        http_response_code(200);
        echo json_encode(["status" => "success", "IdLead" => $idLead, "UUID" => $UUID]);
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(6)));
        break;
    }      
}
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
function precios($data,$product){
    global $db;
    //print_r($data);
    $DateS_raw = $data->SD . "T" . $data->SH;
    $DateE_raw = $data->ED . "T" . $data->EH;
    $objDateS = new DateTime($DateS_raw);
    $objDateE = new DateTime($DateE_raw);
    $fechaS = $objDateS->format('Y-m-d'); // 2026-02-04
    $horaS  = $objDateS->format('H:i');   // 18:00
    $fechaE = $objDateE->format('Y-m-d'); // 2026-02-05
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
        SELECT * FROM v_items_prices_lists
        WHERE Producto = :producto  AND 
                    Estatus_price_list = 1 AND
                    Estatus_price = 1 AND 
        :date BETWEEN  FechaHoraInicio AND FechaHoraFin  $DayWeek 
    ";                            
    $stmt = $db->prepare($query);
    $stmt->bindParam(':producto', $product, PDO::PARAM_INT);
    $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
    //$stmt->bindValue(1, $IdCat);
    //$stmt->bindValue(2, $Date);
    $stmt->execute();
    $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //echo $product." ".$fechaS."---".$DayWeek ;
    //print_r($resultados_p);    
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
    return $resultados_p;
}
function cart_update($resource,$db, $method, $id, $data){
    global $db;
    $itemsActualizados = [];
    foreach ($data->items as $item) {
        $resultados_p = precios($data,$item->id);
        //print_r($resultados_p);
        $itemActualizado = clone $item; // Clonar para no modificar el original
        $itemActualizado->precio = $resultados_p[0]['Price'] ?? $item->precio."*";
        $itemActualizado->existencia = $resultados_p[0]['Quantity'] ?? $item->existencia."*";
        $itemsActualizados[] = $itemActualizado;        
    }
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $itemsActualizados
    ]);     
}
function tnks($table_name,$db, $method, $id, $data){
    global $IDS; 
    switch ($method) {
        case 'GET': 
            //echo $data->idLead;
            //die();
            $idLead = $data->idLead;
            $idCheckout = $data->idCheckout;
            $stmt = $db->prepare("SELECT * FROM  opay_account");
            $stmt->execute();
            $opay_account = $stmt->fetch();           
            // Credenciales
            $merchantId = $opay_account['Id'];
            $privateKey = $opay_account['SecretKey'];
            $pagoRegistrado = false;
            $mensaje = "";
            try {
                // Intentamos consultar el checkout
                $url = "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts/$idCheckout";
                //echo $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $checkout = json_decode($response);
                // SI EL CHECKOUT DA 404, PERO TENEMOS UN ORDER_ID, BUSCAMOS POR TRANSACCION
                if ($httpCode == 404) {
                    $orderIdBusqueda = 'LIQ-' . $idLead.'-1';
                    $urlBusqueda = "https://sandbox-api.openpay.mx/v1/$merchantId/charges?order_id=" . $orderIdBusqueda;
                    //echo $urlBusqueda;
                    curl_setopt($ch, CURLOPT_URL, $urlBusqueda);
                    $responseBusqueda = curl_exec($ch);
                    $charges = json_decode($responseBusqueda);
                    if (!empty($charges) && $charges[0]->status == 'completed') {
                        // Si encontramos la transacción exitosa, simulamos el objeto checkout
                        $transactionId = $charges[0]->id;
                        $montoPagado = $charges[0]->amount;
                        $statusPago = 'completed';
                    } else {
                        $statusPago = 'not_found';
                    }
                } else {
                    $statusPago = $checkout->status;
                    $transactionId = $checkout->charge->id ?? null;
                    $montoPagado = $checkout->charge->amount ?? 0;
                }
                if ($statusPago == 'completed') {
                    $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
                    $stmt->execute([$idLead]);
                    $lead = $stmt->fetch();    
                    $Folio = 0;    
                    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$lead['IdBranch']]);
                    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($Payments){
                        $Folio = $Payments['Folio'];
                    }
                    $Folio+=1;    
                    $stmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE TransactionId = ?");
                    $stmt->execute([$transactionId]);
                    if ($stmt->fetchColumn() > 0) {
                        $pagoRegistrado = true;
                        $mensaje = Trd(1);
                    } else {
                        try {
                            $query = "INSERT INTO payments (IdLead,Type, Folio, DateTime, Platform, Amount, Currency, TransactionId, Estatus,Usuario) 
                                    VALUES (?,'Pay', ?, NOW(), 'OPENPAY_LINK', ?, 'MXN', ?, 'A', 'Link')";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$idLead, $Folio, $montoPagado, $transactionId]);
                            // 6. ACTUALIZAR ESTADO DEL LEAD A 'CONFIRMADO'
                            //$update = $db->prepare("UPDATE v_leads SET status = 'CONFIRMADO' WHERE Id = ?");
                            //$update->execute([$idLead]);
                            $pagoRegistrado = true;
                            $mensaje = Trd(2);
                        } catch (Exception $e) {
                            $mensaje = Trd(3) . $e->getMessage();
                        }
                        $stmt = $db->prepare("SELECT SUM(Amount) as Pagos FROM payments WHERE IdLead = ? AND Estatus = 'A'");
                        $stmt->execute([$idLead]);
                        $lead = $stmt->fetch();               
                        $query = "UPDATE lead SET Balance =  Total - ? WHERE   Id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$lead['Pagos'], $idLead]);            
                        $mensaje = Trd(4);
                    }        
                } else {
                    $mensaje = Trd(5);
                }
                curl_close($ch);
            } catch (Exception $e) {
                $mensaje = Trd(6) . $e->getMessage();
            }            
            http_response_code(200);
            echo json_encode([
                "status" => $pagoRegistrado,
                "mensaje" => $mensaje,
                "transactionId" => $transactionId
            ]);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(7)));
        break;
    }      
}
function tnks_square($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $idLead = $data->idLead;
            $stmt = $db->prepare("SELECT * FROM  square_account");
            $stmt->execute();
            $square_account = $stmt->fetch();     
            $accessToken = $square_account['Token'];
            $locationId  = $square_account['LocalId'];   
            $pagoRegistrado = false;
            $mensaje = "";
            $orderId = 'LIQ-' . $idLead.'-1'; 
            $square = new SquareClient(
                token: $accessToken,
                options: ['baseUrl' => 'https://connect.squareupsandbox.com']
            );
            $resultado = verificarPagoLink($square, $orderId,$db);
            if ($resultado['pagado'] == 'COMPLETED') {
                $transactionId = $resultado['transaccion_id'];
                $montoPagado = $resultado['monto'];
                $moneda = $resultado['moneda'];
                    // Marcar como pagado en tu BD
                    // $db->query("UPDATE leads SET pagado = 1 WHERE id_lead = ?", [$idLead]);
                    $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
                    $stmt->execute([$idLead]);
                    $lead = $stmt->fetch();    
                    $Folio = 0;    
                    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
                    $stmt->execute([$lead['IdBranch']]);
                    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($Payments){
                        $Folio = $Payments['Folio'];
                    }
                    $Folio+=1;    
                    $stmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE TransactionId = ?");
                    $stmt->execute([$transactionId]);
                    if ($stmt->fetchColumn() > 0) {
                        $pagoRegistrado = true;
                        $mensaje = Trd(1);
                    } else {
                        try {
                            $query = "INSERT INTO payments (IdLead,Type, Folio, DateTime, Platform, Amount, Currency, TransactionId, Estatus) 
                                    VALUES (?,'Pay', ?, NOW(), 'OPENPAY_LINK', ?, ?, ?, 'A')";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$idLead, $Folio, $montoPagado, $moneda, $transactionId]);
                            // 6. ACTUALIZAR ESTADO DEL LEAD A 'CONFIRMADO'
                            //$update = $db->prepare("UPDATE v_leads SET status = 'CONFIRMADO' WHERE Id = ?");
                            //$update->execute([$idLead]);
                            $pagoRegistrado = true;
                            $mensaje = Trd(2);
                        } catch (Exception $e) {
                            $mensaje = Trd(3) . $e->getMessage();
                        }
                    }        
                    $mensaje = Trd(4);    
                    //echo "🎉 ¡Gracias! Tu pago fue procesado exitosamente.";
            } else {
                $mensaje = Trd(5);
                //echo "⚠️ Aún no detectamos tu pago. Estado: " . $resultado['estado'];
            }            
            http_response_code(200);
            echo json_encode([
                "status" => $pagoRegistrado,
                "mensaje" => $mensaje,
                "transactionId" => $transactionId
            ]);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(6)));
        break;
    }      
}
function verificarPagoLink(SquareClient $square, string $orderId,$db): array
{

    global $lng;
	$Traducciones = Traducciones('verificarPagoLink',$lng,$db);

    $payments = $square->payments->list(
        new ListPaymentsRequest(['orderId' => $orderId])
    );
    foreach ($payments as $payment) {
        // Obtenemos el objeto Money
        $money = $payment->getAmountMoney();
        return [
            'pagado'         => $payment->getStatus(), // Ej: COMPLETED
            'estado'         => $payment->getStatus(),
            'transaccion_id' => $payment->getId(),
            'monto'          => $money->getAmount() / 100, // Convertir centavos a decimal
            'moneda'         => $money->getCurrency()      // Ej: USD, MXN, etc.
        ];    
    }
    return [
        'pagado'         => Trd(1),
        'estado'         => Trd(1),
        'transaccion_id' => null,
        'monto'          => 0,
        'moneda'         => null
    ];      
}

function distance($table_name,$db, $method, $id, $data){
    global $IDS; 
    switch ($method) {
        case 'POST': 
            $ZIPO = $data->{'ZIPO'};
            $CONO = $data->{'CONO'};
            $ZIPD = $data->{'ZIPD'};
            $COND = $data->{'COND'};
            if ($ZIPD!="" AND $ZIPD != $ZIPO){

                    $query = "select MAX(MaxM) as MAXM from distance_charges_distance";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    //$costo_extra = $stmt->fetchColumn();
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);


                $total_millas = get_distance("$ZIPO,$CONO","$ZIPD,$COND");
                if (str_starts_with($total_millas, 'Error')) {
                    echo Trd(1);
                }
                else{
                    $total_millas = str_replace(" mi", "", $total_millas);
                    $total_millas = str_replace(",", "", $total_millas);
                    $total_millas = $total_millas * 1;
                }            

                if ($total_millas > $data['MAXM']){
                    $respuesta = [
                        "success" => false,
                        "total_millas" => $total_millas,
                    ];
                }
                else{
                    $respuesta = [
                        "success" => true,
                        "total_millas" => $total_millas,
                    ];
                }

                    echo  json_encode(array(
                        "cost" => $respuesta
                    ));                 

            }
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
            if ($ZIPD!="" AND $ZIPD != $ZIPO){
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
                            echo Trd(1);
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
                    $respuesta = [
                        "status" => "success",
                        "total_millas" => $total_millas,
                        "costo_total" => round($costo_total, 2),
                        "taxrate" => $TaxRate
                    ];
                    return json_encode(array(
                        "cost" => $respuesta
                    ));  
                }
                else{
                    $respuesta = [
                        "status" => "success",
                        "total_millas" => 0,
                        "costo_total" => 0,
                        "taxrate" => 0
                    ];
                    return json_encode(array(
                        "cost" => $respuesta
                    ));                 
                }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(2)));
        break;
    }      
}
function validate_gifcard($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST':  
            //echo $data->tel;
            //echo $data->email;
            //echo $data->code;
            $Tipo = '';
            if ($data->tel!=""){
                $query = "SELECT * FROM organizations WHERE TelefonoCelular = :tel";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':tel', $data->tel, PDO::PARAM_STR);
                $stmt->execute();                    
                $organization = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$organization){
                    $query = "SELECT * FROM customers WHERE TelefonoCelular = :tel";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':tel', $data->tel, PDO::PARAM_STR);
                    $stmt->execute();                    
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($customer){
                        $Tipo = 'C';
                    }                    
                }         
                else{
                    $Tipo = 'O';
                }
            }
            else{
                $query = "SELECT * FROM organizations WHERE Correo = :email";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $data->email, PDO::PARAM_STR);
                $stmt->execute();                    
                $organization = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$organization){
                    $query = "SELECT * FROM customers WHERE Correo = :email";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':email', $data->email, PDO::PARAM_STR);
                    $stmt->execute();                    
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($customer){
                        $Tipo = 'C';
                    }                        
                }else{
                    $Tipo = 'O';
                }            
            }
            if ($Tipo == 'C'){
                $query = "SELECT * FROM gifcard  WHERE Code = :code AND CusType = :tipo AND Customer = :id AND Estatus = 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':code', $data->code, PDO::PARAM_STR);
                $stmt->bindParam(':tipo', $Tipo, PDO::PARAM_STR);
                $stmt->bindParam(':id', $customer['Id'], PDO::PARAM_INT);
                $stmt->execute();   
                $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$gifcard){
                    http_response_code(200);
                    echo json_encode(array("status"=>'error',"message" => Trd(1)." $Tipo ."));                        
                }else{
                    $fechaActual = new DateTime(); // Fecha de hoy
                    $fechaExpiracion = new DateTime($gifcard['FechaExpiracion']); // Fecha desde la DB
                    if ($fechaActual > $fechaExpiracion) {
                        // La tarjeta ya caducó
                        http_response_code(200);
                        echo json_encode(array(
                            "status" => "error", 
                            "message" => Trd(2). $fechaExpiracion->format('d/m/Y') . "."
                        ));
                    } else {
                        // La tarjeta es válida y está vigente
                        http_response_code(200);
                        echo json_encode(array(
                            "status" => "success",
                            "message" => Trd(3),
                            "data" => $gifcard, 
                            "tipo"=> $Tipo,
                            "customer" => $customer,
                        ));
                    }
                }
            }
            else if ($Tipo == 'O'){
                $query = "SELECT * FROM gifcard  WHERE Code = :code AND CusType = :tipo AND Customer = :id AND Estatus = 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':code', $data->code, PDO::PARAM_STR);
                $stmt->bindParam(':tipo', $Tipo, PDO::PARAM_STR);
                $stmt->bindParam(':id', $organization['Id'], PDO::PARAM_INT);
                $stmt->execute();
                $gifcard = $stmt->fetch(PDO::FETCH_ASSOC);   
                if (!$gifcard){
                    http_response_code(200);
                    echo json_encode(array("status"=>'error',"message" => Trd(1)." $Tipo."));                        
                }else{
                    $fechaActual = new DateTime(); // Fecha de hoy
                    $fechaExpiracion = new DateTime($gifcard['FechaExpiracion']); // Fecha desde la DB
                    if ($fechaActual > $fechaExpiracion) {
                        // La tarjeta ya caducó
                        http_response_code(200);
                        echo json_encode(array(
                            "status" => "error", 
                            "message" => Trd(2) . $fechaExpiracion->format('d/m/Y') . "."
                        ));
                    } else {
                        // La tarjeta es válida y está vigente
                        http_response_code(200);
                        echo json_encode(array(
                            "status" => "success",
                            "message" => Trd(3),
                            "data" => $gifcard, 
                            "tipo"=> $Tipo,
                            "customer" => $organization,
                        ));
                    }                        
                }                        
            }
            else{
                http_response_code(200);
                echo json_encode(array("status"=>'error',"message" => Trd(4)));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(5)));
        break;
    }      
}
function validate_coupon($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST':  
                //echo $data->IdCupon;
                $query = "SELECT Code, Type, Amount, Quantity, Used, Unlimited FROM discounts WHERE Code = :code AND Active = 1 AND now() <= DateExp ";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':code', $data->IdCupon, PDO::PARAM_STR);
                $stmt->execute();                    
                $discount = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($discount){
                    http_response_code(200);
                    if ($discount['Unlimited'] == 1){
                        echo json_encode(array("status"=>'true',"Message" => Trd(1),"code" => $discount['Code'],"type" => $discount['Type'],"val" => $discount['Amount']));
                    }
                    else{
                        if($discount['Used'] < $discount['Quantity']){
                            echo json_encode(array("status"=>'true',"Message" => Trd(1),"code" => $discount['Code'],"type" => $discount['Type'],"val" => $discount['Amount']));
                        }
                        else{
                            echo json_encode(array("status"=>'error',"Message" => Trd(2)));
                        }
                    }
                }
                else{
                    echo json_encode(array("status"=>'error',"Message" => Trd(2)));
                }

        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => Trd(3)));
        break;
    }      
}


function Traducciones($fnc,$lgn,$db){
    $query = "select Traduccion FROM  api_web_traduccion where Programa = ? AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $fnc);
    $stmt->bindValue(2, $lgn);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones[]='';
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones[]=$registro['Traduccion'];
        }
    }
    return $Traducciones;
}

function Traducciones_web($fnc,$lgn,$db){
    $query = "select Traduccion FROM  web_traduccion where Programa = ? AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $fnc);
    $stmt->bindValue(2, $lgn);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones[]='';
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones[]=$registro['Traduccion'];
        }
    }
    return $Traducciones;
}

function Traducciones_web_sales($fnc,$lgn,$db){
    $query = "select Traduccion FROM  web_sale_traduccion where Programa = ? AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $fnc);
    $stmt->bindValue(2, $lgn);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones[]='';
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones[]=$registro['Traduccion'];
        }
    }
    return $Traducciones;
}

function Trd($Idt){
    global $Traducciones;
    return $Traducciones[$Idt];
}


?>