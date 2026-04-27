<?php
include 'conection.php';

// Validar parámetros
$fecha   = $_GET['fecha']   ?? null;
$espacio = $_GET['espacio'] ?? null;

if (!$fecha || !$espacio) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros."]);
    exit;
}

// Validar formato de fecha (opcional pero recomendable)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    echo json_encode(["error" => "Formato de fecha inválido."]);
    exit;
}

// Validar que el espacio exista
$stmt = $conn->prepare("SELECT pk_id_espacio FROM Espacios WHERE pk_id_espacio = ?");
$stmt->bind_param("i", $espacio);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Espacio no encontrado."]);
    exit;
}
$stmt->close();

// Horarios institucionales
$horarios = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00'];
$resultado = [];

$stmt = $conn->prepare("SELECT COUNT(*) FROM Reservas WHERE fecha_reserva = ? AND hora = ? AND espacio = ?");
foreach ($horarios as $hora) {
    $stmt->bind_param("ssi", $fecha, $hora, $espacio);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $resultado[] = [
        'hora' => $hora,
        'disponible' => $total == 0
    ];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($resultado);
?>
