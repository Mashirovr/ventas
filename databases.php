<?php

$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "information_schema"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener todas las bases de datos excepto la base de datos del sistema
$sql = "SHOW DATABASES WHERE `Database` NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')";
$result = $conn->query($sql);

$databases = array();

// Verificar si se encontraron bases de datos
if ($result->num_rows > 0) {
    // Almacenar el nombre de cada base de datos en un array
    while ($row = $result->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}


$conn->close();
?>
