<?php
require 'funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header('Location: ../index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../admin/documentos.php');
    exit();
}

conectar();

$id = (int)$_POST['id'];
$titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
$descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
$categoria_id = (int)$_POST['categoria_id'];
$version = mysqli_real_escape_string($conexion, $_POST['version']);
$tags = mysqli_real_escape_string($conexion, $_POST['tags']);
$activo = isset($_POST['activo']) ? 1 : 0;

// Verificar si hay nuevo archivo
$actualizar_archivo = false;
$nombre_archivo = '';
$archivo_original = '';
$tipo_archivo = '';
$tamaño = 0;

if (isset($_FILES['nuevo_archivo']) && $_FILES['nuevo_archivo']['error'] === 0) {
    $carpeta = "../uploads/documentos/";
    $archivo_original = $_FILES['nuevo_archivo']['name'];
    $extension = strtolower(pathinfo($archivo_original, PATHINFO_EXTENSION));
    $permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];

    if (in_array($extension, $permitidas)) {
        $nombre_archivo = uniqid() . "." . $extension;
        $ruta_completa = $carpeta . $nombre_archivo;

        if (move_uploaded_file($_FILES['nuevo_archivo']['tmp_name'], $ruta_completa)) {
            $actualizar_archivo = true;
            $tipo_archivo = $extension;
            $tamaño = $_FILES['nuevo_archivo']['size'];
            
            // Eliminar archivo anterior
            $sql_old = "SELECT nombre_archivo FROM documentos WHERE id = ?";
            $stmt_old = mysqli_prepare($conexion, $sql_old);
            mysqli_stmt_bind_param($stmt_old, "i", $id);
            mysqli_stmt_execute($stmt_old);
            $result_old = mysqli_stmt_get_result($stmt_old);
            
            if ($fila_old = mysqli_fetch_assoc($result_old)) {
                $archivo_anterior = $carpeta . $fila_old['nombre_archivo'];
                if (file_exists($archivo_anterior)) {
                    unlink($archivo_anterior);
                }
            }
            mysqli_stmt_close($stmt_old);
        }
    }
}

// Actualizar documento
if ($actualizar_archivo) {
    $sql = "UPDATE documentos SET titulo = ?, descripcion = ?, categoria_id = ?, nombre_archivo = ?, archivo_original = ?, tipo_archivo = ?, tamaño = ?, version = ?, tags = ?, activo = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssisssiisii", $titulo, $descripcion, $categoria_id, $nombre_archivo, $archivo_original, $tipo_archivo, $tamaño, $version, $tags, $activo, $id);
} else {
    $sql = "UPDATE documentos SET titulo = ?, descripcion = ?, categoria_id = ?, version = ?, tags = ?, activo = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssiisii", $titulo, $descripcion, $categoria_id, $version, $tags, $activo, $id);
}

if (mysqli_stmt_execute($stmt)) {
    header('Location: ../admin/documentos.php?mensaje=documento_actualizado');
} else {
    header('Location: ../admin/documentos.php?error=error_actualizando');
}

mysqli_stmt_close($stmt);
desconectar();
?>

<?php
// ===== admin/editar_documento.php =====
// (Este archivo ya está incluido en el artifact scripts_documentos)
?>