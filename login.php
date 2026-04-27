<?php
session_start();
include("conection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cuit = $_POST["cuit"];
    $rol = $_POST["rol"];

    $sql = "SELECT * FROM Usuarios WHERE pk_cuit_usuario = ? AND fk_id_rol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cuit, $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $_SESSION["usuario"] = $cuit;
        $_SESSION["rol"] = $rol;

        header("Location: " . ($rol == 1 ? "administrador/panel_admin.php" : "pagina_principal.php"));
        exit;
    } else {
        $error = "CUIT o rol incorrecto.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
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
            color: #007BFF;
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
            background: #007BFF;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #0056b3;
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
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="cuit">CUIT</label>
                <input type="text" name="cuit" id="cuit" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select name="rol" id="rol" required>
                    <option value="">Seleccioná tu rol</option>
                    <option value="1">Administrador</option>
                    <option value="2">Profesor</option>
                    <option value="3">Preceptor</option>
                    <option value="4">Personal Administrativo</option>
                </select>
            </div>
            <button class="btn" type="submit">Accederr</button>
        </form>
            <button class="btn-volver" onclick="window.location.href='index.php'">Volver</button>
            <center><a href="registro.php" style="text-decoration: none; color: #007BFF; margin-top: 20px;">¿No tienes una cuenta? Regístrate!!</a></center>
    </div>
</body>
</html>
