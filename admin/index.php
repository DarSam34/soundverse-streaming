<?php
// Si el usuario ya había iniciado sesión antes, lo mandamos directo al menú
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: menu_principal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Soundverse Admin</title>
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            padding: 30px;
            text-align: center;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card border-0">
                    <div class="login-header">
                        <i class="fas fa-compact-disc fa-3x mb-2 fa-spin" style="animation-duration: 3s;"></i>
                        <h3 class="fw-bold mb-0">Soundverse</h3>
                        <p class="text-white-50 mb-0">Panel de Administración</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="formLogin">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" id="loginEmail" name="correo" class="form-control"
                                        placeholder="admin@soundverse.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fas fa-lock text-muted"></i></span>
                                    <input type="password" id="loginPassword" name="clave" class="form-control"
                                        placeholder="********" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    Iniciar Sesión <i class="fas fa-sign-in-alt ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/funciones.js?v=1.1"></script>
    <script>
        configurarLogin();
    </script>
</body>

</html>