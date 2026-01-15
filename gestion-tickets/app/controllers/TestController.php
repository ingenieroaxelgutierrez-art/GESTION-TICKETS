<?php
// app/controllers/TestController.php

class TestController extends Controller
{
    public function __construct()
    {
        // Permitir acceso solo si está autenticado (opcional para pruebas)
        Auth::requireRole(['admin', 'agent', 'user']);
    }

    public function backendTest()
    {
        $pageTitle = 'Pruebas de Backend';
        $this->view('test/backend-test', ['pageTitle' => $pageTitle]);
    }
}
