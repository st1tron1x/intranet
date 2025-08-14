<?php
// ===== scripts/init_sistema.php =====
require_once 'funciones.php';

// Script para inicializar el sistema con datos de ejemplo y migrar datos existentes

function migrarDatos() {
    global $conexion;
    
    echo "🔄 Iniciando migración y configuración del sistema...\n";
    
    // 1. Migrar usuarios existentes (actualizar contraseñas a hash)
    echo "📋 Migrando usuarios existentes...\n";
    $usuarios_viejos = mysqli_query($conexion, "SELECT usuario, clave FROM usuarios WHERE LENGTH(clave) < 60");
    
    while ($user = mysqli_fetch_assoc($usuarios_viejos)) {
        $clave_hash = password_hash($user['clave'], PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET clave = ? WHERE usuario = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $clave_hash, $user['usuario']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Usuario {$user['usuario']} migrado\n";
    }
    
    // 2. Crear categorías de documentos iniciales
    echo "📁 Creando categorías de documentos...\n";
    $categorias_docs = [
        ['Manuales de Procedimientos', 'Manuales operativos y de procedimientos', 'book', '#28a745', 1],
        ['Políticas Corporativas', 'Políticas y reglamentos de la empresa', 'gavel', '#dc3545', 2],
        ['Formatos y Plantillas', 'Plantillas y formatos corporativos', 'file-text-o', '#17a2b8', 3],
        ['Documentos Contables', 'Documentos contables y financieros', 'calculator', '#ffc107', 4],
        ['Manuales de Sistemas', 'Manuales de Reggis, BMC y otras aplicaciones', 'computer', '#6f42c1', 5],
        ['Boletines BMC', 'Boletines de la Bolsa Mercantil', 'bullhorn', '#fd7e14', 6]
    ];
    
    foreach ($categorias_docs as $cat) {
        $sql = "INSERT IGNORE INTO categorias_documentos (nombre, descripcion, icono, color, orden) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $cat[0], $cat[1], $cat[2], $cat[3], $cat[4]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Categoría creada: {$cat[0]}\n";
    }
    
    // 3. Crear salas iniciales
    echo "🏢 Creando salas iniciales...\n";
    $salas = [
        [
            'nombre' => 'Sala de Juntas Principal',
            'descripcion' => 'Sala principal para reuniones ejecutivas y juntas directivas',
            'capacidad' => 12,
            'equipamiento' => json_encode(['Proyector', 'Audio', 'Video conferencia', 'Pizarra', 'Aire acondicionado', 'Wifi'])
        ],
        [
            'nombre' => 'Sala de Capacitación',
            'descripcion' => 'Espacio para entrenamientos, talleres y capacitaciones',
            'capacidad' => 20,
            'equipamiento' => json_encode(['Proyector', 'Audio', 'Computadores', 'Pizarra', 'Aire acondicionado', 'Wifi'])
        ],
        [
            'nombre' => 'Sala de Reuniones Pequeña',
            'descripcion' => 'Sala para reuniones de equipos pequeños',
            'capacidad' => 6,
            'equipamiento' => json_encode(['Pantalla', 'Audio', 'Wifi', 'Pizarra'])
        ]
    ];
    
    foreach ($salas as $sala) {
        $sql = "INSERT IGNORE INTO salas (nombre, descripcion, capacidad, equipamiento) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssis", $sala['nombre'], $sala['descripcion'], $sala['capacidad'], $sala['equipamiento']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Sala creada: {$sala['nombre']}\n";
    }
    
    // 4. Crear categorías principales actualizadas
    echo "🗂️ Actualizando categorías principales...\n";
    $categorias_principales = [
        ['Noticias', 'Noticias y comunicados internos', 'noticias.php', 'newspaper-o', 1],
        ['Directorio', 'Directorio de empleados', 'directorio.php', 'users', 2],
        ['Documentos', 'Biblioteca de documentos', 'documentos.php', 'folder-open', 3],
        ['Solicitudes', 'Sistema de solicitudes', 'solicitudes.php', 'file-text', 4],
        ['Reservas', 'Reservas de salas y recursos', 'reservas.php', 'calendar', 5],
        ['Calendario', 'Calendario corporativo', 'calendario.php', 'calendar', 6],
        ['Capacitación', 'Formación y desarrollo', 'capacitaciones.php', 'graduation-cap', 7]
    ];
    
    foreach ($categorias_principales as $cat) {
        $sql = "INSERT INTO categorias (categoria, descripcion, ruta, icono, orden) VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), ruta = VALUES(ruta), icono = VALUES(icono), orden = VALUES(orden)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $cat[0], $cat[1], $cat[2], $cat[3], $cat[4]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Categoría actualizada: {$cat[0]}\n";
    }
    
    // 5. Crear eventos corporativos iniciales
    echo "📅 Creando eventos corporativos iniciales...\n";
    $eventos = [
        [
            'titulo' => 'Reunión Mensual de Gerencia',
            'descripcion' => 'Reunión mensual del equipo gerencial',
            'tipo' => 'reunion',
            'fecha_inicio' => date('Y-m-01 09:00:00', strtotime('next month')),
            'fecha_fin' => date('Y-m-01 11:00:00', strtotime('next month')),
            'color' => '#007bff',
            'recurrente' => 1
        ],
        [
            'titulo' => 'Capacitación Trimestral',
            'descripcion' => 'Capacitación trimestral para todo el personal',
            'tipo' => 'capacitacion',
            'fecha_inicio' => date('Y-m-15 14:00:00', strtotime('next month')),
            'fecha_fin' => date('Y-m-15 17:00:00', strtotime('next month')),
            'color' => '#28a745',
            'recurrente' => 0
        ]
    ];
    
    foreach ($eventos as $evento) {
        $sql = "INSERT INTO eventos (titulo, descripcion, tipo, fecha_inicio, fecha_fin, color, creado_por) VALUES (?, ?, ?, ?, ?, ?, 'admin')";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $evento['titulo'], $evento['descripcion'], $evento['tipo'], 
                               $evento['fecha_inicio'], $evento['fecha_fin'], $evento['color']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Evento creado: {$evento['titulo']}\n";
    }
    
    // 6. Crear foros iniciales
    echo "💬 Creando foros iniciales...\n";
    $foros = [
        ['Anuncios Generales', 'Foro para anuncios importantes de la empresa', 'general'],
        ['Sugerencias y Mejoras', 'Espacio para sugerencias de mejora', 'sugerencias'],
        ['Soporte Técnico', 'Foro de soporte y ayuda técnica', 'soporte'],
        ['Eventos Sociales', 'Organización de eventos sociales y actividades', 'social']
    ];
    
    foreach ($foros as $foro) {
        $sql = "INSERT IGNORE INTO foros (titulo, descripcion, categoria) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $foro[0], $foro[1], $foro[2]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Foro creado: {$foro[0]}\n";
    }
    
    // 7. Configurar valores iniciales del sistema
    echo "⚙️ Configurando valores del sistema...\n";
    $configuraciones = [
        ['empresa_nombre', 'Correagro SCB', 'Nombre de la empresa'],
        ['empresa_email', 'info@correagro.com', 'Email principal de la empresa'],
        ['empresa_telefono', '+57 (1) 234-5678', 'Teléfono principal'],
        ['empresa_direccion', 'Bogotá, Colombia', 'Dirección de la empresa'],
        ['max_file_size', '10485760', 'Tamaño máximo de archivo en bytes (10MB)'],
        ['session_timeout', '3600', 'Tiempo de sesión en segundos (1 hora)'],
        ['backup_frequency', '24', 'Frecuencia de backup en horas'],
        ['maintenance_mode', '0', 'Modo de mantenimiento (0=off, 1=on)'],
        ['allow_user_registration', '0', 'Permitir registro de usuarios (0=no, 1=sí)'],
        ['default_user_permissions', 'documentos,noticias', 'Permisos por defecto para nuevos usuarios']
    ];
    
    foreach ($configuraciones as $config) {
        $sql = "INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $config[0], $config[1], $config[2]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "  ✅ Configuración: {$config[0]} = {$config[1]}\n";
    }
    
    // 8. Crear directorios necesarios
    echo "📂 Creando directorios necesarios...\n";
    $directorios = [
        '../uploads/documentos/',
        '../uploads/imagenes_noticias/',
        '../uploads/fotos/',
        '../uploads/capacitaciones/',
        '../logs/',
        '../backups/'
    ];
    
    foreach ($directorios as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "  ✅ Directorio creado: $dir\n";
        } else {
            echo "  ℹ️ Directorio ya existe: $dir\n";
        }
    }
    
    // 9. Crear archivo .htaccess para seguridad
    echo "🔒 Configurando seguridad...\n";
    $htaccess_uploads = '../uploads/.htaccess';
    if (!file_exists($htaccess_uploads)) {
        file_put_contents($htaccess_uploads, "Options -Indexes\nOptions -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi");
        echo "  ✅ Archivo .htaccess creado en uploads/\n";
    }
    
    // 10. Log de auditoría de la migración
    logAuditoria('admin', "Sistema inicializado y migrado", 'sistema');
    
    echo "\n✅ ¡Migración completada exitosamente!\n";
    echo "📊 Resumen:\n";
    echo "  - Usuarios migrados con contraseñas seguras\n";
    echo "  - Categorías de documentos creadas\n";
    echo "  - Salas de reuniones configuradas\n";
    echo "  - Eventos corporativos iniciales\n";
    echo "  - Foros de comunicación\n";
    echo "  - Configuraciones del sistema\n";
    echo "  - Directorios de seguridad\n";
    echo "\n🎉 El sistema está listo para usar!\n";
}

