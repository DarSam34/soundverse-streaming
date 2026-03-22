<?php
/**
 * ARCHIVO: admin/vistas/usuarios.php
 * AUTOR: Mario Roger Mejía Elvir - Equipo Proyecto 6
 * PROPÓSITO: 
 * Proporcionar la interfaz de mantenimiento para la gestión de usuarios.
 * Este archivo se carga dinámicamente en el 'contenedor-vistas' del menu_principal.php.
 */

// Conexión a BD y Consulta SQL
$ruta_conexion = dirname(__DIR__, 2) . "/classes/Conexion.php";
require_once $ruta_conexion;

$database = new Conexion();
$db = $database->conectar();

// Traemos los usuarios unidos a su tipo de suscripción
$sql = "SELECT u.PK_id_usuario, u.nombre_completo, u.correo, t.nombre_plan 
        FROM Usuario u 
        INNER JOIN Tipo_Suscripcion t ON u.FK_id_tipo = t.PK_id_tipo
        WHERE u.estado_disponible = 1
        ORDER BY u.PK_id_usuario DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid animate__animated animate__fadeIn">

    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i> Administración de Usuarios</h2>
            <p class="text-muted">Módulo para el registro, edición y control de acceso de la plataforma Soundverse.</p>
            <hr>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-user-plus me-2"></i> Nuevo Registro</h5>
                </div>

                <div class="card-body">
                    <form id="formNuevoUsuario">
                        <input type="hidden" id="id_usuario" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" id="nombre" class="form-control" placeholder="Ej. Mario Mejía" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" id="email" class="form-control" placeholder="mario@example.com"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Suscripción</label>
                            <select id="rol" class="form-select">
                                <option value="1">Free</option>
                                <option value="2">Premium</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña Temporal</label>
                            <input type="password" id="password" class="form-control" minlength="8">
                            <div class="form-text">Mínimo 8 caracteres. En edición, déjalo vacío para no cambiar la
                                clave.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="btn-submit-usuario" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Registrar Usuario
                            </button>
                            <button type="button" id="btn-cancelar-usuario" class="btn btn-outline-secondary"
                                style="display:none;" onclick="cancelarEdicionUsuario()">
                                <i class="fas fa-times me-2"></i>Cancelar Edición
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark"><i class="fas fa-list me-2 text-success"></i> Lista de
                        Usuarios</h5>
                    <span class="badge bg-secondary">Total:
                        <?php echo count($lista_usuarios); ?>
                    </span>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Plan</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuariosBody">
                                <?php if (count($lista_usuarios) > 0): ?>
                                    <?php foreach ($lista_usuarios as $user): ?>
                                        <tr>
                                            <td>
                                                <?php echo $user['PK_id_usuario']; ?>
                                            </td>
                                            <td><strong>
                                                    <?php echo htmlspecialchars($user['nombre_completo']); ?>
                                                </strong></td>
                                            <td>
                                                <?php echo htmlspecialchars($user['correo']); ?>
                                            </td>
                                            <td>
                                                <?php if ($user['nombre_plan'] == 'Premium'): ?>
                                                    <span class="badge bg-warning text-dark"><i
                                                            class="fas fa-star"></i>Premium</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark">
                                                        <?php echo htmlspecialchars($user['nombre_plan']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button title="Editar" class="btn btn-outline-warning btn-sm me-1" onclick="editarUsuario(
                                                        <?php echo $user['PK_id_usuario']; ?>,
                                                        '<?php echo addslashes(htmlspecialchars($user['nombre_completo'])); ?>',
                                                        '<?php echo addslashes(htmlspecialchars($user['correo'])); ?>',
                                                        <?php echo ($user['nombre_plan'] === 'Premium') ? 2 : 1; ?>
                                                    )">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button title="Eliminar" class="btn btn-outline-danger btn-sm"
                                                    onclick="eliminarUsuario(<?php echo $user['PK_id_usuario']; ?>, '<?php echo addslashes(htmlspecialchars($user['nombre_completo'])); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay usuarios registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>