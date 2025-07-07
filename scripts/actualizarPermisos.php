<?php
require 'funciones.php';
//Validacion de la sesion como administrador:
if (! haIniciadoSesion() || ! esAdmin()) 
{
    header('Location: index.html');
    exit();
}
//validacion del parametro POST
if (isset($_POST['txtUsuario']))
    $usuario = $_POST['txtUsuario'];
else {
    header('Location: ../admin/panelAdmin.php');
    exit();
}
//actualizar de categorias
conectar();
//Funcion para eliminar permisos
eliminarPermisos($usuario);
//Reasignar Permisos
$categorias = getTodasCategorias();
foreach ($categorias as $categoria):
    if(isset( $_POST['categoria' .$categoria[0]] ))
    asignarPermisos($usuario,$categoria[0]);
endforeach;

desconectar();

// CORRECCIÓN: Redirigir de vuelta al panel con mensaje de éxito
header('Location: ../admin/panelAdmin.php?mensaje=permisos_actualizados');
exit();

?>