<?php include '../../conection.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Reservas</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/calendario.png" width="25px"> Listado de Reservas</h1>
  <nav>
    <ul>
      <li><a href="agregar.php" class="crear-salir" ><img src="../../img/mas.png" alt=""> Nueva Reserva</a></li>
      <li><a href="../panel_admin.php" class="crear-salir" > <img src="../../img/casa.png" alt=""> Volver al Panel</a></li>
    </ul>
  </nav>
</header>

<section class="tabla-contenedor">
  <table>
    <tr>
      <th>ID</th>
      <th>Fecha Reservada</th>
      <th>Hora Inicio</th>
      <th>Hora Fin</th>
      <th>Usuario</th>
      <th>Espacio</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
    <?php
    $sql = "
      SELECT 
        r.pk_id_reserva,
        r.fecha_reservada,
        r.hora_inicio,
        r.hora_fin,
        u.nombre AS nombre_usuario,
        e.nombre AS nombre_espacio,
        es.estado AS nombre_estado
      FROM reservas r
      INNER JOIN usuarios u ON r.fk_cuit_usuario = u.pk_cuit_usuario
      INNER JOIN espacios e ON r.fk_id_espacio = e.pk_id_espacio
      INNER JOIN estados es ON r.fk_id_estado = es.pk_id_estado
      ORDER BY r.fecha_reservada DESC
    ";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
      echo "<tr><td colspan='8'>Error en la consulta: " . mysqli_error($conn) . "</td></tr>";
    } else {
      while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
          <td>{$row['pk_id_reserva']}</td>
          <td>{$row['fecha_reservada']}</td>
          <td>{$row['hora_inicio']}</td>
          <td>{$row['hora_fin']}</td>
          <td>{$row['nombre_usuario']}</td>
          <td>{$row['nombre_espacio']}</td>
          <td>{$row['nombre_estado']}</td>
          <td>
            <div class='acciones'>
              <a href='editar.php?id={$row['pk_id_reserva']}' class='btn editar'><img src='../../img/pencil.png' width='16'> Editar</a>
              <a href='eliminar.php?id={$row['pk_id_reserva']}' class='btn eliminar'><img src='../../img/tacho.png' width='16'>  Eliminar</a>
            </div>
          </td>
        </tr>";
      }
    }
    ?>
  </table>
</section>

</body>
</html>
