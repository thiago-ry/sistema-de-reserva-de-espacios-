<?php
include '../../conection.php';

if (!isset($_GET['id'])) {
    echo "ID de usuario no especificado.";
    exit;
}

$id = $_GET['id'];

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conn, $_POST['apellido']);
    $rol = mysqli_real_escape_string($conn, $_POST['rol']);

    $sql = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', fk_id_rol='$rol' WHERE pk_cuit_usuario='$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: listar.php?msg=editado");
        exit;
    } else {
        $error = "Error al actualizar: " . mysqli_error($conn);
    }
}

// Obtener datos actuales del usuario
$sql = "SELECT * FROM usuarios WHERE pk_cuit_usuario='$id'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Usuario no encontrado.";
    exit;
}
$usuario = mysqli_fetch_assoc($result);

// Obtener lista de roles
$roles = mysqli_query($conn, "SELECT pk_id_rol, rol FROM roles");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
<header class="main-header">
    <h1><img src="../../img/pencil.png" alt=""> Editar Usuario</h1>
    <nav>
        <ul>
            <li><a href="listar.php">← Volver al Listado</a></li>
        </ul>
    </nav>
</header>

<section class="form-contenedor">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <label>Nombre:</label>
        <input type="text" name="nombre" class="editar-input" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

        <label>Apellido:</label>
        <input type="text" name="apellido" class="editar-input" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>

        <label>Rol:</label>
        <select name="rol" class="editar-input" required>
            <?php while ($r = mysqli_fetch_assoc($roles)) { ?>
                <option value="<?= $r['pk_id_rol'] ?>" <?= ($usuario['fk_id_rol'] == $r['pk_id_rol']) ? 'selected' : '' ?>>
                    <?= $r['rol'] ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" class="btn editar">Guardar Cambios</button>
    </form>
</section>
</body>
</html>
