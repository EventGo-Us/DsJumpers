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


function get_distance($ORG, $DST, $apiKey)
{
    $url = 'https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix';

    $payload = [
        'origins' => [
            [
                'waypoint' => [
                    'address' => (string) $ORG,
                ],
            ],
        ],
        'destinations' => [
            [
                'waypoint' => [
                    'address' => (string) $DST,
                ],
            ],
        ],
        'travelMode' => 'DRIVE',
        'units' => 'IMPERIAL',
    ];

    $headers = [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . $apiKey,
        'X-Goog-FieldMask: originIndex,destinationIndex,distanceMeters,duration,localizedValues,status,condition',
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        echo "Estado: CURL_ERROR\n";
        echo "Mensaje: " . $error . "\n";
        return 'Error al llamar la API';
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    // 1. Verificar errores HTTP o de API Key / configuración
    if ($httpCode >= 400 || !is_array($data)) {
        $status = $data['error']['status'] ?? ('HTTP_' . $httpCode);
        $message = $data['error']['message'] ?? $response;
        echo "Estado: " . $status . "\n";
        echo "Mensaje: " . $message . "\n";
        return 'Error al llamar la API';
    }

    // computeRouteMatrix devuelve un arreglo de elementos
    $elemento = $data[0] ?? null;

    if (!$elemento) {
        echo "Estado: EMPTY_RESPONSE\n";
        echo "Mensaje: No se recibieron elementos en la respuesta.\n";
        return 'Error al llamar la API';
    }

    // Verificar si el elemento tuvo error propio
    if (isset($elemento['status']) && !empty($elemento['status']) && ($elemento['status']['code'] ?? 0) !== 0) {
        echo "Estado: " . ($elemento['status']['message'] ?? 'ELEMENT_ERROR') . "\n";
        return "Error los códigos postales no existen o no hay ruta terrestre entre ellos.";
    }

    // 2. Verificar si encontró la ruta
    $condition = $elemento['condition'] ?? '';

    if ($condition === 'ROUTE_EXISTS') {
        $distanceText = $elemento['localizedValues']['distance']['text'] ?? null;

        // Respaldo en millas si localizedValues no viniera en algún caso
        if (!$distanceText && isset($elemento['distanceMeters'])) {
            $miles = $elemento['distanceMeters'] / 1609.344;
            $distanceText = number_format($miles, 1) . ' mi';
        }

        // Si también necesitas la duración formateada:
        // $durationText = $elemento['localizedValues']['duration']['text'] ?? '';

        return $distanceText ?? 'Error al llamar la API';
    } else {
        return "Error los códigos postales no existen o no hay ruta terrestre entre ellos.";
    }
}


function limpiarJsonDeSaltosDeLinea($jsonString) {
    // 1. Decodificar el JSON a un array asociativo
    $datos = json_decode($jsonString, true);
    
    // Si hay un error de sintaxis en el JSON, devolvemos el original o false
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $jsonString; 
    }

    // 2. Función recursiva para recorrer el array y limpiar los strings
    $limpiarRecursivo = function(&$valor) use (&$limpiarRecursivo) {
        if (is_array($valor)) {
            foreach ($valor as &$item) {
                $limpiarRecursivo($item);
            }
        } elseif (is_string($valor)) {
            // Reemplazamos \r\n, \n y \r por espacios o los eliminamos (aquí los eliminamos con '')
            $valor = str_replace(["\r\n", "\n", "\r"], "", $valor);
        }
    };

    // Aplicar la limpieza
    $limpiarRecursivo($datos);

    // 3. Volver a convertir a JSON (mantiene los caracteres sin escapar si usas JSON_UNESCAPED_UNICODE)
    return json_encode($datos, JSON_UNESCAPED_UNICODE);
}
?>