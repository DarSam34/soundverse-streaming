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
            // ACTIVAR ESCUCHADORES DE FORMULARIOS SEGÚN LA VISTA
            // ============================================================
            if (urlVista.includes('usuarios.php')) {
                configurarFormularioUsuarios();
            } 
            // [NUEVA MODIFICACIÓN]: Inicializar la vista del Catálogo Musical
            else if (urlVista.includes('catalogo.php')) {
                cargarSelectsCancion();
                cargarTablaCanciones();
                configurarFormularioCanciones();
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
 * Función: configurarFormularioUsuarios
<<<<<<< HEAD
 * Maneja el formulario en modo CREAR y EDITAR.
 * Detecta el modo según el campo oculto #id_usuario (0 = nuevo, >0 = editar).
=======
 * Captura los datos del formulario de usuarios y los envía a queries.php vía POST.
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
 */
function configurarFormularioUsuarios() {
    const form = document.getElementById('formNuevoUsuario');
    if (!form) return;

    form.addEventListener('submit', function(e) {
<<<<<<< HEAD
        e.preventDefault();

        const id      = document.getElementById('id_usuario').value;
        const esEditar = id && parseInt(id) > 0;

        const password = document.getElementById('password').value;

        // Validar contraseña según modo
        if (!esEditar && password === '') {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debes ingresar una contraseña para registrar un usuario.', confirmButtonColor: '#0d6efd' });
            return;
        }
        if (password !== '' && password.length < 8) {
            Swal.fire({ icon: 'warning', title: 'Contraseña insegura', text: 'La contraseña debe tener un mínimo de 8 caracteres.', confirmButtonColor: '#0d6efd' });
            return;
        }

        const datos = new FormData();
        datos.append('accion',     esEditar ? 'actualizarUsuario' : 'registrarUsuario');
        datos.append('id_usuario', id);
        datos.append('nombre',     document.getElementById('nombre').value);
        datos.append('email',      document.getElementById('email').value);
        datos.append('rol',        document.getElementById('rol').value);
        datos.append('password',   password);

        fetch('php/queries.php', { method: 'POST', body: datos })
=======
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
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
<<<<<<< HEAD
                    title: esEditar ? '¡Actualizado!' : '¡Registro Exitoso!',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                });
                cancelarEdicionUsuario(); // Limpia el form y resetea modo
                cargarVista('vistas/usuarios.php');
=======
                    title: '¡Registro Exitoso!',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                });
                form.reset(); // Limpiar campos
                cargarVista('vistas/usuarios.php'); // Recargar la tabla para ver el nuevo registro
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
<<<<<<< HEAD
            Swal.fire('Error Crítico', 'No se pudo procesar la solicitud.', 'error');
=======
            Swal.fire('Error Crítico', 'No se pudo procesar el registro.', 'error');
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        });
    });
}

<<<<<<< HEAD
// Prellenar el formulario con los datos del usuario seleccionado (modo editar)
window.editarUsuario = function(id, nombre, email, rol) {
    document.getElementById('id_usuario').value = id;
    document.getElementById('nombre').value      = nombre;
    document.getElementById('email').value       = email;
    document.getElementById('rol').value         = rol;
    document.getElementById('password').value    = ''; // No se muestra el hash; vacío = no cambiar

    // Cambiar el botón a modo editar
    const btn = document.getElementById('btn-submit-usuario');
    btn.classList.replace('btn-primary', 'btn-warning');
    btn.innerHTML = '<i class="fas fa-save me-2"></i>Actualizar Usuario';

    // Mostrar botón cancelar y hacer scroll al formulario
    document.getElementById('btn-cancelar-usuario').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Resetear el formulario a modo crear
window.cancelarEdicionUsuario = function() {
    document.getElementById('formNuevoUsuario').reset();
    document.getElementById('id_usuario').value = '0';

    const btn = document.getElementById('btn-submit-usuario');
    btn.classList.replace('btn-warning', 'btn-primary');
    btn.innerHTML = '<i class="fas fa-save me-2"></i>Registrar Usuario';

    document.getElementById('btn-cancelar-usuario').style.display = 'none';
};

=======
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
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

// =========================================================================
// BLOQUE DE FUNCIONES: CATÁLOGO MUSICAL (CANCIONES)
// =========================================================================

function cargarSelectsCancion() {
    let datos = new FormData();
    datos.append('accion', 'datos_selects_cancion');

    fetch('php/queries.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            const selectAlbum = document.getElementById('album');
            const selectGenero = document.getElementById('genero');
            
            // Limpiar opciones previas excepto la primera
            selectAlbum.innerHTML = '<option value="">Seleccione...</option>';
            selectGenero.innerHTML = '<option value="">Seleccione...</option>';

            data.albumes.forEach(a => selectAlbum.innerHTML += `<option value="${a.PK_id_album}">${a.titulo}</option>`);
            data.generos.forEach(g => selectGenero.innerHTML += `<option value="${g.PK_id_genero}">${g.nombre_genero}</option>`);
        })
        .catch(err => console.error('Error cargando selects:', err));
}

