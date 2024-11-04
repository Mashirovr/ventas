<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Bases de Datos</title>
    
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/1.css">
    
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Gestor de Datos</a>
        <a class="btn btn-outline-light ml-2" href="indexSS.php">Consultas</a>
        <div class="ml-auto">
            <a href="login.php" class="btn btn-outline-light ml-2">Salir</a>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <?php 
            // Conectar a MySQL
            $conn = new mysqli("localhost", "root", "12345678");

            // Verificar la conexión
            if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
            }

            // Obtener las bases de datos disponibles
            $result = $conn->query("SHOW DATABASES");

            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                $databases = [];
                while ($row = $result->fetch_assoc()) {
                    $databases[] = $row['Database'];
                }
            } else {
                $databases = [];
            }

            // Manejar el cambio de nombre de la base de datos
            if (isset($_POST['renameDb'])) {
                $oldDbName = $_POST['oldDbName'];
                $newDbName = $_POST['newDbName'];
                
                // Conectar a MySQL
                $conn = new mysqli("localhost", "root", "12345678");
                
                // Verificar la conexión
                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }
                
                // Crear una nueva base de datos
                $conn->query("CREATE DATABASE $newDbName");
                
                // Copiar los datos de la base de datos antigua a la nueva
                $tables = $conn->query("SHOW TABLES FROM $oldDbName");
                while ($table = $tables->fetch_array()) {
                    $tableName = $table[0];
                    $conn->query("CREATE TABLE $newDbName.$tableName LIKE $oldDbName.$tableName");
                    $conn->query("INSERT INTO $newDbName.$tableName SELECT * FROM $oldDbName.$tableName");
                }
                
                // Eliminar la base de datos antigua
                $conn->query("DROP DATABASE $oldDbName");
                
                // Redirigir a la página principal
                header("Location: indexS.php");
                exit();
            }

            // Manejar la eliminación de la base de datos
            if (isset($_POST['deleteDb'])) {
                $dbName = $_POST['dbName'];
                $conn->query("DROP DATABASE $dbName");
                header("Location: indexS.php");
                exit();
            }

            // Cerrar la conexión
            $conn->close();
            ?>

            <ul class="nav flex-column">
                <?php foreach ($databases as $db) { ?>
                  <li class="nav-item">
                    <div class="d-flex align-items-center justify-content-between">
                      <a class="nav-link" href="tablas.php?db=<?php echo urlencode($db); ?>">
                        <i class="icon bi bi-database me-2"></i>
                        <?php echo $db; ?>
                      </a>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $db; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $db; ?>">
                          <li><a class="dropdown-item" href="#" onclick="openRenameModal('<?php echo $db; ?>')">Cambiar nombre</a></li>
                          <li><a class="dropdown-item" href="#" onclick="confirmDelete(this)" data-name="<?php echo $db; ?>">Eliminar</a></li>
                        </ul>
                      </div>
                    </div>
                  </li>
                <?php } ?>
            </ul>
        </div>

        <div class="content">

            <img src="img/sql3.png" alt="100%" width="100%">
        </div>
    </div>

    <!-- Modal para cambiar nombre -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="renameModalLabel">Cambiar nombre de base de datos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="renameForm" method="post">
              <div class="mb-3">
                <label for="newDbName" class="form-label">Nuevo nombre:</label>
                <input type="text" class="form-control" id="newDbName" name="newDbName" required>
                <input type="hidden" id="oldDbName" name="oldDbName">
                <input type="hidden" name="renameDb" value="1">
              </div>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para confirmar eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>¿Estás seguro de que quieres eliminar la base de datos <strong id="dbToDelete"></strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <form id="deleteForm" method="post">
              <input type="hidden" id="deleteDbName" name="dbName">
              <input type="hidden" name="deleteDb" value="1">
              <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      function openRenameModal(dbName) {
        const renameModal = new bootstrap.Modal(document.getElementById('renameModal'));
        document.getElementById('oldDbName').value = dbName;
        renameModal.show();
      }

      function confirmDelete(button) {
        const dbName = button.getAttribute('data-name');
        document.getElementById('dbToDelete').textContent = dbName;
        document.getElementById('deleteDbName').value = dbName;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
      }
    </script>
</body>
</html>
