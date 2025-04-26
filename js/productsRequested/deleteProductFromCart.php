<?php
session_start();

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    if (isset($_SESSION['INV'][$id])) {
        unset($_SESSION['INV'][$id]);
        echo json_encode(['status' => 'Success']);
    } else {
        echo json_encode(['status' => 'Not_Found']);
    }
} else {
    echo json_encode(['status' => 'Resquest Invalid']);
}
?>
