<?php
$pageTitle = 'Crear Ticket - TICKETS';
include __DIR__ . '/../layouts/header.php';
?>

    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                 <img src="/gestion-tickets/assets/favicon_rideco.png" alt="Logo">
            </div>
            <ul class="sidebar-menu">
                <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/tickets/create" class="active"><i class="fas fa-plus"></i> Crear Ticket</a></li>
                <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/tickets"><i class="fas fa-list"></i> Mis Tickets</a></li>
                <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/profile"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <button class="btn btn-secondary btn-sm" onclick="toggleSidebar()" aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Crear Nuevo Ticket</h1>
                </div>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>

            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Form Container -->
            <div class="form-container">
                <h2 class="form-title">Información del Ticket</h2>
                <p class="form-subtitle">Completa los siguientes campos para crear tu solicitud de soporte</p>

                <form id="ticketForm" method="POST" enctype="multipart/form-data">
                    <!-- CSRF Token -->
                    <?php if (function_exists('csrf_field')) { echo csrf_field(); } ?>

                    <!-- User Info (Read Only) + Título -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Título del Ticket <span class="required">*</span></label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="form-control"
                                placeholder="Resumen corto del problema"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Nombre del Solicitante</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>" 
                                disabled
                            >
                        </div>
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? 'usuario@empresa.com'); ?>" 
                                disabled
                            >
                        </div>
                    </div>

                    <!-- Departments -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dept_origen">
                                Mi Departamento (Origen) <span class="required">*</span>
                            </label>
                            <select id="dept_origen" name="department_from_id" class="form-control" required>
                                <option value="">Seleccionar departamento...</option>
                                <?php if(!empty($departments)): ?>
                                    <?php foreach($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="form-help">📍 Selecciona el departamento al que perteneces</p>
                        </div>
                        <div class="form-group">
                            <label for="dept_destino">
                                Departamento que Atenderá (Destino) <span class="required">*</span>
                            </label>
                            <select id="dept_destino" name="department_to_id" class="form-control" required onchange="loadCategoriesForDept(this.value)">
                                <option value="">Seleccionar departamento...</option>
                                <?php if(!empty($receptorDepts)): ?>
                                    <?php foreach($receptorDepts as $rd): ?>
                                        <option value="<?= $rd['id'] ?>"><?= htmlspecialchars($rd['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="form-help"><i class="fas fa-info-circle"></i> ¿Quién debe atender tu solicitud?</p>
                        </div>
                    </div>

                    <!-- Incident Type -->
                    <div class="form-group-full">
                        <label for="incidencia">
                            Tipo de Incidencia <span class="required">*</span>
                        </label>
                        <select id="incidencia" name="category_id" class="form-control" required>
                            <option value="">Seleccionar tipo de incidencia...</option>
                            <?php if(!empty($categories)): ?>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No hay categorías disponibles</option>
                            <?php endif; ?>
                        </select>
                        <p class="form-help"><i class="fas fa-info-circle"></i> Selecciona el tipo que mejor describe tu problema</p>
                    </div>

                    <!-- Description -->
                    <div class="form-group-full">
                        <label for="descripcion">
                            Descripción del Problema <span class="required">*</span>
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="description" 
                            class="form-control" 
                            placeholder="Describe detalladamente tu problema:&#10;&#10;• ¿Qué estaba haciendo cuando ocurrió?&#10;• ¿Cuándo comenzó el problema?&#10;• ¿Hay algún mensaje de error?&#10;• ¿Ya intentaste algo para solucionarlo?"
                            required
                        ></textarea>
                        <div id="charCounter" class="char-counter">0 / 20 caracteres mínimos</div>
                        <p class="form-help"><i class="fas fa-info-circle"></i> Mientras más detalles proporciones, más rápido podemos ayudarte</p>
                    </div>

                    <!-- File Upload -->
                    <div class="form-group-full">
                        <label>Archivos Adjuntos (opcional)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-area" id="dropArea">
                                <div class="upload-icon">📎</div>
                                <div class="upload-text">Arrastra archivos aquí o haz clic para seleccionar</div>
                                <div class="upload-hint">Formatos: JPG, PNG, PDF, Word, Excel • Máximo 5MB por archivo</div>
                            </div>
                            <input 
                                type="file" 
                                id="fileInput" 
                                name="attachments[]" 
                                class="file-input" 
                                multiple 
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
                            >
                            <div class="uploaded-files" id="uploadedFiles"></div>
                        </div>
                        <p class="form-help">📷 Las capturas de pantalla ayudan mucho a entender el problema</p>
                    </div>

                    <!-- Priority -->
                    <div class="form-group-full">
                        <label>Prioridad Sugerida</label>
                        <div class="priority-selector">
                            <div class="priority-option">
                                <input type="radio" name="priority" id="prioridad_baja" value="baja" checked>
                                <label for="prioridad_baja" class="priority-card">
                                    <div class="priority-icon">🟢</div>
                                    <div class="priority-name">Baja</div>
                                    <div class="priority-desc">No es urgente</div>
                                </label>
                            </div>
                            <div class="priority-option">
                                <input type="radio" name="priority" id="prioridad_media" value="media">
                                <label for="prioridad_media" class="priority-card">
                                    <div class="priority-icon">🟡</div>
                                    <div class="priority-name">Media</div>
                                    <div class="priority-desc">Importante</div>
                                </label>
                            </div>
                            <div class="priority-option">
                                <input type="radio" name="priority" id="prioridad_alta" value="alta">
                                <label for="prioridad_alta" class="priority-card">
                                    <div class="priority-icon">🟠</div>
                                    <div class="priority-name">Alta</div>
                                    <div class="priority-desc">Afecta mi trabajo</div>
                                </label>
                            </div>
                            <div class="priority-option">
                                <input type="radio" name="priority" id="prioridad_urgente" value="urgente">
                                <label for="prioridad_urgente" class="priority-card">
                                    <div class="priority-icon">🔴</div>
                                    <div class="priority-name">Urgente</div>
                                    <div class="priority-desc">Necesito ayuda YA</div>
                                </label>
                            </div>
                        </div>
                        <p class="form-help">⚡ El equipo de soporte puede ajustar la prioridad según la evaluación</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-sync-alt"></i>
                            <span>Limpiar Formulario</span>
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span><i class="fas fa-paper-plane"></i></span>
                            <span>Enviar Ticket</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <div class="info-title">
                    <span><i class="fas fa-lightbulb"></i></span>
                    <span>Consejos para Obtener una Respuesta Más Rápida</span>
                </div>
                <ul class="info-list">
                    <li><strong>Sé específico:</strong> Describe exactamente qué está pasando y cuándo ocurre el problema</li>
                    <li><strong>Incluye capturas de pantalla:</strong> Una imagen vale más que mil palabras, especialmente para problemas visuales</li>
                    <li><strong>Menciona intentos previos:</strong> Si ya intentaste solucionar el problema, déjanos saber qué hiciste</li>
                    <li><strong>Prioridad real:</strong> Marca como urgente solo si el problema está bloqueando completamente tu trabajo</li>
                    <li><strong>Revisa tu ticket:</strong> Después de crear el ticket, puedes ver su progreso en "Mis Tickets"</li>
                </ul>
            </div>
        </main>
    </div>

    <script>
        // Cargar categorías para un departamento receptor
        function loadCategoriesForDept(deptId) {
            const incidenciaSelect = document.getElementById('incidencia');
            incidenciaSelect.innerHTML = '<option>Cargando...</option>';

            // Filtrar localmente usando la lista pre-cargada `ALL_CATEGORIES`
            try {
                if (window.ALL_CATEGORIES && Array.isArray(window.ALL_CATEGORIES)) {
                    const filtered = window.ALL_CATEGORIES.filter(c => String(c.department_id) === String(deptId));
                    incidenciaSelect.innerHTML = '';
                    if (filtered.length > 0) {
                        filtered.forEach(cat => {
                            const opt = document.createElement('option');
                            opt.value = cat.id;
                            opt.textContent = cat.name + ' (' + (cat.department_name || '') + ')';
                            incidenciaSelect.appendChild(opt);
                        });
                        return;
                    }
                    incidenciaSelect.innerHTML = '<option value="">No hay categorías disponibles</option>';
                    return;
                }
            } catch (e) {
                console.error('Error filtrando categorías localmente', e);
            }

            // Fallback: intentar petición al servidor
            fetch(`<?= base_url('/api/categorias/') ?>` + deptId, {
                method: 'GET',
                credentials: 'include',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                incidenciaSelect.innerHTML = '';
                if (data && data.success && Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        incidenciaSelect.appendChild(opt);
                    });
                } else {
                    incidenciaSelect.innerHTML = '<option value="">No hay categorías disponibles</option>';
                }
            })
            .catch(err => {
                incidenciaSelect.innerHTML = '<option>Error cargando categorías</option>';
                console.error(err);
            });
        }

        // Character counter for description
        const descripcionTextarea = document.getElementById('descripcion');
        const charCounter = document.getElementById('charCounter');
        const minChars = 20;

        descripcionTextarea.addEventListener('input', function() {
            const length = this.value.trim().length;
            charCounter.textContent = `${length} / ${minChars} caracteres mínimos`;
            
            if (length >= minChars) {
                charCounter.classList.add('valid');
                this.style.borderColor = '#10B981';
            } else {
                charCounter.classList.remove('valid');
                this.style.borderColor = '#e0e0e0';
            }
        });

        // File upload handling
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('fileInput');
        const uploadedFilesDiv = document.getElementById('uploadedFiles');
        let selectedFiles = [];

        // Click to select files
        dropArea.addEventListener('click', () => fileInput.click());

        // Drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('dragover');
            }, false);
        });

        dropArea.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFileSelect, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            for (let file of files) {
                if (file.size > maxSize) {
                    showAlert(`El archivo ${file.name} excede el tamaño máximo de 5MB`, 'error');
                    continue;
                }
                if (!allowedTypes.includes(file.type)) {
                    showAlert(`El archivo ${file.name} no es un tipo permitido`, 'error');
                    continue;
                }
                selectedFiles.push(file);
            }

            displayUploadedFiles();
        }

        function displayUploadedFiles() {
            uploadedFilesDiv.innerHTML = '';
            if (selectedFiles.length > 0) {
                uploadedFilesDiv.classList.add('has-files');
                selectedFiles.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div class="file-info">
                            <div class="file-icon">📄</div>
                            <div class="file-details">
                                <div class="file-name">${file.name}</div>
                                <div class="file-size">${(file.size / 1024).toFixed(2)} KB</div>
                            </div>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile(${index})">✕</button>
                    `;
                    uploadedFilesDiv.appendChild(fileItem);
                });
            } else {
                uploadedFilesDiv.classList.remove('has-files');
            }
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displayUploadedFiles();
        }

        // Form submission
        document.getElementById('ticketForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span><span>Enviando...</span>';

            const formData = new FormData(this);

            // Add files to formData
            selectedFiles.forEach(file => {
                formData.append('attachments[]', file);
            });

            try {
                const response = await fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/ajax/tickets/create', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'X-CSRF-Token': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('¡Ticket creado exitosamente!', 'success');
                    this.reset();
                    selectedFiles = [];
                    displayUploadedFiles();
                    setTimeout(() => {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets') + '/tickets';
                    }, 2000);
                } else {
                    showAlert(data.error || data.message || 'Error al crear el ticket', 'error');
                }
            } catch (error) {
                showAlert('Error de conexión. Intenta de nuevo.', 'error');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span><i class="fas fa-paper-plane"></i></span><span>Enviar Ticket</span>';
            }
        });

        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <span class="alert-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
                <span>${message}</span>
            `;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        function resetForm() {
            document.getElementById('ticketForm').reset();
            selectedFiles = [];
            displayUploadedFiles();
            charCounter.textContent = '0 / 20 caracteres mínimos';
        }
    </script>
    <script>
        // Exponer todas las categorías para uso en cliente
        window.ALL_CATEGORIES = <?php echo json_encode($categories ?? []); ?>;

        // Si hay un depto seleccionado inicialmente, filtrar categorías
        document.addEventListener('DOMContentLoaded', function(){
            const deptSel = document.getElementById('dept_destino');
            if (deptSel && deptSel.value) {
                loadCategoriesForDept(deptSel.value);
            }
        });
    </script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>


