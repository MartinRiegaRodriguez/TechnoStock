<?php
/**
 * Punto de entrada principal
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/includes/helpers.php';

if (isset($_SESSION['usuario'])) {
    header('Location: ' . base_url('modules/dashboard/index.php'));
} else {
    header('Location: ' . base_url('modules/auth/login.php'));
}
exit;
