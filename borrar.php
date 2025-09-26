<?php
include_once("MyDatabase.php");

$config = parse_ini_file("config.ini");
$conexion = new MyDatabase(
    $config["server"],
    $config["user"],
    $config["pass"],
    $config["database"]
);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Lo marcamos como inactivo
    $conexion->execute("UPDATE pokemon SET activo = 0 WHERE id = $id");

    header("Location: index.php?msg=baja_ok");
    exit;
}
header("Location: index.php?msg=error");
exit;
