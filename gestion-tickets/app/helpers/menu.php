<?php
/**
 * Helper para generar el menú basado en rol
 * Controla qué elementos se muestran según el rol del usuario
 */

function getMenuByRole($userRole = null) {
    // Si no se especifica rol, obtenerlo de Auth
    if ($userRole === null) {
        require_once __DIR__ . '/../core/Auth.php';
        $userRole = Auth::role();
    }
    
    $baseUrl = defined('BASE_URL') ? BASE_URL : (isset($BASE_URL) ? $BASE_URL : '/gestion-tickets');
    
    // Definir menú para cada rol
    $menus = [
        'admin' => [
            [
                'icon' => 'fa-chart-line',
                'label' => 'Dashboard',
                'url' => $baseUrl . '/dashboard',
                'id' => 'menu-dashboard'
            ],
            [
                'icon' => 'fa-ticket-alt',
                'label' => 'Tickets',
                'url' => $baseUrl . '/tickets',
                'id' => 'menu-tickets'
            ],
            [
                'icon' => 'fa-users',
                'label' => 'Usuarios',
                'url' => $baseUrl . '/admin/users',
                'id' => 'menu-users'
            ],
            [
                'icon' => 'fa-building',
                'label' => 'Departamentos',
                'url' => $baseUrl . '/admin/departments',
                'id' => 'menu-departments'
            ],
            [
                'icon' => 'fa-list',
                'label' => 'Categorías',
                'url' => $baseUrl . '/admin/categories',
                'id' => 'menu-categories'
            ],
            [
                'icon' => 'fa-chart-bar',
                'label' => 'Reportes',
                'url' => $baseUrl . '/reports',
                'id' => 'menu-reports'
            ],
            [
                'icon' => 'fa-cog',
                'label' => 'Configuración',
                'url' => $baseUrl . '/settings',
                'id' => 'menu-settings'
            ],
            [
                'icon' => 'fa-sign-out-alt',
                'label' => 'Cerrar Sesión',
                'url' => $baseUrl . '/logout',
                'id' => 'menu-logout',
                'divider_before' => true
            ]
        ],
        'agent' => [
            [
                'icon' => 'fa-chart-line',
                'label' => 'Dashboard',
                'url' => $baseUrl . '/dashboard/agent',
                'id' => 'menu-dashboard'
            ],
            [
                'icon' => 'fa-ticket-alt',
                'label' => 'Tickets',
                'url' => $baseUrl . '/tickets',
                'id' => 'menu-tickets'
            ],
            [
                'icon' => 'fa-chart-bar',
                'label' => 'Reportes',
                'url' => $baseUrl . '/reports',
                'id' => 'menu-reports'
            ],
            [
                'icon' => 'fa-user-circle',
                'label' => 'Perfil',
                'url' => $baseUrl . '/profile',
                'id' => 'menu-profile'
            ],
            [
                'icon' => 'fa-cog',
                'label' => 'Configuración',
                'url' => $baseUrl . '/settings',
                'id' => 'menu-settings'
            ],
            [
                'icon' => 'fa-sign-out-alt',
                'label' => 'Cerrar Sesión',
                'url' => $baseUrl . '/logout',
                'id' => 'menu-logout',
                'divider_before' => true
            ]
        ],
        'user' => [
            [
                'icon' => 'fa-plus-circle',
                'label' => 'Crear Ticket',
                'url' => $baseUrl . '/tickets/create',
                'id' => 'menu-create-ticket'
            ],
            [
                'icon' => 'fa-ticket-alt',
                'label' => 'Mis Tickets',
                'url' => $baseUrl . '/tickets',
                'id' => 'menu-tickets'
            ],
            [
                'icon' => 'fa-user-circle',
                'label' => 'Perfil',
                'url' => $baseUrl . '/profile',
                'id' => 'menu-profile'
            ],
            [
                'icon' => 'fa-cog',
                'label' => 'Configuración',
                'url' => $baseUrl . '/settings',
                'id' => 'menu-settings'
            ],
            [
                'icon' => 'fa-sign-out-alt',
                'label' => 'Cerrar Sesión',
                'url' => $baseUrl . '/logout',
                'id' => 'menu-logout',
                'divider_before' => true
            ]
        ]
    ];
    
    return $menus[$userRole] ?? $menus['user']; // Retorna menú de user por defecto
}

/**
 * Renderiza el HTML del menú
 */
function renderMenu($userRole = null) {
    $menu = getMenuByRole($userRole);
    $html = '<ul class="sidebar-menu">';
    
    foreach ($menu as $item) {
        if (!empty($item['divider_before'])) {
            $html .= '<li class="menu-divider"></li>';
        }
        
        $html .= '<li>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" id="' . $item['id'] . '">';
        $html .= '<span class="menu-icon"><i class="fas ' . $item['icon'] . '"></i></span>';
        $html .= '<span>' . htmlspecialchars($item['label']) . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

/**
 * Verifica si un usuario puede acceder a una ruta según su rol
 */
function canAccessRoute($userRole, $routePath) {
    // Mapeo de rutas permitidas por rol
    $permissions = [
        'admin' => [
            '/dashboard',
            '/tickets',
            '/admin/users',
            '/reports',
            '/profile',
            '/settings',
            '/logout'
        ],
        'agent' => [
            '/tickets',
            '/reports',
            '/profile',
            '/settings',
            '/logout'
        ],
        'user' => [
            '/tickets',
            '/tickets/create',
            '/profile',
            '/settings',
            '/logout'
        ]
    ];
    
    $allowed = $permissions[$userRole] ?? [];
    
    // Verificar si la ruta coincide (con o sin parámetros)
    foreach ($allowed as $route) {
        if ($routePath === $route || strpos($routePath, $route . '/') === 0) {
            return true;
        }
    }
    
    return false;
}
?>
