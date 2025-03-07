<?php require "./includes/session_start.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <?php include "./includes/header.php"; ?>
</head>

<body class="">
    <?php
    if (!isset($_GET['page']) || $_GET['page'] == "") {
        $_GET['page'] = "login";
    }
    if (is_file("./pages/" . $_GET['page'] . ".php") && $_GET['page'] != "login" && $_GET['page'] != "404") {

        /*== Cerrar sesion ==*/
        if ((!isset($_SESSION['id']) || $_SESSION['id'] == "") || (!isset($_SESSION['usuario']) || $_SESSION['usuario'] == "")) {
            include "./pages/logout.php";
            exit();
        }

        if ($_SESSION['rol'] == "Administrador") {
            include "./includes/navbarAdmin.php";
        } elseif ($_SESSION['rol'] == "Supervisor") {
            include "./includes/navbarSup.php";
        } elseif ($_SESSION['rol'] == "Logistica") {
            include "./includes/navbarLog.php";
        } else {
            echo "";
        }

        include "./pages/" . $_GET['page'] . ".php";

        //require_once "./includes/footer.php";

        include "./includes/script.php";
    } else {
        if ($_GET['page'] == "login") {
            include "./pages/login.php";
        } else {
            include "./pages/404.php";
        }
    }
    ?>
</body>
</html>