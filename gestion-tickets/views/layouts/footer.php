<!-- Scripts -->
    <script src="/gestion-tickets/assets/js/app.js"></script>
    <script src="/gestion-tickets/assets/js/ajax.js"></script>  <!-- Para todos los modales -->
    
    <!-- Inyectar botón de tema oscuro en el header -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update year in footer
            const yearElement = document.getElementById('year');
            if (yearElement) {
                yearElement.textContent = new Date().getFullYear();
            }

            const headers = document.querySelectorAll('.header, .topbar');
            headers.forEach(header => {
                // Solo agregar si no existe ya
                if (!header.querySelector('#themeToggleBtn')) {
                    const themeBtn = document.createElement('button');
                    themeBtn.id = 'themeToggleBtn';
                    themeBtn.className = 'theme-toggle-btn';
                    if (userInfo) {
                        userInfo.parentElement.insertBefore(themeBtn, userInfo);
                    } else {
                        header.appendChild(themeBtn);
                    }
                }
            });
        });
    </script>
    
    <!-- Footer -->
    <footer class="app-footer">
        <p>&copy; <span id="year"><?php echo date('Y'); ?></span> Procesos & TI | Designed by Axel & Armando</p>
    </footer>
    <?php if(isset($additionalScripts)): ?>
        <?php foreach($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <style>
        /* Footer Minimalista */
        .app-footer {
            background: var(--bg-primary);
            color: var(--text-secondary);
            border-top: 1px solid var(--border);
            padding: 20px;
            text-align: center;
            margin-top: auto;
            transition: all 0.3s ease;
        }

        .app-footer p {
            margin: 0;
            font-size: 13px;
            letter-spacing: 0.3px;
            font-weight: 500;
        }

        .app-footer a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .app-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .app-footer {
                padding: 15px 10px;
            }

            .app-footer p {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .app-footer p {
                font-size: 11px;
                line-height: 1.6;
            }
        }
    </style>
</body>

</html>