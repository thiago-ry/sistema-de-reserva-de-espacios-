<?php
session_start();
include("conection.php");

// Validación de sesión y rol permitido
$roles_validos = [2, 3, 4];
if (!isset($_SESSION["usuario"]) || !in_array($_SESSION["rol"], $roles_validos)) {
    header("Location: login.php");
    exit;
}

$cuit = $_SESSION["usuario"];
$rol = $_SESSION["rol"];

// Nombre del rol
switch ($rol) {
    case 2: $nombre_rol = "Profesor"; break;
    case 3: $nombre_rol = "Preceptor"; break;
    case 4: $nombre_rol = "Administrativo"; break;
    default: $nombre_rol = "Usuario"; break;
}

// Obtener datos del usuario
$sql = "SELECT nombre, apellido, fk_id_rol FROM Usuarios WHERE pk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cuit);
$stmt->execute();
$stmt->bind_result($nombre_usuario, $apellido_usuario, $rol_usuario);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú Principal</title>
  <link rel="stylesheet" href="css/styles.css">

  <style>
@keyframes slideFadeIn {
  0% { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: translateY(0); }
}

.bienvenida h1 {
  font-size: 2em;
  margin-bottom: 10px;
  animation: slideFadeIn 0.8s ease-out forwards;
}

.bienvenida p {
  font-size: 1.1em;
  margin-bottom: 30px;
  animation: slideFadeIn 1s ease-out forwards;
  animation-delay: 0.3s;
}

.card {
  opacity: 0;
  transform: scale(0.95);
  animation: fadeInCard 0.6s ease-out forwards;
}

.card:nth-child(1) { animation-delay: 0.2s; }
.card:nth-child(2) { animation-delay: 0.4s; }
.card:nth-child(3) { animation-delay: 0.6s; }

@keyframes fadeInCard {
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>

</head>
<body>
  <!-- 🔷 Header institucional -->
  <header class="header" style="background-color: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div class="logo" style="display: flex; align-items: center;"><img src="img/image.png" alt="Logo" width="70px"><p>Sistema de Reservas</p></div>
    <nav class="nav">
      <a href="mis_reservas.php" style="display: flex; align-items: center; flex-direction: column; text-decoration: none;"><img src="img/calendar.png" width="30px" alt="Mis reservas"><p style="color: white;">Mis reservas</p></a>
      <a href="#" onclick="abrirModal()" style="display: flex; align-items: center; flex-direction: column; text-decoration: none;"><img src="img/actualizar.png" alt="Modificar perfil" width="30px"> <p style="color: white;">Modificar perfil</p></a>
      <a href="logout.php" style="display: flex; align-items: center; flex-direction: column; text-decoration: none;"><img src="img/logout.png" width="30px" alt="Cerrar sesión"><p style="color: white;">Cerrar sesión</p></a>
    </nav>
  </header>

  <!-- 🔷 Contenido principal -->
  <main class="main-content">
<section class="bienvenida">
  <div class="bienvenida-box">
    <h1>Bienvenido, <?= htmlspecialchars(ucfirst($nombre_usuario) . " " . ucfirst($apellido_usuario)) ?></h1>
    <p>Seleccioná un espacio para ver su calendario y realizar reservas.</p>
  </div>
</section>




    <section class="acciones">
      <h2>Espacios disponibles</h2>
      <div class="card-container">
        <?php
        $espacios = $conn->query("SELECT pk_id_espacio, nombre FROM Espacios ORDER BY nombre");
        while ($row = $espacios->fetch_assoc()) {
          $id_espacio = $row["pk_id_espacio"];
          $nombre_espacio = htmlspecialchars($row["nombre"]);
          echo "<a href='calendario_espacio.php?espacio_id={$id_espacio}' class='card'>";
          echo "<h3>🏢 {$nombre_espacio}</h3>";
          echo "<p>Ver disponibilidad y reservar.</p>";
          echo "</a>";
        }
        ?>
      </div>
    </section>
  </main>

  <!-- 🔷 Modal para editar perfil -->
  <div id="modalPerfil" class="modal">
    <div class="modal-contenido">
      <span class="cerrar" onclick="cerrarModal()">&times;</span>
      <h2>Modificar perfil</h2>
      <form method="POST" action="actualizar_perfil.php">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre_usuario) ?>" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" value="<?= htmlspecialchars($apellido_usuario) ?>" required>

        <label for="rol">Rol:</label>
        <select name="rol" required>
          <option value="2" <?= $rol_usuario == 2 ? 'selected' : '' ?>>Profesor</option>
          <option value="3" <?= $rol_usuario == 3 ? 'selected' : '' ?>>Preceptor</option>
          <option value="4" <?= $rol_usuario == 4 ? 'selected' : '' ?>>Administrativo</option>
        </select>

        <button type="submit">Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- 🔷 Scripts -->
  <script>
    function abrirModal() {
      document.getElementById("modalPerfil").style.display = "block";
    }
    function cerrarModal() {
      document.getElementById("modalPerfil").style.display = "none";
    }
  </script>

  <script>
  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.card').forEach(card => {
      card.style.animationPlayState = 'running';
    });
  });
</script>

</body>
</html>
