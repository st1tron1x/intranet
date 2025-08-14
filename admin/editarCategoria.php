<?php
    require'../scripts/funciones.php';
    //validar la sesion como admin
    if(!haIniciadoSesion() || !esAdmin() )
    {
        header('Location: ../index.html');
        exit();
    }
    //verificacion de parametro GET:
    if(isset($_GET['id']))
        $id=$_GET['id'];
    else header('Location: ../admin/panelAdmin.php');
    //captura categorias
    conectar();
    $categoria = getCategoriaPorId($id);
    desconectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoias</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'menu-superior.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php'?>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Edicion de Categorias</h1>

                <h4 class="sub-header">modificando categorias a mostrar</h4>
                <p>Se estan modificando las categorias del sistema, que son de visual para los usuarios</p>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        Categoría actualizada exitosamente.
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Edicion de Categorias</h3>
                            </div>
                            <div class="panel-body">
                                <form action="../scripts/editar-categoria.php" method="POST">
                                    <div class="form-group">
                                        <label for="txtId">ID Categoria</label>
                                        <input type="number" class="form-control" id="txtId" name="txtId" value="<?= $categoria[0]?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="txtNombre">Nombre</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" value="<?= $categoria[1]?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="txtDescripcion">descripcion</label>
                                        <input type="text" class="form-control" id="txtDescripcion" name="txtDescripcion" value="<?= $categoria[2]?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="txtRuta">Ruta</label>
                                        <input type="text" class="form-control" id="txtRuta" name="txtRuta" value="<?= $categoria[3]?>">
                                    </div>
                                    <button type="submit" class="btn btn-success">Guardar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>