function cargarTablaCanciones() {
    let datos = new FormData();
    datos.append('accion', 'listar_canciones');

    fetch('php/queries.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(canciones => {
            const tbody = document.getElementById('tbody-canciones');
            let html = '';
            canciones.forEach(c => {
                html += `<tr>
                    <td>${c.PK_id_cancion}</td>
                    <td>${c.titulo}</td>
                    <td>${c.artista}</td>
                    <td>${c.album}</td>
                    <td>${c.genero}</td>
                    <td>${c.duracion_segundos}s</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editarCancion(${c.PK_id_cancion})"><i class="fas fa-edit"></i> Editar</button>
                        <button class="btn btn-sm btn-danger ms-1" onclick="eliminarCancion(${c.PK_id_cancion})"><i class="fas fa-trash"></i> Eliminar</button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        })
        .catch(err => console.error('Error cargando tabla:', err));
}

function configurarFormularioCanciones() {
    const form = document.getElementById('form-cancion');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Recoge todos los datos de los inputs automáticamente
        const datos = new FormData(form);
        // Aseguramos que la acción sea la correcta
        datos.set('accion', 'guardar_cancion');

        fetch('php/queries.php', { method: 'POST', body: datos })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', data.message, 'success');
                    limpiarFormCancion();
                    cargarTablaCanciones();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error guardando canción:', err);
                Swal.fire('Error', 'Problema al comunicarse con el servidor.', 'error');
            });
    });
}

window.editarCancion = function(id) {
    let datos = new FormData();
    datos.append('accion', 'obtener_cancion');
    datos.append('id', id);

    fetch('php/queries.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            document.getElementById('id_cancion').value = data.PK_id_cancion;
            document.getElementById('titulo').value = data.titulo;
            document.getElementById('album').value = data.FK_id_album;
            document.getElementById('genero').value = data.FK_id_genero;
            document.getElementById('duracion_segundos').value = data.duracion_segundos;
            document.getElementById('ruta_archivo_audio').value = data.ruta_archivo_audio;
            document.getElementById('letra_sincronizada').value = data.letra_sincronizada;
            
            document.getElementById('titulo-form-cancion').innerText = 'Editar Canción';
            const btn = document.getElementById('btn-submit-cancion');
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-warning');
            btn.innerText = 'Actualizar';
            
            // Subir al inicio de la página para ver el formulario
            window.scrollTo(0, 0);
        })
        .catch(err => console.error('Error obteniendo canción:', err));
};

window.eliminarCancion = function(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "La canción será enviada a la papelera (Borrado Lógico).",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append('accion', 'eliminar_cancion');
            datos.append('id', id);

            fetch('php/queries.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('¡Eliminado!', data.message, 'success');
                        cargarTablaCanciones();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => console.error('Error al eliminar:', err));
        }
    });
};

window.limpiarFormCancion = function() {
    document.getElementById('form-cancion').reset();
    document.getElementById('id_cancion').value = '0';
    document.getElementById('titulo-form-cancion').innerText = 'Registrar Nueva Canción';
    
    const btn = document.getElementById('btn-submit-cancion');
    btn.classList.remove('btn-warning');
    btn.classList.add('btn-primary');
    btn.innerText = 'Guardar';
<<<<<<< HEAD
};
// =========================================================================
// BLOQUE: LOGIN (Inicio de Sesin)
// =========================================================================

/**
 * Funcion: configurarLogin
 * Captura el formulario de login y lo envia a queries.php va Fetch API.
 * Se llama desde admin/index.php al cargar la pgina.
 */
function configurarLogin() {
    const form = document.getElementById('formLogin');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const email    = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        let datos = new FormData();
        datos.append('accion', 'iniciarSesion');
        datos.append('email', email);
        datos.append('password', password);

        fetch('php/queries.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'menu_principal.php';
            } else {
                Swal.fire('Acceso Denegado', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        });
    });
}
=======
};
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
