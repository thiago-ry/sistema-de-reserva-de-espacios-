<?php
include '../../conection.php';

$error = '';
$exito = '';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fecha_hoy       = date('Y-m-d');
  $fecha_reservada = isset($_POST['fecha_reservada']) ? trim($_POST['fecha_reservada']) : '';
  $hora_inicio     = $_POST['hora_inicio'] ?? '';
  $hora_fin        = $_POST['hora_fin'] ?? '';
  $id_estado       = $_POST['estado'] ?? '';
  $id_espacio      = $_POST['espacio'] ?? '';
  $cuit_usuario    = $_POST['cuit'] ?? '';

  // Validaciones básicas
  if (empty($fecha_reservada)) {
    $error = "La fecha reservada es obligatoria.";
  } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reservada)) {
    $error = "La fecha reservada no tiene el formato correcto (aaaa-mm-dd).";
  } elseif (strtotime($fecha_reservada) < strtotime($fecha_hoy)) {
    $error = "No se puede reservar una fecha que ya pasó.";
  } elseif (strtotime($hora_inicio) >= strtotime($hora_fin)) {
    $error = "La hora de inicio debe ser anterior a la hora de fin.";
  } else {
    // Validar existencia del CUIT
    $verifica_cuit = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE pk_cuit_usuario = ?");
    $verifica_cuit->bind_param("i", $cuit_usuario);
    $verifica_cuit->execute();
    $verifica_cuit->bind_result($existe_cuit);
    $verifica_cuit->fetch();
    $verifica_cuit->close();

    if ($existe_cuit == 0) {
      $error = "El CUIT ingresado no corresponde a ningún usuario registrado.";
    }
  }

  // Si no hay error, verificar solapamiento
  if (empty($error)) {
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
        $exito = "Reserva creada correctamente.";
        header("Location: listar.php?msg=creado");
        exit;
      } else {
        $error = "Error al insertar: " . $stmt->error;
      }
      $stmt->close();
    }
    $verificar->close();
  }
}

// Cargar espacios dinámicamente
$espacios = [];
$consulta = $conn->query("SELECT pk_id_espacio, nombre FROM espacios ORDER BY nombre ASC");
if ($consulta) {
  while ($row = $consulta->fetch_assoc()) {
    $espacios[] = $row;
  }
} else {
  echo "<p class='error'>Error al cargar espacios: " . $conn->error . "</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Reserva</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/mas.png" width="25px"> Nueva Reserva</h1>
  <nav>
    <ul>
      <li><a href="listar.php">← Volver al Listado</a></li>
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
    <label>CUIT del Usuario</label>
    <input type="text" name="cuit" required>

    <label>Fecha Reservada</label>
    <input type="date" name="fecha_reservada" required min="<?= date('Y-m-d') ?>">

    <label>Hora de Inicio</label>
    <input type="time" name="hora_inicio" required>

    <label>Hora de Fin</label>
    <input type="time" name="hora_fin" required>

    <label>Espacio</label>
    <select name="espacio" required>
      <option value="">-- Seleccionar Espacio --</option>
      <?php foreach ($espacios as $espacio): ?>
        <option value="<?= $espacio['pk_id_espacio'] ?>">
          <?= htmlspecialchars($espacio['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Estado</label>
    <select name="estado" required>
      <option value="1">Pendiente</option>
    </select>

    <button type="submit" class="btn editar">Reservar</button>
  </form>
</section>

</body>
</html>
