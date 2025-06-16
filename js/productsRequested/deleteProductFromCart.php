<?php
session_start();

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    if (isset($_SESSION['INV'][$id])) {
        unset($_SESSION['INV'][$id]);
        echo json_encode(['status' => 'Success']);
        setcookie("persist_cart", json_encode($_SESSION['INV']), time() + 604800, "/");
    } else {
        echo json_encode(['status' => 'Not_Found']);
    }
} else {
    echo json_encode(['status' => 'Resquest Invalid']);
}
?>
