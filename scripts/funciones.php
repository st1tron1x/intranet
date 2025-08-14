<?php

$conexion = null;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir PHPMailer
require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';

// Función para logging de auditoría
if (!function_exists('logAuditoria')) {
function logAuditoria($usuario, $accion, $tabla = null, $registro_id = null, $datos_anteriores = null, $datos_nuevos = null) {
    global $conexion;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $sql = "INSERT INTO audit_logs (usuario, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    
    $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores) : null;
    $datos_nuevos_json = $datos_nuevos ? json_encode($datos_nuevos) : null;
    
    mysqli_stmt_bind_param($stmt, "sssissss", $usuario, $accion, $tabla, $registro_id, $datos_anteriores_json, $datos_nuevos_json, $ip, $user_agent);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
}

// Función de conexión mejorada
if(!function_exists('conectar')){
function conectar() {
    global $conexion;
    
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'intranet';
    
    $conexion = new mysqli($host, $user, $pass, $db);
    
    if ($conexion->connect_error) {
        error_log("Error de conexión: " . $conexion->connect_error);
        die("Error de conexión a la base de datos");
    }
    
    $conexion->set_charset('utf8mb4');
    return $conexion;
}

function desconectar() {
    global $conexion;
    if ($conexion) {
        $conexion->close();
    }
}
}

// Función para sanitizar entrada
if(!function_exists('sanitizarEntrada')){
function sanitizarEntrada($data) {
    if (is_array($data)) {
        return array_map('sanitizarEntrada', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
}

// Validación de login con seguridad mejorada
if(!function_exists('validarLogin')){
function validarLogin($usuario, $clave) {
    global $conexion;
    
    // Limpiar entrada
    $usuario = sanitizarEntrada($usuario);
    
    // Verificar bloqueo por intentos fallidos
    $sql_check = "SELECT id, clave, admin, nombre_completo, intentos_login, bloqueado_hasta FROM usuarios WHERE usuario = ? AND activo = 1";
    $stmt = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($fila = mysqli_fetch_assoc($result)) {
        // Verificar si está bloqueado
        if ($fila['bloqueado_hasta'] && $fila['bloqueado_hasta'] > date('Y-m-d H:i:s')) {
            logAuditoria($usuario, "Login bloqueado - Usuario en tiempo de espera");
            return false;
        }
        
        // Verificar contraseña
        if (password_verify($clave, $fila['clave'])) {
            // Login exitoso - iniciar sesión
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                session_regenerate_id(true); // Prevenir session fixation
            }
            
            $_SESSION['usuario'] = $usuario;
            $_SESSION['admin'] = $fila['admin'];
            $_SESSION['user_id'] = $fila['id'];
            $_SESSION['nombre_completo'] = $fila['nombre_completo'];
            $_SESSION['last_activity'] = time();
            
            if ($fila['admin'] == 1) {
                $_SESSION['rol'] = 'admin';
            } else {
                $_SESSION['rol'] = 'usuario';
            }
            
            // Resetear intentos fallidos y actualizar último login
            $sql_update = "UPDATE usuarios SET intentos_login = 0, bloqueado_hasta = NULL, ultimo_login = NOW() WHERE usuario = ?";
            $stmt_update = mysqli_prepare($conexion, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "s", $usuario);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            logAuditoria($usuario, "Login exitoso");
            return true;
        } else {
            // Login fallido - incrementar intentos
            $intentos = $fila['intentos_login'] + 1;
            $bloqueado_hasta = null;
            
            // Bloquear por 15 minutos después de 5 intentos fallidos
            if ($intentos >= 5) {
                $bloqueado_hasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            
            $sql_update = "UPDATE usuarios SET intentos_login = ?, bloqueado_hasta = ? WHERE usuario = ?";
            $stmt_update = mysqli_prepare($conexion, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "iss", $intentos, $bloqueado_hasta, $usuario);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            logAuditoria($usuario, "Login fallido - intento #$intentos");
        }
    } else {
        logAuditoria($usuario, "Login fallido - Usuario no encontrado");
    }
    
    mysqli_stmt_close($stmt);
    return false;
}
}

// Verificar si ha iniciado sesión con timeout
if(!function_exists('haIniciadoSesion')){
function haIniciadoSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario'])) {
        return false;
    }
    
    // Verificar timeout de sesión (1 hora por defecto)
    $timeout = 3600;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}
}

if(!function_exists('esAdmin')){
function esAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
}
}

// Funciones de categorías con seguridad mejorada
if(!function_exists('getTodasCategorias')){
function getTodasCategorias() {
    global $conexion;
    $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY orden ASC, categoria ASC";
    $result = mysqli_query($conexion, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_BOTH) : [];
}
}

