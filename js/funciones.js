/**
 * ARCHIVO: funciones.js
 * AUTOR: Mario Roger Mejía Elvir - Equipo Proyecto 6
 * PROPÓSITO:
 * Motor principal para la navegación SPA (Single Page Application) usando Vanilla JavaScript (Fetch API).
 * Permite recargar solo el contenedor principal sin actualizar toda la página, mejorando el rendimiento.
 */

function cargarVista(urlVista) {
    // 1. Identificar el contenedor donde inyectaremos el código
    const contenedor = document.getElementById('contenedor-vistas');
    
    // 2. Mostrar un "spinner" de carga mientras el servidor responde (Toque profesional)
    contenedor.innerHTML = `
        <div class="text-center mt-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando módulo...</p>
        </div>`;

    // 3. Realizar la petición AJAX al servidor usando Fetch
    fetch(urlVista)
        .then(response => {
            // Si el archivo no existe (ej. Error 404), lanzamos una excepción
            if (!response.ok) {
                throw new Error('No se encontró el archivo: ' + urlVista);
            }
            // Si todo está bien, extraemos el texto (el código HTML del archivo)
            return response.text();
        })
        .then(html => {
            // 4. Inyectar el HTML limpio en nuestro contenedor central
            contenedor.innerHTML = html;

            // ============================================================
            // [NUEVA MODIFICACIÓN]: ACTIVAR ESCUCHADORES DE FORMULARIOS
            // Si la vista cargada es la de usuarios, activamos su lógica
            // ============================================================
            if (urlVista.includes('usuarios.php')) {
                configurarFormularioUsuarios();
            }
        })
        .catch(error => {
            // 5. Manejo de errores amigable usando SweetAlert2 (Requisito de la clase)
            console.error('Error en petición AJAX:', error);
            
            // Dejamos un mensaje de error en el contenedor
            contenedor.innerHTML = `
                <div class="alert alert-danger mt-3 border-0 border-start border-5 border-danger shadow-sm">
                    <h4><i class="fas fa-exclamation-triangle"></i> Módulo no disponible</h4>
                    <p>El archivo <strong>${urlVista}</strong> aún no ha sido creado o tiene errores.</p>
                </div>`;
            
            // Mostramos la alerta pop-up
            Swal.fire({
                icon: 'error',
                title: 'Error de navegación',
                text: 'No se pudo cargar el módulo solicitado.',
                confirmButtonColor: '#0d6efd'
            });
        });
}

/**
 * [NUEVA FUNCIÓN]: configurarFormularioUsuarios
 * Captura los datos del formulario de usuarios y los envía a queries.php vía POST.
 */
function configurarFormularioUsuarios() {
    const form = document.getElementById('formNuevoUsuario');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar recarga de página

        const datos = new FormData();
        datos.append('accion', 'registrarUsuario');
        datos.append('nombre', document.getElementById('nombre').value);
        datos.append('email', document.getElementById('email').value);
        datos.append('rol', document.getElementById('rol').value);
        datos.append('password', document.getElementById('password').value);

        fetch('php/queries.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Registro Exitoso!',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                });
                form.reset(); // Limpiar campos
                cargarVista('vistas/usuarios.php'); // Recargar la tabla para ver el nuevo registro
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error Crítico', 'No se pudo procesar el registro.', 'error');
        });
    });
}

// Función para eliminar un usuario con SweetAlert2
function eliminarUsuario(id, nombre) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Estás a punto de eliminar permanentemente a ${nombre}. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', // Rojo peligro
        cancelButtonColor: '#6c757d', // Gris secundario
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, hacemos la petición a PHP
            let datos = new FormData();
            datos.append('accion', 'eliminarUsuario');
            datos.append('id_usuario', id);

            fetch('php/queries.php', {
                method: 'POST',
                body: datos
            })
            .then(respuesta => respuesta.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Eliminado!', data.message, 'success');
                    // ============================================================
                    // [CORRECCIÓN APLICADA]: Ruta completa para recargar la vista
                    // ============================================================
                    cargarVista('vistas/usuarios.php'); 
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error("Error en Fetch: ", error);
                Swal.fire('Error', 'Hubo un problema de conexión con el servidor.', 'error');
            });
        }
    });
}