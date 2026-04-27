<?php include '../../conection.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Usuarios</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>

<header class="main-header">
    <h1><img src="../../img/user.png" width="25px"> Gestión de Usuarios</h1>
    <nav>
        <ul>
            <li><a href="agregar.php" class="crear-salir"> <img src="../../img/mas.png" alt=""> Nuevo Usuario</a></li>
            <li><a href="../panel_admin.php" class="crear-salir" > <img src="../../img/casa.png" alt=""> Volver al Panel</a></li>
        </ul>
    </nav>
</header>

<section class="tabla-contenedor">
    <table>
        <tr>
            <th>CUIT</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT 
            u.pk_cuit_usuario,
            u.nombre,
            u.apellido,
            r.rol AS nombre_rol
        FROM usuarios u
        INNER JOIN roles r
            ON u.fk_id_rol = r.pk_id_rol
        ");
        if (!$result) {
            echo "<tr><td colspan='5'>Error en la consulta: " . mysqli_error($conn) . "</td></tr>";
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['pk_cuit_usuario']}</td>
                    <td>{$row['nombre']}</td>
                    <td>{$row['apellido']}</td>
                    <td>{$row['nombre_rol']}</td>
                    <td>
                        <div class='acciones'>
                            <a href='editar.php?id={$row['pk_cuit_usuario']}' class='btn editar'><img src='../../img/pencil.png' width='16'> Editar</a>
                            <a href='eliminar.php?id={$row['pk_cuit_usuario']}' class='btn eliminar'><img src='../../img/tacho.png' width='16'> Eliminar</a>
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