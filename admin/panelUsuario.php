<?php
require_once("../scripts/funciones.php");

if (!haIniciadoSesion()) {
    header("Location: login.php");
    exit;
}

conectar(); // Asegura conexión activa

$categorias = getCategoiasPorUser();

// Redirigir automáticamente si solo tiene una categoría
if (count($categorias) === 1) {
    header("Location: categorias/" . htmlspecialchars($categorias[0][2]));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">
    <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <title>Panel Usuario</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/dashboard/">

    <!-- Bootstrap core CSS -->
    <!--<link href="../../dist/css/bootstrap.min.css" rel="stylesheet">-->

    <!-- Custom styles for this template -->
    <link href="../css/panelAdmin.css" rel="stylesheet">
</head>
<body>

<?php include("menu-superior.php"); ?>

<div class="container-fluid">
    <div class="row">
        <?php include("menu-lateral.php"); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h2 class="mt-4">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></h2>

            <?php if (count($categorias) === 0): ?>
                <div class="alert alert-warning mt-4">Sin permisos asignados</div>
            <?php else: ?>
                <div class="row mt-4">
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="col-md-4">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($categoria[0]) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($categoria[1]) ?></p>
                                    <a href="categorias/<?= htmlspecialchars($categoria[2]) ?>" class="btn btn-success">Ir</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

</body>
</html>
