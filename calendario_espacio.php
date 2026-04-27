<?php
session_start();
include("conection.php");

// 🔄 Marcar reservas pasadas como completadas
$hoy = date("Y-m-d");

$sql_update = "UPDATE Reservas 
               SET fk_id_estado = 3 
               WHERE fecha_reservada < ? AND fk_id_estado = 1";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("s", $hoy);
$stmt->execute();
$stmt->close();


if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$espacio_id = isset($_GET["espacio_id"]) ? intval($_GET["espacio_id"]) : 0;
if ($espacio_id <= 0) {
    die("Espacio no válido.");
}

$mes = isset($_GET["mes"]) ? intval($_GET["mes"]) : date("m");
$anio = isset($_GET["anio"]) ? intval($_GET["anio"]) : date("Y");
if ($mes < 1 || $mes > 12) $mes = date("m");
if ($anio < 2000 || $anio > 2100) $anio = date("Y");

$sql_espacio = "SELECT nombre FROM Espacios WHERE pk_id_espacio = ?";
$stmt = $conn->prepare($sql_espacio);
$stmt->bind_param("i", $espacio_id);
$stmt->execute();
$stmt->bind_result($nombre_espacio);
$stmt->fetch();
$stmt->close();

$fecha_inicio = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
$fecha_fin = date("Y-m-t", strtotime($fecha_inicio));

$sql_reservas = "SELECT fecha_reservada, hora_inicio, hora_fin 
                 FROM Reservas 
                 WHERE fk_id_espacio = ? AND fk_id_estado = 1 
                 AND fecha_reservada BETWEEN ? AND ? 
                 ORDER BY fecha_reservada, hora_inicio";
