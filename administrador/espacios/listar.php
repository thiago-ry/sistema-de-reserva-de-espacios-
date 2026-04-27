<?php include '../../conection.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Espacios</title>
  <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
  <h1><img src="../../img/gestion.png" width="35px"> Gestión de Espacios</h1>
  <nav>
    <ul>
      <li><a href="agregar.php" class="crear-salir"><img src="../../img/mas.png"> Nuevo Espacio</a></li>
      <li><a href="../panel_admin.php" class="crear-salir" ><img src="../../img/casa.png" alt=""> Volver al Panel</a></li>
    </ul>
  </nav>
</header>

<section class="tabla-contenedor">
  <table>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Capacidad</th>
      <th>Tipo</th>
      <th>Acciones</th>
    </tr>
    <?php
    $result = mysqli_query($conn, "SELECT 
  e.pk_id_espacio,
  e.nombre,
  e.capacidad,
  t.tipos_espacioscol AS nombre_tipo
FROM espacios e
INNER JOIN tipos_espacios t
  ON e.fk_id_tipo_espacio = t.pk_id_tipo_espacio;
");
    if (!$result) {
      echo "<tr><td colspan='5'>Error en la consulta: " . mysqli_error($conn) . "</td></tr>";
    } else {
while ($row = mysqli_fetch_assoc($result)) {
  echo "<tr>
    <td>{$row['pk_id_espacio']}</td>
    <td>{$row['nombre']}</td>
    <td>{$row['capacidad']}</td>
    <td>{$row['nombre_tipo']}</td>
    <td>
      <div class='acciones'>
        <a href='editar.php?id={$row['pk_id_espacio']}' class='btn editar'> <img src='../../img/pencil.png' width='16'> Editar</a>
        <a href='eliminar.php?id={$row['pk_id_espacio']}' class='btn eliminar'><img src='../../img/tacho.png' width='16'>  Eliminar</a>
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
