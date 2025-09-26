<?php
session_start();
include_once("MyDatabase.php");

$config = parse_ini_file("config.ini");
$conexion = new MyDatabase(
    $config["server"],
    $config["user"],
    $config["pass"],
    $config["database"]
);


$nombreAdmin = 'Red';
$passwordAdmin = 'charmander123';
$rolAdmin = 'admin';

$checkAdmin = $conexion->query("SELECT * FROM usuarios WHERE nombre = 'Red' LIMIT 1");
if (!$checkAdmin) {
    $hash = password_hash($passwordAdmin, PASSWORD_DEFAULT);
    $conexion->query("INSERT INTO usuarios (nombre, password, rol) VALUES ('Red', '$hash', '$rolAdmin')");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE nombre = '".$conexion->escape($nombre)."' LIMIT 1";
    $usuario = $conexion->query($sql);

    if ($usuario && password_verify($password, $usuario[0]['password'])) {
        $_SESSION['usuario_id'] = $usuario[0]['id'];
        $_SESSION['usuario_nombre'] = $usuario[0]['nombre'];
        $_SESSION['usuario_rol'] = $usuario[0]['rol'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Iniciar sesión</h2>

    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Usuario</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p class="mt-3">¿No tenés cuenta? <a href="registro.php">Registrate acá</a></p>
</div>
</body>
</html>
