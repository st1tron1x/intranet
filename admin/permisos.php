<?php
require '../scripts/funciones.php';
//Validacion de la sesion como administrador:
if (! haIniciadoSesion() || ! esAdmin()) {
    header('Location: index.html');
}
//validacion del paametro GET
if (isset($_GET['usuario']))
    $usuario = $_GET['usuario'];
else header('Location: panelAdmin.php');
//captura de categorias
conectar();
$categorias = getTodasCategorias();
desconectar();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">
    <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <title>Panel Administrador</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/dashboard/">

    <!-- Bootstrap core CSS -->
    <link href="../../dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/panelAdmin.css" rel="stylesheet">
</head>

<body>
    <?php include 'menu-superior.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php'; ?>

            <div class="col-sm-9 col-sm-ffset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Panel Administrativo</h1>
                <h3 class="sub-header">Bienvenido, Administrador.</h3>
                <p> Se estan modificando los permisos para el usuario:</p>
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <!--<div class="col-sm-4">-->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Edicion de Permisos</h3>
                                </div>
                                <div class="panel-body">
                                    <form action="../scripts/actualizarPermisos.php" method="POST">
                                        <div class="form-group">
                                            <label for="txtUsuario">Usuario</label>
                                            <input type="text" class="form-control" id="txtUsuario" value="<?= $_GET['usuario']?>">
                                        </div>
                                        <?php foreach ($categorias as $categoria): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox"> <?= $categoria[1] ?>
                                            </label>
                                        </div>
                                        <?php endforeach ?>
                                        <button type="submit" class="btn btn-default">Guardar</button>
                                    </form>
                                </div>
                            </div>
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

</body>

</html>