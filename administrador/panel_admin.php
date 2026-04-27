<?php include '../conection.php'; 

session_start();
$cuit = $_SESSION["usuario"];
$rol = $_SESSION["rol"];

// Nombre del rol
if ($rol == 1) {
    $nombre_rol = "Administrador";
} else {
    $nombre_rol = "Usuario";
}

// Obtener datos del usuario
$sql = "SELECT nombre, apellido FROM Usuarios WHERE pk_cuit_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cuit);
$stmt->execute();
$stmt->bind_result($nombre_usuario, $apellido_usuario);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="sidebar">
  <h2><img src="../img/image.png" width="100px"></h2>
  <ul>
    <li><a href="usuarios/listar.php" class="herramientas"><img src="../img/user.png" alt=""> Usuarios</a></li>
    <li><a href="espacios/listar.php" class="herramientas"><img src="../img/espacio.png" alt=""> Espacios</a></li>
    <li><a href="reservas/listar.php" class="herramientas"><img src="../img/calendario.png" alt=""> Reservas</a></li>
  </ul>
</div>

<div class="main">
<header class="main-header" style="display: flex; justify-content: space-between; align-items: center;">
  <h1>Bienvenido, <?= htmlspecialchars($nombre_usuario . " " . $apellido_usuario) ?> al panel de administrador</h1>
  <a href="../logout.php" class="logout-link">
    <img src="../img/logout.png" alt="Cerrar sesión" width="50px" title="Cerrar sesión">
  </a>
</header>



  <section class="dashboard">
    <div class="card">
      <h3>Usuarios registrados</h3>
      <p>
        <?php
        $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM usuarios");
        $data = mysqli_fetch_assoc($result);
        echo $data['total'];
        ?>
      </p>
    </div>
    <div class="card">
      <h3>Reservas activas</h3>
      <p>
        <?php
        $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservas WHERE fk_id_estado = 1");
        $data = mysqli_fetch_assoc($result);
        echo $data['total'];
        ?>
      </p>
    </div>
    <div class="card">
      <h3>Reservas hoy</h3>
      <p>
        <?php
        $hoy = date('Y-m-d');
        $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservas WHERE fecha_hoy = '$hoy'");
        $data = mysqli_fetch_assoc($result);
        echo $data['total'];
        ?>
      </p>
    </div>
  </section>


<?php
$sql = "SELECT fecha_reservada, COUNT(*) AS cantidad FROM reservas GROUP BY fecha_reservada ORDER BY fecha_reservada DESC LIMIT 7";
$result = mysqli_query($conn, $sql);

$fechas = [];
$valores = [];

while ($row = mysqli_fetch_assoc($result)) {
  $fechas[] = $row['fecha_reservada'];
  $valores[] = $row['cantidad'];
}

// Usuarios con más reservas
$sqlUsuarios = "
  SELECT u.nombre, COUNT(*) AS cantidad 
  FROM reservas r 
  INNER JOIN usuarios u ON r.fk_cuit_usuario = u.pk_cuit_usuario 
  GROUP BY u.nombre 
  ORDER BY cantidad DESC 
  LIMIT 5
";
$resultUsuarios = mysqli_query($conn, $sqlUsuarios);
$usuarios = [];
$reservasPorUsuario = [];
while ($row = mysqli_fetch_assoc($resultUsuarios)) {
  $usuarios[] = $row['nombre'];
  $reservasPorUsuario[] = $row['cantidad'];
}

// Espacios más reservados
$sqlEspacios = "
  SELECT e.nombre, COUNT(*) AS cantidad 
  FROM reservas r 
  INNER JOIN espacios e ON r.fk_id_espacio = e.pk_id_espacio 
  GROUP BY e.nombre 
  ORDER BY cantidad DESC 
  LIMIT 5
";
$resultEspacios = mysqli_query($conn, $sqlEspacios);
$nombresEspacios = [];
$reservasPorEspacio = [];
while ($row = mysqli_fetch_assoc($resultEspacios)) {
  $nombresEspacios[] = $row['nombre'];
  $reservasPorEspacio[] = $row['cantidad'];
}

?>
<h1 class="titulo_estadistica">Estadistísticas</h1>
<section class="grafico-box">
  <h2 style="text-align: center;">Reservas por Día</h2>
  <canvas id="graficoReservas" width="500" height="300"></canvas>
</section>

<section class="grafico-box">
  <center>
  <h2 style="text-align: center;">Usuarios con Más Reservas</h2>
  <canvas id="graficoUsuarios" width="500" height="300"></canvas>
  </center>
</section>

<section class="grafico-box">
  <center>
  <h2 style="text-align: center;">Espacios Más Reservados</h2>
  <canvas id="graficoEspacios" width="500" height="300"></canvas>
  </center>
</section>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('graficoReservas').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($fechas) ?>,
    datasets: [{
      label: 'Reservas por día',
      data: <?= json_encode($valores) ?>,
      backgroundColor: '#3498db'
    }]
  },
options: {
  responsive: false,
  maintainAspectRatio: false,
  scales: {
    y: { beginAtZero: true }
  }
}

});
</script>
<script>
const ctxUsuarios = document.getElementById('graficoUsuarios').getContext('2d');
new Chart(ctxUsuarios, {
  type: 'bar',
  data: {
    labels: <?= json_encode($usuarios) ?>,
    datasets: [{
      label: 'Reservas por Usuario',
      data: <?= json_encode($reservasPorUsuario) ?>,
      backgroundColor: '#2ecc71'
    }]
  },
  options: {
    responsive: false,
    maintainAspectRatio: false,
    scales: {
      y: { beginAtZero: true }
    }
  }
});

const ctxEspacios = document.getElementById('graficoEspacios').getContext('2d');
new Chart(ctxEspacios, {
  type: 'bar',
  data: {
    labels: <?= json_encode($nombresEspacios) ?>,
    datasets: [{
      label: 'Reservas por Espacio',
      data: <?= json_encode($reservasPorEspacio) ?>,
      backgroundColor: '#e67e22'
    }]
  },
  options: {
    responsive: false,
    maintainAspectRatio: false,
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>


</div>

</body>
</html>
