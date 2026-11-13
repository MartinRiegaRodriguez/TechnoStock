<?php
/**
 * Cierre de Sesión Seguro
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/includes/helpers.php';

// Limpiar todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Iniciar nueva sesión únicamente para el mensaje de despedida
session_start();
setFlashMessage('info', 'Ha cerrado sesión correctamente. ¡Hasta pronto!');

header('Location: ' . base_url('modules/auth/login.php'));
exit;
