<?php
include '../../conection.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $cuit     = $_POST['cuit'];
  $nombre   = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $rol      = $_POST['rol'];

  // Validación básica
  if (!is_numeric($cuit) || strlen($cuit) < 8) {
    $error = "CUIT inválido.";
  } else {
    // Verificar si el CUIT ya existe
    $verificar = $conn->prepare("SELECT pk_cuit_usuario FROM usuarios WHERE pk_cuit_usuario = ? ");
    $verificar->bind_param("s", $cuit);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
      $error = "El CUIT ya está registrado.";
    } else {
      // Insertar usuario
      $stmt = $conn->prepare("INSERT INTO usuarios (pk_cuit_usuario, nombre, apellido, fk_id_rol) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("sssi", $cuit, $nombre, $apellido, $rol);
      if ($stmt->execute()) {
        $exito = "Usuario creado correctamente.";
        header("Location: listar.php?msg=creado");
      } else {
        $error = "Error al insertar: " . $stmt->error;
      }
      $stmt->close();
    }
    $verificar->close();
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Usuario</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/mas.png" width="25px"> Agregar Usuario</h1>
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
    <label>CUIT</label>
    <input type="number" name="cuit" min="20000000000" max="29999999999" required>

    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Apellido</label>
    <input type="text" name="apellido" required>

    <label>Rol</label>
    <select name="rol" required>
      <option value="1">Administrador</option>
      <option value="2">Profesor</option>
      <option value="3">Preceptor</option>
      <option value="4">Administrativo</option>
    </select>

    <button type="submit" class="btn editar">Agregar</button>
  </form>
</section>

</body>
</html>
