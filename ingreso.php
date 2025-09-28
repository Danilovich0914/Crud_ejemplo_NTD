<?php
session_start();
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "ejemplo";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if(!$enlace){
    die("Error de conexión: " . mysqli_connect_error());
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ingreso de Usuario</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        .form-container h2 { margin-bottom: 20px; }
        .form-container input, .form-container select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .form-container input[type="submit"] {
            background: #11998e;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background: #0e7d6d;
        }
        .mensaje { margin-top: 20px; font-weight: bold; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Ingreso de Usuario</h2>
        <form action="#" method="post">
            <input type="text" name="nombre" placeholder="Nombre" required> 
            
            <select name="rol" required>
                <option value="datos">Usuario Normal</option>
                <option value="administrador">Administrador</option>
                <option value="tecnico">Técnico</option>
            </select>

            <input type="submit" name="ingreso" value="Ingresar">
        </form>

        <a class="link" href="index.php">👉 Ir al registro</a>

        <?php
        if(isset($_POST['ingreso'])){
            $nombre = $_POST['nombre'];
            $rol = $_POST['rol'];

            $sql = "SELECT * FROM $rol WHERE nombre='$nombre' LIMIT 1";
            $resultado = mysqli_query($enlace, $sql);

            if(mysqli_num_rows($resultado) > 0){
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = $rol;
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<p class='mensaje error'>❌ Usuario no encontrado en la tabla $rol.</p>";
            }
        }
        ?>
    </div>
</body>
</html>
