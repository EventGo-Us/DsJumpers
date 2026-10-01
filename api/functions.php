<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function enviarEmail($config, $destinatario, $asunto, $mensaje, $adjuntos = [], $contenidoBinario = null, $nombreArchivo = "") {
    
    $mail = new PHPMailer(true);
    

    try {
        // --- Configuración del Servidor ---
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        //$mail->SMTPSecure = $config['encryption']; // 'tls' o 'ssl'
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';

        // --- Remitente y Destinatario ---
        $mail->setFrom($config['username'], $config['nombre_remitente']);
        $mail->addAddress($destinatario);

        // --- Adjuntos ---
        if (!empty($adjuntos)) {
            foreach ($adjuntos as $ruta) {
                if (file_exists($ruta)) {
                    $mail->addAttachment($ruta);
                }
            }
        }
        if ($nombreArchivo != "")
            $mail->addStringAttachment($contenidoBinario, $nombreArchivo);

        // --- Contenido ---
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;
        $mail->AltBody = strip_tags($mensaje); // Versión en texto plano

        $mail->send();
        return ["status" => true, "message" => "Correo enviado con éxito."];

    } catch (Exception $e) {
        return ["status" => false, "message" => "Error al enviar: {$mail->ErrorInfo}"];
    }
}

function generarHtmlCotizacion($html, $datos) {
    $busqueda = array_map(function($key) {
        return '*' . $key . '*';
    }, array_keys($datos));
    $sustitucion = array_values($datos);
    return str_replace($busqueda, $sustitucion, $html);
}

function dividirNombreCompleto($nombreCompleto) {
    // Limpiamos espacios extras
    $nombreCompleto = trim($nombreCompleto);
    $partes = explode(' ', $nombreCompleto);
    $numPartes = count($partes);

    $resultado = [
        'nombres' => '',
        'apellido_paterno' => '',
        'apellido_materno' => ''
    ];

    switch ($numPartes) {
        case 1:
            $resultado['nombres'] = $partes[0];
            break;
        case 2:
            $resultado['nombres'] = $partes[0];
            $resultado['apellido_paterno'] = $partes[1];
            break;
        case 3:
            $resultado['nombres'] = $partes[0];
            $resultado['apellido_paterno'] = $partes[1];
            $resultado['apellido_materno'] = $partes[2];
            break;
        default:
            // Para 4 o más partes, asumimos que los dos últimos son apellidos
            // y todo lo anterior son nombres.
            $resultado['apellido_materno'] = array_pop($partes);
            $resultado['apellido_paterno'] = array_pop($partes);
            $resultado['nombres'] = implode(' ', $partes);
            break;
    }

    return $resultado;
}



// ─── Carga imagen según MIME real ────────────────────────────────────────────
function cargarImagen(string $ruta): \GdImage|false {
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
    return match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($ruta),
        'image/png'  => imagecreatefrompng($ruta),
        'image/webp' => imagecreatefromwebp($ruta),
        'image/avif' => imagecreatefromavif($ruta),
        default      => false
    };
}

// ─── Calcula dimensiones proporcionales ──────────────────────────────────────
function calcularDimensiones(\GdImage $img, int $anchoDestino): array {
    $anchoOrig = imagesx($img);
    $altoOrig  = imagesy($img);
    if ($anchoDestino >= $anchoOrig) {
        $anchoDestino = $anchoOrig;
    }
    $altoDestino = (int) round(($anchoDestino / $anchoOrig) * $altoOrig);
    return [$anchoOrig, $altoOrig, $anchoDestino, $altoDestino];
}

// ─── 1. Thumbnail AVIF pequeño y liviano ─────────────────────────────────────
function generarThumbnailAVIF(\GdImage $original, string $destino, int $ancho = 150): bool {
    [$anchoOrig, $altoOrig, $anchoFinal, $altoFinal] = calcularDimensiones($original, $ancho);

    $lienzo = imagecreatetruecolor($anchoFinal, $altoFinal);
    imagealphablending($lienzo, false);
    imagesavealpha($lienzo, true);
    imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOrig, $altoOrig);

    $ok = imageavif($lienzo, $destino, 45);
    imagedestroy($lienzo);
    return $ok;
}

// ─── 2. Imagen normal AVIF buena calidad ─────────────────────────────────────
function generarNormalAVIF(\GdImage $original, string $destino, int $ancho = 1200): bool {
    [$anchoOrig, $altoOrig, $anchoFinal, $altoFinal] = calcularDimensiones($original, $ancho);

    $lienzo = imagecreatetruecolor($anchoFinal, $altoFinal);
    imagealphablending($lienzo, false);
    imagesavealpha($lienzo, true);
    imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOrig, $altoOrig);

    $ok = imageavif($lienzo, $destino, 82);
    imagedestroy($lienzo);
    return $ok;
}

// ─── 3. Thumbnail JPG pequeño y liviano ──────────────────────────────────────
function generarThumbnailJPG(\GdImage $original, string $destino, int $ancho = 150): bool {
    [$anchoOrig, $altoOrig, $anchoFinal, $altoFinal] = calcularDimensiones($original, $ancho);

    $lienzo = imagecreatetruecolor($anchoFinal, $altoFinal);

    // ✅ Fondo blanco ANTES del resampling (JPG no soporta transparencia)
    $fondo = imagecolorallocate($lienzo, 255, 255, 255);
    imagefill($lienzo, 0, 0, $fondo);

    imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOrig, $altoOrig);

    $ok = imagejpeg($lienzo, $destino, 55);
    imagedestroy($lienzo);
    return $ok;
}

function get_distance($ORG,$DST){

    $apiKey = GOOGLE_API_KEY;
    $cpOrigen = urlencode($ORG);
    $cpDestino = urlencode($DST);

    // Construir URL
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$cpOrigen}&destinations={$cpDestino}&units=imperial&key={$apiKey}";

    //echo "--- Iniciando prueba de conexión ---\n";

    // Realizar la petición
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // 1. Verificar errores de conexión o API Key
    if ($data['status'] !== 'OK') {
        //echo "❌ ERROR DE CONFIGURACIÓN:\n";
        //echo "Estado: " . $data['status'] . "\n";
        //if (isset($data['error_message'])) {
        //    echo "Mensaje: " . $data['error_message'] . "\n";
        //}
        //echo "Revisa: Que la API Key sea correcta y la 'Distance Matrix API' esté habilitada.\n";
        return 'Error al llamar la API';
    }

    // 2. Verificar si encontró la ruta
    $elemento = $data['rows'][0]['elements'][0];

    if ($elemento['status'] === 'OK') {
        //echo "✅ ¡CONEXIÓN EXITOSA!\n";
        //echo "------------------------------\n";
        //echo "Origen: " . $data['origin_addresses'][0] . "\n";
        //echo "Destino: " . $data['destination_addresses'][0] . "\n";
        //echo "Distancia en carretera: " . $elemento['distance']['text'] . "\n";
        //echo "Tiempo estimado: " . $elemento['duration']['text'] . "\n";
        //echo "------------------------------\n";
        return $elemento['distance']['text'];
    } else {
        //echo "❌ ERROR DE RUTA: " . $elemento['status'] . "\n";
        return "Error los códigos postales no existen o no hay ruta terrestre entre ellos.";
    }

}
?>