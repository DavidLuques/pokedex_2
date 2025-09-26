<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Borrar <?= htmlspecialchars($pokemon['nombre']) ?> - Pokedex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
            padding-bottom: 70px;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-bottom: 2px solid #ccc;
            padding: 15px 0;
            text-align: center;
        }

        .header .logo {
            height: 80px;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .pokedex-card {
            max-width: 800px;
            margin: auto;
            background: white;
            border: 3px solid #333;
            border-radius: 12px;
            padding: 20px;
        }

        .badge {
            border-radius: 12px;
            font-weight: bold;
            font-size: 1rem;
        }
    </style>
</head>

<body class="bg-light">

    <header class="header">
        <div class="container position-relative">
            <img src="img/pokeball.png" alt="Pokeball" class="logo">
            <h1>Pokedex</h1>
        </div>
    </header>

    <main>
        <div class="container my-4">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Eliminar Pokémon</h4>
                <p>¿Estás seguro de que deseas eliminar este Pokémon? Esta acción no se puede deshacer.</p>
                <hr>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <?php if (isset($_GET['id'])): ?>
                    <a href="borrar.php?id=<?php echo htmlspecialchars($_GET['id']); ?>" class="btn btn-danger">Eliminar</a>
                <?php else: ?>
                    <span class="text-danger">ID no especificado</span>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>