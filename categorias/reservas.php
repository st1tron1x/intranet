<?php
// ===== categorias/reservas.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header("Location: ../index.html");
    exit();
}

conectar();

// Procesar nueva reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_reserva') {
    // Validar que no haya conflictos de horario
    $sql_check = "SELECT COUNT(*) as conflictos FROM reservas_salas 
                  WHERE sala_id = ? AND estado != 'cancelada' 
                  AND ((fecha_inicio BETWEEN ? AND ?) OR (fecha_fin BETWEEN ? AND ?) 
                  OR (fecha_inicio <= ? AND fecha_fin >= ?))";
    
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "issssss", 
        $_POST['sala_id'], $_POST['fecha_inicio'], $_POST['fecha_fin'],
        $_POST['fecha_inicio'], $_POST['fecha_fin'], 
        $_POST['fecha_inicio'], $_POST['fecha_fin']);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $conflictos = mysqli_fetch_assoc($result_check)['conflictos'];
    
    if ($conflictos == 0) {
        $sql = "INSERT INTO reservas_salas (sala_id, usuario, titulo, descripcion, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "isssss", 
            $_POST['sala_id'], $_SESSION['usuario'], $_POST['titulo'], 
            $_POST['descripcion'], $_POST['fecha_inicio'], $_POST['fecha_fin']);
        
        if (mysqli_stmt_execute($stmt)) {
            logAuditoria($_SESSION['usuario'], "Reserva de sala creada", 'reservas_salas', mysqli_insert_id($conexion));
            header('Location: reservas.php?mensaje=reserva_creada');
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_mensaje = "conflicto_horario";
    }
    mysqli_stmt_close($stmt_check);
}

// Cancelar reserva
if (isset($_GET['cancelar'])) {
    $reserva_id = (int)$_GET['cancelar'];
    
    // Verificar que la reserva pertenece al usuario o es admin
    $sql_verify = "SELECT usuario FROM reservas_salas WHERE id = ?";
    $stmt_verify = mysqli_prepare($conexion, $sql_verify);
    mysqli_stmt_bind_param($stmt_verify, "i", $reserva_id);
    mysqli_stmt_execute($stmt_verify);
    $result_verify = mysqli_stmt_get_result($stmt_verify);
    
    if ($row = mysqli_fetch_assoc($result_verify)) {
        if ($row['usuario'] === $_SESSION['usuario'] || esAdmin()) {
            $sql_cancel = "UPDATE reservas_salas SET estado = 'cancelada' WHERE id = ?";
            $stmt_cancel = mysqli_prepare($conexion, $sql_cancel);
            mysqli_stmt_bind_param($stmt_cancel, "i", $reserva_id);
            mysqli_stmt_execute($stmt_cancel);
            
            logAuditoria($_SESSION['usuario'], "Reserva cancelada", 'reservas_salas', $reserva_id);
            mysqli_stmt_close($stmt_cancel);
        }
    }
    mysqli_stmt_close($stmt_verify);
    
    header('Location: reservas.php?mensaje=reserva_cancelada');
    exit();
}

// Obtener salas disponibles
$salas = mysqli_fetch_all(mysqli_query($conexion, "SELECT * FROM salas WHERE activo = 1 ORDER BY nombre"), MYSQLI_ASSOC);

