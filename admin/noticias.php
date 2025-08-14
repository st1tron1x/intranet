<?php
require '../scripts/funciones.php';

// Validación de acceso
if (!haIniciadoSesion()) {
    header('Location: ../index.html');
    exit();
}

if (!esAdmin()) {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

conectar();

// Acciones: eliminar
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $conn->query("DELETE FROM noticias WHERE id = $id");
    header("Location: noticias.php?msg=eliminado");
    exit();
}

// Obtener noticias
global $conexion;
$noticias = mysqli_query($conexion, "SELECT * FROM noticias ORDER BY fecha_publicacion DESC");

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Noticias</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
</head>
<body>
<?php include 'menu-superior.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'menu-lateral.php'; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header">Noticias</h1>

            <!-- Formulario para agregar o editar noticia -->
            <div class="panel panel-default">
                <div class="panel-heading">Crear Noticia</div>
                <div class="panel-body">
                    <form action="../scripts/guardar_noticia.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="titulo">Título</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="contenido">Contenido</label>
                            <textarea class="form-control" name="contenido" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="imagen">Imagen</label>
                            <input type="file" class="form-control" name="imagen">
                        </div>
                        <div class="form-group">
                            <label for="fecha_publicacion">Fecha de Publicación</label>
                            <input type="date" class="form-control" name="fecha_publicacion" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estado" class="form-control">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Noticia</button>
                    </form>
                </div>
            </div>

            <!-- Lista de noticias existentes -->
            <h3>Noticias Publicadas</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($n = $noticias->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($n['titulo']) ?></td>
                            <td><?= $n['fecha_publicacion'] ?></td>
                            <td><?= $n['estado'] ?></td>
                            <td>
                                <a href="editar_noticia.php?id=<?= $n['id'] ?>" class="btn btn-xs btn-warning">Editar</a>
                                <a href="noticias.php?eliminar=<?= $n['id'] ?>" class="btn btn-xs btn-danger" onclick="return confirm('Confirma eliminar?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
</body>
</html>