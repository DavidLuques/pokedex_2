<?php
include_once("MyDatabase.php");

$config = parse_ini_file("config.ini");
$conexion = new MyDatabase(
    $config["server"],
    $config["user"],
    $config["pass"],
    $config["database"]
);

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) die("No se especificó un Pokémon válido.");

// Traigo Pokémon actual
$sql = "SELECT p.id, p.numero, p.nombre, p.imagen, p.descripcion, p.altura, p.peso,
               GROUP_CONCAT(t.nombre) AS tipos
        FROM pokemon p
        LEFT JOIN pokemon_tipo pt ON p.id = pt.id_pokemon
        LEFT JOIN tipo t ON pt.id_tipo = t.id
        WHERE p.id = $id
        GROUP BY p.id";
$result = $conexion->query($sql);
if (!$result || count($result) === 0) die("Pokémon no encontrado.");
$pokemon = $result[0];
$tipos = array_map("trim", explode(",", $pokemon['tipos']));

$colores = [
    'Normal' => '#A8A77A','Fuego'=>'#EE8130','Agua'=>'#6390F0','Eléctrico'=>'#F7D02C',
    'Planta'=>'#7AC74C','Hielo'=>'#96D9D6','Lucha'=>'#C22E28','Veneno'=>'#A33EA1',
    'Tierra'=>'#E2BF65','Volador'=>'#A98FF3','Psíquico'=>'#F95587','Bicho'=>'#A6B91A',
    'Roca'=>'#B6A136','Fantasma'=>'#735797','Dragón'=>'#6F35FC','Siniestro'=>'#705746',
    'Acero'=>'#B7B7CE','Hada'=>'#D685AD'
];

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero']);
    $nombre = trim($_POST['nombre']);
    $imagen = trim($_POST['imagen']);
    $descripcion = trim($_POST['descripcion']);
    $altura = trim($_POST['altura']);
    $peso = trim($_POST['peso']);
    $tiposForm = array_filter(array_map("trim", isset($_POST['tipos']) ? $_POST['tipos'] : []));

    if ($numero === '' || $nombre === '' || $imagen === '' || $descripcion === '' || $altura === '' || $peso === '')
        $errores[] = "Todos los campos son obligatorios.";
    if (count($tiposForm) < 1)
        $errores[] = "El Pokémon debe tener al menos un tipo.";

    $sqlCheck = "SELECT id FROM pokemon WHERE numero = '$numero' AND activo = 1 AND id <> $id";
    if ($conexion->query($sqlCheck)) {
        $errores[] = "El número ya está en uso por otro Pokémon activo.";
    }

    $sqlCheck = "SELECT id FROM pokemon WHERE nombre = '$nombre' AND activo = 1 AND id <> $id";
    if ($conexion->query($sqlCheck)) {
        $errores[] = "El nombre ya está en uso por otro Pokémon activo.";
    }




    if (empty($errores)) {
        $conexion->execute("UPDATE pokemon 
                            SET numero='$numero', nombre='$nombre', imagen='$imagen', 
                                descripcion='$descripcion', altura='$altura', peso='$peso' 
                            WHERE id=$id");

        $conexion->execute("DELETE FROM pokemon_tipo WHERE id_pokemon=$id");
        foreach ($tiposForm as $tipo) {
            $tipo = $conexion->escape($tipo);
            $res = $conexion->query("SELECT id FROM tipo WHERE nombre='$tipo'");
            if (!$res) {
                $errores[] = "Tipo '$tipo' no existe.";
            } else {
                $idTipo = $res[0]['id'];
                $conexion->execute("INSERT INTO pokemon_tipo (id_pokemon,id_tipo) VALUES ($id,$idTipo)");
            }
        }

        if (empty($errores)) {
            header("Location: detalle.php?id=$id&msg=editado");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar <?= htmlspecialchars($pokemon['nombre']) ?> - Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; padding-bottom: 70px; }
        .header { position: fixed; top: 0; left: 0; right: 0; background:#fff; border-bottom:2px solid #ccc; padding:15px 0; text-align:center; }
        .header .logo { height:80px; position:absolute; left:15px; top:50%; transform:translateY(-50%); }
        .header h1 { margin:0; font-size:2.5rem; font-weight:bold; }
        .pokedex-card { max-width:800px; margin:auto; background:white; border:3px solid #333; border-radius:12px; padding:20px; }
        .badge { border-radius:12px; font-weight:bold; font-size:1rem; }
    </style>
</head>
<body class="bg-light">

<header class="header">
    <div class="container position-relative">
        <img src="img/pokeball.png" alt="Pokeball" class="logo">
        <h1>Pokedex</h1>
    </div>
</header>

<main class="container py-4">
    <div class="pokedex-card shadow">

        <h2 class="mb-4">Editar Pokémon</h2>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <?= implode("<br>", $errores) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Número</label>
                <input type="number" name="numero" class="form-control" value="<?= htmlspecialchars(isset($_POST['numero']) ? $_POST['numero'] : $pokemon['numero']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars(isset($_POST['nombre']) ? $_POST['nombre'] : $pokemon['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen (URL o ruta)</label>
                <input type="text" name="imagen" class="form-control" value="<?= htmlspecialchars(isset($_POST['imagen']) ? $_POST['imagen'] : $pokemon['imagen']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" required><?= htmlspecialchars(isset($_POST['descripcion']) ? $_POST['descripcion'] : $pokemon['descripcion']) ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Altura (m)</label>
                    <input type="number" step="0.01" name="altura" class="form-control" value="<?= htmlspecialchars(isset($_POST['altura']) ? $_POST['altura'] : $pokemon['altura']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Peso (kg)</label>
                    <input type="number" step="0.01" name="peso" class="form-control" value="<?= htmlspecialchars(isset($_POST['peso']) ? $_POST['peso'] : $pokemon['peso']) ?>" required>
                </div>
            </div>

            <!-- Tipos -->
            <div class="mb-3">
                <label class="form-label">Tipos</label>
                <div id="tipos-container">
                    <?php
                    $tiposExistentes = isset($_POST['tipos']) ? $_POST['tipos'] : $tipos;
                    foreach ($tiposExistentes as $tipo): ?>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" name="tipos[]" class="form-control" value="<?= htmlspecialchars($tipo) ?>" required>
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="agregarTipo()">+ Agregar tipo</button>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="detalle.php?id=<?= $id ?>" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</main>

<script>
    function agregarTipo() {
        const div = document.createElement('div');
        div.className = "d-flex gap-2 mb-2";
        div.innerHTML = `<input type="text" name="tipos[]" class="form-control" required>
                     <button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.remove()">X</button>`;
        document.getElementById("tipos-container").appendChild(div);
    }
</script>

</body>
</html>
