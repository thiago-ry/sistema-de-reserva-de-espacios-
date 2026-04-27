<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "ID de reserva no especificado.";
    exit;
}

$id_reserva = $_GET['id'];

// Verificar si la reserva existe
$sql = "
  SELECT r.fecha_reservada, r.hora_inicio, r.hora_fin, u.nombre AS nombre_usuario, e.nombre AS nombre_espacio
  FROM reservas r
  INNER JOIN usuarios u ON r.fk_cuit_usuario = u.pk_cuit_usuario
  INNER JOIN espacios e ON r.fk_id_espacio = e.pk_id_espacio
  WHERE r.pk_id_reserva = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$stmt->bind_result($fecha, $inicio, $fin, $usuario, $espacio);
if (!$stmt->fetch()) {
    echo "Reserva no encontrada.";
    exit;
}
$stmt->close();

// Procesar eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM reservas WHERE pk_id_reserva = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_reserva);
    if ($stmt->execute()) {
        header("Location: listar.php?msg=eliminado");
        exit;
    } else {
        $error = "Error al eliminar: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar Reserva</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/tacho.png" alt=""> Eliminar Reserva</h1>
  <nav>
    <ul>
      <li><a href="listar.php">← Volver al Listado</a></li>
    </ul>
  </nav>
</header>

<section class="form-contenedor">
  <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

  <p>¿Estás seguro que querés eliminar la reserva de <strong><?= htmlspecialchars($usuario) ?></strong> en <strong><?= htmlspecialchars($espacio) ?></strong> el <strong><?= $fecha ?></strong> de <strong><?= $inicio ?></strong> a <strong><?= $fin ?></strong>?</p>

  <form method="POST">
    <div class="botones_elimina">
      <button type="submit" class="confirmar_eliminacion">Confirmar Eliminación</button>
      <a href="listar.php" class="cancelar_eliminacion">Cancelar</a>
    </div>
  </form>
</section>

</body>
</html>
