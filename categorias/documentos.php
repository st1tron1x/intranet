<?php
include_once '../scripts/funciones.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!haIniciadoSesion()) {
    header("Location: ../index.html");
    exit();
}

conectar();

// Manejar búsqueda
$documentos = [];
$categorias = getCategoriasDocumentos();
$busqueda = '';

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];
    $documentos = buscarDocumentos($busqueda);
} elseif (isset($_GET['categoria'])) {
    $categoria_id = (int)$_GET['categoria'];
    $documentos = getDocumentosPorCategoria($categoria_id);
} else {
    $documentos = getTodosDocumentos();
}

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentación</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .doc-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: box-shadow 0.2s;
        }
        .doc-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .doc-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .doc-meta {
            font-size: 0.9em;
            color: #666;
        }
        .categoria-btn {
            margin: 5px;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../admin/menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin/menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">
                    <span class="glyphicon glyphicon-folder-open"></span> Documentación
                </h1>
                
                <!-- Búsqueda -->
                <div class="search-box">
                    <form method="GET" class="form-inline">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" name="buscar" class="form-control" placeholder="Buscar documentos..." value="<?= htmlspecialchars($busqueda) ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">
                                    <span class="glyphicon glyphicon-search"></span>
                                </button>
                            </span>
                        </div>
                        <?php if ($busqueda): ?>
                            <a href="documentos.php" class="btn btn-default">Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Filtros por categoría -->
                <div class="panel panel-default">
                    <div class="panel-heading">Categorías</div>
                    <div class="panel-body">
                        <a href="documentos.php" class="btn btn-default categoria-btn <?= !isset($_GET['categoria']) ? 'active' : '' ?>">
                            <span class="glyphicon glyphicon-th"></span> Todos
                        </a>
                        <?php foreach ($categorias as $cat): ?>
                            <a href="documentos.php?categoria=<?= $cat['id'] ?>" 
                               class="btn btn-info categoria-btn <?= isset($_GET['categoria']) && $_GET['categoria'] == $cat['id'] ? 'active' : '' ?>">
                                <span class="glyphicon glyphicon-<?= $cat['icono'] ?>"></span> <?= htmlspecialchars($cat['nombre']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resultados -->
                <?php if ($busqueda): ?>
                    <h4>Resultados para: "<?= htmlspecialchars($busqueda) ?>" (<?= count($documentos) ?> encontrados)</h4>
                <?php endif; ?>

                <div class="row">
                    <?php if (!empty($documentos)): ?>
                        <?php foreach ($documentos as $doc): ?>
                            <div class="col-md-4">
                                <div class="doc-card">
                                    <div class="text-center doc-icon">
                                        <?php
                                        $iconos = [
                                            'pdf' => 'file-pdf-o text-danger',
                                            'doc' => 'file-word-o text-primary',
                                            'docx' => 'file-word-o text-primary',
                                            'xls' => 'file-excel-o text-success',
                                            'xlsx' => 'file-excel-o text-success',
                                            'ppt' => 'file-powerpoint-o text-warning',
                                            'pptx' => 'file-powerpoint-o text-warning',
                                            'txt' => 'file-text-o',
                                            'jpg' => 'file-image-o text-info',
                                            'jpeg' => 'file-image-o text-info',
                                            'png' => 'file-image-o text-info'
                                        ];
                                        $icono = $iconos[$doc['tipo_archivo']] ?? 'file-o';
                                        ?>
                                        <span class="glyphicon glyphicon-<?= $icono ?>"></span>
                                    </div>
                                    
                                    <h4 style="margin-top: 0;"><?= htmlspecialchars($doc['titulo']) ?></h4>
                                    
                                    <?php if ($doc['descripcion']): ?>
                                        <p><?= htmlspecialchars(substr($doc['descripcion'], 0, 100)) ?><?= strlen($doc['descripcion']) > 100 ? '...' : '' ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="doc-meta">
                                        <small>
                                            <span class="glyphicon glyphicon-tag"></span> <?= htmlspecialchars($doc['categoria_nombre'] ?? '') ?><br>
                                            <span class="glyphicon glyphicon-calendar"></span> <?= date('d/m/Y', strtotime($doc['fecha_actualizacion'])) ?><br>
                                            <span class="glyphicon glyphicon-user"></span> <?= htmlspecialchars($doc['usuario_subida']) ?><br>
                                            <span class="glyphicon glyphicon-download"></span> <?= $doc['descargas'] ?> descargas<br>
                                            <span class="glyphicon glyphicon-tag"></span> v<?= htmlspecialchars($doc['version']) ?>
                                        </small>
                                    </div>
                                    
                                    <div style="margin-top: 15px;">
                                        <a href="../scripts/descargar_documento.php?id=<?= $doc['id'] ?>" 
                                           class="btn btn-success btn-sm">
                                            <span class="glyphicon glyphicon-download"></span> Descargar
                                        </a>
                                        
                                        <button type="button" class="btn btn-info btn-sm" 
                                                data-toggle="modal" data-target="#modal<?= $doc['id'] ?>">
                                            <span class="glyphicon glyphicon-eye-open"></span> Ver Detalles
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Modal para detalles -->
                                <div class="modal fade" id="modal<?= $doc['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                                <h4 class="modal-title"><?= htmlspecialchars($doc['titulo']) ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($doc['descripcion'])) ?></p>
                                                <p><strong>Categoría:</strong> <?= htmlspecialchars($doc['categoria_nombre'] ?? '') ?></p>
                                                <p><strong>Tipo:</strong> <?= strtoupper($doc['tipo_archivo']) ?></p>
                                                <p><strong>Tamaño:</strong> <?= formatBytes($doc['tamaño']) ?></p>
                                                <p><strong>Versión:</strong> <?= htmlspecialchars($doc['version']) ?></p>
                                                <p><strong>Subido por:</strong> <?= htmlspecialchars($doc['usuario_subida']) ?></p>
                                                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($doc['fecha_actualizacion'])) ?></p>
                                                <?php if ($doc['tags']): ?>
                                                    <p><strong>Tags:</strong> <?= htmlspecialchars($doc['tags']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="../scripts/descargar_documento.php?id=<?= $doc['id'] ?>" 
                                                   class="btn btn-success">
                                                    <span class="glyphicon glyphicon-download"></span> Descargar
                                                </a>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-12">
                            <div class="alert alert-info text-center">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                <?php if ($busqueda): ?>
                                    No se encontraron documentos que coincidan con "<?= htmlspecialchars($busqueda) ?>"
                                <?php else: ?>
                                    No hay documentos disponibles en esta categoría.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Función auxiliar para formatear tamaños de archivo
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>