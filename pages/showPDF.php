<?php
$comandaID = $_GET['ComandaID'];
$pathPDF = './documents/' . $comandaID . '.pdf';

if (file_exists($pathPDF)) {
    echo '
    <style>
        #pdf-container {
            width: 100%;
            height: 90vh;
        }

        #pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        @media (max-width: 768px) {
            #pdf-container {
                height: 85vh;
            }
        }

        h3 {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <h3>' . htmlspecialchars($comandaID) . '</h3>
                        <div id="pdf-container">
                            <iframe src="' . $pathPDF . '#toolbar=1&navpanes=1&scrollbar=1"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
} else {
    echo "<div class='alert alert-danger'>El archivo no existe.</div>";
}