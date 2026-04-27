<?php
include '../../conection.php';

$error = '';
$exito = '';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre       = trim($_POST['nombre']);
  $capacidad    = $_POST['capacidad'];
  $tipo_espacio = $_POST['espacio'];

  // Validación
  if (empty($nombre)) {
    $error = "El nombre es obligatorio.";
  } elseif (!is_numeric($capacidad) || $capacidad <= 0) {
    $error = "La capacidad debe ser un número positivo.";
  } elseif (empty($tipo_espacio)) {
    $error = "El tipo de espacio es obligatorio.";
  } else {
    // Verificar si el nombre del espacio ya existe
    $verificar = $conn->prepare("SELECT pk_id_espacio FROM espacios WHERE nombre = ?");
    $verificar->bind_param("s", $nombre);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
      $error = "Ya existe un espacio con ese nombre.";
    } else {
      // Insertar espacio
      $stmt = $conn->prepare("INSERT INTO espacios (nombre, capacidad, fk_id_tipo_espacio) VALUES (?, ?, ?)");
      $stmt->bind_param("sii", $nombre, $capacidad, $tipo_espacio);
      if ($stmt->execute()) {
        $exito = "Espacio creado correctamente.";
        header("Location: listar.php?msg=creado");
      } else {
        $error = "Error al insertar: " . $stmt->error;
      }
      $stmt->close();
    }
    $verificar->close();
  }
}

// Cargar tipos de espacio dinámicamente
$espacios = [];
$consulta = $conn->query("SELECT pk_id_tipo_espacio, tipos_espacioscol FROM tipos_espacios ORDER BY tipos_espacioscol ASC");
if ($consulta) {
  while ($row = $consulta->fetch_assoc()) {
    $espacios[] = $row;
  }
} else {
  echo "<p class='error'>Error al cargar tipos de espacio: " . $conn->error . "</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Espacio</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/mas.png" width="25px"> Agregar Espacio</h1>
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

    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Capacidad</label>
    <input type="text" name="capacidad" required>

    <label>Tipo de espacio</label>
    <select name="espacio" required>
      <option value="">-- Tipo de Espacio --</option>
      <?php foreach ($espacios as $espacio): ?>
        <option value="<?= $espacio['pk_id_tipo_espacio'] ?>">
          <?= htmlspecialchars($espacio['tipos_espacioscol']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="btn editar">Agregar</button>
  </form>
</section>

</body>
</html>
