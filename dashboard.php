<?php
session_start();

// Conexión a la base de datos
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "ejemplo";
$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if(!$enlace){
    die("❌ Error de conexión: " . mysqli_connect_error());
}

// Simulación de login (en tu proyecto esto vendrá desde el login.php)
if(!isset($_SESSION['nombre']) || !isset($_SESSION['rol'])){
    // Para pruebas puedes asignar manualmente
    $_SESSION['nombre'] = "usuario_demo";
    $_SESSION['rol'] = "datos"; // Cambia entre datos, tecnico, administrador
}

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - <?php echo ucfirst($rol); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; padding: 0; }
        header { background: #333; color: #fff; padding: 15px; text-align: center; }
        nav { background: #444; padding: 10px; }
        nav a { color: #fff; margin-right: 15px; text-decoration: none; }
        nav a:hover { text-decoration: underline; }
        .contenido { padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #eee; }
        form { margin-top: 10px; }
        input, select, textarea { padding: 5px; margin: 3px 0; width: 100%; max-width: 400px; }
        input[type="submit"] { background: #28a745; color: #fff; border: none; cursor: pointer; padding: 8px 12px; }
        input[type="submit"]:hover { background: #218838; }
        .mensaje { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .exito { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<header>
    <h1>Dashboard - Rol: <?php echo ucfirst($rol); ?></h1>
    <p>Bienvenido, <?php echo $nombre; ?></p>
</header>
<nav>
    <a href="dashboard.php">Inicio</a>
    <?php if($rol == "datos"): ?>
        <a href="dashboard.php?pagina=crear_ticket">Crear Ticket</a>
        <a href="dashboard.php?pagina=mis_tickets">Mis Tickets</a>
    <?php elseif($rol == "tecnico"): ?>
        <a href="dashboard.php?pagina=tickets_asignados">Tickets Asignados</a>
    <?php elseif($rol == "administrador"): ?>
        <a href="dashboard.php?pagina=gestionar_tickets">Gestionar Tickets</a>
        <a href="dashboard.php?pagina=clientes">Clientes</a>
        <a href="dashboard.php?pagina=tecnicos">Técnicos</a>
    <?php endif; ?>
    <a href="logout.php">Salir</a>
</nav>
<div class="contenido">

<?php
// ====================== CLIENTE ======================
if($rol == "datos"){

    // Crear ticket
    if(isset($_GET['pagina']) && $_GET['pagina'] == "crear_ticket"){
        echo "<h2>Crear Ticket</h2>";
        if(isset($_POST['crear'])){
            $asunto = $_POST['asunto'];
            $descripcion = $_POST['descripcion'];
            $tecnico = $_POST['tecnico'] ?: NULL;
            $sql = "INSERT INTO tickets (nombre_usuario, asunto, descripcion, tecnico_asignado) VALUES ('$nombre','$asunto','$descripcion','$tecnico')";
            if(mysqli_query($enlace, $sql)){
                echo "<p class='mensaje exito'>✅ Ticket creado correctamente.</p>";
            } else {
                echo "<p class='mensaje error'>❌ Error: ".mysqli_error($enlace)."</p>";
            }
        }
        $tecnicos = mysqli_query($enlace, "SELECT nombre FROM tecnico");
        echo "<form method='post'>
                <label>Asunto</label><input type='text' name='asunto' required>
                <label>Descripción</label><textarea name='descripcion' required></textarea>
                <label>Asignar Técnico (opcional)</label>
                <select name='tecnico'>
                    <option value=''>-- Sin asignar --</option>";
        while($t = mysqli_fetch_assoc($tecnicos)){
            echo "<option value='".$t['nombre']."'>".$t['nombre']."</option>";
        }
        echo "</select>
              <input type='submit' name='crear' value='Crear Ticket'>
              </form>";
    }

    // Mis tickets
    elseif(isset($_GET['pagina']) && $_GET['pagina'] == "mis_tickets"){
        echo "<h2>Mis Tickets</h2>";

        // Eliminar ticket
        if(isset($_GET['eliminar'])){
            $idEliminar = intval($_GET['eliminar']);
            mysqli_query($enlace, "DELETE FROM tickets WHERE id=$idEliminar AND nombre_usuario='$nombre'");
            echo "<p class='mensaje exito'>✅ Ticket eliminado.</p>";
        }

        $res = mysqli_query($enlace, "SELECT * FROM tickets WHERE nombre_usuario='$nombre' ORDER BY fecha DESC");
        if(mysqli_num_rows($res) > 0){
            echo "<table><tr><th>ID</th><th>Asunto</th><th>Descripción</th><th>Técnico</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>";
            while($fila = mysqli_fetch_assoc($res)){
                echo "<tr>
                        <td>".$fila['id']."</td>
                        <td>".$fila['asunto']."</td>
                        <td>".$fila['descripcion']."</td>
                        <td>".($fila['tecnico_asignado'] ?: "Sin asignar")."</td>
                        <td>".$fila['estado']."</td>
                        <td>".$fila['fecha']."</td>
                        <td><a href='dashboard.php?pagina=mis_tickets&eliminar=".$fila['id']."' onclick=\"return confirm('¿Eliminar ticket?');\">🗑️ Eliminar</a></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No has creado tickets aún.</p>";
        }
    }

}

// ====================== TÉCNICO ======================
elseif($rol == "tecnico" && isset($_GET['pagina']) && $_GET['pagina'] == "tickets_asignados"){
    echo "<h2>Tickets Asignados</h2>";

    // Cambiar estado
    if(isset($_POST['cambiar_estado'])){
        $id = intval($_POST['ticket_id']);
        $estado = $_POST['estado'];
        mysqli_query($enlace, "UPDATE tickets SET estado='$estado' WHERE id=$id AND tecnico_asignado='$nombre'");
        echo "<p class='mensaje exito'>✅ Estado del ticket #$id actualizado.</p>";
    }

    $res = mysqli_query($enlace, "SELECT * FROM tickets WHERE tecnico_asignado='$nombre' ORDER BY fecha DESC");
    if(mysqli_num_rows($res) > 0){
        echo "<table><tr><th>ID</th><th>Usuario</th><th>Asunto</th><th>Descripción</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>";
        while($fila = mysqli_fetch_assoc($res)){
            echo "<tr>
                    <td>".$fila['id']."</td>
                    <td>".$fila['nombre_usuario']."</td>
                    <td>".$fila['asunto']."</td>
                    <td>".$fila['descripcion']."</td>
                    <td>".$fila['estado']."</td>
                    <td>".$fila['fecha']."</td>
                    <td>
                        <form method='post'>
                            <input type='hidden' name='ticket_id' value='".$fila['id']."'>
                            <select name='estado'>
                                <option ".($fila['estado']=='Pendiente'?'selected':'').">Pendiente</option>
                                <option ".($fila['estado']=='En Progreso'?'selected':'').">En Progreso</option>
                                <option ".($fila['estado']=='Resuelto'?'selected':'').">Resuelto</option>
                            </select>
                            <input type='submit' name='cambiar_estado' value='Actualizar'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tienes tickets asignados.</p>";
    }
}

// ====================== ADMINISTRADOR ======================
elseif($rol == "administrador"){

    // Gestionar tickets
    if(isset($_GET['pagina']) && $_GET['pagina'] == "gestionar_tickets"){
        echo "<h2>Gestionar Tickets</h2>";

        if(isset($_POST['asignar_ticket'])){
            $id = intval($_POST['ticket_id']);
            $tec = $_POST['tecnico'];
            mysqli_query($enlace, "UPDATE tickets SET tecnico_asignado='$tec' WHERE id=$id");
            echo "<p class='mensaje exito'>✅ Ticket asignado a $tec.</p>";
        }
        if(isset($_POST['cambiar_estado'])){
            $id = intval($_POST['ticket_id']);
            $estado = $_POST['estado'];
            mysqli_query($enlace, "UPDATE tickets SET estado='$estado' WHERE id=$id");
            echo "<p class='mensaje exito'>✅ Estado del ticket #$id actualizado.</p>";
        }

        $tecnicos = mysqli_query($enlace, "SELECT nombre FROM tecnico");
        $res = mysqli_query($enlace, "SELECT * FROM tickets ORDER BY fecha DESC");
        if(mysqli_num_rows($res) > 0){
            echo "<table><tr><th>ID</th><th>Usuario</th><th>Asunto</th><th>Descripción</th><th>Técnico</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>";
            while($fila = mysqli_fetch_assoc($res)){
                echo "<tr>
                        <td>".$fila['id']."</td>
                        <td>".$fila['nombre_usuario']."</td>
                        <td>".$fila['asunto']."</td>
                        <td>".$fila['descripcion']."</td>
                        <td>".($fila['tecnico_asignado'] ?: "Sin asignar")."</td>
                        <td>".$fila['estado']."</td>
                        <td>".$fila['fecha']."</td>
                        <td>";

                if(!$fila['tecnico_asignado']){
                    echo "<form method='post' style='display:inline;'>
                            <input type='hidden' name='ticket_id' value='".$fila['id']."'>
                            <select name='tecnico'>";
                    mysqli_data_seek($tecnicos, 0);
                    while($t = mysqli_fetch_assoc($tecnicos)){
                        echo "<option value='".$t['nombre']."'>".$t['nombre']."</option>";
                    }
                    echo "</select>
                          <input type='submit' name='asignar_ticket' value='Asignar'>
                          </form><br>";
                }
                echo "<form method='post'>
                        <input type='hidden' name='ticket_id' value='".$fila['id']."'>
                        <select name='estado'>
                            <option ".($fila['estado']=='Pendiente'?'selected':'').">Pendiente</option>
                            <option ".($fila['estado']=='En Progreso'?'selected':'').">En Progreso</option>
                            <option ".($fila['estado']=='Resuelto'?'selected':'').">Resuelto</option>
                        </select>
                        <input type='submit' name='cambiar_estado' value='Actualizar'>
                      </form>";

                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay tickets registrados.</p>";
        }
    }

    // Clientes
    elseif(isset($_GET['pagina']) && $_GET['pagina'] == "clientes"){
        echo "<h2>Clientes</h2>";
        if(isset($_GET['eliminar_cliente'])){
            $id = intval($_GET['eliminar_cliente']);
            mysqli_query($enlace, "DELETE FROM datos WHERE id=$id");
            echo "<p class='mensaje exito'>✅ Cliente eliminado.</p>";
        }
        $res = mysqli_query($enlace, "SELECT * FROM datos");
        if(mysqli_num_rows($res) > 0){
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr>";
            while($fila = mysqli_fetch_assoc($res)){
                echo "<tr>
                        <td>".$fila['id']."</td>
                        <td>".$fila['nombre']."</td>
                        <td>".$fila['correo']."</td>
                        <td>".$fila['telefono']."</td>
                        <td><a href='dashboard.php?pagina=clientes&eliminar_cliente=".$fila['id']."' onclick=\"return confirm('¿Eliminar cliente?');\">🗑️ Eliminar</a></td>
                      </tr>";
            }
            echo "</table>";
        } else { echo "<p>No hay clientes registrados.</p>"; }
    }

    // Técnicos
    elseif(isset($_GET['pagina']) && $_GET['pagina'] == "tecnicos"){
        echo "<h2>Técnicos</h2>";
        if(isset($_POST['crear_tecnico'])){
            $nom = $_POST['nombre'];
            $cor = $_POST['correo'];
            $tel = $_POST['telefono'];
            mysqli_query($enlace, "INSERT INTO tecnico (nombre, correo, telefono) VALUES ('$nom','$cor','$tel')");
            echo "<p class='mensaje exito'>✅ Técnico creado.</p>";
        }
        if(isset($_GET['eliminar_tecnico'])){
            $id = intval($_GET['eliminar_tecnico']);
            mysqli_query($enlace, "DELETE FROM tecnico WHERE id=$id");
            echo "<p class='mensaje exito'>✅ Técnico eliminado.</p>";
        }
        echo "<form method='post'>
                <label>Nombre</label><input type='text' name='nombre' required>
                <label>Correo</label><input type='email' name='correo' required>
                <label>Teléfono</label><input type='text' name='telefono' required>
                <input type='submit' name='crear_tecnico' value='Crear Técnico'>
              </form>";

        $res = mysqli_query($enlace, "SELECT * FROM tecnico");
        if(mysqli_num_rows($res) > 0){
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr>";
            while($fila = mysqli_fetch_assoc($res)){
                echo "<tr>
                        <td>".$fila['id']."</td>
                        <td>".$fila['nombre']."</td>
                        <td>".$fila['correo']."</td>
                        <td>".$fila['telefono']."</td>
                        <td><a href='dashboard.php?pagina=tecnicos&eliminar_tecnico=".$fila['id']."' onclick=\"return confirm('¿Eliminar técnico?');\">🗑️ Eliminar</a></td>
                      </tr>";
            }
            echo "</table>";
        } else { echo "<p>No hay técnicos registrados.</p>"; }
    }

}
?>
</div>
</body>
</html>
