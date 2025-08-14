<?php
require 'funciones.php';

if (!haIniciadoSesion()) {
    header('Location: ../index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    conectar();
    
    $data = [
        'tipo' => $_POST['tipo'],
        'motivo' => $_POST['motivo'],
        'correo_notificacion' => $_POST['correo_notificacion'],
        'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
        'fecha_fin' => $_POST['fecha_fin'] ?? null
    ];
    
    registrarSolicitud($data, $_SESSION['usuario']);
    
    desconectar();
    
    header('Location: ../categorias/solicitudes.php?mensaje=solicitud_creada');
    exit();
} else {
    header('Location: ../categorias/solicitudes.php');
    exit();
}
?>