<?php
// =========================================================================
// [MODIFICACIÓN]: Conexión a BD y Consultas Dinámicas para el Dashboard
// =========================================================================
$ruta_conexion = dirname(__DIR__, 2) . "/classes/conexion.php";
require_once $ruta_conexion;

$database = new Conexion();
$db = $database->conectar();

$totalUsuarios = 0;
$totalCanciones = 0;
$totalPro = 0;

if ($db) {
    // 1. Contar Usuarios Totales
    try {
        $resUsers = $db->query("SELECT COUNT(*) as total FROM Usuario")->fetch(PDO::FETCH_ASSOC);
        $totalUsuarios = $resUsers['total'];
    } catch (Exception $e) {}

    // 2. Contar Canciones (Protegido por si la tabla Cancion aún no existe)
    try {
        $resSongs = $db->query("SELECT COUNT(*) as total FROM Cancion")->fetch(PDO::FETCH_ASSOC);
        $totalCanciones = $resSongs['total'];
    } catch (Exception $e) {}

    // 3. Contar Suscripciones Pro (Usuarios Premium, asumiendo que el ID 2 es Premium)
    try {
        $resPro = $db->query("SELECT COUNT(*) as total FROM Usuario WHERE FK_id_tipo = 2")->fetch(PDO::FETCH_ASSOC);
        $totalPro = $resPro['total'];
    } catch (Exception $e) {}
}
// =========================================================================
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Panel de Control - Soundverse</h2>
            <p class="text-muted">Resumen general de la plataforma de streaming.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm border-0 mb-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1">Usuarios Totales</h6>
                        <h2 class="mb-0"><?php echo $totalUsuarios; ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm border-0 mb-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1">Canciones Activas</h6>
                        <h2 class="mb-0"><?php echo $totalCanciones; ?></h2>
                    </div>
                    <i class="fas fa-music fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow-sm border-0 mb-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1">Suscripciones Pro</h6>
                        <h2 class="mb-0"><?php echo $totalPro; ?></h2>
                    </div>
                    <i class="fas fa-star fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>