/**
 * ARCHIVO: funciones.js
 * AUTOR: Mario Roger Mejía Elvir - Equipo Proyecto 6
 * PROPÓSITO:
 * Motor principal para la navegación SPA (Single Page Application) usando Vanilla JavaScript (Fetch API).
 * Permite recargar solo el contenedor principal sin actualizar toda la página.
 */

function cargarVista(urlVista) {
    const contenedor = document.getElementById('contenedor-vistas');
    
    // Spinner de carga mientras responde el servidor
    contenedor.innerHTML = `
        <div class="text-center mt-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando módulo...</p>
        </div>`;

    fetch(urlVista)
        .then(response => {
            if (!response.ok) throw new Error('No se encontró el archivo: ' + urlVista);
            return response.text();
        })
        .then(html => {
            // Inyectar HTML en el DOM
            contenedor.innerHTML = html;

            // Inicializar scripts de la vista correspondiente
            if (urlVista.includes('usuarios.php')) {
                configurarFormularioUsuarios();
            } else if (urlVista.includes('catalogo.php')) {
                cargarSelectsCancion();
                cargarTablaCanciones();
                configurarFormularioCanciones();
            }
        })
        .catch(error => {
            console.error('Error en petición AJAX:', error);
            contenedor.innerHTML = `
                <div class="alert alert-danger mt-3 border-0 border-start border-5 border-danger shadow-sm">
                    <h4><i class="fas fa-exclamation-triangle"></i> Módulo no disponible</h4>
                    <p>El archivo <strong>${urlVista}</strong> aún no ha sido creado o tiene errores.</p>
                </div>`;
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
 * Módulo de Usuarios (Nuevo o Actualizar)
 */
function configurarFormularioUsuarios() {
    const form = document.getElementById('formNuevoUsuario');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('id_usuario').value;
        const esEditar = (id && parseInt(id) > 0);
        const password = document.getElementById('password').value;

        // Validación frontend
        if (!esEditar && password === '') {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debes ingresar una contraseña para registrar un usuario.', confirmButtonColor: '#0d6efd' });
            return;
        }
        if (password !== '' && password.length < 8) {
            Swal.fire({ icon: 'warning', title: 'Contraseña insegura', text: 'La contraseña debe tener un mínimo de 8 caracteres.', confirmButtonColor: '#0d6efd' });
            return;
        }

        const datos = new FormData();
        datos.append('id_usuario', id);
        datos.append('nombre',     document.getElementById('nombre').value);
        datos.append('email',      document.getElementById('email').value);
        datos.append('rol',        document.getElementById('rol').value);
        datos.append('password',   password);

        // Notar que la operación CRUD la define 'caso' en el endpoint URL GET, no en FormData POST.
        const casoRequerido = esEditar ? 'actualizarUsuario' : 'registrarUsuario';

        fetch(`php/queries.php?caso=${casoRequerido}`, { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: esEditar ? '¡Actualizado!' : '¡Registro Exitoso!',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                });
                cancelarEdicionUsuario();
                cargarVista('vistas/usuarios.php');
            } else {
                Swal.fire('Error al procesar', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            Swal.fire('Error Crítico', 'No se pudo procesar la solicitud con el servidor.', 'error');
        });
    });
}

