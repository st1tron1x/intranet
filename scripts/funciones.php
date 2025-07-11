<?php

$conexion = null;

function conectar()
{
    global $conexion;
    $conexion = mysqli_connect('localhost','root', '', 'intranet');
    mysqli_set_charset($conexion, 'utf8');
}

function getTodasCategorias()
{
    global $conexion;
    $respuesta = mysqli_query($conexion, "SELECT * FROM categorias");
    return $respuesta->fetch_all();
}

function getCategoiasPorUser()
{
    global $conexion;
    $respuesta = mysqli_query($conexion, "SELECT C.categoría, C.descripcion, C.ruta FROM permisos P INNER JOIN categorias C ON P.ID_Categoria = C.ID_Categoria WHERE usuario='".$_SESSION['usuario']."'");
    return $respuesta->fetch_all();
    
}

function getCategoriaPorId($id)
{
    global $conexion;
    $respuesta = mysqli_query($conexion, "SELECT * FROM categorias WHERE ID_Categoria = " . intval($id));
    if($respuesta && mysqli_num_rows($respuesta) > 0) {
        return mysqli_fetch_row($respuesta);
    }
    return false;
}

function editarCategoria($id, $nombre, $descripcion, $ruta)
{
    global $conexion;
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $ruta = mysqli_real_escape_string($conexion, $ruta);
    
    $consulta = "UPDATE categorias SET categoría='".$nombre."', descripcion='".$descripcion."', ruta='".$ruta."' WHERE ID_Categoria=".intval($id);
    return mysqli_query($conexion, $consulta);
}

function getUsuarios()
{
    global $conexion;
    $respuesta = mysqli_query($conexion, "SELECT * FROM usuarios WHERE admin<>1");
    return $respuesta->fetch_all();
}

function validarLogin($usuario, $clave)
{
    global $conexion;
    $consulta = "SELECT * FROM usuarios WHERE usuario='".$usuario."' AND clave='".$clave."'";
    $respuesta = mysqli_query($conexion, $consulta);

    if($fila = mysqli_fetch_row($respuesta))
    {
        session_start();
        $_SESSION['usuario']=$usuario;
        $_SESSION['admin']=$fila[2];
        return true;
    }
    return false;
}

function eliminarPermisos($usuario)
{
    global $conexion;
    mysqli_query($conexion, "DELETE FROM permisos WHERE usuario='".$usuario."'");
}

function asignarPermisos($usuario, $idCat)
{
    global $conexion;
    mysqli_query($conexion, "INSERT INTO permisos VALUES ('".$usuario."',".$idCat.")");
}

function tienePermisos($usuario, $idCat)
{
    global $conexion;
    $result = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM permisos WHERE usuario='".$usuario."' AND ID_Categoria='".$idCat."'");
    $row = mysqli_fetch_assoc($result);
    $numero = $row['total'];
    return $numero > 0;

}

function haIniciadoSesion()
{
    session_start();
    return isset($_SESSION['usuario']);
}

function esAdmin()
{
    return $_SESSION['admin'];
}
    
function desconectar()
{
    global $conexion;
    mysqli_close($conexion);
}

    ///foreach ($respuesta as $fila){
    ///    echo $fila[0] . " " . $fila[1] . " " . $fila[2] . " " . $fila[3];
    ///}
?>