// Ejecutar solo si se llama directamente
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    conectar();
    migrarDatos();
    desconectar();
}
?>

<?php
// ===== admin/dashboard.php =====
require_once '../scripts/funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header('Location: ../index.html');
    exit();
}

conectar();

// Obtener estadísticas del sistema
$stats = [];

// Usuarios activos
$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
$stats['usuarios_activos'] = mysqli_fetch_assoc($result)['total'];

// Documentos totales
$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM documentos WHERE activo = 1");
$stats['documentos'] = mysqli_fetch_assoc($result)['total'];

// Solicitudes pendientes
$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'");
$stats['solicitudes_pendientes'] = mysqli_fetch_assoc($result)['total'];

// Reservas de hoy
$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM reservas_salas WHERE DATE(fecha_inicio) = CURDATE() AND estado = 'confirmada'");
$stats['reservas_hoy'] = mysqli_fetch_assoc($result)['total'];

// Eventos próximos (7 días)
$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM eventos WHERE fecha_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stats['eventos_proximos'] = mysqli_fetch_assoc($result)['total'];

// Actividad reciente (últimas 10 acciones)
$actividad_reciente = mysqli_fetch_all(
    mysqli_query($conexion, "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10"), 
    MYSQLI_ASSOC
);

// Solicitudes recientes
$solicitudes_recientes = mysqli_fetch_all(
    mysqli_query($conexion, "SELECT s.*, u.nombre_completo FROM solicitudes s LEFT JOIN usuarios u ON s.usuario = u.usuario ORDER BY s.fecha_solicitud DESC LIMIT 5"), 
    MYSQLI_ASSOC
);

