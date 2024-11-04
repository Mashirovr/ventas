<?php
session_start();

if (isset($_GET['db'])) {
    $selected_db = $_GET['db'];
} else {
    header("location: home.php");
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "12345678";
$dbname = $selected_db;

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener las tablas de la base de datos seleccionada
$sql = "SHOW TABLES";
$result = $conn->query($sql);

$tables = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tables[] = array_values($row)[0];
    }
}

// Crear una tabla nueva
if (isset($_POST['create_table'])) {
    $new_table_name = $conn->real_escape_string($_POST['new_table_name']);
    $create_sql = "CREATE TABLE `$new_table_name` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data VARCHAR(255) NOT NULL
    )";
    if ($conn->query($create_sql) === TRUE) {
        echo "Tabla '$new_table_name' creada con éxito.";
        header("Location: ?db=" . urlencode($selected_db));
        exit;
    } else {
        echo "Error al crear tabla: " . $conn->error;
    }
}

// Eliminar una tabla
if (isset($_POST['delete_table'])) {
    $table_name = $conn->real_escape_string($_POST['table_name']);
    $delete_sql = "DROP TABLE `$table_name`";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Tabla '$table_name' eliminada con éxito.";
        header("Location: ?db=" . urlencode($selected_db));
        exit;
    } else {
        echo "Error al eliminar tabla: " . $conn->error;
    }
}

// Renombrar una tabla
if (isset($_POST['rename_table'])) {
    $old_table_name = $conn->real_escape_string($_POST['old_table_name']);
    $new_table_name = $conn->real_escape_string($_POST['new_table_name']);
    $rename_sql = "RENAME TABLE `$old_table_name` TO `$new_table_name`";
    if ($conn->query($rename_sql) === TRUE) {
        echo "Tabla '$old_table_name' renombrada a '$new_table_name'.";
        header("Location: ?db=" . urlencode($selected_db));
        exit;
    } else {
        echo "Error al renombrar tabla: " . $conn->error;
    }
}

// Obtener datos de la tabla seleccionada
$table_data = [];
if (isset($_GET['table'])) {
    $table_name = $conn->real_escape_string($_GET['table']);
    $data_sql = "SELECT * FROM `$table_name`";
    $data_result = $conn->query($data_sql);
    if ($data_result->num_rows > 0) {
        while ($row = $data_result->fetch_assoc()) {
            $table_data[] = $row;
        }
    }
}

// Eliminar una fila
if (isset($_POST['delete_row'])) {
    $table_name = $conn->real_escape_string($_POST['table_name']);
    $row_id = intval($_POST['row_id']);
    $delete_row_sql = "DELETE FROM `$table_name` WHERE id = $row_id";
    if ($conn->query($delete_row_sql) === TRUE) {
        echo "Fila con ID '$row_id' eliminada con éxito.";
        header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($table_name));
        exit;
    } else {
        echo "Error al eliminar fila: " . $conn->error;
    }
}

