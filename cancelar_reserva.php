<?php
session_start();
include("conection.php");

if (!isset($_POST["id_reserva"])) {
    header("Location: mis_reservas.php");
    exit;
}

$id = $_POST["id_reserva"];
$stmt = $conn->prepare("DELETE FROM Reservas WHERE pk_id_reserva = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: mis_reservas.php");
exit;
