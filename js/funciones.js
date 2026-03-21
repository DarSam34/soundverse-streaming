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