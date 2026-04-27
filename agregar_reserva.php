<?php
session_start();
include 'conection.php';

$error = '';
$exito = '';

// Obtener datos predefinidos
$fecha_reservada = $_GET['fecha'] ?? '';
$id_espacio = isset($_GET['espacio_id']) ? intval($_GET['espacio_id']) : 0;
$cuit_usuario = $_SESSION['usuario'] ?? null; // ✅ CUIT tomado correctamente
$fecha_hoy = date('Y-m-d');

// Validar que estén todos los datos
if (!$fecha_reservada || $id_espacio <= 0 || !$cuit_usuario) {
  echo "<p class='error'>Datos inválidos: falta fecha, espacio o sesión de usuario.</p>";
  exit;
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hora_inicio  = $_POST['hora_inicio'] ?? '';
  $hora_fin     = $_POST['hora_fin'] ?? '';
  $id_estado    = $_POST['estado'] ?? 1;

  if (strtotime($fecha_reservada) < strtotime($fecha_hoy)) {
    $error = "No se puede reservar una fecha que ya pasó.";
  } elseif (strtotime($hora_inicio) >= strtotime($hora_fin)) {
    $error = "La hora de inicio debe ser anterior a la hora de fin.";
  } else {
    // Validar solapamiento
    $verificar = $conn->prepare("
      SELECT pk_id_reserva FROM reservas 
      WHERE fk_id_espacio = ? AND fecha_reservada = ?
      AND (
        (hora_inicio < ? AND hora_fin > ?) OR
        (hora_inicio < ? AND hora_fin > ?)
      )
    ");
    $verificar->bind_param("isssss", $id_espacio, $fecha_reservada, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
      $error = "Ya existe una reserva en ese espacio y horario.";
    } else {
      // Insertar reserva
      $stmt = $conn->prepare("
        INSERT INTO reservas 
        (fecha_hoy, hora_inicio, hora_fin, fk_id_estado, fk_id_espacio, fecha_reservada, fk_cuit_usuario) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->bind_param("sssiiss", $fecha_hoy, $hora_inicio, $hora_fin, $id_estado, $id_espacio, $fecha_reservada, $cuit_usuario);
      if ($stmt->execute()) {
        header("Location: pagina_principal.php?msg=creado");
        exit;
      } else {
        $error = "Error al insertar: " . $stmt->error;
      }
      $stmt->close();
    }
    $verificar->close();
  }
}

// Obtener nombre del espacio
$nombre_espacio = '';
$espacio_q = $conn->prepare("SELECT nombre FROM espacios WHERE pk_id_espacio = ?");
$espacio_q->bind_param("i", $id_espacio);
$espacio_q->execute();
$espacio_q->bind_result($nombre_espacio);
$espacio_q->fetch();
$espacio_q->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Reserva</title>
  <link rel="stylesheet" href="administrador/admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="img/mas.png" width="25px"> Nueva Reserva</h1>
  <nav>
    <ul>
      <li><a href="pagina_principal.php">← Volver al menu</a></li>
    </ul>
  </nav>
</header>

<section class="form-contenedor">
  <?php if ($error): ?>
    <p class="error"><?= $error ?></p>
  <?php elseif ($exito): ?>
    <p class="exito"><?= $exito ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>Fecha Reservada</label>
    <input type="text" name="fecha_reservada" value="<?= htmlspecialchars($fecha_reservada) ?>" readonly>

    <label>Hora de Inicio</label>
    <input type="time" name="hora_inicio" required>

    <label>Hora de Fin</label>
    <input type="time" name="hora_fin" required>

    <label>Espacio</label>
    <input type="text" value="<?= htmlspecialchars($nombre_espacio) ?>" readonly>
    <input type="hidden" name="espacio" value="<?= $id_espacio ?>">

    <label>Estado</label>
    <select name="estado" required>
      <option value="1">Pendiente</option>
    </select>

    <button type="submit" class="btn editar">Reservar</button>
  </form>
</section>

</body>
</html>
