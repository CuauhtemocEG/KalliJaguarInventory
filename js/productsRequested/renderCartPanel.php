<?php
session_start();
ob_start();

if (isset($_SESSION['INV']) && count($_SESSION['INV']) > 0) {
    $total = 0;
    $totalItem = 0;
    $res = 0;
    foreach ($_SESSION['INV'] as $key => $item) {
        $unidadesRes = '';

        if ($item['tipo'] == "Pesable") {
            if ($item['cantidad'] >= 1.0) {
                $unidadesRes = 'Kg';
                $res = number_format($item["cantidad"], 3);
            } else {
                $unidadesRes = 'grs';
                $res = number_format($item["cantidad"], 3);
            }
        } else {
            $unidadesRes = 'Un';
            $res = number_format($item["cantidad"], 0);
        }

        $totalItem += $item['cantidad'];
        $percentage = $item['precio'] * (1 + 0.16);
        $total += $item['cantidad'] * $percentage;

        echo '
<div class="col-md-12 mb-3">
    <div class="card mb-3 shadow-sm border-light">
        <div class="card-body d-flex flex-column position-relative">
            
            <span class="badge badge-danger badge-counter position-absolute" style="top: 0; right: 0;">
                ' . $res . ' ' . $unidadesRes . '
            </span>
            
            <div class="text-md font-weight-bold text-dark mb-2">
                ' . $item["nombre"] . '
            </div>
            
            <div class="d-flex justify-content-between text-muted mb-3">
                <small>Precio unitario: $' . number_format($item["precio"], 2) . '</small>
                <small>Total: $' . number_format($item["precio"] * $item["cantidad"], 2) . '</small>
            </div>
            
            <div class="d-flex justify-content-end">
                <button class="btn btn-danger btn-sm btn-delete-item" data-id="' . $key . '">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>';
    }
    echo '
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg p-4">
                <h6 class="card-title text-center mb-4">Resumen de la Solicitud</h6>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total de productos:</strong>
                    <span class="h5 text-primary">' . $totalItem . '</span>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total a pagar:</strong>
                    <span class="h5 text-success">$' . number_format($total, 2) . '</span>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmModal">
                        <i class="fas fa-check-circle"></i> Enviar Solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>';
} else {
    echo '<p>No hay productos en el carrito.</p>';
}
