<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "ID de reserva no especificado.";
    exit;
}

$id = $_GET['id'];

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_reservada = mysqli_real_escape_string($conn, $_POST['fecha_reservada']);
    $hora_inicio = mysqli_real_escape_string($conn, $_POST['hora_inicio']);
    $hora_fin = mysqli_real_escape_string($conn, $_POST['hora_fin']);
    $fk_id_estado = mysqli_real_escape_string($conn, $_POST['fk_id_estado']);

    $sql = "UPDATE reservas SET 
        fecha_reservada='$fecha_reservada', 
        hora_inicio='$hora_inicio', 
        hora_fin='$hora_fin', 
        fk_id_estado='$fk_id_estado'
        WHERE pk_id_reserva='$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: listar.php?msg=editado");
        exit;
    } else {
        $error = "Error al actualizar: " . mysqli_error($conn);
    }
}

// Obtener datos actuales de la reserva
$sql = "SELECT * FROM reservas WHERE pk_id_reserva='$id'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Reserva no encontrada.";
    exit;
}
$reserva = mysqli_fetch_assoc($result);

// Obtener estados para el select
$estados = [];
$estados_sql = "SELECT pk_id_estado, estado FROM estados";
$estados_result = mysqli_query($conn, $estados_sql);
while ($row = mysqli_fetch_assoc($estados_result)) {
    $estados[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
<header class="main-header">
    <h1>✏️ Editar Reserva</h1>
    <nav>
        <ul>
            <li><a href="listar.php">← Volver al Listado</a></li>
        </ul>
    </nav>
</header>
<section class="form-contenedor">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <label>Fecha Reservada:</label>
        <input type="date" name="fecha_reservada" value="<?php echo htmlspecialchars($reserva['fecha_reservada']); ?>" required>
        <label>Hora Inicio:</label>
        <input type="time" name="hora_inicio" value="<?php echo htmlspecialchars($reserva['hora_inicio']); ?>" required>
        <label>Hora Fin:</label>
        <input type="time" name="hora_fin" value="<?php echo htmlspecialchars($reserva['hora_fin']); ?>" required>
        <label>Estado:</label>
        <select name="fk_id_estado" required>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado['pk_id_estado']; ?>" <?php if ($estado['pk_id_estado'] == $reserva['fk_id_estado']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($estado['estado']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn editar">Guardar Cambios</button>
    </form>
</section>
</body>
</html>