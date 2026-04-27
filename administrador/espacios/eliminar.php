<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "ID de espacio no especificado.";
    exit;
}

$id_espacio = $_GET['id'];

// Verificar si el espacio existe
$sql = "SELECT nombre FROM espacios WHERE pk_id_espacio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_espacio);
$stmt->execute();
$stmt->bind_result($nombre);
if (!$stmt->fetch()) {
    echo "Espacio no encontrado.";
    exit;
}
$stmt->close();

// Verificar si hay reservas asociadas
$sql = "SELECT COUNT(*) FROM reservas WHERE fk_id_espacio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_espacio);
$stmt->execute();
$stmt->bind_result($total_reservas);
$stmt->fetch();
$stmt->close();

$bloqueado = $total_reservas > 0;

// Procesar eliminación si se confirma y no hay reservas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {
    $sql = "DELETE FROM espacios WHERE pk_id_espacio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_espacio);
    if ($stmt->execute()) {
        header("Location: listar.php?msg=espacio_eliminado");
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
  <title>Eliminar Espacio</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/tacho.png" alt=""> Eliminar Espacio</h1>
  <nav>
    <ul>
      <li><a href="listar.php">← Volver al Listado</a></li>
    </ul>
  </nav>
</header>

<section class="form-contenedor">
  <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

  <?php if ($bloqueado): ?>
    <p class="error">
      No se puede eliminar el espacio <strong><?= htmlspecialchars($nombre) ?></strong> porque tiene reserva(s) asociada(s).
    </p>
    <a href="listar.php" class="cancelar_eliminacion">Volver</a>
  <?php else: ?>
    <p>¿Estás seguro que querés eliminar el espacio <strong><?= htmlspecialchars($nombre) ?></strong>?</p>
    <form method="POST">
      <div class="botones_elimina">
        <button type="submit" class="confirmar_eliminacion">Confirmar Eliminación</button>
        <a href="listar.php" class="cancelar_eliminacion">Cancelar</a>
      </div>
    </form>
  <?php endif; ?>
</section>

</body>
</html>
