<?php
session_start();
include("conection.php");

// Validación de sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$cuit = $_SESSION["usuario"];

// Capturar datos del formulario
$id_reserva = $_POST["id_reserva"];
$fecha = $_POST["fecha"];
$hora_inicio = $_POST["hora_inicio"];
$hora_fin = $_POST["hora_fin"];
$id_espacio = $_POST["espacio"];

// Validación de solapamiento
$sql = "SELECT COUNT(*) FROM Reservas
        WHERE fk_id_espacio = ?
          AND fecha_reservada = ?
          AND pk_id_reserva != ?
          AND (
            (hora_inicio < ? AND hora_fin > ?) OR
            (hora_inicio >= ? AND hora_inicio < ?)
          )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isissss", $id_espacio, $fecha, $id_reserva, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin);
$stmt->execute();
$stmt->bind_result($conflictos);
$stmt->fetch();
$stmt->close();

if ($conflictos > 0) {
    echo "<script>alert('Ya existe una reserva en ese espacio y horario. Por favor elegí otro horario.'); window.history.back();</script>";
    exit;
}

// Actualizar reserva
$sql = "UPDATE Reservas SET fecha_reservada = ?, hora_inicio = ?, hora_fin = ?, fk_id_espacio = ? WHERE pk_id_reserva = ? AND fk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssisi", $fecha, $hora_inicio, $hora_fin, $id_espacio, $id_reserva, $cuit);
$stmt->execute();
$stmt->close();

header("Location: mis_reservas.php");
exit;
