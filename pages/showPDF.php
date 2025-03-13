<?php
if (isset($_GET['pdf'])) {
    $pdfPath = urldecode($_GET['pdf']);
    if (file_exists($pdfPath)) {
        // Mostrar el PDF en el navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
        readfile($pdfPath);
        exit();
    } else {
        echo "El archivo PDF no existe.";
    }
} else {
    echo "No se ha proporcionado un archivo PDF.";
}
?>