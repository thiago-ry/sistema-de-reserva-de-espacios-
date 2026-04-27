<?php
session_start();
include("conection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cuit = $_POST["cuit"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $rol = $_POST["rol"];

    $sql = "INSERT INTO Usuarios (pk_cuit_usuario, nombre, apellido, fk_id_rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $cuit, $nombre, $apellido, $rol);

    if ($stmt->execute()) {
        // Guardar sesión automáticamente
        $_SESSION["usuario"] = $cuit;
        $_SESSION["rol"] = $rol;

        // Redirigir a la página principal
        header("Location: pagina_principal.php");
        exit;
    } else {
        $error = "Error al registrar. Verificá los datos.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .registro-container {
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #28a745;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      font-weight: 600;
      display: block;
      margin-bottom: 8px;
    }
    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }
    .btn {
      width: 100%;
      background: #28a745;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .btn:hover {
      background: #218838;
    }
           .btn-volver {
            width: 100%;
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }
        .btn-volver:hover {
            background: #5a6268;
        }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="registro-container">
    <h2>Registro de Usuario</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
      <div class="form-group">
        <label for="cuit">CUIT</label>
        <input type="text" name="cuit" id="cuit" required>
      </div>
      <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" required>
      </div>
      <div class="form-group">
        <label for="apellido">Apellido</label>
        <input type="text" name="apellido" id="apellido" required>
      </div>
      <div class="form-group">
        <label for="rol">Rol</label>
        <select name="rol" id="rol" required>
          <option value="">Seleccioná tu rol</option>
          <option value="2">Profesor</option>
          <option value="3">Preceptor</option>
          <option value="4">Personal Administrativo</option>
        </select>
      </div>
      <button class="btn" type="submit">Registrarme</button>
    </form>
    <button class="btn-volver" onclick="window.location.href='index.php'">Volver</button>
  </div>
</body>
</html>
