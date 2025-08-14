<?php
require '../scripts/funciones.php';

// Validar acceso
if (!haIniciadoSesion() || !esAdmin()) {
    header("Location: ../index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: noticias.php");
    exit();
}

$id = (int) $_GET['id'];
conectar();

$sql = "SELECT * FROM noticias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2>Noticia no encontrada</h2>";
    exit();
}

$noticia = $result->fetch_assoc();
$stmt->close();
desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Noticia</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
</head>
<body>
<?php include 'menu-superior.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'menu-lateral.php'; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header">Editar Noticia</h1>

            <div class="panel panel-default">
                <div class="panel-heading">Formulario de Edición</div>
                <div class="panel-body">
                    <form action="../scripts/actualizar_noticia.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
                        <div class="form-group">
                            <label for="titulo">Título</label>
                            <input type="text" class="form-control" name="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="contenido">Contenido</label>
                            <textarea class="form-control" name="contenido" rows="5" required><?= htmlspecialchars($noticia['contenido']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="imagen">Imagen Actual:</label><br>
                            <?php if ($noticia['imagen']): ?>
                                <img src="../uploads/imagenes_noticias/<?= $noticia['imagen'] ?>" width="150"><br><br>
                            <?php endif; ?>
                            <input type="file" name="imagen" class="form-control">
                            <small>Subir nueva imagen si deseas reemplazarla</small>
                        </div>
                        <div class="form-group">
                            <label for="fecha_publicacion">Fecha de Publicación</label>
                            <input type="date" class="form-control" name="fecha_publicacion" value="<?= $noticia['fecha_publicacion'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estado" class="form-control">
                                <option value="activo" <?= $noticia['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $noticia['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Actualizar Noticia</button>
                        <a href="noticias.php" class="btn btn-default">Cancelar</a>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
