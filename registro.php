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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    if ($password !== $repassword) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 4) {
        $error = "La contraseña debe tener al menos 4 caracteres.";
    } else {
        $usuarioExistente = $conexion->query("SELECT * FROM usuarios WHERE nombre = '".$conexion->escape($nombre)."' LIMIT 1");

        if ($usuarioExistente) {
            $error = "El usuario ya existe.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, password, rol) VALUES ('".$conexion->escape($nombre)."', '$hash', 'usuario')";
            if ($conexion->execute($sql)) {
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_rol'] = 'usuario';
                header("Location: index.php");
                exit;
            } else {
                $error = "Ocurrió un error al crear el usuario.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Crear cuenta</h2>

    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Usuario</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="repassword" class="form-label">Repetir contraseña</label>
            <input type="password" name="repassword" id="repassword" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrarse</button>
        <a href="login.php" class="btn btn-link">Volver a iniciar sesión</a>
    </form>
</div>
</body>
</html>
