<?php
// ===== categorias/directorio.php =====
require '../scripts/funciones.php';

if (!haIniciadoSesion()) {
    header("Location: ../index.html");
    exit();
}

conectar();

// Obtener empleados con filtros
$departamento = $_GET['departamento'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT * FROM usuarios WHERE activo = 1";
$params = [];
$types = "";

if (!empty($departamento)) {
    $sql .= " AND departamento = ?";
    $params[] = $departamento;
    $types .= "s";
}

if (!empty($busqueda)) {
    $sql .= " AND (nombre_completo LIKE ? OR cargo LIKE ? OR email LIKE ?)";
    $busqueda_like = "%$busqueda%";
    $params = array_merge($params, [$busqueda_like, $busqueda_like, $busqueda_like]);
    $types .= "sss";
}

$sql .= " ORDER BY departamento, nombre_completo";

$stmt = mysqli_prepare($conexion, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$empleados = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Obtener departamentos para filtro
$dept_sql = "SELECT DISTINCT departamento FROM usuarios WHERE activo = 1 AND departamento IS NOT NULL ORDER BY departamento";
$departamentos = mysqli_fetch_all(mysqli_query($conexion, $dept_sql), MYSQLI_ASSOC);

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Directorio de Empleados</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/panelAdmin.css">
    <style>
        .empleado-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: box-shadow 0.2s;
        }
        .empleado-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .foto-perfil {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .foto-default {
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }
        .departamento-header {
            background: #f8f9fa;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #007bff;
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
                    <span class="glyphicon glyphicon-user"></span> Directorio de Empleados
                </h1>
                
                <!-- Filtros -->
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form method="GET" class="form-inline">
                            <div class="form-group">
                                <label for="busqueda">Buscar:</label>
                                <input type="text" name="busqueda" id="busqueda" class="form-control" 
                                       placeholder="Nombre, cargo o email..." value="<?= htmlspecialchars($busqueda) ?>">
                            </div>
                            
                            <div class="form-group" style="margin-left: 15px;">
                                <label for="departamento">Departamento:</label>
                                <select name="departamento" id="departamento" class="form-control">
                                    <option value="">Todos los departamentos</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['departamento']) ?>" 
                                                <?= $departamento === $dept['departamento'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['departamento']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                                <span class="glyphicon glyphicon-search"></span> Buscar
                            </button>
                            
                            <a href="directorio.php" class="btn btn-default">
                                <span class="glyphicon glyphicon-refresh"></span> Limpiar
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Resultados -->
                <div class="row">
                    <?php if (!empty($empleados)): ?>
                        <?php 
                        $departamento_actual = '';
                        foreach ($empleados as $empleado): 
                            // Mostrar header de departamento si cambió
                            if ($empleado['departamento'] !== $departamento_actual): 
                                $departamento_actual = $empleado['departamento'];
                                ?>
                                <div class="col-md-12">
                                    <div class="departamento-header">
                                        <h4 style="margin: 0;">
                                            <span class="glyphicon glyphicon-briefcase"></span>
                                            <?= htmlspecialchars($departamento_actual ?: 'Sin departamento') ?>
                                        </h4>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-md-4">
                                <div class="empleado-card">
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <?php if ($empleado['foto_perfil'] && file_exists("../uploads/fotos/" . $empleado['foto_perfil'])): ?>
                                                <img src="../uploads/fotos/<?= $empleado['foto_perfil'] ?>" class="foto-perfil" alt="Foto">
                                            <?php else: ?>
                                                <div class="foto-perfil foto-default">
                                                    <?= strtoupper(substr($empleado['nombre_completo'] ?: $empleado['usuario'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-xs-8">
                                            <h5 style="margin-top: 0;">
                                                <?= htmlspecialchars($empleado['nombre_completo'] ?: $empleado['usuario']) ?>
                                            </h5>
                                            
                                            <?php if ($empleado['cargo']): ?>
                                                <p class="text-muted" style="margin-bottom: 5px;">
                                                    <small><span class="glyphicon glyphicon-tag"></span> <?= htmlspecialchars($empleado['cargo']) ?></small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($empleado['email']): ?>
                                                <p style="margin-bottom: 5px;">
                                                    <small>
                                                        <span class="glyphicon glyphicon-envelope"></span>
                                                        <a href="mailto:<?= htmlspecialchars($empleado['email']) ?>">
                                                            <?= htmlspecialchars($empleado['email']) ?>
                                                        </a>
                                                    </small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($empleado['telefono']): ?>
                                                <p style="margin-bottom: 5px;">
                                                    <small>
                                                        <span class="glyphicon glyphicon-phone"></span>
                                                        <a href="tel:<?= htmlspecialchars($empleado['telefono']) ?>">
                                                            <?= htmlspecialchars($empleado['telefono']) ?>
                                                        </a>
                                                    </small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($empleado['fecha_ingreso']): ?>
                                                <p class="text-muted" style="margin-bottom: 0;">
                                                    <small>
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                        Desde <?= date('M Y', strtotime($empleado['fecha_ingreso'])) ?>
                                                    </small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-12">
                            <div class="alert alert-info text-center">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                No se encontraron empleados con los criterios seleccionados.
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
// ===== admin/gestionar_empleados.php =====
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
            case 'crear_usuario':
                $datos = [
                    'usuario' => $_POST['usuario'],
                    'nombre_completo' => $_POST['nombre_completo'],
                    'email' => $_POST['email'],
                    'telefono' => $_POST['telefono'],
                    'cargo' => $_POST['cargo'],
                    'departamento' => $_POST['departamento'],
                    'fecha_ingreso' => $_POST['fecha_ingreso']
                ];
                
                if (crearUsuario($datos, $_POST['clave'])) {
                    header('Location: gestionar_empleados.php?mensaje=usuario_creado');
                } else {
                    header('Location: gestionar_empleados.php?error=error_crear_usuario');
                }
                exit();
                break;
                
            case 'toggle_activo':
                $id = (int)$_POST['user_id'];
                $activo = (int)$_POST['activo'];
                
                $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $activo, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                logAuditoria($_SESSION['usuario'], "Usuario " . ($activo ? "activado" : "desactivado"), 'usuarios', $id);
                header('Location: gestionar_empleados.php?mensaje=usuario_actualizado');
                exit();
                break;
        }
    }
}

$empleados = mysqli_fetch_all(mysqli_query($conexion, "SELECT * FROM usuarios ORDER BY nombre_completo"), MYSQLI_ASSOC);

desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Empleados</title>
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
                    <span class="glyphicon glyphicon-users"></span> Gestionar Empleados
                </h1>

                <?php if(isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        switch($_GET['mensaje']) {
                            case 'usuario_creado': echo 'Usuario creado exitosamente.'; break;
                            case 'usuario_actualizado': echo 'Usuario actualizado exitosamente.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario para crear usuario -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Crear Nuevo Usuario</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear_usuario">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Usuario *</label>
                                        <input type="text" name="usuario" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contraseña Temporal *</label>
                                        <input type="password" name="clave" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre Completo *</label>
                                        <input type="text" name="nombre_completo" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="telefono" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Cargo</label>
                                        <input type="text" name="cargo" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Departamento</label>
                                        <select name="departamento" class="form-control">
                                            <option value="">Seleccionar...</option>
                                            <option value="Administración">Administración</option>
                                            <option value="Contabilidad">Contabilidad</option>
                                            <option value="Comercial">Comercial</option>
                                            <option value="IT">Tecnología</option>
                                            <option value="RRHH">Recursos Humanos</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Fecha de Ingreso</label>
                                <input type="date" name="fecha_ingreso" class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Crear Usuario
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de empleados -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Empleados Registrados</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Cargo</th>
                                        <th>Departamento</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empleados as $emp): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($emp['usuario']) ?></td>
                                            <td><?= htmlspecialchars($emp['nombre_completo'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($emp['email'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($emp['cargo'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($emp['departamento'] ?: '-') ?></td>
                                            <td>
                                                <?php if ($emp['activo']): ?>
                                                    <span class="label label-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="label label-default">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="accion" value="toggle_activo">
                                                    <input type="hidden" name="user_id" value="<?= $emp['id'] ?>">
                                                    <input type="hidden" name="activo" value="<?= $emp['activo'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-xs <?= $emp['activo'] ? 'btn-warning' : 'btn-success' ?>">
                                                        <?= $emp['activo'] ? 'Desactivar' : 'Activar' ?>
                                                    </button>
                                                </form>
                                                
                                                <a href="editarPermisos.php?usuario=<?= $emp['usuario'] ?>" class="btn btn-xs btn-info">
                                                    <span class="glyphicon glyphicon-lock"></span> Permisos
                                                </a>
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