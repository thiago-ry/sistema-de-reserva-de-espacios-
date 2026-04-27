<?php
session_start();
include("conection.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 2) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["fecha"]) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $_GET["fecha"])) {
    header("Location: calendario.php?error=fecha_invalida");
    exit;
}

$fecha = $_GET["fecha"];
$cuit = $_SESSION["usuario"];

// Verificar si ya existe una reserva para esa fecha y usuario
$check = $conn->prepare("SELECT COUNT(*) FROM Reservas WHERE fecha_reserva = ? AND fk_cuit_usuario = ?");
$check->bind_param("si", $fecha, $cuit);
$check->execute();
$check->bind_result($existe);
$check->fetch();
$check->close();

if ($existe > 0) {
    header("Location: calendario.php?mes=" . date("n", strtotime($fecha)) . "&anio=" . date("Y", strtotime($fecha)) . "&error=ya_reservado");
    exit;
}

// Insertar nueva reserva
$sql = "INSERT INTO Reservas (fecha_reserva, fk_cuit_usuario) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $fecha, $cuit);

if ($stmt->execute()) {
    header("Location: calendario.php?mes=" . date("n", strtotime($fecha)) . "&anio=" . date("Y", strtotime($fecha)) . "&success=1");
} else {
    header("Location: calendario.php?mes=" . date("n", strtotime($fecha)) . "&anio=" . date("Y", strtotime($fecha)) . "&error=fallo_insert");
}
?>
