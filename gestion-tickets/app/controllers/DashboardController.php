<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../models/Ticket.php';

class DashboardController extends Controller
{
    private $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
    }

    // Método simple para mostrar perfil (placeholder)
    public function profile()
    {
        $this->requireAuth();
        
        $pageTitle = 'Mi Perfil';
        $this->view('dashboard/profile', ['pageTitle' => $pageTitle]);
    }

    public function reports()
    {
        $this->requireAuth();
        
        $pageTitle = 'Reportes';
        $this->view('dashboard/reports', ['pageTitle' => $pageTitle]);
    }

    public function settings()
    {
        $this->requireAuth();
        
        $pageTitle = 'Configuración';
        $this->view('dashboard/settings', ['pageTitle' => $pageTitle]);
    }

    public function index()
    {
        // Verificar autenticación
        $this->requireAuth();

        $userRole = Auth::role();
        
        // Redirigir al dashboard personalizado según el rol
        switch ($userRole) {
            case 'admin':
                // Admin ve el dashboard principal
                $pageTitle = 'Dashboard Admin';
                $this->view('dashboard/dashboard', ['pageTitle' => $pageTitle]);
                break;
            case 'agent':
                // Agentes ven el dashboard de agente
                $pageTitle = 'Dashboard Agente';
                $this->view('dashboard/dashboard-agent', ['pageTitle' => $pageTitle]);
                break;
            case 'user':
                // Usuarios ven el dashboard de usuario
                $pageTitle = 'Dashboard';
                $this->view('dashboard/dashboard-user', ['pageTitle' => $pageTitle]);
                break;
            default:
                $this->redirect('/gestion-tickets/tickets');
        }
    }

    public function notifications()
    {
        $this->requireAuth();
        
        $pageTitle = 'Notificaciones';
        $this->view('dashboard/notifications', ['pageTitle' => $pageTitle]);
    }

    // Método específico para dashboard del agente
    public function agent()
    {
        Auth::requireRole(['agent']); // Solo agentes
        
        $pageTitle = 'Dashboard Agente';
        $this->view('dashboard/dashboard-agent', ['pageTitle' => $pageTitle]);
    }

}