if(!function_exists('getCategoiasPorUser')){
function getCategoiasPorUser() {
    global $conexion;

    if (!isset($_SESSION['usuario'])) {
        return [];
    }

    $usuario = $_SESSION['usuario'];
    $sql = "SELECT c.categoria, c.descripcion, c.ruta, c.icono 
            FROM permisos p 
            INNER JOIN categorias c ON p.ID_Categoria = c.ID_Categoria 
            WHERE p.usuario = ? AND c.activo = 1
            ORDER BY c.orden ASC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categorias = [];
    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
        $categorias[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $categorias;
}
}

if(!function_exists('getCategoriaPorId')){
function getCategoriaPorId($id) {
    global $conexion;
    $sql = "SELECT * FROM categorias WHERE ID_Categoria = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
        mysqli_stmt_close($stmt);
        return $row;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}
}

if(!function_exists('editarCategoria')){
function editarCategoria($id, $nombre, $descripcion, $ruta) {
    global $conexion;
    
    // Obtener datos anteriores para auditoría
    $categoria_anterior = getCategoriaPorId($id);
    
    $sql = "UPDATE categorias SET categoria = ?, descripcion = ?, ruta = ? WHERE ID_Categoria = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $nombre, $descripcion, $ruta, $id);
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        $nuevos_datos = ['categoria' => $nombre, 'descripcion' => $descripcion, 'ruta' => $ruta];
        logAuditoria($_SESSION['usuario'], "Categoría editada", 'categorias', $id, $categoria_anterior, $nuevos_datos);
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

// Funciones de usuarios
if(!function_exists('getUsuarios')){
function getUsuarios() {
    global $conexion;
    $sql = "SELECT usuario, nombre_completo, email, cargo, departamento, activo FROM usuarios WHERE admin != 1 ORDER BY nombre_completo";
    $result = mysqli_query($conexion, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_BOTH) : [];
}
}

if(!function_exists('crearUsuario')){
function crearUsuario($datos, $clave) {
    global $conexion;
    
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (usuario, clave, nombre_completo, email, telefono, cargo, departamento, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", 
        $datos['usuario'], $clave_hash, $datos['nombre_completo'], 
        $datos['email'], $datos['telefono'], $datos['cargo'], 
        $datos['departamento'], $datos['fecha_ingreso']
    );
    
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        logAuditoria($_SESSION['usuario'], "Usuario creado", 'usuarios', null, null, $datos);
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

// Funciones de permisos
if(!function_exists('eliminarPermisos')){
function eliminarPermisos($usuario) {
    global $conexion;
    $sql = "DELETE FROM permisos WHERE usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

if(!function_exists('asignarPermisos')){
function asignarPermisos($usuario, $idCat) {
    global $conexion;
    $sql = "INSERT INTO permisos (usuario, ID_Categoria) VALUES (?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $usuario, $idCat);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

if(!function_exists('tienePermisos')){
function tienePermisos($usuario, $idCat) {
    global $conexion;
    $sql = "SELECT COUNT(*) as total FROM permisos WHERE usuario = ? AND ID_Categoria = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $usuario, $idCat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row['total'] > 0;
}
}

// Funciones de solicitudes con seguridad mejorada
if(!function_exists('registrarSolicitud')){
function registrarSolicitud($data, $usuario) {
    global $conexion;

    $sql = "INSERT INTO solicitudes (usuario, tipo, titulo, fecha_inicio, fecha_fin, motivo, correo_notificacion, prioridad, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
    $stmt = mysqli_prepare($conexion, $sql);
    
    $titulo = $data['titulo'] ?? ucfirst($data['tipo']);
    $prioridad = $data['prioridad'] ?? 'media';
    
    mysqli_stmt_bind_param($stmt, "ssssssss", 
        $usuario, $data['tipo'], $titulo, $data['fecha_inicio'], 
        $data['fecha_fin'], $data['motivo'], $data['correo_notificacion'], $prioridad
    );

    $resultado = mysqli_stmt_execute($stmt);
    $solicitud_id = mysqli_insert_id($conexion);
    
    if ($resultado) {
        logAuditoria($usuario, "Solicitud creada", 'solicitudes', $solicitud_id, null, $data);
        
        // Opcional: Enviar notificación por email
        /*
        $asunto = "Nueva Solicitud: " . $titulo;
        $mensaje = "Se ha registrado una nueva solicitud de tipo: " . $data['tipo'];
        enviarCorreoSMTP($data['correo_notificacion'], $asunto, $mensaje);
        */
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

if(!function_exists('obtenerSolicitudesPorUsuario')){
function obtenerSolicitudesPorUsuario($usuario) {
    global $conexion;
    $sql = "SELECT * FROM solicitudes WHERE usuario = ? ORDER BY fecha_solicitud DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $solicitudes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $solicitudes;
}
}

if(!function_exists('getSolicitudes')){
function getSolicitudes() {
    global $conexion;
    $sql = "SELECT s.*, u.nombre_completo 
            FROM solicitudes s 
            LEFT JOIN usuarios u ON s.usuario = u.usuario 
            ORDER BY s.fecha_solicitud DESC";
    $result = mysqli_query($conexion, $sql);
    
    $solicitudes = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $solicitudes[] = $row;
        }
    }
    
    return $solicitudes;
}
}

if(!function_exists('cambiarEstadoSolicitud')){
function cambiarEstadoSolicitud($id, $estado, $comentarios = null) {
    global $conexion;
    
    // Obtener datos anteriores
    $sql_old = "SELECT * FROM solicitudes WHERE id = ?";
    $stmt_old = mysqli_prepare($conexion, $sql_old);
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $datos_anteriores = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_old));
    mysqli_stmt_close($stmt_old);
    
    $sql = "UPDATE solicitudes SET estado = ?, comentarios_admin = ?, fecha_respuesta = NOW(), respondido_por = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    $respondido_por = $_SESSION['usuario'] ?? null;
    mysqli_stmt_bind_param($stmt, "sssi", $estado, $comentarios, $respondido_por, $id);
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        $nuevos_datos = ['estado' => $estado, 'comentarios_admin' => $comentarios, 'respondido_por' => $respondido_por];
        logAuditoria($_SESSION['usuario'], "Estado de solicitud cambiado", 'solicitudes', $id, $datos_anteriores, $nuevos_datos);
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

if(!function_exists('eliminarSolicitud')){
function eliminarSolicitud($id) {
    global $conexion;
    
    // Obtener datos para auditoría
    $sql_old = "SELECT * FROM solicitudes WHERE id = ?";
    $stmt_old = mysqli_prepare($conexion, $sql_old);
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $datos_anteriores = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_old));
    mysqli_stmt_close($stmt_old);
    
    $sql = "DELETE FROM solicitudes WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        logAuditoria($_SESSION['usuario'], "Solicitud eliminada", 'solicitudes', $id, $datos_anteriores);
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

// Funciones de documentos con validación mejorada
if(!function_exists('getCategoriasDocumentos')){
function getCategoriasDocumentos() {
    global $conexion;
    $sql = "SELECT * FROM categorias_documentos WHERE activo = 1 ORDER BY orden ASC, nombre ASC";
    $result = mysqli_query($conexion, $sql);
    
    $categorias = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categorias[] = $row;
        }
    }
    
    return $categorias;
}
}

if(!function_exists('getDocumentosPorCategoria')){
function getDocumentosPorCategoria($categoria_id) {
    global $conexion;
    $sql = "SELECT d.*, cd.nombre as categoria_nombre 
            FROM documentos d 
            INNER JOIN categorias_documentos cd ON d.categoria_id = cd.id 
            WHERE d.categoria_id = ? AND d.activo = 1 
            ORDER BY d.fecha_actualizacion DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $categoria_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $documentos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $documentos[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $documentos;
}
}

if(!function_exists('getTodosDocumentos')){
function getTodosDocumentos() {
    global $conexion;
    $sql = "SELECT d.*, cd.nombre as categoria_nombre 
            FROM documentos d 
            LEFT JOIN categorias_documentos cd ON d.categoria_id = cd.id 
            WHERE d.activo = 1 
            ORDER BY d.fecha_actualizacion DESC";
    $result = mysqli_query($conexion, $sql);
    
    $documentos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $documentos[] = $row;
        }
    }
    
    return $documentos;
}
}

if(!function_exists('validarTipoArchivo')){
function validarTipoArchivo($archivo) {
    $tipos_permitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'image/jpeg',
        'image/png'
    ];
    
    $extensiones_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    return in_array($mime_type, $tipos_permitidos) && in_array($extension, $extensiones_permitidas);
}
}

if(!function_exists('subirDocumento')){
function subirDocumento($data, $archivo, $usuario) {
    global $conexion;
    
    // Validar tipo de archivo
    if (!validarTipoArchivo($archivo)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    // Verificar tamaño (10MB máximo)
    if ($archivo['size'] > 10485760) {
        return ['success' => false, 'message' => 'El archivo es demasiado grande (máximo 10MB)'];
    }
    
    $carpeta = "../uploads/documentos/";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $nombre_archivo = uniqid() . "_" . time() . "." . $extension;
    $ruta_completa = $carpeta . $nombre_archivo;
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        $sql = "INSERT INTO documentos (titulo, descripcion, categoria_id, nombre_archivo, archivo_original, tipo_archivo, tamaño, usuario_subida, version, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        
        $titulo = sanitizarEntrada($data['titulo']);
        $descripcion = sanitizarEntrada($data['descripcion']);
        $categoria_id = (int)$data['categoria_id'];
        $archivo_original = sanitizarEntrada($archivo['name']);
        $tamaño = $archivo['size'];
        $version = sanitizarEntrada($data['version'] ?: '1.0');
        $tags = sanitizarEntrada($data['tags'] ?: '');
        
        mysqli_stmt_bind_param($stmt, "ssisssiiss", 
            $titulo, $descripcion, $categoria_id, $nombre_archivo, 
            $archivo_original, $extension, $tamaño, $usuario, $version, $tags);
        
        if (mysqli_stmt_execute($stmt)) {
            $documento_id = mysqli_insert_id($conexion);
            logAuditoria($usuario, "Documento subido", 'documentos', $documento_id, null, $data);
            mysqli_stmt_close($stmt);
            return ['success' => true, 'message' => 'Documento subido exitosamente'];
        } else {
            unlink($ruta_completa);
            mysqli_stmt_close($stmt);
            return ['success' => false, 'message' => 'Error al guardar en base de datos'];
        }
    }
    
    return ['success' => false, 'message' => 'Error al subir archivo'];
}
}

if(!function_exists('eliminarDocumento')){
function eliminarDocumento($id) {
    global $conexion;
    
    // Obtener info del archivo
    $sql = "SELECT * FROM documentos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $archivo = "../uploads/documentos/" . $row['nombre_archivo'];
        
        // Eliminar de BD
        $sql_delete = "DELETE FROM documentos WHERE id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            // Eliminar archivo físico
            if (file_exists($archivo)) {
                unlink($archivo);
            }
            logAuditoria($_SESSION['usuario'], "Documento eliminado", 'documentos', $id, $row);
            mysqli_stmt_close($stmt_delete);
            mysqli_stmt_close($stmt);
            return true;
        }
        mysqli_stmt_close($stmt_delete);
    }
    
    mysqli_stmt_close($stmt);
    return false;
}
}

if(!function_exists('incrementarDescargas')){
function incrementarDescargas($id) {
    global $conexion;
    $sql = "UPDATE documentos SET descargas = descargas + 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        logAuditoria($_SESSION['usuario'] ?? 'anonimo', "Documento descargado", 'documentos', $id);
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

if(!function_exists('buscarDocumentos')){
function buscarDocumentos($termino) {
    global $conexion;
    $termino_like = "%" . $termino . "%";
    $sql = "SELECT d.*, cd.nombre as categoria_nombre 
            FROM documentos d 
            LEFT JOIN categorias_documentos cd ON d.categoria_id = cd.id 
            WHERE d.activo = 1 AND (d.titulo LIKE ? OR d.descripcion LIKE ? OR d.tags LIKE ?)
            ORDER BY d.fecha_actualizacion DESC";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $termino_like, $termino_like, $termino_like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $documentos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $documentos[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $documentos;
}
}

// Funciones de noticias
if(!function_exists('guardarNoticia')){
function guardarNoticia($datos, $archivo = null) {
    global $conexion;
    
    $imagen_nombre = null;
    
    // Procesar imagen si se subió
    if ($archivo && $archivo['error'] === 0) {
        if (!validarImagenNoticia($archivo)) {
            return ['success' => false, 'message' => 'Tipo de imagen no válido'];
        }
        
        $carpeta = "../uploads/imagenes_noticias/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $imagen_nombre = uniqid() . "_" . time() . "." . $extension;
        $ruta_completa = $carpeta . $imagen_nombre;
        
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            return ['success' => false, 'message' => 'Error al subir imagen'];
        }
    }
    
    $sql = "INSERT INTO noticias (titulo, contenido, imagen, fecha_publicacion, estado, autor) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    
    $titulo = sanitizarEntrada($datos['titulo']);
    $contenido = sanitizarEntrada($datos['contenido']);
    $fecha = $datos['fecha_publicacion'];
    $estado = $datos['estado'];
    $autor = $_SESSION['usuario'];
    
    mysqli_stmt_bind_param($stmt, "ssssss", $titulo, $contenido, $imagen_nombre, $fecha, $estado, $autor);
    
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        $noticia_id = mysqli_insert_id($conexion);
        logAuditoria($autor, "Noticia creada", 'noticias', $noticia_id, null, $datos);
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => 'Noticia guardada exitosamente'];
    }
    
    // Si falló, eliminar imagen subida
    if ($imagen_nombre && file_exists($carpeta . $imagen_nombre)) {
        unlink($carpeta . $imagen_nombre);
    }
    
    mysqli_stmt_close($stmt);
    return ['success' => false, 'message' => 'Error al guardar noticia'];
}
}

if(!function_exists('validarImagenNoticia')){
function validarImagenNoticia($archivo) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    return in_array($mime_type, $tipos_permitidos) && 
           in_array($extension, $extensiones_permitidas) && 
           $archivo['size'] <= 5242880; // 5MB máximo
}
}

if(!function_exists('obtenerNoticias')){
function obtenerNoticias($activas_solo = true) {
    global $conexion;
    
    $sql = "SELECT n.*, u.nombre_completo as autor_nombre 
            FROM noticias n 
            LEFT JOIN usuarios u ON n.autor = u.usuario";
    
    if ($activas_solo) {
        $sql .= " WHERE n.estado = 'activo'";
    }
    
    $sql .= " ORDER BY n.fecha_publicacion DESC";
    
    $result = mysqli_query($conexion, $sql);
    
    $noticias = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $noticias[] = $row;
        }
    }
    
    return $noticias;
}
}