// Documentos más descargados
$docs_populares = mysqli_fetch_all(
    mysqli_query($conexion, "SELECT titulo, descargas FROM documentos WHERE activo = 1 ORDER BY descargas DESC LIMIT 5"), 
    MYSQLI_ASSOC
);

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrativo</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
        .activity-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-time {
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'menu-superior.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'menu-lateral.php'; ?>
            
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header">
                    <span class="glyphicon glyphicon-dashboard"></span> Dashboard Administrativo
                </h1>
                
                <p class="lead">Bienvenido, <?= htmlspecialchars($_SESSION['nombre_completo'] ?? $_SESSION['usuario']) ?>. 
                   Aquí tienes un resumen del estado actual del sistema.</p>
                
                <!-- Tarjetas de estadísticas -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-number text-primary"><?= $stats['usuarios_activos'] ?></div>
                            <div class="stat-label">Usuarios Activos</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-number text-success"><?= $stats['documentos'] ?></div>
                            <div class="stat-label">Documentos</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-number text-warning"><?= $stats['solicitudes_pendientes'] ?></div>
                            <div class="stat-label">Solicitudes Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number text-info"><?= $stats['reservas_hoy'] ?></div>
                            <div class="stat-label">Reservas Hoy</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number text-danger"><?= $stats['eventos_proximos'] ?></div>
                            <div class="stat-label">Eventos (7 días)</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Actividad reciente -->
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <span class="glyphicon glyphicon-time"></span> Actividad Reciente
                                </h3>
                            </div>
                            <div class="panel-body" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($actividad_reciente as $actividad): ?>
                                    <div class="activity-item">
                                        <div>
                                            <strong><?= htmlspecialchars($actividad['usuario'] ?? 'Sistema') ?></strong>
                                            <?= htmlspecialchars($actividad['accion']) ?>
                                        </div>
                                        <div class="activity-time">
                                            <?= date('d/m/Y H:i', strtotime($actividad['created_at'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Solicitudes recientes -->
                    <div class="col-md-6">
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <span class="glyphicon glyphicon-file-text"></span> Solicitudes Recientes
                                </h3>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($solicitudes_recientes)): ?>
                                    <?php foreach ($solicitudes_recientes as $solicitud): ?>
                                        <div class="activity-item">
                                            <div>
                                                <span class="label label-<?= $solicitud['estado'] === 'pendiente' ? 'warning' : 'success' ?>">
                                                    <?= ucfirst($solicitud['estado']) ?>
                                                </span>
                                                <strong><?= htmlspecialchars($solicitud['nombre_completo'] ?? $solicitud['usuario']) ?></strong>
                                                - <?= htmlspecialchars($solicitud['tipo']) ?>
                                            </div>
                                            <div class="activity-time">
                                                <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center">
                                        <a href="../categorias/solicitudes.php" class="btn btn-sm btn-warning">Ver Todas</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted text-center">No hay solicitudes recientes</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Documentos populares -->
                    <div class="col-md-6">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <span class="glyphicon glyphicon-download"></span> Documentos Más Descargados
                                </h3>
                            </div>
                            <div class="panel-body">
                                <?php foreach ($docs_populares as $doc): ?>
                                    <div class="activity-item">
                                        <div>
                                            <?= htmlspecialchars($doc['titulo']) ?>
                                            <span class="badge pull-right"><?= $doc['descargas'] ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accesos rápidos -->
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <span class="glyphicon glyphicon-flash"></span> Accesos Rápidos
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <a href="gestionar_empleados.php" class="btn btn-info btn-block">
                                            <span class="glyphicon glyphicon-user"></span><br>Empleados
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <a href="documentos.php" class="btn btn-success btn-block">
                                            <span class="glyphicon glyphicon-folder-open"></span><br>Documentos
                                        </a>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-xs-6">
                                        <a href="noticias.php" class="btn btn-warning btn-block">
                                            <span class="glyphicon glyphicon-bullhorn"></span><br>Noticias
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <a href="gestionar_eventos.php" class="btn btn-danger btn-block">
                                            <span class="glyphicon glyphicon-calendar"></span><br>Eventos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    
    <script>
        // Auto-refresh estadísticas cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>