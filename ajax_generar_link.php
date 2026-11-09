<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idLead'])) {
    

    $idLead = $_POST['idLead'];
    $monto = $_POST['monto'];

    $stmt = $db->prepare("SELECT NombreCompania, WebSite, Currency FROM  account");
    $stmt->execute();
    $account = $stmt->fetch();           

    $stmt = $db->prepare("SELECT * FROM  opay_account");
    $stmt->execute();
    $opay_account = $stmt->fetch();     
    $merchantId = $opay_account['Id'];
    $privateKey = $opay_account['SecretKey'];
    $isSandbox  = $opay_account['sandbox'];
    $extra_pctj = (int) ($opay_account['extracharge'] ?? 0) === 1
        ? (float) ($opay_account['percentage'] ?? 0) / 100
        : 0;

    if ((float) $monto <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El monto debe ser mayor a 0']);
        exit;
    }

    $baseAmountCents = (int) round((float) $monto * 100);
    $extraAmountCents = $extra_pctj > 0
        ? (int) round($baseAmountCents * $extra_pctj)
        : 0;
    $totalAmountCents = $baseAmountCents + $extraAmountCents;
    $description = 'Liquidación de saldo - Lead #' . $idLead;
    if ($extraAmountCents > 0) {
        $description .= ' | Monto: $' . number_format($baseAmountCents / 100, 2)
            . ' ' . $account['Currency']
            . ' | Cargo por tarjeta: $' . number_format($extraAmountCents / 100, 2)
            . ' ' . $account['Currency']
            . ' (' . number_format($extra_pctj * 100, 2) . '%)';
    }

    $stmt = $db->prepare("SELECT Organization, Customer FROM  lead WHERE Id = ?");
    $stmt->execute([$idLead]);
    $lead = $stmt->fetch();
    if ($lead['Organization'] > 0){
        $stmt = $db->prepare("SELECT Nombre as Nombres, '' as Apellidos, Correo, TelefonoCelular FROM  organizations WHERE Id = ?");
        $stmt->execute([$lead['Organization']]);
        $Client = $stmt->fetch();
    }  
    else{
        $stmt = $db->prepare("SELECT Nombres, Apellidos, Correo, TelefonoCelular FROM  customers WHERE Id = ?");
        $stmt->execute([$lead['Customer']]);
        $Client = $stmt->fetch();
    }

    $Client['Correo'] = !empty($Client['Correo']) ? $Client['Correo'] : "contact@eventgo.solutions.com";    

    // El Order ID debe ser consistente para poder recuperarlo
    $orderId = 'LIQ-'.$idLead.'-1'; 
    $base_url = $isSandbox ? "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts" : "https://api.openpay.mx/v1/$merchantId/checkouts";
    //$base_url = "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts";

    // Función auxiliar para consultar a Openpay
    function callOpenpay($url, $method, $key, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ":");
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $httpCode, 'data' => json_decode($response)];
    }

    if ($Client['Apellidos'] ==''){
        // 1. Intentar crear el Checkout
        $payload = [
            'amount' => $totalAmountCents / 100,
            'currency' => $account['Currency'],
            'description' => $description,
            'order_id' => $orderId,
            'send_email' => false,
            'expiration_date' => date('Y-m-d H:i', strtotime('+3 days')),
            'redirect_url' => $account['WebSite']."tnks.php?IdLead=$idLead&token=".md5($idLead . $account['NombreCompania']),
                'customer' => [
                'name' => $Client['Nombres'],
                'email' => $Client['Correo'],
                'phone_number' => $Client['TelefonoCelular']
            ]
        ];    
   
    }
    else{
        // 1. Intentar crear el Checkout
        $payload = [
            'amount' => $totalAmountCents / 100,
            'currency' => $account['Currency'],
            'description' => $description,
            'order_id' => $orderId,
            'send_email' => false,
            'expiration_date' => date('Y-m-d H:i', strtotime('+3 days')),
            'redirect_url' => $account['WebSite']."tnks.php?IdLead=$idLead&token=".md5($idLead . $account['NombreCompania']),
                'customer' => [
                'name' => $Client['Nombres'],
                'last_name' => $Client['Apellidos'],
                'email' => $Client['Correo'],
                'phone_number' => $Client['TelefonoCelular']
            ]
        ];    
    
    }


    $res = callOpenpay($base_url, 'POST', $privateKey, $payload);

    // 2. Si ya existe (Error 409 o Error Code 2005)
    if ($res['code'] == 409 || (isset($res['data']->error_code) && $res['data']->error_code == 2005)) {
        
        // Consultamos la lista de checkouts filtrando por nuestro Order ID
        $searchUrl = $base_url . "?order_id=" . $orderId;
        $searchRes = callOpenpay($searchUrl, 'GET', $privateKey);

        if ($searchRes['code'] == 200 && !empty($searchRes['data'])) {
            // Buscamos el que esté disponible
            foreach ($searchRes['data'] as $checkout) {
                if ($checkout->order_id == $orderId && $checkout->status == 'available') {
                    echo json_encode([
                        'success' => true, 
                        'checkout_link' => $checkout->checkout_link,
                        'info' => 'Link recuperado'
                    ]);
                    exit;
                }
            }
        }
        
        // Si llegamos aquí es porque existe pero quizás ya está pagado o expirado
        echo json_encode(['success' => false, 'error' => 'La orden ya existe pero no está disponible para pago. Verifique su panel.']);
    } 
    // 3. Respuesta de éxito normal
    elseif ($res['code'] == 201 || $res['code'] == 200) {
        echo json_encode(['success' => true, 'checkout_link' => $res['data']->checkout_link]);
    } 
    // 4. Otros errores
    else {
        echo json_encode(['success' => false, 'error' => $res['data']->description ?? 'Error desconocido']);
    }
}

//http://localhost/eventgo_wpage/tnks.php?IdLead=77&token=24d546e9395e646948a48d6d4f5cbff2&id=tremplbyo0gcncklgh5j