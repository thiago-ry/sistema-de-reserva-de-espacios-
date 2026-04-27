<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "CUIT de usuario no especificado.";
    exit;
}

$cuit = $_GET['id'];

// Verificar si el usuario existe
$sql = "SELECT nombre, apellido FROM usuarios WHERE pk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cuit);
$stmt->execute();
$stmt->bind_result($nombre, $apellido);
if (!$stmt->fetch()) {
    echo "Usuario no encontrado.";
    exit;
}
$stmt->close();

// Procesar eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM usuarios WHERE pk_cuit_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cuit);
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
  <title>Eliminar Usuario</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/tacho.png" alt=""> Eliminar Usuario</h1>
  <nav>
    <ul>
      <li><a href="listar.php">← Volver al Listado</a></li>
    </ul>
  </nav>
</header>

<section class="form-contenedor">
  <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

  <p>¿Estás seguro que querés eliminar al usuario <strong><?= htmlspecialchars($nombre . " " . $apellido) ?></strong> con CUIT <strong><?= $cuit ?></strong>?</p>

  <form method="POST">
    <div class="botones_elimina">
    <button type="submit" class="confirmar_eliminacion">Confirmar Eliminación</button>
    <a href="listar.php" class="cancelar_eliminacion">Cancelar</a>
    </div>
  </form>
</section>

</body>
</html>
