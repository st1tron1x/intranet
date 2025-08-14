<?php
require '../scripts/funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header('Location: ../index.html');
    exit();
}

conectar();

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if (eliminarDocumento($id)) {
        header('Location: documentos.php?mensaje=documento_eliminado');
    } else {
        header('Location: documentos.php?error=error_eliminando');
    }
    exit();
}

$categorias = getCategoriasDocumentos();
$documentos = getTodosDocumentos();

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Documentos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
</head>
<body>
    <?php include 'menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">
                    <span class="glyphicon glyphicon-folder-open"></span> Gestión de Documentos
                </h1>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'documento_subido':
                                echo 'Documento subido exitosamente.';
                                break;
                            case 'documento_eliminado':
                                echo 'Documento eliminado exitosamente.';
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        switch($_GET['error']) {
                            case 'error_subiendo':
                                echo 'Error al subir el documento.';
                                break;
                            case 'error_eliminando':
                                echo 'Error al eliminar el documento.';
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario para subir documentos -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span class="glyphicon glyphicon-cloud-upload"></span> Subir Nuevo Documento
                        </h3>
                    </div>
                    <div class="panel-body">
                        <form action="../scripts/subir_documento.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="titulo">Título del Documento *</label>
                                        <input type="text" class="form-control" name="titulo" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="categoria_id">Categoría *</label>
                                        <select name="categoria_id" class="form-control" required>
                                            <option value="">Seleccionar categoría...</option>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3" placeholder="Describe brevemente el contenido del documento..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="archivo">Archivo *</label>
                                        <input type="file" class="form-control" name="archivo" required>
                                        <small class="help-block">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG (Max: 10MB)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="version">Versión</label>
                                        <input type="text" class="form-control" name="version" value="1.0" placeholder="1.0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tags">Tags (opcional)</label>
                                        <input type="text" class="form-control" name="tags" placeholder="manual, contable, importante">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-cloud-upload"></span> Subir Documento
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de documentos existentes -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Documentos Existentes (<?= count($documentos) ?>)</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoría</th>
                                        <th>Tipo</th>
                                        <th>Tamaño</th>
                                        <th>Versión</th>
                                        <th>Usuario</th>
                                        <th>Fecha</th>
                                        <th>Descargas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $doc): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($doc['titulo']) ?></strong>
                                                <?php if ($doc['descripcion']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($doc['descripcion'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($doc['categoria_nombre']) ?></td>
                                            <td>
                                                <span class="label label-default"><?= strtoupper($doc['tipo_archivo']) ?></span>
                                            </td>
                                            <td><?= formatBytes($doc['tamaño']) ?></td>
                                            <td><?= htmlspecialchars($doc['version']) ?></td>
                                            <td><?= htmlspecialchars($doc['usuario_subida']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($doc['fecha_actualizacion'])) ?></td>
                                            <td>
                                                <span class="badge"><?= $doc['descargas'] ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-xs">
                                                    <a href="../scripts/descargar_documento.php?id=<?= $doc['id'] ?>" 
                                                       class="btn btn-success" title="Descargar">
                                                        <span class="glyphicon glyphicon-download"></span>
                                                    </a>
                                                    <a href="editar_documento.php?id=<?= $doc['id'] ?>" 
                                                       class="btn btn-warning" title="Editar">
                                                        <span class="glyphicon glyphicon-edit"></span>
                                                    </a>
                                                    <a href="documentos.php?eliminar=<?= $doc['id'] ?>" 
                                                       class="btn btn-danger" title="Eliminar"
                                                       onclick="return confirm('¿Estás seguro de eliminar este documento?')">
                                                        <span class="glyphicon glyphicon-trash"></span>
                                                    </a>
                                                </div>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
function formatBytes($size, $precision = 2) {
    if ($size == 0) return '0 B';
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>