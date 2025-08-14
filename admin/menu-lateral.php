<?php
// ===== admin/menu-lateral.php (ACTUALIZADO) =====
require_once '../scripts/funciones.php';

if (haIniciadoSesion()):
?>
<div class="col-sm-3 col-md-2 sidebar">
  <ul class="nav nav-sidebar">
    <?php 
    // Detectar si estamos en la carpeta admin o en una subcarpeta
    $rutaBase = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../admin/';
    $rutaCategoria = (basename(dirname($_SERVER['PHP_SELF'])) === 'categorias') ? '' : '../categorias/';
    
    if (esAdmin()): ?>
        <!-- Rutas para administradores -->
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; border-bottom: 1px solid #ddd;">
            <span class="glyphicon glyphicon-user"></span> Panel Admin
        </li>
        <li><a href="<?= $rutaBase ?>dashboard.php">
            <span class="glyphicon glyphicon-dashboard"></span> Dashboard
        </a></li>
        <li><a href="<?= $rutaBase ?>panelAdmin.php">
            <span class="glyphicon glyphicon-home"></span> Inicio Admin
        </a></li>
        
        <!-- Gestión de Contenido -->
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
            Gestión de Contenido
        </li>
        <li><a href="<?= $rutaBase ?>noticias.php">
            <span class="glyphicon glyphicon-bullhorn"></span> Noticias
        </a></li>
        <li><a href="<?= $rutaBase ?>documentos.php">
            <span class="glyphicon glyphicon-folder-open"></span> Documentos
        </a></li>
        <li><a href="<?= $rutaBase ?>gestionar_eventos.php">
            <span class="glyphicon glyphicon-calendar"></span> Eventos
        </a></li>
        
        <!-- Gestión de Usuarios -->
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
            Gestión de Usuarios
        </li>
        <li><a href="<?= $rutaBase ?>gestionar_empleados.php">
            <span class="glyphicon glyphicon-users"></span> Empleados
        </a></li>
        <li><a href="<?= $rutaBase ?>permisos.php">
            <span class="glyphicon glyphicon-lock"></span> Permisos
        </a></li>
        
        <!-- Configuración del Sistema -->
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
            Configuración
        </li>
        <li><a href="<?= $rutaBase ?>listarCategorias.php">
            <span class="glyphicon glyphicon-list"></span> Categorías
        </a></li>
        <li><a href="<?= $rutaBase ?>gestionar_salas.php">
            <span class="glyphicon glyphicon-home"></span> Salas
        </a></li>
        <li><a href="<?= $rutaBase ?>configuracion.php">
            <span class="glyphicon glyphicon-cog"></span> Sistema
        </a></li>
        
    <?php else: ?>
        <!-- Rutas para usuarios normales -->
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; border-bottom: 1px solid #ddd;">
            <span class="glyphicon glyphicon-user"></span> Mi Panel
        </li>
        <li><a href="<?= $rutaBase ?>panelUsuario.php">
            <span class="glyphicon glyphicon-home"></span> Inicio
        </a></li>
    <?php endif; ?>
    
    <!-- Enlaces disponibles para todos los usuarios logueados -->
    <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
        Información
    </li>
    <li><a href="<?= $rutaCategoria ?>noticias.php">
        <span class="glyphicon glyphicon-bullhorn"></span> Noticias
    </a></li>
    <li><a href="<?= $rutaCategoria ?>directorio.php">
        <span class="glyphicon glyphicon-user"></span> Directorio
    </a></li>
    <li><a href="<?= $rutaCategoria ?>calendario.php">
        <span class="glyphicon glyphicon-calendar"></span> Calendario
    </a></li>
    
    <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
        Servicios
    </li>
    <li><a href="<?= $rutaCategoria ?>documentos.php">
        <span class="glyphicon glyphicon-folder-open"></span> Documentos
    </a></li>
    <li><a href="<?= $rutaCategoria ?>solicitudes.php">
        <span class="glyphicon glyphicon-file-text"></span> Solicitudes
    </a></li>
    <li><a href="<?= $rutaCategoria ?>reservas.php">
        <span class="glyphicon glyphicon-calendar"></span> Reservas
    </a></li>
    <li><a href="<?= $rutaCategoria ?>capacitaciones.php">
        <span class="glyphicon glyphicon-graduation-cap"></span> Capacitación
    </a></li>
    
    <?php 
    // Mostrar categorías personalizadas para el usuario (si no es admin)
    if (!esAdmin()) {
        conectar();
        $categoriasUsuario = getCategoiasPorUser();
        if (!empty($categoriasUsuario)):
    ?>
        <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
            Mis Categorías
        </li>
        <?php foreach ($categoriasUsuario as $categoria): ?>
            <li><a href="<?= $rutaCategoria ?><?= htmlspecialchars($categoria[2]) ?>">
                <span class="glyphicon glyphicon-<?= htmlspecialchars($categoria[3] ?? 'folder') ?>"></span>
                <?= htmlspecialchars($categoria[0]) ?>
            </a></li>
        <?php endforeach; ?>
    <?php 
        endif;
        desconectar();
    } 
    ?>
    
    <!-- Comunicación (para todos) -->
    <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
        Comunicación
    </li>
    <li><a href="<?= $rutaCategoria ?>foros.php">
        <span class="glyphicon glyphicon-comment"></span> Foros
    </a></li>
    <li><a href="<?= $rutaCategoria ?>encuestas.php">
        <span class="glyphicon glyphicon-stats"></span> Encuestas
    </a></li>
    
    <!-- Mi Cuenta -->
    <li class="nav-header" style="padding: 10px 20px; font-weight: bold; color: #777; margin-top: 15px;">
        Mi Cuenta
    </li>
    <li><a href="<?= $rutaBase ?>mi_perfil.php">
        <span class="glyphicon glyphicon-user"></span> Mi Perfil
    </a></li>
    <li><a href="<?= $rutaBase ?>cambiar_clave.php">
        <span class="glyphicon glyphicon-lock"></span> Cambiar Clave
    </a></li>
    
    <!-- Estado del sistema (solo para admins) -->
    <?php if (esAdmin()): ?>
        <li style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; margin-left: 10px; margin-right: 10px;">
            <small class="text-muted">
                <strong>Estado del Sistema</strong><br>
                <span class="glyphicon glyphicon-ok text-success"></span> Sistema Operativo<br>
                <span class="glyphicon glyphicon-user"></span> <?php
                    conectar();
                    $usuarios_online = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE ultimo_login >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)"))['total'];
                    desconectar();
                    echo $usuarios_online;
                ?> usuarios activos
            </small>
        </li>
    <?php endif; ?>
  </ul>
