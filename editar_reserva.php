<?php
session_start();
include("conection.php");

// Validación de sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$cuit = $_SESSION["usuario"];
$id_reserva = $_GET["id"] ?? null;

if (!$id_reserva) {
    header("Location: mis_reservas.php");
    exit;
}

// Obtener datos actuales de la reserva
$sql = "SELECT fecha_reservada, hora_inicio, hora_fin, fk_id_espacio FROM Reservas WHERE pk_id_reserva = ? AND fk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_reserva, $cuit);
$stmt->execute();
$stmt->bind_result($fecha, $hora_inicio, $hora_fin, $id_espacio);
$stmt->fetch();
$stmt->close();

// Obtener lista de espacios
$espacios = $conn->query("SELECT pk_id_espacio, nombre FROM Espacios ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Reserva</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .form-container {
      max-width: 500px;
      margin: 40px auto;
      background-color: #ecf0f1;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .form-container h2 {
      margin-bottom: 20px;
      color: #2c3e50;
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #bdc3c7;
    }
    button {
      margin-top: 20px;
      background-color: #2980b9;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 4px;
      cursor: pointer;
    }
    .volver {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: #2980b9;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Editar Reserva</h2>
    <form method="POST" action="procesar_edicion.php">
      <input type="hidden" name="id_reserva" value="<?= $id_reserva ?>">

      <label for="fecha">Fecha:</label>
      <input type="date" name="fecha" value="<?= $fecha ?>" required>

      <label for="hora_inicio">Hora inicio:</label>
      <input type="time" name="hora_inicio" value="<?= $hora_inicio ?>" required>

      <label for="hora_fin">Hora fin:</label>
      <input type="time" name="hora_fin" value="<?= $hora_fin ?>" required>

      <label for="espacio">Espacio:</label>
      <select name="espacio" required>
        <?php while ($row = $espacios->fetch_assoc()): ?>
          <option value="<?= $row["pk_id_espacio"] ?>" <?= $row["pk_id_espacio"] == $id_espacio ? 'selected' : '' ?>>
            <?= htmlspecialchars($row["nombre"]) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Guardar cambios</button>
    </form>
    <a href="mis_reservas.php" class="volver">← Volver a mis reservas</a>
  </div>
</body>
</html>
