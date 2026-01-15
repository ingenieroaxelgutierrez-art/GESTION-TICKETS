<?php
// Compatibilidad: este archivo mantiene compatibilidad con includes antiguos.
require_once __DIR__ . '/Department.php';

// Si alguna parte del código incluye 'Departament.php' y espera la clase
// 'Departament', ofrecemos una clase alias que extiende la clase correcta.
if (!class_exists('Departament') && class_exists('Department')) {
    class Departament extends Department {}
}