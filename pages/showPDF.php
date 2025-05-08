<?php
$comandaID = $_GET['ComandaID'];
$pathPDF = './documents/' . $comandaID . '.pdf';

// Verifica si el archivo existe
if (file_exists($pathPDF)) {
    // Redirigir a una página HTML que abra el archivo en una nueva ventana
    //echo '<script type="text/javascript"> window.open("' . $pathPDF . '", "_blank");</script>';
    echo '
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <h3>'.$comandaID.'</h3>
                            <div id="pdf-container">
                                <embed src="'.$pathPDF
                                .'" type="application/pdf" width="100%" height="800px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
     </div>';
} else {
    echo "El archivo no existe.";
}