</div>
<?php endif; ?>

<style>
/* Estilos adicionales para el menú */
.nav-sidebar > li > a {
    padding: 8px 20px;
    font-size: 13px;
    transition: background-color 0.2s;
}

.nav-sidebar > li > a:hover {
    background-color: #f8f9fa;
}

.nav-sidebar > .active > a,
.nav-sidebar > li > a:focus {
    color: #fff;
    background-color: #428bca;
}

.nav-header {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sidebar {
    background-color: #fff;
    border-right: 1px solid #e3e3e3;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

@media (max-width: 767px) {
    .sidebar {
        position: static;
        height: auto;
    }
}
</style>

<?php
// ===== admin/mi_perfil.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header('Location: ../index.html');
    exit();
}

conectar();

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_perfil') {
    $usuario = $_SESSION['usuario'];
    
    // Procesar foto si se subió
    $foto_perfil = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $carpeta = "../uploads/fotos/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $foto_perfil = $usuario . "_" . time() . "." . $extension;
            move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta . $foto_perfil);
        }
    }
    
    // Actualizar datos
    $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, telefono = ?, cargo = ?, departamento = ?";
    $params = [$_POST['nombre_completo'], $_POST['email'], $_POST['telefono'], $_POST['cargo'], $_POST['departamento']];
    $types = "sssss";
    
    if ($foto_perfil) {
        $sql .= ", foto_perfil = ?";
        $params[] = $foto_perfil;
        $types .= "s";
    }
    
    $sql .= " WHERE usuario = ?";
    $params[] = $usuario;
    $types .= "s";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        logAuditoria($usuario, "Perfil actualizado");
        $mensaje = "Perfil actualizado exitosamente";
    } else {
        $error = "Error al actualizar el perfil";
    }
    mysqli_stmt_close($stmt);
}

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['usuario']);
mysqli_stmt_execute($stmt);
$usuario_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
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
                    <span class="glyphicon glyphicon-user"></span> Mi Perfil
                </h1>

                <?php if(isset($mensaje)): ?>
                    <div class="alert alert-success"><?= $mensaje ?></div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Información Personal</h3>
                            </div>
                            <div class="panel-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="accion" value="actualizar_perfil">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Usuario</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario_data['usuario']) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre Completo</label>
                                                <input type="text" name="nombre_completo" class="form-control" 
                                                       value="<?= htmlspecialchars($usuario_data['nombre_completo'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" 
                                                       value="<?= htmlspecialchars($usuario_data['email'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Teléfono</label>
                                                <input type="text" name="telefono" class="form-control" 
                                                       value="<?= htmlspecialchars($usuario_data['telefono'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cargo</label>
                                                <input type="text" name="cargo" class="form-control" 
                                                       value="<?= htmlspecialchars($usuario_data['cargo'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Departamento</label>
                                                <select name="departamento" class="form-control">
                                                    <option value="">Seleccionar...</option>
                                                    <?php 
                                                    $departamentos = ['Administración', 'Contabilidad', 'Comercial', 'IT', 'RRHH'];
                                                    foreach ($departamentos as $dept): ?>
                                                        <option value="<?= $dept ?>" <?= $usuario_data['departamento'] === $dept ? 'selected' : '' ?>>
                                                            <?= $dept ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Foto de Perfil</label>
                                        <input type="file" name="foto" class="form-control" accept="image/*">
                                        <small class="help-block">Formatos: JPG, PNG. Tamaño máximo: 2MB</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-save"></span> Actualizar Perfil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Foto Actual</h3>
                            </div>
                            <div class="panel-body text-center">
                                <?php if ($usuario_data['foto_perfil'] && file_exists("../uploads/fotos/" . $usuario_data['foto_perfil'])): ?>
                                    <img src="../uploads/fotos/<?= $usuario_data['foto_perfil'] ?>" 
                                         class="img-thumbnail" style="max-width: 200px;">
                                <?php else: ?>
                                    <div style="width: 150px; height: 150px; background: #007bff; border-radius: 50%; 
                                                display: flex; align-items: center; justify-content: center; 
                                                color: white; font-size: 48px; margin: 0 auto;">
                                        <?= strtoupper(substr($usuario_data['nombre_completo'] ?: $usuario_data['usuario'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Información de Cuenta</h3>
                            </div>
                            <div class="panel-body">
                                <p><strong>Fecha de Ingreso:</strong><br>
                                   <?= $usuario_data['fecha_ingreso'] ? date('d/m/Y', strtotime($usuario_data['fecha_ingreso'])) : 'No definida' ?>
                                </p>
                                <p><strong>Último Acceso:</strong><br>
                                   <?= $usuario_data['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario_data['ultimo_login'])) : 'Nunca' ?>
                                </p>
                                <p><strong>Rol:</strong><br>
                                   <?= $usuario_data['admin'] ? 'Administrador' : 'Usuario' ?>
                                </p>
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
// ===== admin/cambiar_clave.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header('Location: ../index.html');
    exit();
}

$mensaje = '';
$error = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    conectar();
    
    $clave_actual = $_POST['clave_actual'];
    $clave_nueva = $_POST['clave_nueva'];
    $clave_confirmar = $_POST['clave_confirmar'];
    
    if ($clave_nueva !== $clave_confirmar) {
        $error = "Las contraseñas nuevas no coinciden";
    } elseif (strlen($clave_nueva) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        if (cambiarContrasena($_SESSION['usuario'], $clave_actual, $clave_nueva)) {
            $mensaje = "Contraseña cambiada exitosamente";
        } else {
            $error = "La contraseña actual es incorrecta";
        }
    }
    
    desconectar();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
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
                    <span class="glyphicon glyphicon-lock"></span> Cambiar Contraseña
                </h1>

                <?php if($mensaje): ?>
                    <div class="alert alert-success"><?= $mensaje ?></div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Cambiar Contraseña</h3>
                            </div>
                            <div class="panel-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Contraseña Actual</label>
                                        <input type="password" name="clave_actual" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Nueva Contraseña</label>
                                        <input type="password" name="clave_nueva" class="form-control" required minlength="6">
                                        <small class="help-block">Mínimo 6 caracteres</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Confirmar Nueva Contraseña</label>
                                        <input type="password" name="clave_confirmar" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-save"></span> Cambiar Contraseña
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Consejos de Seguridad</h3>
                            </div>
                            <div class="panel-body">
                                <ul>
                                    <li>Use al menos 8 caracteres</li>
                                    <li>Combine letras, números y símbolos</li>
                                    <li>No use información personal</li>
                                    <li>Cambie su contraseña regularmente</li>
                                    <li>No comparta su contraseña</li>
                                </ul>
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
        // Validar que las contraseñas coincidan en tiempo real
        $('input[name="clave_confirmar"]').on('keyup', function() {
            var nueva = $('input[name="clave_nueva"]').val();
            var confirmar = $(this).val();
            
            if (nueva !== confirmar && confirmar.length > 0) {
                $(this).parent().addClass('has-error');
            } else {
                $(this).parent().removeClass('has-error');
            }
        });
    </script>
</body>
</html>