// Obtener reservas del usuario o todas si es admin
if (esAdmin()) {
    $sql_reservas = "SELECT r.*, s.nombre as sala_nombre, u.nombre_completo 
                     FROM reservas_salas r 
                     JOIN salas s ON r.sala_id = s.id 
                     LEFT JOIN usuarios u ON r.usuario = u.usuario 
                     WHERE r.fecha_inicio >= CURDATE() - INTERVAL 7 DAY 
                     ORDER BY r.fecha_inicio DESC";
    $reservas = mysqli_fetch_all(mysqli_query($conexion, $sql_reservas), MYSQLI_ASSOC);
} else {
    $sql_reservas = "SELECT r.*, s.nombre as sala_nombre 
                     FROM reservas_salas r 
                     JOIN salas s ON r.sala_id = s.id 
                     WHERE r.usuario = ? AND r.fecha_inicio >= CURDATE() - INTERVAL 7 DAY 
                     ORDER BY r.fecha_inicio DESC";
    $stmt_reservas = mysqli_prepare($conexion, $sql_reservas);
    mysqli_stmt_bind_param($stmt_reservas, "s", $_SESSION['usuario']);
    mysqli_stmt_execute($stmt_reservas);
    $reservas = mysqli_fetch_all(mysqli_stmt_get_result($stmt_reservas), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_reservas);
}

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas de Salas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .sala-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .reserva-item {
            border-left: 4px solid #007bff;
            padding: 10px;
            margin-bottom: 10px;
            background: white;
        }
        .reserva-item.confirmada { border-color: #28a745; }
        .reserva-item.cancelada { border-color: #dc3545; opacity: 0.7; }
    </style>
</head>
<body>
    <?php include '../admin/menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin/menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">
                    <span class="glyphicon glyphicon-calendar"></span> Reservas de Salas
                </h1>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'reserva_creada': echo 'Reserva creada exitosamente.'; break;
                            case 'reserva_cancelada': echo 'Reserva cancelada.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error_mensaje)): ?>
                    <div class="alert alert-danger">
                        <?php 
                        switch($error_mensaje) {
                            case 'conflicto_horario': echo 'La sala ya está reservada en ese horario.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de nueva reserva -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Nueva Reserva</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear_reserva">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sala *</label>
                                        <select name="sala_id" class="form-control" required>
                                            <option value="">Seleccionar sala...</option>
                                            <?php foreach ($salas as $sala): ?>
                                                <option value="<?= $sala['id'] ?>">
                                                    <?= htmlspecialchars($sala['nombre']) ?> 
                                                    (Capacidad: <?= $sala['capacidad'] ?> personas)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Título de la Reunión *</label>
                                        <input type="text" name="titulo" class="form-control" required 
                                               placeholder="Ej: Reunión de equipo">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2" 
                                          placeholder="Descripción opcional de la reunión..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha y Hora de Inicio *</label>
                                        <input type="datetime-local" name="fecha_inicio" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha y Hora de Fin *</label>
                                        <input type="datetime-local" name="fecha_fin" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Crear Reserva
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Información de salas -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Salas Disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php foreach ($salas as $sala): ?>
                                <div class="col-md-6">
                                    <div class="sala-card">
                                        <h5><?= htmlspecialchars($sala['nombre']) ?></h5>
                                        <p><strong>Capacidad:</strong> <?= $sala['capacidad'] ?> personas</p>
                                        <?php if ($sala['descripcion']): ?>
                                            <p><?= htmlspecialchars($sala['descripcion']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($sala['equipamiento']): ?>
                                            <p><strong>Equipamiento:</strong><br>
                                            <?php 
                                            $equipamiento = json_decode($sala['equipamiento'], true);
                                            if (is_array($equipamiento)) {
                                                foreach ($equipamiento as $equipo) {
                                                    echo '<span class="label label-default">' . htmlspecialchars($equipo) . '</span> ';
                                                }
                                            }
                                            ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Mis reservas -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <?= esAdmin() ? 'Todas las Reservas' : 'Mis Reservas' ?> 
                            (últimos 7 días)
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($reservas)): ?>
                            <?php foreach ($reservas as $reserva): ?>
                                <div class="reserva-item <?= $reserva['estado'] ?>">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 style="margin-top: 0;">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                                <?= htmlspecialchars($reserva['titulo']) ?>
                                            </h5>
                                            
                                            <p style="margin-bottom: 5px;">
                                                <strong>Sala:</strong> <?= htmlspecialchars($reserva['sala_nombre']) ?>
                                                <?php if (esAdmin() && isset($reserva['nombre_completo'])): ?>
                                                    | <strong>Usuario:</strong> <?= htmlspecialchars($reserva['nombre_completo']) ?>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <p style="margin-bottom: 5px;">
                                                <strong>Fecha:</strong> 
                                                <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> - 
                                                <?= date('H:i', strtotime($reserva['fecha_fin'])) ?>
                                            </p>
                                            
                                            <?php if ($reserva['descripcion']): ?>
                                                <p style="margin-bottom: 0;">
                                                    <small><?= htmlspecialchars($reserva['descripcion']) ?></small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 text-right">
                                            <span class="label label-<?= $reserva['estado'] === 'confirmada' ? 'success' : ($reserva['estado'] === 'cancelada' ? 'default' : 'warning') ?>">
                                                <?= ucfirst($reserva['estado']) ?>
                                            </span>
                                            
                                            <?php if ($reserva['estado'] === 'confirmada' && 
                                                     ($reserva['usuario'] === $_SESSION['usuario'] || esAdmin()) &&
                                                     strtotime($reserva['fecha_inicio']) > time()): ?>
                                                <br><br>
                                                <a href="reservas.php?cancelar=<?= $reserva['id'] ?>" 
                                                   class="btn btn-xs btn-danger"
                                                   onclick="return confirm('¿Seguro que desea cancelar esta reserva?')">
                                                    <span class="glyphicon glyphicon-remove"></span> Cancelar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                No tienes reservas registradas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    
    <script>
        // Validar que fecha fin sea mayor a fecha inicio
        $('input[name="fecha_inicio"], input[name="fecha_fin"]').on('change', function() {
            var inicio = $('input[name="fecha_inicio"]').val();
            var fin = $('input[name="fecha_fin"]').val();
            
            if (inicio && fin && fin <= inicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                $('input[name="fecha_fin"]').val('');
            }
        });
        
        // Establecer fecha mínima como hoy
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('input[name="fecha_inicio"], input[name="fecha_fin"]').attr('min', now.toISOString().slice(0,16));
    </script>
</body>
</html>

<?php
// ===== admin/gestionar_salas.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header('Location: ../index.html');
    exit();
}

conectar();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear_sala':
                $equipamiento = isset($_POST['equipamiento']) ? json_encode($_POST['equipamiento']) : null;
                
                $sql = "INSERT INTO salas (nombre, descripcion, capacidad, equipamiento) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ssis", $_POST['nombre'], $_POST['descripcion'], $_POST['capacidad'], $equipamiento);
                
                if (mysqli_stmt_execute($stmt)) {
                    logAuditoria($_SESSION['usuario'], "Sala creada", 'salas', mysqli_insert_id($conexion));
                    header('Location: gestionar_salas.php?mensaje=sala_creada');
                } else {
                    header('Location: gestionar_salas.php?error=error_crear_sala');
                }
                mysqli_stmt_close($stmt);
                exit();
                break;
                
            case 'toggle_activo':
                $id = (int)$_POST['sala_id'];
                $activo = (int)$_POST['activo'];
                
                $sql = "UPDATE salas SET activo = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $activo, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                logAuditoria($_SESSION['usuario'], "Sala " . ($activo ? "activada" : "desactivada"), 'salas', $id);
                header('Location: gestionar_salas.php?mensaje=sala_actualizada');
                exit();
                break;
        }
    }
}

