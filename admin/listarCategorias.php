<?php
require '../scripts/funciones.php';
//validar la sesion como admin
if(!haIniciadoSesion() || !esAdmin() )
{
    header('Location: ../index.html');
    exit();
}

//captura categorias
conectar();
$categorias = getTodasCategorias();
desconectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'menu-superior.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php'?>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Gestión de Categorías</h1>
                <h4 class="sub-header">Administrar categorías del sistema</h4>
                <p>Aquí puedes ver y editar todas las categorías disponibles en el sistema</p>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'categoria_actualizada':
                                echo 'Categoría actualizada exitosamente.';
                                break;
                            case 'categoria_eliminada':
                                echo 'Categoría eliminada exitosamente.';
                                break;
                            default:
                                echo 'Operación realizada exitosamente.';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        switch($_GET['error']) {
                            case 'categoria_no_encontrada':
                                echo 'La categoría solicitada no existe.';
                                break;
                            default:
                                echo 'Ha ocurrido un error.';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Lista de Categorías</h3>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Ruta</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($categorias as $categoria): ?>
                                            <tr>
                                                <td><?= $categoria[0] ?></td>
                                                <td><?= htmlspecialchars($categoria[1]) ?></td>
                                                <td><?= htmlspecialchars($categoria[2]) ?></td>
                                                <td><?= htmlspecialchars($categoria[3]) ?></td>
                                                <td>
                                                    <a href="editarCategoria.php?id=<?= $categoria[0] ?>" class="btn btn-sm btn-primary">
                                                        <span class="glyphicon glyphicon-edit"></span> Editar
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>