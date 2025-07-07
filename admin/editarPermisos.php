<?php
    require '../scripts/funciones.php';
    //validacion de inicio de sesion como admin
    if(! haIniciadoSesion() || ! esAdmin() )
    {
        header('Location: index.html');
        exit();
    }
    //verificacion parametro GET
    if( isset($_GET['usuario']))
        $usuario = $_GET['usuario'];
    else {
        header('Location: panelAdmin.php');
        exit();
    } 
    //Captura de categorias:
    conectar();
    $categorias = getTodasCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administracion</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
</head>

<body>
    <?php include 'menu-superior.php';?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php';?>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Panel Administrativo</h1>
                <h4 class="sub-header">modificando permisos de acceso</h4>
                <p>Se estan modificando los permisos para el usuario:</p>
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <div class="panel panel-success">
                            <div class="panel-heading"><h3 class="panel-tittle">Edicion de permisos</h3></div>
                            <div class="panel-body">
                                <form action="../scripts/actualizarPermisos.php" method="POST">
                                    <div class="form-group">
                                        <label for="txtUsuario">Usuario</label>
                                        <input type="text" class="form-control" name="txtUsuario" id="txtUsuario" value="<?= htmlspecialchars($usuario)?>" readonly>
                                    </div>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <div class="checkbox">
                                            <label for="">
                                                <input type="checkbox" name="categoria<?= $categoria[0]?>" <?php if(tienePermisos($usuario, $categoria[0])) echo 'checked'; ?>><?=$categoria[1]?>
                                            </label>
                                        </div>
                                    <?php
                                        endforeach;
                                        desconectar(); 
                                    ?>
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