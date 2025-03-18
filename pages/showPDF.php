<?php
$comandaID = $_GET['ComandaID'];
$pathPDF = './documents/' . $comandaID . '.pdf';

// Verifica si el archivo existe
if (file_exists($pathPDF)) {
    // Redirigir a una página HTML que abra el archivo en una nueva ventana
    //echo '<script type="text/javascript"> window.open("' . $pathPDF . '", "_blank");</script>';
    echo '<h1>Visualizar PDF en un Div</h1>
<div id="pdf-container">
    <embed src="'.$pathPDF.'" type="application/pdf" width="100%" height="100%">
</div>';
} else {
    echo "El archivo no existe.";
}