// Actualizar una fila
if (isset($_POST['update_row'])) {
    $table_name = $conn->real_escape_string($_POST['table_name']);
    $row_id = intval($_POST['row_id']);
    $new_data = $conn->real_escape_string($_POST['data']);
    $update_sql = "UPDATE `$table_name` SET data = '$new_data' WHERE id = $row_id";
    if ($conn->query($update_sql) === TRUE) {
        echo "Fila con ID '$row_id' actualizada con éxito.";
        header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($table_name));
        exit;
    } else {
        echo "Error al actualizar fila: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MBD - <?php echo htmlspecialchars($selected_db); ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
  
  <link href="css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="css/all.min.css" crossorigin="anonymous">

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Gestor de Datos</a>
    <a class="btn btn-outline-light ml-2" href="indexSS.php">Consultas</a>
    <div class="ml-auto">
        <a href="login.php" class="btn btn-outline-light ml-2">Salir</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar">
            <div class="pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">
                            <i class="ti ti-credit-card-refund"></i> Volver
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#createTableModal">
                            <i class="ti ti-plus"></i> Crear Tabla
                        </a>
                    </li>
                    <?php foreach ($tables as $table) { ?>
                    <li class="nav-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <a class="nav-link" href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($table); ?>">
                                <i class="ti ti-table"></i> <?php echo htmlspecialchars($table); ?>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo htmlspecialchars($table); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo htmlspecialchars($table); ?>">
                                    <li><a class="dropdown-item" href="#" onclick="editTableName('<?php echo htmlspecialchars($table); ?>')">Editar</a></li>
                                    <li><button class="dropdown-item delete-table-btn" data-name="<?php echo htmlspecialchars($table); ?>" onclick="confirmDeleteTable(this)">Eliminar</button></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </nav>
        <main class="col-md-10 main-content">
            <h1 class="mt-4">Tablas de <?php echo htmlspecialchars($selected_db); ?></h1>
            <p>Selecciona una tabla para ver sus datos.</p>
            <?php if (isset($_GET['table'])) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if (!empty($table_data)) {
                            foreach (array_keys($table_data[0]) as $header) {
                                echo "<th>" . htmlspecialchars($header) . "</th>";
                            }
                        } ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data as $row) { ?>
                    <tr>
                        <?php foreach ($row as $column => $value) { ?>
                        <td><?php echo htmlspecialchars($value); ?></td>
                        <?php } ?>

                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editRow(<?php echo htmlspecialchars(json_encode(['id' => $row['id'], 'data' => $row['data'], 'table_name' => $table_name])); ?>)">Actualizar</button>

                            <button class="btn btn-danger btn-sm" onclick="confirmDeleteRow(<?php echo htmlspecialchars(json_encode(['id' => $row['id'], 'table_name' => $table_name])); ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
        </main>
    </div>
</div>

<!-- Modal para crear una tabla -->
<div class="modal fade" id="createTableModal" tabindex="-1" aria-labelledby="createTableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTableModalLabel">Crear Tabla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_table_name">Nombre de la tabla</label>
                        <input type="text" class="form-control" id="new_table_name" name="new_table_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="create_table">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para eliminar una tabla -->
<div class="modal fade" id="deleteTableModal" tabindex="-1" aria-labelledby="deleteTableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTableModalLabel">Eliminar Tabla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar esta tabla?</p>
                    <input type="hidden" id="table_name" name="table_name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" name="delete_table">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para renombrar una tabla -->
<div class="modal fade" id="renameTableModal" tabindex="-1" aria-labelledby="renameTableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameTableModalLabel">Renombrar Tabla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_table_name">Nuevo nombre de la tabla</label>
                        <input type="text" class="form-control" id="new_table_name" name="new_table_name" required>
                        <input type="hidden" id="old_table_name" name="old_table_name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="rename_table">Renombrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para eliminar una fila -->
<div class="modal fade" id="deleteRowModal" tabindex="-1" aria-labelledby="deleteRowModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRowModalLabel">Eliminar Fila</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar esta fila?</p>
                    <input type="hidden" id="row_id" name="row_id">
                    <input type="hidden" id="table_name_row" name="table_name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" name="delete_row">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para actualizar una fila -->
<div class="modal fade" id="editRowModal" tabindex="-1" aria-labelledby="editRowModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRowModalLabel">Actualizar Fila</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="data">Datos</label>
                        <input type="text" class="form-control" id="data" name="data" required>
                        <input type="hidden" id="edit_row_id" name="row_id">
                        <input type="hidden" id="edit_table_name" name="table_name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="update_row">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDeleteTable(button) {
        const tableName = button.getAttribute('data-name');
        document.getElementById('table_name').value = tableName;
        new bootstrap.Modal(document.getElementById('deleteTableModal')).show();
    }

    function editTableName(table) {
        document.getElementById('old_table_name').value = table;
        new bootstrap.Modal(document.getElementById('renameTableModal')).show();
    }

    function confirmDeleteRow(rowData) {
        document.getElementById('row_id').value = rowData.id;
        document.getElementById('table_name_row').value = rowData.table_name;
        new bootstrap.Modal(document.getElementById('deleteRowModal')).show();
    }

    function editRow(rowData) {
        document.getElementById('edit_row_id').value = rowData.id;
        document.getElementById('data').value = rowData.data;
        document.getElementById('edit_table_name').value = rowData.table_name;
        new bootstrap.Modal(document.getElementById('editRowModal')).show();
    }
</script>

<script src="js/popper.min.js" crossorigin="anonymous"></script>
<script src="js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
