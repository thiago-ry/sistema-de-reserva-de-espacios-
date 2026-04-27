<?php
session_start();
include("conection.php");

// Validación de sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

// Validación de datos
if (!isset($_POST["nombre"], $_POST["apellido"], $_POST["rol"])) {
    header("Location: pagina_principal.php?perfil=error_datos");
    exit;
}

$cuit = $_SESSION["usuario"];
$nombre = trim($_POST["nombre"]);
$apellido = trim($_POST["apellido"]);
$rol = (int) $_POST["rol"];

// Actualización
$sql = "UPDATE Usuarios SET nombre = ?, apellido = ?, fk_id_rol = ? WHERE pk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $nombre, $apellido, $rol, $cuit);

if ($stmt->execute()) {
    header("Location: pagina_principal.php?perfil=actualizado");
} else {
    header("Location: pagina_principal.php?perfil=error_sql");
}
?>
