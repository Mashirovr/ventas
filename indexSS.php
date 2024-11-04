<?php
session_start();



function ejecutarConsulta($consulta, $database) {
    try {
      if (empty(trim($consulta))) {
        return "Por favor, ingresa una consulta.";
    }
        $servername = "localhost";
        $username = "root"; 
        $password = ""; 

        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);

        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
     
        $result = $conn->query($consulta);

        // Verificar si la consulta se ejecutó correctamente
        if ($result) {
            // Obtener el tipo de consulta
            $consulta_trim = strtolower(trim($consulta));
            if (strpos($consulta_trim, "create database") === 0) {
                return "create database: la base de datos se ha creado correctamente";
            } elseif (strpos($consulta_trim, "create table") === 0) {
                return "create table: la tabla se ha creado correctamente";
            } elseif (strpos($consulta_trim, "delete") === 0) {
                return "delete: los datos se han eliminado correctamente";
            } elseif (strpos($consulta_trim, "update") === 0) {
                return "update: los datos se han actualizado correctamente";
            } elseif (strpos($consulta_trim, "drop table") === 0) {
                return "drop table: la tabla se ha eliminado correctamente";
            } elseif (strpos($consulta_trim, "drop database") === 0) {
                return "drop database: la base de datos se ha eliminado correctamente";
            } elseif (strpos($consulta_trim, "insert into") === 0) {
                return "insert into: datos insertados correctamente";
            } elseif (strpos($consulta_trim, "rename table") === 0) {
                return "rename table: la tabla se ha modificado correctamente";
            } else {
                $table = '<table class="table table-bordered">';
                $table .= '<thead><tr>';

                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    $table .= '<th>' . $field->name . '</th>';
                }

                $table .= '</tr></thead><tbody>';

                while ($row = $result->fetch_assoc()) {
                    $table .= '<tr>';
                    foreach ($row as $value) {
                        $table .= '<td>' . $value . '</td>';
                    }
                    $table .= '</tr>';
                }

                $table .= '</tbody></table>';

                $conn->close();

                return $table;
            }
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $conn->error);
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

// Verificar si se ha enviado una consulta a través del método POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["consulta"]) && isset($_POST["database"])) {
    // Obtener la base de datos seleccionada
    $database = $_POST["database"];

    // Ejecutar la consulta
    $resultado = ejecutarConsulta($_POST["consulta"], $database);

    // Imprimir los resultados
    echo $resultado;

    // Finalizar el script
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Bases de Datos</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/2.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="indexS.php">Gestor de Datos</a>
        <a class="btn btn-outline-light ml-2" href="indexS.php">Gestionar</a>
        <div class="ml-auto">
            <a href="#" class="btn btn-outline-light ml-2">Salir</a>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="content">
            <h2>Bienvenidos al manejador de base</h2>
            <p>Selecciona la base de datos con la que desea usar</p>
            <div class="form-group">
                <label for="database">Seleccionar base de datos</label>
                <select class="form-control" id="database">
                    <option value="" selected disabled>Seleccionar base de datos</option>
                    <?php
                    // Obtener las bases de datos disponibles y mostrarlas en el select
                    $conn = new mysqli("localhost", "root", "", "");
                    $result = $conn->query("SHOW DATABASES");
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row['Database'] . '">' . $row['Database'] . '</option>';
                    }
                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="query">Escribe tus consultas:</label>
                <textarea class="form-control" id="query" rows="5" placeholder="Escribe tu consulta SQL aquí..."></textarea>
            </div>
            <button class="btn btn-primary" id="ejecutar">Ejecutar</button>
            <button class="btn btn-secondary" id="limpiar">Limpiar Consulta</button>
            <h3>Resultados:</h3>
            <div id="results">
                <!-- Aquí se mostrarán los resultados de las consultas -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ejecutar').click(function() {
                var consulta = $('#query').val();
                var database = $('#database').val();

                if (!consulta || !database) {
                    alert('Por favor, ingresa una consulta y selecciona una base de datos.');
                    return;
                }

                $.post('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', { consulta: consulta, database: database }, function(data) {
                    $('#results').html(data);
                });
            });

            $('#limpiar').click(function() {
                $('#query').val('');
                $('#results').html('');
            });
        });
    </script>
</body>
</html>

