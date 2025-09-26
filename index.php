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

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$mensajeNoEncontrado = '';

if ($busqueda !== '') {
    $busqueda_esc = $conexion->escape($busqueda);

    if (is_numeric($busqueda)) {
        $sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, t.nombre AS tipo
                FROM pokemon p
                LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
                LEFT JOIN tipo t ON pt.id_tipo = t.id
                WHERE p.numero = $busqueda_esc
                  AND p.activo = 1
                ORDER BY p.numero, t.nombre";
        $datos = $conexion->query($sql);
    } else {
        $sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, t.nombre AS tipo
                FROM pokemon p
                LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
                LEFT JOIN tipo t ON pt.id_tipo = t.id
                WHERE p.id IN (
                    SELECT pt2.id_pokemon
                    FROM pokemon_tipo pt2
                    LEFT JOIN tipo t2 ON pt2.id_tipo = t2.id
                    WHERE t2.nombre LIKE '%$busqueda_esc%'
                )
                AND p.activo = 1
                ORDER BY p.numero, t.nombre";
        $datos = $conexion->query($sql);

        if (empty($datos)) {
            $sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, t.nombre AS tipo
                    FROM pokemon p
                    LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
                    LEFT JOIN tipo t ON pt.id_tipo = t.id
                    WHERE p.nombre LIKE '%$busqueda_esc%'
                      AND p.activo = 1
                    ORDER BY p.numero, t.nombre";
            $datos = $conexion->query($sql);
        }
    }

    if (empty($datos)) {
        $mensajeNoEncontrado = "<div class='alert alert-warning'>Pokémon no encontrado</div>";
        $sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, t.nombre AS tipo
                FROM pokemon p
                LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
                LEFT JOIN tipo t ON pt.id_tipo = t.id
                WHERE p.activo = 1
                ORDER BY p.numero, t.nombre";
        $datos = $conexion->query($sql);
    }
} else {
    $sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, t.nombre AS tipo
            FROM pokemon p
            LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
            LEFT JOIN tipo t ON pt.id_tipo = t.id
            WHERE p.activo = 1
            ORDER BY p.numero, t.nombre";
    $datos = $conexion->query($sql);
}

/* --- Agrupar resultados por Pokémon --- */
$pokemonData = [];
foreach ($datos as $fila) {
    $id = $fila['id'];

    if (!isset($pokemonData[$id])) {
        $pokemonData[$id] = [
            'id' => $fila['id'],
            'numero' => $fila['numero'],
            'nombre' => $fila['nombre'],
            'imagen' => $fila['imagen'],
            'descripcion' => $fila['descripcion'],
            'tipos' => []
        ];
    }

    if (!empty($fila['tipo'])) {
        $pokemonData[$id]['tipos'][] = $fila['tipo'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/pokemon-hollow" rel="stylesheet">
    <style>
        body {
            padding-top: 120px;
            padding-bottom: 80px;
            background: url('img/fondo.png') no-repeat center center fixed;
            background-size: cover;
        }

        .tabla-fondo {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            padding: 1rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .table td,
        .table th {
            background: rgba(255, 255, 255, 0.6) !important;
        }

        .table-dark th {
            background: rgba(0, 0, 0, 0.8) !important;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border-bottom: 2px solid #ccc;
            padding: 20px 0;
        }

        .header .logo {
            height: 80px;
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .header h1 {
            margin: 0;
            font-size: 3rem;
            font-weight: bold;
            text-align: center;
        }

        .header .login-area {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border-top: 2px solid #ccc;
            padding: 10px 0;
        }

        .table td,
        .table th {
            padding: 1rem;
            font-size: 1.2rem;
        }

        .table img {
            width: 80px;
            height: auto;
        }

        .detalle-img {
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-block;
        }

        .detalle-img:hover {
            cursor: pointer;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .detalle-nombre {
            text-decoration: none;
            color: inherit;
            font-size: 1.2rem;
            transition: color 0.2s, text-decoration 0.2s;
        }

        .detalle-nombre:hover {
            cursor: pointer;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
        }

        .btn {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>

<body class="bg-light">

    <header class="header">
        <div class="container position-relative">
            <img src="img/pokeball.png" alt="Pokeball" class="logo">
            <h1>Pokedex</h1>
            <div class="login-area">
                <?php if (!isset($_SESSION['usuario_nombre'])): ?>
                    <a href="login.php" class="btn btn-primary">Iniciar sesión</a>
                <?php else: ?>
                    Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Cerrar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <form method="GET" class="input-group mb-4">
            <input type="text" name="busqueda" class="form-control" placeholder="Ingrese el nombre, tipo o número de Pokémon" value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn btn-primary" type="submit">¿Quién es este Pokémon?</button>
        </form>

        <?php if (!empty($mensajeNoEncontrado)) echo $mensajeNoEncontrado; ?>
        <div class="tabla-fondo">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Imagen</th>
                            <th>Tipo</th>
                            <th>Número</th>
                            <th>Nombre</th>
                            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pokemonData as $dato): ?>
                            <tr>
                                <td>
                                    <a href="detalle.php?id=<?= $dato['id'] ?>" class="detalle-img">
                                        <img src="<?= $dato['imagen'] ?>" alt="<?= $dato['nombre'] ?>">
                                    </a>
                                </td>
                                <td>
                                    <?php foreach ($dato['tipos'] as $tipo): ?>
                                        <img src="img/tipoIndex/<?= strtolower($tipo) ?>.png"
                                            alt="<?= $tipo ?>"
                                            title="<?= $tipo ?>"
                                            style="width:28px;height:28px;margin:0 6px;">
                                    <?php endforeach; ?>
                                </td>
                                <td><?= str_pad($dato['numero'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <a href="detalle.php?id=<?= $dato['id'] ?>" class="detalle-nombre"><?= $dato['nombre'] ?></a>
                                </td>
                                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                    <td>
                                        <a href="editar.php?id=<?= $dato['id'] ?>" class="btn btn-warning btn-sm">Modificar</a>
                                        <a href="darDeBaja.php?id=<?= $dato['id'] ?>"
                                            class="btn btn-danger btn-sm">
                                            Baja
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container text-center">
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <a href="alta.php" class="btn btn-success btn-lg">Nuevo Pokémon</a>
            <?php endif; ?>
        </div>
    </footer>

</body>

</html>