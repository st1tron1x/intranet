<?php
require 'scripts/funciones.php';
// ✅ FUNCIÓN CORREGIDA
if(! haIniciadoSesion())
{
    header('Location: index.html');
    exit();
}
conectar();

$categorias = getCategoiasPorUser();

desconectar();
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <link rel="canonical" href="https://getbootstrap.com/docs/3.4/examples/jumbotron-narrow/">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="css/jumbotron-narrow.css">
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li role="presentation" class="active"><a href="#">Home</a></li>
                    <li role="presentation"><a href="scripts/cerrar-sesion.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Intranet Correagro</h3>
        </div>

        <div class="jumbotron">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario'])?></h1>
            <p class="lead">En esta sección podrás acceder a diversas categorías de nuestro sitio.</p>
        </div>

        <div class="row marketing">
            <div class="col-lg-6">
                <?php if(count($categorias) > 0): ?>
                    <?php foreach ($categorias as $fila): ?>
                        <h4><a href="categorias/<?= htmlspecialchars($fila[2])?>"><?= htmlspecialchars($fila[0])?></a></h4>
                        <p><?= htmlspecialchars($fila[1])?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h4>Sin permisos asignados</h4>
                        <p>Actualmente no tienes permisos para acceder a ninguna categoría. Contacta al administrador para obtener los permisos necesarios.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2025 Stiven Vanegas Jimenez, Inc.</p>
        </footer>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>