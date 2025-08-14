<?php
// ===== categorias/calendario.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header("Location: ../index.html");
    exit();
}

conectar();

// Obtener eventos del mes actual
$mes = $_GET['mes'] ?? date('Y-m');
$fecha_inicio = $mes . '-01';
$fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

// Obtener eventos públicos y del usuario
$sql = "SELECT e.*, u.nombre_completo as creado_por_nombre 
        FROM eventos e 
        LEFT JOIN usuarios u ON e.creado_por = u.usuario 
        WHERE (fecha_inicio BETWEEN ? AND ? OR fecha_fin BETWEEN ? AND ?)
        AND (visible_para IS NULL OR JSON_CONTAINS(visible_para, ?) OR creado_por = ?)
        ORDER BY fecha_inicio";

$stmt = mysqli_prepare($conexion, $sql);
$usuario_json = '"' . $_SESSION['usuario'] . '"';
mysqli_stmt_bind_param($stmt, "ssssss", $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin, $usuario_json, $_SESSION['usuario']);
mysqli_stmt_execute($stmt);
$eventos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Obtener cumpleaños del mes
$mes_num = date('m', strtotime($fecha_inicio));
$sql_cumple = "SELECT nombre_completo, fecha_ingreso, 
               CONCAT(YEAR(?), '-', MONTH(fecha_ingreso), '-', DAY(fecha_ingreso)) as cumple_este_año
               FROM usuarios 
               WHERE activo = 1 AND fecha_ingreso IS NOT NULL 
               AND MONTH(fecha_ingreso) = ?";
$stmt_cumple = mysqli_prepare($conexion, $sql_cumple);
mysqli_stmt_bind_param($stmt_cumple, "si", $fecha_inicio, $mes_num);
mysqli_stmt_execute($stmt_cumple);
$cumpleanos = mysqli_fetch_all(mysqli_stmt_get_result($stmt_cumple), MYSQLI_ASSOC);

desconectar();

