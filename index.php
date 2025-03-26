<?php require "./includes/session_start.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Kalli Jaguar Inventory</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body id="page-top">
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
            include "./includes/navbarNewTop.php";
        } elseif ($_SESSION['rol'] == "Supervisor") {
            include "./includes/navbarSup.php";
        } elseif ($_SESSION['rol'] == "Logistica") {
            include "./includes/navbarLog.php";
        } else {
            echo "";
        }

        include "./pages/" . $_GET['page'] . ".php";

        include "./includes/script.php";

        if ($_SESSION['rol'] == "Administrador") {
            include "./includes/navbarNewBottom.php";
        } elseif ($_SESSION['rol'] == "Supervisor") {
            include "./includes/navbarNewBottom.php";
        } elseif ($_SESSION['rol'] == "Logistica") {
            include "./includes/navbarNewBottom.php";
        } else {
            echo "";
        }
    } else {
        if ($_GET['page'] == "login") {
            include "./pages/login.php";
        } else {
            include "./pages/404.php";
        }
    }
    ?>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>