// Variables Globales en window
window.editarUsuario = function(id, nombre, email, rol) {
    document.getElementById('id_usuario').value = id;
    document.getElementById('nombre').value      = nombre;
    document.getElementById('email').value       = email;
    document.getElementById('rol').value         = rol;
    document.getElementById('password').value    = ''; 

    const btn = document.getElementById('btn-submit-usuario');
    btn.classList.replace('btn-primary', 'btn-warning');
    btn.innerHTML = '<i class="fas fa-save me-2"></i>Actualizar Usuario';

    document.getElementById('btn-cancelar-usuario').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.cancelarEdicionUsuario = function() {
    const form = document.getElementById('formNuevoUsuario');
    if(form) form.reset();
    
    document.getElementById('id_usuario').value = '0';

    const btn = document.getElementById('btn-submit-usuario');
    if(btn) {
        btn.classList.replace('btn-warning', 'btn-primary');
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Registrar Usuario';
        document.getElementById('btn-cancelar-usuario').style.display = 'none';
    }
};

window.eliminarUsuario = function(id, nombre) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Estás a punto de eliminar lógicamente a ${nombre}. No aparecerá más, pero sus registros seguirán existiendo en base de datos.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append('id_usuario', id);

            fetch('php/queries.php?caso=eliminarUsuario', {
                method: 'POST',
                body: datos
            })
            .then(respuesta => respuesta.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Desactivado!', data.message, 'success');
                    cargarVista('vistas/usuarios.php'); 
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error("Fetch Error: ", error);
                Swal.fire('Error', 'Hubo un problema de conexión con el servidor.', 'error');
            });
        }
    });
};


// =========================================================================
// MÓDULO CANCIONES
// =========================================================================

function cargarSelectsCancion() {
    fetch('php/queries.php?caso=datos_selects_cancion', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            const selectAlbum = document.getElementById('album');
            const selectGenero = document.getElementById('genero');
            if(!selectAlbum || !selectGenero) return;

            selectAlbum.innerHTML = '<option value="">Seleccione...</option>';
            selectGenero.innerHTML = '<option value="">Seleccione...</option>';

            data.albumes.forEach(a => selectAlbum.innerHTML += `<option value="${a.PK_id_album}">${a.titulo}</option>`);
            data.generos.forEach(g => selectGenero.innerHTML += `<option value="${g.PK_id_genero}">${g.nombre_genero}</option>`);
        })
        .catch(err => console.error('Error cargando selects:', err));
}

function cargarTablaCanciones() {
    fetch('php/queries.php?caso=listar_canciones', { method: 'POST' })
        .then(res => res.json())
        .then(canciones => {
            const tbody = document.getElementById('tbody-canciones');
            if(!tbody) return;
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
        .catch(err => console.error('Error cargando tabla canciones:', err));
}

function configurarFormularioCanciones() {
    const form = document.getElementById('form-cancion');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const datos = new FormData(form);

        fetch('php/queries.php?caso=guardar_cancion', { method: 'POST', body: datos })
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
            .catch(err => Swal.fire('Error', 'Problema comunicación servidor.', 'error'));
    });
}

window.editarCancion = function(id) {
    let datos = new FormData();
    datos.append('id', id);

    fetch('php/queries.php?caso=obtener_cancion', { method: 'POST', body: datos })
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
            
            window.scrollTo(0, 0);
        })
        .catch(err => console.error(err));
};

window.eliminarCancion = function(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "La canción será eliminada lógicamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append('id', id);

            fetch('php/queries.php?caso=eliminar_cancion', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('¡Eliminado!', data.message, 'success');
                        cargarTablaCanciones();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => console.error(err));
        }
    });
};

window.limpiarFormCancion = function() {
    const form = document.getElementById('form-cancion');
    if(form) form.reset();
    
    const idCancion = document.getElementById('id_cancion');
    if(idCancion) idCancion.value = '0';
    
    const titleForm = document.getElementById('titulo-form-cancion');
    if(titleForm) titleForm.innerText = 'Registrar Nueva Canción';
    
    const btn = document.getElementById('btn-submit-cancion');
    if(btn) {
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-primary');
        btn.innerText = 'Guardar';
    }
};

// =========================================================================
// MÓDULO LOGIN
// =========================================================================

function configurarLogin() {
    const form = document.getElementById('formLogin');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const email    = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        let datos = new FormData();
        datos.append('email', email);
        datos.append('password', password);

        fetch('php/queries.php?caso=iniciarSesion', {
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
            console.error('Error Login:', error);
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        });
    });
}
