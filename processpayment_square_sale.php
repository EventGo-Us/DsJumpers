<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
include_once 'api/process_op.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';

require 'vendor/autoload.php';

use Square\SquareClient;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
use Square\Exceptions\SquareApiException;
use Square\Exceptions\SquareException;

// ── Recibir token del frontend ─────────────────────────────────────────────────
//$input = json_decode(file_get_contents('php://input'), true);
$token_id =  $_POST['token_id'] ?? null;

if (!$token_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token de pago requerido']);
    exit;
}

    $tokenId    = $_POST['token_id'] ?? null;
    $IdLead      = $_POST['token'] ?? null;
    $deviceId   = $_POST['deviceIdHiddenFieldName'] ?? null;
    $amount     = $_POST['monto'] ?? 0;
    
    $amount = $amount * 100;

    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT * FROM  square_account");
    $stmt->execute();
    $square_account = $stmt->fetch();       

    $accessToken = $square_account['Token'];
    $locationId  = $square_account['LocalId'];

    $lead['IdBranch'] = 1;

    $stmt = $db->prepare("SELECT customer_id FROM sales WHERE id = ? ");
    $stmt->execute([$IdLead]);
    $sales = $stmt->fetch();    
    if ($sales){
        $stmt = $db->prepare("SELECT firstname as Nombres, lastname as Apellidos, email as Correo, phone as TelefonoCelular FROM  sale_customers WHERE id = ?");
        $stmt->execute([$sales['customer_id']]);
        $Client = $stmt->fetch();
    }  

    // Datos del cliente
    $customerData = [
        'name'      => $Client['Nombres'],
        'last_name' => $Client['Apellidos'],
        'email'     => $Client['Correo'],
        'phone_number' => $Client['TelefonoCelular'] ?? '5500000000'
    ];

// ── Inicializar cliente Square (v45) ───────────────────────────────────────────
$square = new SquareClient(
    token: $accessToken,
    options: ['baseUrl' => 'https://connect.squareup.com'] // sandbox
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
            'note' => 'Pago de anticipo/servicio - ' . $customerData['name'],
        ])
    );

    $payment = $response->getPayment();

    $Folio = 0;    
    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'PaySale'");
    $stmt->execute([$lead['IdBranch']]);
    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($Payments){
        $Folio = $Payments['Folio'];
    }
    $Folio+=1;    

    $amount = $amount / 100;

    $sqlPay = "INSERT INTO payments_sale (IdSale,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario,Type) 
                            VALUES  (?,?,now(),'Square',?,?,?,'A','','Pay')";
    $stmtPay = $db->prepare($sqlPay);
    $stmtPay->execute([$IdLead,$Folio,$amount,Currency::Usd->value,$payment->getId()]);    

    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'PaySale'");
    $stmt->execute([$Folio,$lead['IdBranch']]);        

    $stmt = $db->prepare(" UPDATE sales SET Balance = Balance - ? WHERE Id = ?");
    $stmt->execute([$amount, $IdLead]);

    //METER A OPERACION!!
    //process_op($IdLead,$db);
    //METER A OPERACION!!

    $query = "SELECT Id, Type, Folio, DateTime, Platform, Amount, Currency, TransactionId, Estatus, evidencia, Usuario FROM payments_sale WHERE IdSale = ? ORDER BY DateTime DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $IdLead);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);          

    echo json_encode([
        'success'    => true,
        'payment_id' => $payment->getId(),
        'status'     => $payment->getStatus(),
        'amount'     => $payment->getAmountMoney()->getAmount() / 100,
        'currency'   => $payment->getAmountMoney()->getCurrency(),
        'status' => 'success',
        'message' => '¡Pago realizado con éxito!',
        'transaction_id' => $payment->getId(),
        'pagos' => $payments
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
        'error'   => 'Error interno: ' . $e->getMessage(),
        'status' => 'error',
        'description' => $e->getMessage()
    ]);
}