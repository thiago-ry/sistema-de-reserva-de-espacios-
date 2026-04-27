<?php
session_start();
include("conection.php");

// Validación de sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$cuit = $_SESSION["usuario"];

// Consulta de reservas del usuario
$sql = "SELECT r.pk_id_reserva, e.nombre AS espacio, r.fecha_reservada, r.hora_inicio, r.hora_fin
        FROM Reservas r
        JOIN Espacios e ON r.fk_id_espacio = e.pk_id_espacio
        WHERE r.fk_cuit_usuario = ? AND r.fk_id_estado = 1
        ORDER BY r.fecha_reservada DESC, r.hora_inicio ASC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cuit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Reservas</title>
<style>
  .reservas-container {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
  }

  .reserva-card {
    background-color: #f8f9fa;
    border-left: 5px solid #2980b9;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .reserva-card h3 {
    margin: 0 0 6px;
    font-size: 16px;
    color: #2c3e50;
  }

  .reserva-card p {
    margin: 4px 0;
    font-size: 14px;
    color: #34495e;
  }

  .acciones {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
  }

  .acciones form button,
  .acciones a {
    font-size: 13px;
    padding: 6px 10px;
    border-radius: 4px;
    text-decoration: none;
    border: none;
    cursor: pointer;
  }

  .acciones form button {
    background-color: #e74c3c;
    color: white;
  }

  .acciones a {
    background-color: #27ae60;
    color: white;
  }
</style>

</head>
<body>
<header class="header" style="background-color: #2c3e50; color: white; padding: 10px 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <h1><img src="img/calendario.png" alt="" width="30px"> Mis Reservas</h1>
    <a href="pagina_principal.php" style="color: white; text-decoration: underline;">← Volver al menú</a>
  </div>
</header>

<main class="reservas-container">
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="reserva-card">
        <h3> <?= htmlspecialchars($row["espacio"]) ?></h3>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($row["fecha_reservada"]) ?></p>
        <p><strong>Horario:</strong> <?= htmlspecialchars($row["hora_inicio"]) ?> a <?= htmlspecialchars($row["hora_fin"]) ?></p>
        <div class="acciones">
          <form method="POST" action="cancelar_reserva.php" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
            <input type="hidden" name="id_reserva" value="<?= $row["pk_id_reserva"] ?>">
            <button type="submit">Cancelar</button>
          </form>
          <a href="editar_reserva.php?id=<?= $row["pk_id_reserva"] ?>">Editar</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No tenés reservas registradas.</p>
  <?php endif; ?>
</main>
</body>
</html>
