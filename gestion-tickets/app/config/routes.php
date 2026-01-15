<?php
// app/config/routes.php

// Página inicial
$router->add('GET', '/', 'DashboardController@index');
// Alias para acceder al dashboard con /dashboard
$router->add('GET', '/dashboard', 'DashboardController@index');
// Dashboard específico del agente
$router->add('GET', '/dashboard/agent', 'DashboardController@agent');

// Perfil y otras rutas comunes
$router->add('GET', '/profile', 'DashboardController@profile');

// Auth
$router->add('GET',  '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET',  '/logout', 'AuthController@logout');
$router->add('GET',  '/auth/check', 'AuthController@check');
$router->add('POST', '/auth/change-password', 'AuthController@changePassword');

// Tickets (usuario normal)
$router->add('GET',  '/tickets', 'TicketController@index');
$router->add('GET',  '/tickets/create', 'TicketController@createForm');
$router->add('POST', '/tickets/create', 'TicketController@store');
$router->add('GET',  '/tickets/{id}', 'TicketController@show');

// Tickets acciones
$router->add('PUT',  '/tickets/{id}/status',    'TicketController@changeStatus');
$router->add('PUT',  '/tickets/{id}/priority',  'TicketController@changePriority');
$router->add('PUT',  '/tickets/{id}/assign',    'TicketController@assign');
$router->add('POST', '/tickets/{id}/comment',   'TicketController@addComment');
$router->add('GET',  '/tickets/attachment/{id}', 'TicketController@downloadAttachment');

// AJAX
$router->add('POST', '/ajax/tickets/create', 'TicketController@ajaxStore');

// API
$router->add('GET', '/api/departamentos/receptores', 'ApiController@receptores');
$router->add('GET', '/api/categorias/{dept_id}',     'ApiController@categoriasPorDepartamento');
$router->add('GET', '/api/agents-by-department/{dept_id}', 'ApiController@agentsByDepartment');
$router->add('GET', '/api/tickets',                  'ApiController@tickets');
$router->add('PUT', '/api/tickets/{id}',             'ApiController@updateTicket');
$router->add('GET', '/api/tickets/export',           'ApiController@exportTickets');

// Admin panel
$router->add('GET',  '/admin/users',          'AdminController@users');
$router->add('POST', '/admin/users',          'AdminController@storeUser');
$router->add('PUT',  '/admin/users/{id}',     'AdminController@updateUser');
$router->add('POST', '/admin/users/{id}/password', 'AdminController@changePassword');
$router->add('DELETE', '/admin/users/{id}',   'AdminController@deleteUser');

$router->add('GET',  '/admin/departments', 'AdminController@departments');
$router->add('POST', '/admin/departments', 'AdminController@storeDepartment');

$router->add('GET',  '/admin/categories', 'AdminController@categories');
$router->add('POST', '/admin/categories', 'AdminController@storeCategory');

// Notificaciones
$router->add('GET', '/notifications', 'DashboardController@notifications');

// Reportes y configuración
$router->add('GET', '/reports', 'DashboardController@reports');
$router->add('GET', '/settings', 'DashboardController@settings');

// Tickets del usuario
$router->add('GET', '/tickets/my-tickets', 'TicketController@index');
