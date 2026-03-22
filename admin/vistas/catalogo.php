<div class="container-fluid mt-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-music text-primary me-2"></i> Catálogo Musical</h2>
    </div>
    
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 id="titulo-form-cancion" class="mb-0"><i class="fas fa-plus-circle me-1"></i> Registrar Nueva Canción</h5>
        </div>
        <div class="card-body bg-light">
            <form id="form-cancion">
                <input type="hidden" id="id_cancion" name="id_cancion" value="0">
                <input type="hidden" name="accion" value="guardar_cancion">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Título de la Canción *</label>
                        <input type="text" class="form-control shadow-sm" id="titulo" name="titulo" placeholder="Ej: Yellow Submarine" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Álbum *</label>
                        <select class="form-select shadow-sm" id="album" name="album" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Género *</label>
                        <select class="form-select shadow-sm" id="genero" name="genero" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Duración (segundos) *</label>
                        <input type="number" class="form-control shadow-sm" id="duracion_segundos" name="duracion_segundos" placeholder="Ej: 245" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Ruta Archivo Audio *</label>
                        <input type="text" class="form-control shadow-sm" id="ruta_archivo_audio" name="ruta_archivo_audio" placeholder="/assets/musica/tema.mp3" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Letra Sincronizada (Opcional)</label>
                        <textarea class="form-control shadow-sm" id="letra_sincronizada" name="letra_sincronizada" rows="3" placeholder="Introduce la letra aquí..."></textarea>
                    </div>
                </div>
                
                <div class="text-end mt-2">
                    <button type="button" class="btn btn-secondary me-2 px-4" onclick="limpiarFormCancion()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btn-submit-cancion" class="btn btn-primary px-4">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Artista</th>
                        <th>Álbum</th>
                        <th>Género</th>
                        <th>Duración</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-canciones">
                </tbody>
            </table>
        </div>
    </div>
</div>