$stmt = $conn->prepare($sql_reservas);
$stmt->bind_param("iss", $espacio_id, $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();

$reservas_por_dia = [];
while ($row = $result->fetch_assoc()) {
    $fecha = $row["fecha_reservada"];
    $hora_inicio = $row["hora_inicio"];
    $hora_fin = $row["hora_fin"];
    $reservas_por_dia[$fecha][] = [
        "inicio" => $hora_inicio,
        "fin" => $hora_fin
    ];
}

$mes_anterior = $mes - 1;
$anio_anterior = $anio;
if ($mes_anterior < 1) {
    $mes_anterior = 12;
    $anio_anterior--;
}
$mes_siguiente = $mes + 1;
$anio_siguiente = $anio;
if ($mes_siguiente > 12) {
    $mes_siguiente = 1;
    $anio_siguiente++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calendario de <?= htmlspecialchars($nombre_espacio) ?></title>
  <style>
    body {
  /* 🔷 Estructura general */
body {
  font-family: Arial, sans-serif;
  background: #f4f4f4;
  margin: 0;
  padding: 0;
}

.header {
  background: #004080;
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header h1 {
  margin: 0;
}

.volver {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

/* 🔷 Contenido principal */
.main-content {
  padding: 20px;
}

/* 🔷 Calendario visual */
.calendario {
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
  max-width: 700px;
  margin: 0 auto;
}

.calendario h2 {
  text-align: center;
  font-size: 1.2em;
  margin-bottom: 10px;
}

.grilla {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  margin-top: 10px;
}

.encabezado-dia {
  font-weight: bold;
  text-align: center;
  background: #004080;
  color: white;
  padding: 5px;
  border-radius: 4px;
  font-size: 0.9em;
}

/* 🔷 Celdas del calendario */
.dia {
  background: #e6f0ff;
  padding: 6px;
  border-radius: 5px;
  min-height: 60px;
  cursor: pointer;
  transition: background 0.3s;
  position: relative;
  font-size: 0.85em;
}

.dia:hover {
  background: #d0e4ff;
}

.dia.vacío {
  background: transparent;
  cursor: default;
}

.dia strong {
  display: block;
  font-size: 1em;
  margin-bottom: 4px;
}

.reserva {
  background: #cce5ff;
  margin-top: 3px;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.75em;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

/* 🔷 Navegación entre meses */
.navegacion {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.navegacion a {
  background: #004080;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: bold;
  font-size: 0.9em;
  transition: background 0.3s;
}

.navegacion a:hover {
  background: #003060;
}

/* 🔷 Modal de reservas */
.modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-contenido {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
  position: relative;
}

.modal-contenido h2 {
  margin-top: 0;
  font-size: 1.1em;
}

.cerrar {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  font-weight: bold;
  color: #004080;
  cursor: pointer;
}

#contenidoModal table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

#contenidoModal th, #contenidoModal td {
  padding: 8px;
  text-align: left;
  border-bottom: 1px solid #ccc;
}

#contenidoModal th {
  background-color: #004080;
  color: white;
}

/* Colores condicionales por cantidad de reservas */
.dia.amarillo {
  background-color: #fff3b0; /* amarillo suave */
}

.dia.rojo {
  background-color: #f8d7da; /* rojo claro */
}

  </style>
</head>
<body>
  <header class="header" style="background-color: #2c3e50; color: white;">
    <h1><img src="img/calendario.png" width="30px"> Calendario de reservas: <?= htmlspecialchars($nombre_espacio) ?></h1>
    <a href="pagina_principal.php" class="volver">← Volver al menú</a>
  </header>

  <main class="main-content">
    <div class="calendario">
      <div class="navegacion">
        <a href="?espacio_id=<?= $espacio_id ?>&mes=<?= $mes_anterior ?>&anio=<?= $anio_anterior ?>">← Mes anterior</a>
        <a href="?espacio_id=<?= $espacio_id ?>&mes=<?= $mes_siguiente ?>&anio=<?= $anio_siguiente ?>">Mes siguiente →</a>
      </div>

      <?php
      setlocale(LC_TIME, 'es_ES.UTF-8');
      $nombre_mes = ucfirst(strftime("%B", strtotime($fecha_inicio)));
      echo "<h2>$nombre_mes $anio</h2>";
      echo "<div class='grilla'>";

      $dias_semana = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
      foreach ($dias_semana as $dia) {
          echo "<div class='encabezado-dia'>$dia</div>";
      }

      $dia_semana_inicio = date("N", strtotime($fecha_inicio));
      $dias_mes = date("t", strtotime($fecha_inicio));

      for ($i = 1; $i < $dia_semana_inicio; $i++) {
          echo "<div class='dia vacío'></div>";
      }

      for ($dia = 1; $dia <= $dias_mes; $dia++) {
          $fecha_actual = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-" . str_pad($dia, 2, "0", STR_PAD_LEFT);
          $reservas_count = isset($reservas_por_dia[$fecha_actual]) ? count($reservas_por_dia[$fecha_actual]) : 0;

$clase_color = "dia";
if ($reservas_count >= 1 && $reservas_count <= 3) {
    $clase_color .= " amarillo";
} elseif ($reservas_count > 3) {
    $clase_color .= " rojo";
}

echo "<div class='$clase_color' data-fecha='$fecha_actual' onclick='mostrarReservasDia(this)'><strong>$dia</strong>";

          if (isset($reservas_por_dia[$fecha_actual])) {
              foreach ($reservas_por_dia[$fecha_actual] as $reserva) {
                  echo "<div class='reserva'>{$reserva['inicio']} - {$reserva['fin']}</div>";
              }
          }

          echo "</div>";
      }

      echo "</div>";
      ?>
    </div>
  </main>

  <!-- 🔷 Modal -->
  <div id="modalReservas" class="modal">
    <div class="modal-contenido">
      <span class="cerrar" onclick="cerrarModal()">&times;</span>
      <h2><center>Reservas del día <span id="fechaModal"></center></span></h2>
      <div id="contenidoModal">Cargando...</div>
    </div>
  </div>

  <!-- 🔷 Scripts -->
  <script>
    function mostrarReservasDia(elemento) {
      const fecha = elemento.getAttribute("data-fecha");
      document.getElementById("fechaModal").textContent = fecha;
      document.getElementById("contenidoModal").innerHTML = "Cargando...";

      fetch("reservas_dia.php?fecha=" + fecha + "&espacio_id=<?= $espacio_id ?>")
        .then(response => response.text())
        .then(data => {
          document.getElementById("contenidoModal").innerHTML = data;
          document.getElementById("modalReservas").style.display = "flex";
        });
    }

    function cerrarModal() {
      document.getElementById("modalReservas").style.display = "none";
    }
  </script>
</body>
</html>
