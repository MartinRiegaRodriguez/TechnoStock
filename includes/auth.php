<?php
/**
 * Middleware de Autenticación
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/includes/helpers.php';

// Asegura que solo usuarios autenticados puedan acceder al archivo que lo incluya
verificarAutenticacion();