// Generar calendario
function generarCalendario($mes, $eventos, $cumpleanos) {
    $fecha = new DateTime($mes . '-01');
    $mes_nombre = $fecha->format('F Y');
    $primer_dia = (int)$fecha->format('N') - 1; // 0 = Lunes
    $dias_mes = (int)$fecha->format('t');
    
    // Crear array de eventos por día
    $eventos_por_dia = [];
    foreach ($eventos as $evento) {
        $dia = date('j', strtotime($evento['fecha_inicio']));
        if (!isset($eventos_por_dia[$dia])) {
            $eventos_por_dia[$dia] = [];
        }
        $eventos_por_dia[$dia][] = $evento;
    }
    
    // Agregar cumpleaños como eventos
    foreach ($cumpleanos as $cumple) {
        $dia = date('j', strtotime($cumple['cumple_este_año']));
        if (!isset($eventos_por_dia[$dia])) {
            $eventos_por_dia[$dia] = [];
        }
        $eventos_por_dia[$dia][] = [
            'titulo' => '🎂 ' . $cumple['nombre_completo'],
            'tipo' => 'cumpleanos',
            'color' => '#ffc107'
        ];
    }
    
    echo "<div class='calendar'>";
    echo "<div class='calendar-header'>";
    echo "<h3>" . ucfirst(strftime('%B %Y', $fecha->getTimestamp())) . "</h3>";
    echo "</div>";
    
    echo "<div class='calendar-grid'>";
    
    // Headers de días
    $dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    foreach ($dias_semana as $dia) {
        echo "<div class='calendar-day-header'>$dia</div>";
    }
    
    // Espacios vacíos al inicio
    for ($i = 0; $i < $primer_dia; $i++) {
        echo "<div class='calendar-day empty'></div>";
    }
    
    // Días del mes
    for ($dia = 1; $dia <= $dias_mes; $dia++) {
        $hoy = date('Y-m-d') === $mes . '-' . sprintf('%02d', $dia);
        $class = 'calendar-day' . ($hoy ? ' today' : '');
        
        echo "<div class='$class' data-dia='$dia'>";
        echo "<div class='day-number'>$dia</div>";
        
        if (isset($eventos_por_dia[$dia])) {
            foreach ($eventos_por_dia[$dia] as $evento) {
                $color = $evento['color'] ?? '#007bff';
                echo "<div class='event' style='background-color: $color' title='" . htmlspecialchars($evento['titulo']) . "'>";
                echo htmlspecialchars(substr($evento['titulo'], 0, 15));
                if (strlen($evento['titulo']) > 15) echo '...';
                echo "</div>";
            }
        }
        
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario Corporativo</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .calendar {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .calendar-header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        
        .calendar-day-header {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        
        .calendar-day {
            min-height: 100px;
            border: 1px solid #dee2e6;
            padding: 5px;
            position: relative;
        }
        
        .calendar-day.empty {
            background: #f8f9fa;
        }
        
        .calendar-day.today {
            background: #e3f2fd;
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .event {
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 10px;
            margin-bottom: 2px;
            cursor: pointer;
        }
        
        .evento-item {
            border-left: 4px solid #007bff;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .evento-item.cumpleanos { border-color: #ffc107; }
        .evento-item.capacitacion { border-color: #28a745; }
        .evento-item.reunion { border-color: #17a2b8; }
        .evento-item.festivo { border-color: #dc3545; }
    </style>
</head>
<body>
    <?php include '../admin/menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin/menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">
                    <span class="glyphicon glyphicon-calendar"></span> Calendario Corporativo
                </h1>
                
                <!-- Navegación de mes -->
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="calendario.php?mes=<?= date('Y-m', strtotime($mes . '-01 -1 month')) ?>" class="btn btn-default">
                                    <span class="glyphicon glyphicon-chevron-left"></span> Mes Anterior
                                </a>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4><?= ucfirst(strftime('%B %Y', strtotime($fecha_inicio))) ?></h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="calendario.php?mes=<?= date('Y-m', strtotime($mes . '-01 +1 month')) ?>" class="btn btn-default">
                                    Mes Siguiente <span class="glyphicon glyphicon-chevron-right"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Calendario visual -->
                    <div class="col-md-8">
                        <?php generarCalendario($mes, $eventos, $cumpleanos); ?>
                    </div>
                    
                    <!-- Lista de eventos -->
                    <div class="col-md-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Eventos del Mes</h3>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($eventos)): ?>
                                    <?php foreach ($eventos as $evento): ?>
                                        <div class="evento-item <?= $evento['tipo'] ?>">
                                            <h5 style="margin-top: 0;">
                                                <?= htmlspecialchars($evento['titulo']) ?>
                                            </h5>
                                            
                                            <p style="margin-bottom: 5px;">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                                <?php if ($evento['todo_el_dia']): ?>
                                                    <?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?>
                                                    <?php if ($evento['fecha_fin'] && $evento['fecha_fin'] !== $evento['fecha_inicio']): ?>
                                                        - <?= date('d/m/Y', strtotime($evento['fecha_fin'])) ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?= date('d/m/Y H:i', strtotime($evento['fecha_inicio'])) ?>
                                                    <?php if ($evento['fecha_fin']): ?>
                                                        - <?= date('H:i', strtotime($evento['fecha_fin'])) ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <?php if ($evento['descripcion']): ?>
                                                <p style="margin-bottom: 5px;">
                                                    <small><?= htmlspecialchars($evento['descripcion']) ?></small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <span class="label label-primary"><?= ucfirst($evento['tipo']) ?></span>
                                            
                                            <?php if ($evento['creado_por_nombre']): ?>
                                                <small class="text-muted">
                                                    por <?= htmlspecialchars($evento['creado_por_nombre']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($cumpleanos)): ?>
                                    <h5>🎂 Cumpleaños</h5>
                                    <?php foreach ($cumpleanos as $cumple): ?>
                                        <div class="evento-item cumpleanos">
                                            <p style="margin: 0;">
                                                <strong><?= htmlspecialchars($cumple['nombre_completo']) ?></strong><br>
                                                <small><?= date('d/m', strtotime($cumple['cumple_este_año'])) ?></small>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (empty($eventos) && empty($cumpleanos)): ?>
                                    <div class="alert alert-info">
                                        No hay eventos programados para este mes.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Accesos rápidos -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Accesos Rápidos</h3>
                            </div>
                            <div class="panel-body">
                                <a href="reservas.php" class="btn btn-sm btn-info btn-block">
                                    <span class="glyphicon glyphicon-home"></span> Reservar Sala
                                </a>
                                
                                <a href="solicitudes.php" class="btn btn-sm btn-warning btn-block">
                                    <span class="glyphicon glyphicon-file-text"></span> Nueva Solicitud
                                </a>
                                
                                <?php if (esAdmin()): ?>
                                    <a href="../admin/gestionar_eventos.php" class="btn btn-sm btn-success btn-block">
                                        <span class="glyphicon glyphicon-plus"></span> Crear Evento
                                    </a>
                                <?php endif; ?>
                            </div>
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
// ===== admin/gestionar_eventos.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header('Location: ../index.html');
    exit();
}

conectar();

// Procesar nuevo evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_evento') {
    $visible_para = null;
    if (isset($_POST['visible_para']) && !empty($_POST['visible_para'])) {
        $visible_para = json_encode($_POST['visible_para']);
    }
    
    $fecha_fin = $_POST['fecha_fin'] ?: null;
    if ($_POST['todo_el_dia']) {
        $fecha_fin = $fecha_fin ?: $_POST['fecha_inicio'];
    }
    
    $sql = "INSERT INTO eventos (titulo, descripcion, tipo, fecha_inicio, fecha_fin, todo_el_dia, color, visible_para, creado_por) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $_POST['titulo'], $_POST['descripcion'], $_POST['tipo'], 
        $_POST['fecha_inicio'], $fecha_fin, $_POST['todo_el_dia'], 
        $_POST['color'], $visible_para, $_SESSION['usuario']);
    
    if (mysqli_stmt_execute($stmt)) {
        logAuditoria($_SESSION['usuario'], "Evento creado", 'eventos', mysqli_insert_id($conexion));
        header('Location: gestionar_eventos.php?mensaje=evento_creado');
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Eliminar evento
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $sql = "DELETE FROM eventos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        logAuditoria($_SESSION['usuario'], "Evento eliminado", 'eventos', $id);
        header('Location: gestionar_eventos.php?mensaje=evento_eliminado');
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Obtener eventos próximos
$eventos = mysqli_fetch_all(mysqli_query($conexion, 
    "SELECT e.*, u.nombre_completo as creado_por_nombre 
     FROM eventos e 
     LEFT JOIN usuarios u ON e.creado_por = u.usuario 
     WHERE fecha_inicio >= CURDATE() 
     ORDER BY fecha_inicio ASC LIMIT 50"), MYSQLI_ASSOC);

// Obtener usuarios para el selector
$usuarios = mysqli_fetch_all(mysqli_query($conexion, 
    "SELECT usuario, nombre_completo FROM usuarios WHERE activo = 1 ORDER BY nombre_completo"), MYSQLI_ASSOC);

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Eventos</title>
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
                    <span class="glyphicon glyphicon-calendar"></span> Gestionar Eventos
                </h1>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'evento_creado': echo 'Evento creado exitosamente.'; break;
                            case 'evento_eliminado': echo 'Evento eliminado exitosamente.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario para crear evento -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Crear Nuevo Evento</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear_evento">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Título del Evento *</label>
                                        <input type="text" name="titulo" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tipo de Evento *</label>
                                        <select name="tipo" class="form-control" required>
                                            <option value="corporativo">Evento Corporativo</option>
                                            <option value="capacitacion">Capacitación</option>
                                            <option value="reunion">Reunión</option>
                                            <option value="festivo">Día Festivo</option>
                                            <option value="cumpleanos">Cumpleaños</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de Inicio *</label>
                                        <input type="datetime-local" name="fecha_inicio" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de Fin</label>
                                        <input type="datetime-local" name="fecha_fin" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="todo_el_dia" value="1"> Todo el día
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Color</label>
                                        <select name="color" class="form-control">
                                            <option value="#007bff">Azul (Predeterminado)</option>
                                            <option value="#28a745">Verde</option>
                                            <option value="#ffc107">Amarillo</option>
                                            <option value="#dc3545">Rojo</option>
                                            <option value="#17a2b8">Cian</option>
                                            <option value="#6f42c1">Púrpura</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Visible Para (dejar vacío para todos)</label>
                                <select name="visible_para[]" class="form-control" multiple size="5">
                                    <?php foreach ($usuarios as $user): ?>
                                        <option value="<?= htmlspecialchars($user['usuario']) ?>">
                                            <?= htmlspecialchars($user['nombre_completo'] ?: $user['usuario']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="help-block">Mantén presionado Ctrl para seleccionar múltiples usuarios</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Crear Evento
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de eventos próximos -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Eventos Próximos</h3>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($eventos)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Creado por</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eventos as $evento): ?>
                                            <tr>
                                                <td>
                                                    <span style="display: inline-block; width: 12px; height: 12px; background-color: <?= $evento['color'] ?>; border-radius: 50%; margin-right: 5px;"></span>
                                                    <?= htmlspecialchars($evento['titulo']) ?>
                                                </td>
                                                <td>
                                                    <span class="label label-default"><?= ucfirst($evento['tipo']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($evento['todo_el_dia']): ?>
                                                        <?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?>
                                                    <?php else: ?>
                                                        <?= date('d/m/Y H:i', strtotime($evento['fecha_inicio'])) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($evento['creado_por_nombre'] ?: $evento['creado_por']) ?></td>
                                                <td>
                                                    <a href="gestionar_eventos.php?eliminar=<?= $evento['id'] ?>" 
                                                       class="btn btn-xs btn-danger"
                                                       onclick="return confirm('¿Seguro que desea eliminar este evento?')">
                                                        <span class="glyphicon glyphicon-trash"></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No hay eventos próximos programados.
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
        // Toggle fecha fin cuando se marca todo el día
        $('input[name="todo_el_dia"]').on('change', function() {
            if (this.checked) {
                $('input[name="fecha_inicio"], input[name="fecha_fin"]').attr('type', 'date');
            } else {
                $('input[name="fecha_inicio"], input[name="fecha_fin"]').attr('type', 'datetime-local');
            }
        });
    </script>
</body>
</html>