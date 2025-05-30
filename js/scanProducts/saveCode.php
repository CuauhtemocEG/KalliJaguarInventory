<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["codigo"])) {
    $codigo = $_POST["codigo"];
    header("Location: index.php?page=updateStockProduct?codigo=" . urlencode($codigo));
    exit();
}
