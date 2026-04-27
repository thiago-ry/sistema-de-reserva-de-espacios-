<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include 'conection.php';

$fecha = $_GET['fecha'] ?? null;
$espacio_id = $_GET['espacio_id'] ?? null;

if (!$fecha || !$espacio_id) {
  die("Parámetros incompletos: fecha o espacio_id faltante.");
}

$horarios = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00'];

function obtenerReservas($conn, $fecha, $espacio_id, $horarios) {
  $reservas = [];
  $query = "SELECT hora_inicio, fk_cuit_usuario FROM Reservas WHERE fecha = ? AND fk_id_espacio = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("si", $fecha, $espacio_id);
  $stmt->execute();
  $stmt->bind_result($hora, $usuario);
  while ($stmt->fetch()) {
    $reservas[$hora] = $usuario;
  }
  $stmt->close();
  return $reservas;
}


function obtenerNombreEspacio($conn, $espacio_id) {
  $stmt = $conn->prepare("SELECT nombre FROM Espacios WHERE pk_id_espacio = ?");
  $stmt->bind_param("i", $espacio_id);
  $stmt->execute();
  $stmt->bind_result($nombre);
  $stmt->fetch();
  $stmt->close();
  return $nombre;
}

$reservas = obtenerReservas($conn, $fecha, $espacio_id, $horarios);
$nombre_espacio = obtenerNombreEspacio($conn, $espacio_id);

echo "<h2>Reservas para <strong>$nombre_espacio</strong> el <strong>$fecha</strong></h2>";

if (count($reservas) === 0) {
  echo "<p>Día completamente libre. Reservá ahora:</p>";
  echo "<form action='registrar_reserva.php' method='POST'>
          <input type='hidden' name='fecha' value='$fecha'>
          <input type='hidden' name='espacio_id' value='$espacio_id'>
          <label>Hora:</label>
          <select name='hora'>";
  foreach ($horarios as $hora) {
    echo "<option value='$hora'>$hora</option>";
  }
  echo "</select>
        <label>Usuario (CUIT):</label>
        <input type='text' name='usuario' required>
        <button type='submit'>Reservar</button>
        </form>";
} elseif (count($reservas) < count($horarios)) {
  echo "<p>Día parcialmente ocupado. Horarios disponibles:</p><ul>";
  foreach ($horarios as $hora) {
    if (!isset($reservas[$hora])) {
      echo "<li><a href='registrar_reserva.php?fecha=$fecha&hora=$hora&espacio_id=$espacio_id'>Reservar $hora</a></li>";
    }
  }
  echo "</ul>";
} else {
  echo "<p>Día completamente ocupado. Reservas existentes:</p><ul>";
  foreach ($reservas as $hora => $usuario) {
    echo "<li>$hora - $usuario</li>";
  }
  echo "</ul>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
  body {
    font-family: Arial, sans-serif;
    margin: 30px;
    background-color: #f9f9f9;
    color: #333;
  }
  h2 {
    color: #2c3e50;
  }
  p {
    font-size: 16px;
  }
  ul {
    padding-left: 20px;
  }
  li {
    margin-bottom: 8px;
  }
  form {
    margin-top: 20px;
    background-color: #fff;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  label {
    display: block;
    margin-top: 10px;
  }
  input, select, button {
    margin-top: 5px;
    padding: 8px;
    width: 100%;
    max-width: 300px;
  }
  button {
    background-color: #3498db;
    color: white;
    border: none;
    cursor: pointer;
  }
  button:hover {
    background-color: #2980b9;
  }
</style>

</head>
<body>
    
</body>
</html>