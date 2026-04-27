
<?php
session_start();
include("conection.php");

// Espacio seleccionado
$espacio_id = isset($_GET["espacio_id"]) ? intval($_GET["espacio_id"]) : 0;

// Mes y año actual o por GET
$mes = isset($_GET["mes"]) ? intval($_GET["mes"]) : date("m");
$anio = isset($_GET["anio"]) ? intval($_GET["anio"]) : date("Y");
$fecha_inicio = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
$fecha_fin = date("Y-m-t", strtotime($fecha_inicio));
if ($mes < 1 || $mes > 12) $mes = date("m");
if ($anio < 2000 || $anio > 2100) $anio = date("Y");

// Obtener espacios
$espacios = $conn->query("SELECT pk_id_espacio, nombre FROM Espacios ORDER BY nombre");

// Obtener los espacios más reservados del mes actual
$sql_top_espacios = "
  SELECT e.pk_id_espacio, e.nombre, COUNT(r.pk_id_reserva) AS total_reservas
  FROM Reservas r
  JOIN Espacios e ON r.fk_id_espacio = e.pk_id_espacio
  WHERE r.fk_id_estado = 1 AND r.fecha_reservada BETWEEN ? AND ?
  GROUP BY e.pk_id_espacio, e.nombre
  ORDER BY total_reservas DESC
  LIMIT 5
";
$stmt = $conn->prepare($sql_top_espacios);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$result_top = $stmt->get_result();

$espacios_top = [];
$labels = [];
$data = [];

while ($row = $result_top->fetch_assoc()) {
    $espacios_top[] = $row;
    $labels[] = $row["nombre"];
    $data[] = $row["total_reservas"];
}
$stmt->close();


// Inicializar variables
$nombre_espacio = "";
$capacidad = "";
$total_reservas = 0;
$dias_con_reserva = 0;
$reservas_por_dia = [];

if ($espacio_id > 0) {
    $sql_espacio = "SELECT nombre, capacidad FROM Espacios WHERE pk_id_espacio = ?";
    $stmt = $conn->prepare($sql_espacio);
    $stmt->bind_param("i", $espacio_id);
    $stmt->execute();
    $stmt->bind_result($nombre_espacio, $capacidad);
    $stmt->fetch();
    $stmt->close();

    $fecha_inicio = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
    $fecha_fin = date("Y-m-t", strtotime($fecha_inicio));

    


    $sql_reservas = "SELECT fecha_reservada FROM Reservas 
                     WHERE fk_id_espacio = ? AND fk_id_estado = 1 
                     AND fecha_reservada BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql_reservas);
    $stmt->bind_param("iss", $espacio_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $fecha = $row["fecha_reservada"];
        if (!isset($reservas_por_dia[$fecha])) {
            $reservas_por_dia[$fecha] = 0;
        }
        $reservas_por_dia[$fecha]++;
    }
    $stmt->close();

    $total_reservas = array_sum($reservas_por_dia);
    $dias_con_reserva = count($reservas_por_dia);
}

// Navegación de meses
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
  <title>Calendario Institucional</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
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
  display: flex;
  align-items: center;
  gap: 10px;
}

.login-btn {
  background: #3498db;
  color: white;
  padding: 8px 16px;
  border-radius: 5px;
  text-decoration: none;
}

.main-content {
  padding: 20px;
}

.card-container h2 {
  text-align: center;
  margin-bottom: 15px;
}

.card-container .espacio-card {
  background-color: #ecf0f1;
  padding: 15px;
  border-radius: 8px;
  width: 200px;
  text-align: center;
  text-decoration: none;
  color: #2c3e50;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: background 0.3s;
}

.card-container .espacio-card:hover {
  background-color: #d0e4ff;
}

.calendario {
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
  max-width: 700px;
  margin: 0 auto;
}

