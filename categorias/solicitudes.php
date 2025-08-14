<?php
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header('Location: ../index.html');
    exit();
}

conectar(); // Conectar ANTES de usar las funciones

// Solo permitir cambios de estado a admins
if (esAdmin()) {
    // Cambiar estado (solo admins)
    if (isset($_GET['cambiar_estado']) && isset($_GET['nuevo_estado'])) {
        $id = (int)$_GET['cambiar_estado'];
        $nuevo_estado = $_GET['nuevo_estado'];
        cambiarEstadoSolicitud($id, $nuevo_estado);
        header('Location: solicitudes.php?mensaje=estado_actualizado');
        exit();
    }

    // Eliminar solicitud (solo admins)
    if (isset($_GET['eliminar'])) {
        $id = (int)$_GET['eliminar'];
        eliminarSolicitud($id);
        header('Location: solicitudes.php?mensaje=solicitud_eliminada');
        exit();
    }
    
    // Admins ven todas las solicitudes
    $solicitudes = getSolicitudes();
} else {
    // Usuarios normales solo ven sus propias solicitudes
    $solicitudes = obtenerSolicitudesPorUsuario($_SESSION['usuario']);
}

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
</head>
<body>
    <?php include("../admin/menu-superior.php"); ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../admin/menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">Solicitudes</h1>
                
                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'solicitud_creada':
                                echo 'Solicitud creada exitosamente.';
                                break;
                            case 'estado_actualizado':
                                echo 'Estado de solicitud actualizado.';
                                break;
                            case 'solicitud_eliminada':
                                echo 'Solicitud eliminada exitosamente.';
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!esAdmin()): ?>
                    <!-- Formulario para crear nueva solicitud (solo usuarios normales) -->
                    <div class="panel panel-default">
                        <div class="panel-heading">Nueva Solicitud</div>
                        <div class="panel-body">
                            <form action="../scripts/crear_solicitud.php" method="POST">
                                <div class="form-group">
                                    <label for="tipo">Tipo de Solicitud</label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="vacaciones">Vacaciones</option>
                                        <option value="certificado_laboral">Certificado Laboral</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="motivo">Motivo/Descripción</label>
                                    <textarea name="motivo" class="form-control" rows="3" required placeholder="Describe tu solicitud..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="correo_notificacion">Correo de Notificación</label>
                                    <input type="email" name="correo_notificacion" class="form-control" required placeholder="tu@email.com">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_inicio">Fecha Inicio (opcional)</label>
                                            <input type="date" name="fecha_inicio" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_fin">Fecha Fin (opcional)</label>
                                            <input type="date" name="fecha_fin" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Crear Solicitud</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <h3><?= esAdmin() ? 'Todas las Solicitudes' : 'Mis Solicitudes' ?></h3>
                
                <?php if (!empty($solicitudes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <?php if(esAdmin()): ?>
                                        <th>Usuario</th>
                                    <?php endif; ?>
                                    <th>Tipo</th>
                                    <th>Motivo</th>
                                    <th>Fecha Creación</th>
                                    <th>Estado</th>
                                    <?php if(esAdmin()): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $solicitud): ?>
                                    <tr>
                                        <?php if(esAdmin()): ?>
                                            <td><?= htmlspecialchars($solicitud['usuario']) ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="label label-info"><?= htmlspecialchars($solicitud['tipo']) ?></span>
                                        </td>
                                        <td>
                                            <span title="<?= htmlspecialchars($solicitud['motivo']) ?>">
                                                <?= htmlspecialchars(substr($solicitud['motivo'], 0, 50)) ?>
                                                <?= strlen($solicitud['motivo']) > 50 ? '...' : '' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                                        <td>
                                            <?php
                                            $estadoClass = '';
                                            switch($solicitud['estado']) {
                                                case 'pendiente': $estadoClass = 'label-warning'; break;
                                                case 'en_proceso': $estadoClass = 'label-info'; break;
                                                case 'aprobada': $estadoClass = 'label-success'; break;
                                                case 'rechazada': $estadoClass = 'label-danger'; break;
                                                default: $estadoClass = 'label-default';
                                            }
                                            ?>
                                            <span class="label <?= $estadoClass ?>"><?= ucfirst(str_replace('_', ' ', $solicitud['estado'])) ?></span>
                                        </td>
                                        
                                        <?php if(esAdmin()): ?>
                                            <td>
                                                <form method="get" style="display: inline-block; margin-right: 5px;">
                                                    <input type="hidden" name="cambiar_estado" value="<?= $solicitud['id'] ?>">
                                                    <select name="nuevo_estado" class="form-control input-sm" style="width: auto; display: inline-block;">
                                                        <option value="pendiente" <?= $solicitud['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                        <option value="en_proceso" <?= $solicitud['estado'] == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                        <option value="aprobada" <?= $solicitud['estado'] == 'aprobada' ? 'selected' : '' ?>>Aprobada</option>
                                                        <option value="rechazada" <?= $solicitud['estado'] == 'rechazada' ? 'selected' : '' ?>>Rechazada</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-xs">Actualizar</button>
                                                </form>
                                                <a href="?eliminar=<?= $solicitud['id'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de eliminar esta solicitud?')" 
                                                   class="btn btn-danger btn-xs">Eliminar</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p><?= esAdmin() ? 'No hay solicitudes registradas en el sistema.' : 'No tienes solicitudes registradas.' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>