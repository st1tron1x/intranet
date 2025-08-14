<?php
require 'funciones.php';

if (!haIniciadoSesion() || !esAdmin()) {
    header("Location: ../index.html");
    exit();
}

if (!isset($_POST['id'])) {
    header("Location: ../admin/noticias.php");
    exit();
}

conectar();

$id = (int) $_POST['id'];
$titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
$contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
$fecha = $_POST['fecha_publicacion'];
$estado = $_POST['estado'];

// Consultar nombre actual de la imagen
$res = $conn->query("SELECT imagen FROM noticias WHERE id = $id LIMIT 1");
$row = $res->fetch_assoc();
$imagen_actual = $row['imagen'];
$imagen_nombre = $imagen_actual;

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $carpeta = "../uploads/imagenes_noticias/";
    $nombre_archivo = basename($_FILES['imagen']['name']);
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extension, $permitidas)) {
        $nuevo_nombre = uniqid() . "." . $extension;
        $ruta_completa = $carpeta . $nuevo_nombre;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
            $imagen_nombre = $nuevo_nombre;
            // Opcional: eliminar imagen anterior si existe
            if ($imagen_actual && file_exists($carpeta . $imagen_actual)) {
                unlink($carpeta . $imagen_actual);
            }
        }
    }
}

$sql = "UPDATE noticias SET titulo = ?, contenido = ?, imagen = ?, fecha_publicacion = ?, estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $titulo, $contenido, $imagen_nombre, $fecha, $estado, $id);

if ($stmt->execute()) {
    header("Location: ../admin/noticias.php?msg=actualizado");
} else {
    echo "Error al actualizar: " . $stmt->error;
}

$stmt->close();
desconectar();
?>
