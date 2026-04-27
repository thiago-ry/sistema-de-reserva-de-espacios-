<?php
include("conection.php");

$fecha = $_GET["fecha"] ?? '';
$espacio_id = isset($_GET["espacio_id"]) ? intval($_GET["espacio_id"]) : 0;

if (!$fecha || $espacio_id <= 0) {
    echo "<p>Datos inválidos.</p>";
    exit;
}

$sql = "SELECT U.nombre, U.apellido, R.hora_inicio, R.hora_fin
        FROM Reservas R
        JOIN Usuarios U ON R.fk_cuit_usuario = U.pk_cuit_usuario
        WHERE R.fecha_reservada = ? AND R.fk_id_espacio = ? AND R.fk_id_estado = 1
        ORDER BY R.hora_inicio";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $fecha, $espacio_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table style='width:100%; border-collapse: collapse;'>";
    echo "<thead><tr style='background:#004080; color:white;'><th>Usuario</th><th>Hora reservada</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        $usuario = htmlspecialchars($row["nombre"] . " " . $row["apellido"]);
        $hora = htmlspecialchars($row["hora_inicio"] . " - " . $row["hora_fin"]);
        echo "<tr style='border-bottom:1px solid #ccc;'><td>$usuario</td><td>$hora</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No hay reservas para este día.</p>";
}
?>
