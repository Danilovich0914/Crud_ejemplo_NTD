<?php
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
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
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
            outline: none;
        }
        .form-container input[type="submit"], 
        .form-container input[type="reset"] {
            background: #4e54c8;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover, 
        .form-container input[type="reset"]:hover {
            background: #3b40a4;
        }
        .mensaje { margin-top: 20px; font-weight: bold; }
        .exito { color: green; }
        .error { color: red; }
        .link { margin-top: 15px; display: block; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Registro de Usuario</h2>
        <form action="#" method="post">
            <input type="text" name="nombre" placeholder="Nombre" required> 
            <input type="email" name="correo" placeholder="Correo" required> 
            <input type="text" name="telefono" placeholder="Teléfono" required> 

            <select name="rol" required>
                <option value="datos">Usuario Normal</option>
                <option value="administrador">Administrador</option>
                <option value="tecnico">Técnico</option>
            </select>

            <input type="submit" name="registro" value="Registrar">
            <input type="reset" value="Limpiar">
        </form>

        <a class="link" href="ingreso.php">👉 Ir al ingreso</a>

        <?php
        if(isset($_POST['registro'])){
            $nombre = $_POST['nombre'];
            $correo = $_POST['correo'];
            $telefono = $_POST['telefono'];
            $rol = $_POST['rol'];

            $sql = "INSERT INTO $rol (nombre, correo, telefono) VALUES('$nombre', '$correo', '$telefono')";
            $ejecutar = mysqli_query($enlace, $sql);

            if($ejecutar){
                echo "<p class='mensaje exito'>✅ Registro exitoso en la tabla $rol.</p>";
            } else {
                echo "<p class='mensaje error'>❌ Error: " . mysqli_error($enlace) . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>
