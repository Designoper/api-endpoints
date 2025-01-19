<?php
function verifyToken($token) {
    // Conectar a la base de datos
    $conn = new mysqli('server', 'usuario', 'contraseña', 'base_de_datos');

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Buscar el token en la base de datos
    $sql = "SELECT * FROM usuarios WHERE token = '$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }

    $conn->close();
}

// Obtener el token del encabezado de la solicitud POST
$token = $_SERVER['HTTP_AUTHORIZATION'];

if (verifyToken($token)) {
    // Procesar la solicitud POST
    echo "Acceso permitido. Procesando datos...";
} else {
    // Denegar acceso
    http_response_code(401);
    echo "Acceso denegado. Token inválido.";
}
