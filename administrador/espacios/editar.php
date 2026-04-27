<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "ID de espacio no especificado.";
    exit;
}

$id = $_GET['id'];

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $capacidad = mysqli_real_escape_string($conn, $_POST['capacidad']);

    $sql = "UPDATE espacios SET nombre='$nombre', capacidad='$capacidad' WHERE pk_id_espacio='$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: listar.php?msg=editado");
        exit;
    } else {
        $error = "Error al actualizar: " . mysqli_error($conn);
    }
}

// Obtener datos actuales del espacio
$sql = "SELECT * FROM espacios WHERE pk_id_espacio='$id'";  
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Espacio no encontrado.";
    exit;
}
$espacio = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Espacio</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
<header class="main-header">
    <h1>✏️ Editar Espacio</h1>
    <nav>
        <ul>
            <li><a href="listar.php">← Volver al Listado</a></li>
        </ul>
    </nav>
</header>
<section class="form-contenedor">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" >
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($espacio['nombre']); ?>" required>
        <label>Capacidad:</label>
        <input type="text" name="capacidad" value="<?php echo htmlspecialchars($espacio['capacidad']); ?>" required>
        <button type="submit" class="btn editar">Guardar Cambios</button>
    </form>
</section>
</body>
</html>