// Funciones de configuración
if(!function_exists('obtenerConfiguracion')){
function obtenerConfiguracion($clave) {
    global $conexion;
    $sql = "SELECT valor FROM configuracion WHERE clave = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $clave);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['valor'];
    }
    
    mysqli_stmt_close($stmt);
    return null;
}
}

if(!function_exists('actualizarConfiguracion')){
function actualizarConfiguracion($clave, $valor) {
    global $conexion;
    $sql = "UPDATE configuracion SET valor = ? WHERE clave = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $valor, $clave);
    $resultado = mysqli_stmt_execute($stmt);
    
    if ($resultado) {
        logAuditoria($_SESSION['usuario'], "Configuración actualizada: $clave", 'configuracion');
    }
    
    mysqli_stmt_close($stmt);
    return $resultado;
}
}

// Función para envío de correos (mejorada)
if(!function_exists('enviarCorreoSMTP')){
function enviarCorreoSMTP($destinatarios, $asunto, $mensajeHtml, $archivos_adjuntos = []) {
    $config = include 'config_mail.php';
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        // Remitente
        $mail->setFrom($config['username'], $config['from_name']);

        // Destinatarios
        if (is_array($destinatarios)) {
            foreach ($destinatarios as $email) {
                $mail->addAddress(trim($email));
            }
        } else {
            $destinatarios_array = explode(",", $destinatarios);
            foreach ($destinatarios_array as $email) {
                $mail->addAddress(trim($email));
            }
        }

        // Archivos adjuntos
        foreach ($archivos_adjuntos as $archivo) {
            if (file_exists($archivo)) {
                $mail->addAttachment($archivo);
            }
        }

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensajeHtml;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
}

// Funciones auxiliares
if(!function_exists('formatBytes')){
function formatBytes($size, $precision = 2) {
    if ($size == 0) return '0 B';
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
}

if(!function_exists('generarToken')){
function generarToken($longitud = 32) {
    return bin2hex(random_bytes($longitud / 2));
}
}

if(!function_exists('validarEmail')){
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
}

if(!function_exists('validarTelefono')){
function validarTelefono($telefono) {
    return preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $telefono);
}
}

// Función para cambiar contraseña
if(!function_exists('cambiarContrasena')){
function cambiarContrasena($usuario, $contrasena_actual, $contrasena_nueva) {
    global $conexion;
    
    // Verificar contraseña actual
    $sql = "SELECT clave FROM usuarios WHERE usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($contrasena_actual, $row['clave'])) {
            // Cambiar contraseña
            $nueva_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET clave = ? WHERE usuario = ?";
            $stmt_update = mysqli_prepare($conexion, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ss", $nueva_hash, $usuario);
            $resultado = mysqli_stmt_execute($stmt_update);
            
            if ($resultado) {
                logAuditoria($usuario, "Contraseña cambiada");
            }
            
            mysqli_stmt_close($stmt_update);
            mysqli_stmt_close($stmt);
            return $resultado;
        }
    }
    
    mysqli_stmt_close($stmt);
    return false;
}
}

?>