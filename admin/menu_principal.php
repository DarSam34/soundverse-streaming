<?php
/**
 * ARCHIVO: menu_principal.php
 * AUTOR: Mario Roger Mejía Elvir - Equipo Proyecto 6
 * PROPÓSITO:
 * Actuar como la plantilla principal (Master Page) del panel de administración.
 * Implementa una arquitectura SPA (Single Page Application), donde el menú 
 * permanece estático y el contenido central se recarga dinámicamente vía AJAX.
 * REGLAS APLICADAS (Lic. Obed Martínez):
 * 1. Uso de HTML5 semántico.
 * 2. Integración de Bootstrap para diseño responsivo.
 * 3. Preparación de validación de sesión para seguridad.
 */

// =========================================================================
// BLOQUE DE SEGURIDAD
// =========================================================================
session_start();

// Si no hay una sesión activa, expulsar al index (Login)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Control de inactividad: si pasaron más de 10 minutos sin actividad, cerrar sesión
if (isset($_SESSION['time'])) {
    if ((time() - $_SESSION['time']) > 600) {
        header("Location: php/logout.php");
        exit();
    }
}
$_SESSION['time'] = time(); // Actualiza el tiempo en cada carga de página
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soundverse - Panel de Administración</title>

    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f4f6f9;
            /* Tono suave para la vista central */
        }

        /* Estilos del Menú Lateral (Sidebar) */
        .sidebar {
            min-height: 100vh;
            background-color: #1a1e21;
            /* Gris oscuro elegante para Soundverse */
            color: white;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: 0.3s;
            font-weight: 500;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #0d6efd;
            /* Azul de selección */
            border-left: 4px solid #fff;
        }

        /* Contenedor de la línea gráfica */
        .logo-container {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #343a40;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0">
        <div class="row g-0">

            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">

                <div class="logo-container">
                    <img src="../assets/img/logo_soundverse_white.png" alt="Soundverse Logo" class="img-fluid px-3"
                        style="max-height: 80px; filter: drop-shadow(0px 0px 2px rgba(255,255,255,0.5));">

                    <h6 class="text-uppercase fw-bold text-light mt-2" style="letter-spacing: 2px;">
                        Soundverse <span class="text-primary">Admin</span>
                    </h6>
                </div>

                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarVista('vistas/dashboard.php')">
                                <i class="fas fa-chart-line me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarVista('vistas/usuarios.php')">
                                <i class="fas fa-users-cog me-2"></i> Gestión de Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarVista('vistas/catalogo.php')">
                                <i class="fas fa-compact-disc me-2"></i> Catálogo Musical
                            </a>
                        </li>
                    </ul>

                    <hr class="text-secondary mx-3">

                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="php/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">

                <div id="contenedor-vistas">

                    <div class="alert alert-primary shadow-sm border-0 border-start border-5 border-primary">
                        <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Bienvenido a Soundverse</h4>
                        <p>El sistema está funcionando correctamente. Selecciona un módulo en el menú lateral izquierdo
                            para comenzar a administrar la plataforma.</p>
                    </div>

                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/funciones.js?v=1.1"></script>

    <script>
        window.onload = function () {
            // Uncomment to load dashboard automatically
            // cargarVista('vistas/dashboard.php');
        };
    </script>

</body>

</html>