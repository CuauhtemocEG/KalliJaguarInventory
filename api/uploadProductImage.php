<?php
/**
 * API para subir imágenes de productos
 * Endpoint: /api/uploadProductImage.php
 * Método: POST (multipart/form-data)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Método no permitido', null, 405);
}

if (!isset($_FILES['imagen'])) {
    sendResponse(false, 'No se recibió ninguna imagen');
}

$file = $_FILES['imagen'];
$uploadDir = '../img/producto/';

// Validaciones
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowedTypes)) {
    sendResponse(false, 'Tipo de archivo no permitido. Solo se aceptan JPG, PNG, GIF, WEBP');
}

if ($file['size'] > $maxSize) {
    sendResponse(false, 'La imagen es demasiado grande. Máximo 2MB');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, 'Error al subir el archivo: ' . $file['error']);
}

// Crear directorio si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generar nombre único
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$nombreProducto = isset($_POST['nombre']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $_POST['nombre']) : 'producto';
$nombreArchivo = $nombreProducto . '_' . uniqid() . '.' . $extension;
$rutaDestino = $uploadDir . $nombreArchivo;

// Mover archivo
if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
    // Opcionalmente redimensionar imagen
    $imageInfo = getimagesize($rutaDestino);
    $maxWidth = 800;
    $maxHeight = 800;
    
    if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
        $ratio = min($maxWidth / $imageInfo[0], $maxHeight / $imageInfo[1]);
        $newWidth = intval($imageInfo[0] * $ratio);
        $newHeight = intval($imageInfo[1] * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($rutaDestino);
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $imageInfo[0], $imageInfo[1]);
                imagejpeg($newImage, $rutaDestino, 85);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($rutaDestino);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $imageInfo[0], $imageInfo[1]);
                imagepng($newImage, $rutaDestino, 9);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($rutaDestino);
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $imageInfo[0], $imageInfo[1]);
                imagegif($newImage, $rutaDestino);
                break;
        }
        
        imagedestroy($newImage);
        imagedestroy($source);
    }
    
    sendResponse(true, 'Imagen subida exitosamente', [
        'filename' => $nombreArchivo,
        'path' => 'img/producto/' . $nombreArchivo,
        'url' => './img/producto/' . $nombreArchivo
    ]);
} else {
    sendResponse(false, 'Error al guardar la imagen', null, 500);
}
