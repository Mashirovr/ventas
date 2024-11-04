<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
        }
        .login-container {
            margin-top: 100px;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.5);
        }
        .btn-primary {
            background-color: #ff4500;
            border-color: #ff4500;
        }
        .btn-primary:hover {
            background-color: #ff6347;
            border-color: #ff6347;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 1px solid #ff4500;
        }
        .form-control::placeholder {
            color: #ff4500;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="login-box">
                    <h2 class="text-center">Login</h2>
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="username">Usuario</label>
                            <input type="text" class="form-control" id="username" placeholder="Usuario">
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" class="form-control" id="password" placeholder="Contraseña">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Iniciar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;

            if (username === 'root' && password === 'root') {
                window.location.href = 'indexS.php';
            } else {
                alert('Usuario o contraseña incorrectos');
            }
        });
    </script>
</body>
</html>
