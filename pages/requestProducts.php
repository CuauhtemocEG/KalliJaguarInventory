<div class="container-fluid" style="padding-top:15px;padding-bottom:15px">
    <?php
    session_start();
    require_once "./controllers/mainController.php";
    ?>

    <div class="card">
        <div class="card-header font-weight-bold">Solicitud de Insumos a Almacén</div>
        <div class="card-body">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar productos..." aria-label="Buscar productos..." aria-describedby="searchButton">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">Buscar</button>
                </div>
            </div>
            <div class="row justify-content-center" id="productList">
            </div>
        </div>
    </div>
</div>