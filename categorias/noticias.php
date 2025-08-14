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
global $conexion;

// Obtener noticias activas
$resultado = mysqli_query($conexion, "SELECT * FROM noticias WHERE estado = 'activo' ORDER BY fecha_publicacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Noticias</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .noticia-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
        }
        .noticia-box img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .noticia-box h3 {
            margin-top: 0;
        }
        .noticia-fecha {
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include '../admin/menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin/menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Noticias</h1>
                
                <div class="row">
                    <?php 
                    if($resultado && mysqli_num_rows($resultado) > 0):
                        while ($n = mysqli_fetch_assoc($resultado)): 
                    ?>
                        <div class="col-md-4">
                            <div class="noticia-box">
                                <?php if ($n['imagen']): ?>
                                    <img src="../uploads/imagenes_noticias/<?= htmlspecialchars($n['imagen']) ?>" alt="Imagen Noticia">
                                <?php endif; ?>
                                <h3><?= htmlspecialchars($n['titulo']) ?></h3>
                                <p class="noticia-fecha">Publicado el <?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></p>
                                <p><?= nl2br(htmlspecialchars(substr($n['contenido'], 0, 150))) ?>...</p>
                                
                                <!-- Botón para ver noticia completa -->
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal<?= $n['id'] ?>">
                                    Leer más
                                </button>
                                
                                <!-- Modal para noticia completa -->
                                <div class="modal fade" id="modal<?= $n['id'] ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h4 class="modal-title"><?= htmlspecialchars($n['titulo']) ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php if ($n['imagen']): ?>
                                                    <img src="../uploads/imagenes_noticias/<?= htmlspecialchars($n['imagen']) ?>" alt="Imagen Noticia" class="img-responsive center-block" style="margin-bottom: 15px;">
                                                <?php endif; ?>
                                                <p class="noticia-fecha"><strong>Publicado el <?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></strong></p>
                                                <p><?= nl2br(htmlspecialchars($n['contenido'])) ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <p>No hay noticias disponibles en este momento.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts de Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    
    <?php
    desconectar();
    ?>
</body>
</html>