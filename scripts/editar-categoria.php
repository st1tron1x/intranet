<?php
require 'funciones.php';
//Validacion de la sesion como administrador:
if (! haIniciadoSesion() || ! esAdmin()) 
{
    header('Location: ../index.html');
    exit();
}
//validacion del parametro POST
if (isset($_POST['txtId']) && isset($_POST['txtNombre'])
    && isset($_POST['txtDescripcion']) && isset($_POST['txtRuta']) )
{
    $id=$_POST['txtId'];
    $nombre=$_POST['txtNombre'];
    $descripcion=$_POST['txtDescripcion'];
    $ruta=$_POST['txtRuta'];
}
else {
    header('Location: ../admin/panelAdmin.php');
    exit();
}
//actualizar de categorias
conectar();

editarCategoria( $id, $nombre, $descripcion, $ruta );



// CORRECCIÓN: Redirigir de vuelta al panel con mensaje de éxito
header('Location: ../admin/editarCategoria.php?id='.$id);
exit();

desconectar();

?>