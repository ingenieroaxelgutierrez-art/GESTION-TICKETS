<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?php echo $pageTitle ?? 'Sistema de Tickets'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/gestion-tickets/assets/favicon_rideco.png">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/gestion-tickets/assets/css/styles.css">
    
    <?php
    require_once __DIR__ . '/../../app/helpers/url.php';
    require_once __DIR__ . '/../../app/helpers/csrf.php';
    ?>
    
    <meta name="csrf-token" content="<?php echo get_csrf_token(); ?>">
    <script>
        const BASE_URL = "<?= base_url() ?>";
        const CSRF_TOKEN = "<?= get_csrf_token() ?>";
        
        console.log('[HEADER] BASE_URL definido como:', BASE_URL);
        console.log('[HEADER] CSRF_TOKEN definido:', CSRF_TOKEN ? 'SI' : 'NO');
        
        // Cargar tema desde localStorage
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Meta tags adicionales -->
    <meta name="description" content="Sistema de gestión de tickets">
    <meta name="robots" content="noindex, nofollow">

    <style>
        /* Estilos responsivos para tema oscuro y sidebar */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                width: 250px;
                height: 100vh;
                transition: left 0.3s ease;
                z-index: 1000;
                background: var(--bg-primary);
                border-right: 1px solid var(--border);
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content.sidebar-closed {
                margin-left: 0;
            }
            
            .header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
            }
        }
    </style>
</head>

<body>
