<?php
$comandaID = $_GET['ComandaID'];
$pathPDF = './documents/'.$comandaID.'.pdf';

// Verifica si el archivo existe
if (file_exists($pathPDF)) {
    // Redirigir a una página HTML que abra el archivo en una nueva ventana
    echo '<script type="text/javascript">
            window.open("' . $pathPDF . '", "_blank");
          </script>';
} else {
    echo "El archivo no existe.";
}
?>