$salas = mysqli_fetch_all(mysqli_query($conexion, "SELECT * FROM salas ORDER BY nombre"), MYSQLI_ASSOC);

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Salas</title>
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
                    <span class="glyphicon glyphicon-home"></span> Gestionar Salas
                </h1>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'sala_creada': echo 'Sala creada exitosamente.'; break;
                            case 'sala_actualizada': echo 'Sala actualizada exitosamente.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario para crear sala -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Crear Nueva Sala</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear_sala">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre de la Sala *</label>
                                        <input type="text" name="nombre" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Capacidad (personas) *</label>
                                        <input type="number" name="capacidad" class="form-control" min="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Equipamiento Disponible</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Proyector"> Proyector</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Audio"> Sistema de Audio</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Video conferencia"> Video Conferencia</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Pizarra"> Pizarra</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Aire acondicionado"> Aire Acondicionado</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Wifi"> WiFi</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Computadores"> Computadores</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="equipamiento[]" value="Teléfono"> Teléfono</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Crear Sala
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de salas -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Salas Registradas</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Capacidad</th>
                                        <th>Equipamiento</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salas as $sala): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sala['nombre']) ?></td>
                                            <td><?= htmlspecialchars($sala['descripcion'] ?: '-') ?></td>
                                            <td><?= $sala['capacidad'] ?> personas</td>
                                            <td>
                                                <?php 
                                                if ($sala['equipamiento']) {
                                                    $equipamiento = json_decode($sala['equipamiento'], true);
                                                    if (is_array($equipamiento)) {
                                                        foreach ($equipamiento as $equipo) {
                                                            echo '<span class="label label-default">' . htmlspecialchars($equipo) . '</span> ';
                                                        }
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($sala['activo']): ?>
                                                    <span class="label label-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="label label-default">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="accion" value="toggle_activo">
                                                    <input type="hidden" name="sala_id" value="<?= $sala['id'] ?>">
                                                    <input type="hidden" name="activo" value="<?= $sala['activo'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-xs <?= $sala['activo'] ? 'btn-warning' : 'btn-success' ?>">
                                                        <?= $sala['activo'] ? 'Desactivar' : 'Activar' ?>
                                                    </button>
                                                </form>
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