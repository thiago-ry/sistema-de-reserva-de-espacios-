<?php
include("conection.php");

$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$espacio_id = $_GET['espacio_id'] ?? null;

if (!$start || !$end || !$espacio_id) {
  http_response_code(400);
  echo json_encode(["error" => "Parámetros incompletos"]);
  exit;
}

// Consulta de reservas por día
$stmt = $conn->prepare("
  SELECT fecha, COUNT(*) as cantidad
  FROM Reservas
  WHERE fk_id_espacio = ? AND fecha BETWEEN ? AND ?
  GROUP BY fecha
");
$stmt->bind_param("iss", $espacio_id, $start, $end);
$stmt->execute();
$result = $stmt->get_result();

$eventos = [];
while ($row = $result->fetch_assoc()) {
  $eventos[] = [
    'title' => "Reservas: " . $row['cantidad'],
    'start' => $row['fecha_reserva'],
    'allDay' => true
  ];
}

$stmt->close();
header('Content-Type: application/json');
echo json_encode($eventos);
