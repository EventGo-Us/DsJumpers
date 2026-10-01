<?php
ob_start();
session_start(); 
include_once '../config/config.php';     
include_once '../config/database.php';   

$database = new Database();
$pdo = $database->getConnection();

require '../vendor/autoload.php';
include_once '../api/functions.php';

use Aws\S3\S3Client;

header('Content-Type: application/json');

$product_id = intval($_POST['product_id'] ?? 0);
$gallery = $_POST['table'] ?? 'products_images';

if (!in_array($gallery, ['products_images', 'products_images_sale'], true)) {
    echo json_encode(['success' => false, 'message' => 'Tabla de imágenes no válida']);
    exit;
}

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Producto no especificado']);
    exit;
}

if (empty($_FILES['upload_file'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron archivos']);
    exit;
}

$results = [];

// Soporte para múltiples archivos
$files = $_FILES['upload_file'];
$count = is_array($files['name']) ? count($files['name']) : 1;

for ($i = 0; $i < $count; $i++) {
    $tmpName  = is_array($files['name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
    $name     = is_array($files['name']) ? $files['name'][$i] : $files['name'];
    $error    = is_array($files['name']) ? $files['error'][$i] : $files['error'];

    if ($error !== UPLOAD_ERR_OK) {
        $results[] = ['success' => false, 'message' => "Error al subir $name"];
        continue;
    }

    $fileName  = "file_" . uniqid();
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $origen    = "tmp/" . $fileName . "." . $extension;

    if (!move_uploaded_file($tmpName, $origen)) {
        $results[] = ['success' => false, 'message' => "Error al mover $name"];
        continue;
    }

    $destinot  = "tmp/thumbnail_" . $fileName . ".avif";
    $destino   = "tmp/" . $fileName . ".avif";
    $destinot2 = "tmp/thumbnail_" . $fileName . ".jpg";

    $imgOriginal = cargarImagen($origen);

    if ($imgOriginal) {
        generarThumbnailAVIF($imgOriginal, $destinot, 150);
        generarNormalAVIF($imgOriginal, $destino, 1200);
        generarThumbnailJPG($imgOriginal, $destinot2, 150);

        imagedestroy($imgOriginal);
        unlink($origen);

        $finalName = $fileName . ".avif";

                    $client = $_SESSION['id_cliente'];
                    $normal = $finalName;
                    $miniatura = "thumbnail_".$finalName;
                    $miniaturaj = "thumbnail_".$finalName;
                    $miniaturaj =  str_replace("avif", "jpg", $miniaturaj);
                    upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj);


        // Obtener el siguiente orden
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(Orden), 0) + 1 AS next_order FROM $gallery WHERE Product = ?");
        $stmt->execute([$product_id]);
        $nextOrder = $stmt->fetch(PDO::FETCH_ASSOC)['next_order'];

        // Insertar en BD
        $stmt = $pdo->prepare("INSERT INTO $gallery (Product, Orden, Image, FechaCreacion, FechaCambio) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$product_id, $nextOrder, $finalName]);
        $newId = $pdo->lastInsertId();


        $results[] = [
            'success' => true,
            'id'      => $newId,
            'image'   => $finalName,
            'orden'   => $nextOrder,
            'thumb'   => "tmp/thumbnail_" . $fileName . ".jpg"
        ];
    } else {
        unlink($origen);
        $results[] = ['success' => false, 'message' => "Formato no soportado: $name"];
    }
}

echo json_encode(['success' => true, 'files' => $results]);
exit;



function upload_Aws($client,$gallery,$normal,$miniatura,$miniaturaj){
    $r2_config = [
        'region' => 'auto',
        'endpoint' => CFENDPOINT,
        'credentials' => [
            'key'    => CFKEY,
            'secret' => CFSECRET,
        ],
    ];

    $s3Client    = new S3Client($r2_config);
    try{
        $bucket_name = 'eventgo';
        $s3Client->putObject([
            'Bucket' => $bucket_name,
            'Key'    => "$client/$gallery/originals/$normal",
            'SourceFile' => 'tmp/'.$normal,
            'ContentType' => 'image/avif'
        ]);
        unlink('tmp/'.$normal);

        $miniatura_ =  str_replace("thumbnail_", "", $miniatura);
        rename('tmp/'.$miniatura, 'tmp/'.$miniatura_);

        $s3Client->putObject([
            'Bucket' => $bucket_name,
            'Key'    => "$client/$gallery/thumbnails/$miniatura_",
            'SourceFile'   => 'tmp/'.$miniatura_,
            'ContentType' => 'image/avif'
        ]);
        
        unlink('tmp/'.$miniatura_);

        $miniatura_ =  str_replace("thumbnail_", "", $miniaturaj);
        rename('tmp/'.$miniaturaj, 'tmp/'.$miniatura_);

        $s3Client->putObject([
            'Bucket' => $bucket_name,
            'Key'    => "$client/$gallery/thumbnails/$miniatura_",
            'SourceFile'   => 'tmp/'.$miniatura_,
            'ContentType' => 'image/jpg'
        ]);
        unlink('tmp/'.$miniatura_);        

        return true;
    } catch (Aws\S3\Exception\S3Exception $e) {
        //error_log("Error al subir a S3: " . $e->getMessage());
        echo"Error al subir a S3: " . $e->getMessage();
        return false;
    }        
} 