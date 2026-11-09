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
try {
    // 2. Recibir datos del formulario

    $referencia = $_POST['orderID'];
    //$detalles = $_POST['detalles'];    

    $tokenId    = $_POST['token_id'] ?? null;
    $IdLead     = $_POST['token'] ?? null;
    $monto      = (float) ($_POST['monto'] ?? 0);
    //$referencia = $_POST['referencia'] ?? null;
    $tipo_pago  = $_POST['tipo-pago'] ?? null;
    $Currency = 'MXN';
    $ahora = date("Y-m-d H:i:s");

    if ($monto <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'description' => 'El monto debe ser mayor a 0']);
        exit;
    }

    $stmt = $db->prepare("SELECT extracharge, percentage FROM paypal_account LIMIT 1");
    $stmt->execute();
    $paypalAccount = $stmt->fetch(PDO::FETCH_ASSOC);
    $extraPctj = (int) ($paypalAccount['extracharge'] ?? 0) === 1
        ? (float) ($paypalAccount['percentage'] ?? 0) / 100
        : 0;

    $totalAmountCents = (int) round($monto * 100);
    $baseAmountCents = $extraPctj > 0
        ? (int) round($totalAmountCents / (1 + $extraPctj))
        : $totalAmountCents;
    $monto = $totalAmountCents / 100;
    $baseAmount = $baseAmountCents / 100;

    //$stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
    //$stmt->execute([$IdLead]);
    //$lead = $stmt->fetch();    

    $lead['IdBranch'] = 1;

    $Folio = 0;    
    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'PaySale'");
    $stmt->execute([$lead['IdBranch']]);
    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($Payments){
        $Folio = $Payments['Folio'];
    }
    $Folio+=1;

    $sqlPay = "INSERT INTO payments_sale (IdSale,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario,Type) 
                            VALUES  (?,?,now(),?,?,?,?,'A','','Pay')";
    $stmtPay = $db->prepare($sqlPay);
    $stmtPay->execute([$IdLead,$Folio,$tipo_pago,$monto,$Currency,$referencia]);    

    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'PaySale'");
    $stmt->execute([$Folio,$lead['IdBranch']]);        

    $stmt = $db->prepare(" UPDATE sales SET Balance = Balance - ? WHERE Id = ?");
    $stmt->execute([$baseAmount, $IdLead]);

    //METER A OPERACION!!
    //process_op($IdLead,$db);
    //METER A OPERACION!!    
    
    $query = "SELECT Id, Type, Folio, DateTime, Platform, Amount, Currency, TransactionId, Estatus, evidencia, Usuario FROM payments_sale WHERE IdSale = ? ORDER BY DateTime DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $IdLead);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    echo json_encode([
        'status' => 'success',
        'message' => '¡Pago realizado con éxito!',
        'pagos' => $payments
    ]);

} catch (\Exception $e) {
    // Errores generales del sistema
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'description' => $e->getMessage()
    ]);
} 


die();
if (isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];
    $detalles = $_POST['detalles'];

    // Aquí procesarías la información:
    // 1. Conectar a la base de datos.
    // 2. Validar el estado del pago: $detalles['status'] == 'COMPLETED'.
    // 3. Guardar el ID de transacción ($detalles['id']).
    // 4. Enviar un correo de confirmación.

    // Ejemplo de respuesta simple
    echo json_encode(['status' => 'success', 'message' => 'Orden recibida']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió el ID']);
}
?>