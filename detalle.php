<?php
session_start();
include_once ("MyDatabase.php");

$config = parse_ini_file("config.ini");
$conexion = new MyDatabase(
        $config["server"],
        $config["user"],
        $config["pass"],
        $config["database"]
);

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("No se especificó un Pokémon válido.");
}

$sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, 
               p.altura, p.peso,
               GROUP_CONCAT(t.nombre SEPARATOR ', ') AS tipos
        FROM pokemon p
        LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
        LEFT JOIN tipo t ON pt.id_tipo = t.id
        WHERE p.id = $id
        GROUP BY p.id";

$result = $conexion->query($sql);
if (!$result || count($result) === 0) {
    die("Pokémon no encontrado.");
}
$pokemon = $result[0];

$colores = [
        'Normal'     => '#A8A77A',
        'Fuego'      => '#EE8130',
        'Agua'       => '#6390F0',
        'Eléctrico'  => '#F7D02C',
        'Planta'     => '#7AC74C',
        'Hielo'      => '#96D9D6',
        'Lucha'      => '#C22E28',
        'Veneno'     => '#A33EA1',
        'Tierra'     => '#E2BF65',
        'Volador'    => '#A98FF3',
        'Psíquico'   => '#F95587',
        'Bicho'      => '#A6B91A',
        'Roca'       => '#B6A136',
        'Fantasma'   => '#735797',
        'Dragón'     => '#6F35FC',
        'Siniestro'  => '#705746',
        'Acero'      => '#B7B7CE',
        'Hada'       => '#D685AD',
];


$tipos = explode(',', $pokemon['tipos']);
$tipoPrincipal = trim($tipos[0]);

$colorDesc = isset($colores[$tipoPrincipal]) ? $colores[$tipoPrincipal] : '#fdf2f2';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $pokemon['nombre'] ?> - Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
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

        body {
            background-image: url('img/tipo/<?= strtolower($tipoPrincipal) ?>.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 100px);
            padding-top: 100px;
        }


        .pokedex-card {
            max-width: 800px;
            background: rgba(255, 255, 255, 0.9);
            border: 3px solid #333;
            border-radius: 12px;
            padding: 20px;
            backdrop-filter: blur(5px);
        }

        .pokemon-img {
            max-width: 100%;
            height: auto;
        }

        .pokemon-desc {
            padding: 15px;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
        }

        .badge {
            border-radius: 12px;
            font-weight: bold;
            font-size: 1rem;
        }
    </style>
</head>
<body>

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

<main class="main-wrapper">
    <div class="pokedex-card shadow">
        <div class="row align-items-center">

            <div class="col-md-4 text-center mb-3 mb-md-0">
                <img src="<?= $pokemon['imagen'] ?>" alt="<?= $pokemon['nombre'] ?>" class="pokemon-img">
            </div>

            <div class="col-md-8">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h2 class="mb-2 mb-md-0">#<?=str_pad($pokemon['numero'], 3, '0', STR_PAD_LEFT);?> <?= $pokemon['nombre'] ?></h2>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tipos as $tipo):
                            $tipo = trim($tipo);
                            $bg = isset($colores[$tipo]) ? $colores[$tipo] : '#ccc';
                            ?>
                            <span class="badge px-3 py-2" style="background: <?= $bg ?>; color: white;">
                                <?= $tipo ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="mt-3"><strong>Altura:</strong> <?= $pokemon['altura'] ?> m</p>
                <p><strong>Peso:</strong> <?= $pokemon['peso'] ?> kg</p>
            </div>
        </div>

        <div class="pokemon-desc mt-4" style="background: <?= $colorDesc ?>;">
            <?= $pokemon['descripcion'] ?>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</main>

</body>
</html>