.navegacion {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.navegacion a {
  background: #29435c;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: bold;
  font-size: 0.9em;
}

.grilla {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
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

.dia {
  background: #e6f0ff;
  padding: 6px;
  border-radius: 5px;
  min-height: 60px;
  font-size: 0.85em;
  text-align: center;
}

.dia.vacío {
  background: transparent;
}

.dia.amarillo {
  background-color: #fff3b0;
}

.dia.rojo {
  background-color: #f8d7da;
}

.leyenda {
  text-align: center;
  margin-top: 10px;
}

.leyenda span {
  margin: 0 10px;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 0.85em;
}

.noticias {
  background: #ffffff;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
  max-width: 700px;
  margin: 30px auto;
}

footer {
  background-color: #2c3e50;
  color: white;
  padding: 15px 20px;
  text-align: center;
  font-size: 0.9em;
  margin-top: 40px;
}

  </style>
</head>
<body>
  <header class="header" style="background:#2c3e50;">
    <h1><img src="img/image.png" width="50px"> Calendario Institucional</h1>
    <?php if (isset($_SESSION["usuario"])): ?>
      <a href="pagina_principal.php" class="login-btn">Ir a mi panel</a>
    <?php else: ?>
      <a href="login.php" class="login-btn"><img src="img/candado.png" width="20px"> Iniciar sesión</a>
    <?php endif; ?>
  </header>

  <main class="main-content">
    <div class="card-container">
      <h2>Seleccioná un espacio para ver su calendario</h2>
      <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
        <?php while ($row = $espacios->fetch_assoc()): ?>
          <a href="?espacio_id=<?= $row["pk_id_espacio"] ?>&mes=<?= $mes ?>&anio=<?= $anio ?>" class="espacio-card">
            <h3> <?= htmlspecialchars($row["nombre"]) ?></h3>
            <p>Ver calendario</p>
          </a>
        <?php endwhile; ?>
      </div>
    </div>


    <?php if ($espacio_id > 0): ?>
      <div class="calendario" style="margin-top:30px;">
        <div class="navegacion">
          <a href="?espacio_id=<?= $espacio_id ?>&mes=<?= $mes_anterior ?>&anio=<?= $anio_anterior ?>">← Mes anterior</a>
          <a href="?espacio_id=<?= $espacio_id ?>&mes=<?= $mes_siguiente ?>&anio=<?= $anio_siguiente ?>">Mes siguiente →</a>
        </div>

        <h2 style="text-align:center"><?= ucfirst(strftime("%B", strtotime("$anio-$mes-01"))) ?> <?= $anio ?></h2>

        <p><strong>Espacio:</strong> <?= $nombre_espacio ?></p>
        <p><strong>Capacidad:</strong> <?= $capacidad ?></p>
        <p><strong>Total de reservas:</strong> <?= $total_reservas ?></p>
        <p><strong>Días con reservas:</strong> <?= $dias_con_reserva ?></p>
      
        <div class="leyenda" style="margin-top:15px; margin-bottom:30px;">
          <span style="background:#fff3b0;">1–3 reservas</span>
          <span style="background:#f8d7da;">Más de 3 reservas</span>
        </div>

        <div class="grilla">
          <?php
          $dias_semana = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
          foreach ($dias_semana as $dia) echo "<div class='encabezado-dia'>$dia</div>";

          $dia_semana_inicio = date("N", strtotime("$anio-$mes-01"));
          $dias_mes = date("t", strtotime("$anio-$mes-01"));

          for ($i = 1; $i < $dia_semana_inicio; $i++) echo "<div class='dia vacío'></div>";

          for ($dia = 1; $dia <= $dias_mes; $dia++) {
              $fecha_actual = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-" . str_pad($dia, 2, "0", STR_PAD_LEFT);
              $cantidad = $reservas_por_dia[$fecha_actual] ?? 0;
              $clase = "dia";
              if ($cantidad >= 1 && $cantidad <= 3) $clase .= " amarillo";
              if ($cantidad > 3) $clase .= " rojo";
              echo "<div class='$clase'><strong>$dia</strong><br>$cantidad reservas</div>";
          }
          ?>
        </div>
      </div>

    <?php endif; ?>
        <div class="noticias">
  <h2 style="text-align:center;">Espacios más reservados en <?= ucfirst(strftime("%B", strtotime($fecha_inicio))) ?> <?= $anio ?></h2>
  <ul>
    <?php foreach ($espacios_top as $espacio): ?>
      <li><strong><?= htmlspecialchars($espacio["nombre"]) ?></strong>: <?= $espacio["total_reservas"] ?> reservas</li>
    <?php endforeach; ?>
  </ul>

  <canvas id="graficoTopEspacios" width="600" height="300" style="margin-top:30px;"></canvas>
</div>
  </main>

  <footer>
    <p>Sistema de Reservas Institucional</p>
    <p>&copy; <?= date("Y") ?>
     </footer>

  <script>
  const ctxTop = document.getElementById('graficoTopEspacios').getContext('2d');
  new Chart(ctxTop, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Reservas por espacio',
        data: <?= json_encode($data) ?>,
        backgroundColor: '#2c3e50'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
</script>

</body>
</html>