<?php
/**
 * Funciones Auxiliares y Seguridad
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitiza una cadena para prevenir ataques XSS (Cross-Site Scripting).
 *
 * @param mixed $valor
 * @return string
 */
function e($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna la URL base del proyecto dinámicamente,
 * funcionando tanto en 'http://localhost/technostock/' como en 'http://localhost:8000/'.
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string {
    // Detecta la ruta raíz del script en ejecución
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Si estamos en un subdirectorio como /modules/productos, subimos a la raíz
    $parts = explode('/', trim($scriptDir, '/'));
    $rootSegments = [];
    foreach ($parts as $segment) {
        if ($segment === 'modules' || $segment === 'includes' || $segment === 'config') {
            break;
        }
        if ($segment !== '') {
            $rootSegments[] = $segment;
        }
    }
    
    $base = '/' . implode('/', $rootSegments);
    if ($base !== '/') {
        $base .= '/';
    }

    return $base . ltrim($path, '/');
}

/**
 * Guarda un mensaje flash para ser mostrado en la siguiente petición.
 *
 * @param string $tipo 'success', 'danger', 'warning', 'info'
 * @param string $mensaje
 */
function setFlashMessage(string $tipo, string $mensaje): void {
    $_SESSION['flash'] = [
        'tipo'    => $tipo,
        'mensaje' => $mensaje
    ];
}

/**
 * Obtiene y limpia el mensaje flash actual.
 *
 * @return array|null
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Renderiza el mensaje flash como una alerta responsiva de Bootstrap 5.
 */
function renderFlashAlert(): void {
    $flash = getFlashMessage();
    if ($flash) {
        $tipo = e($flash['tipo']);
        $mensaje = e($flash['mensaje']);
        echo "
        <div class='alert alert-{$tipo} alert-dismissible fade show shadow-sm' role='alert'>
            <strong>" . ($tipo === 'success' ? '✔ Éxito:' : ($tipo === 'danger' ? '✖ Error:' : 'ℹ Aviso:')) . "</strong> {$mensaje}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Cerrar'></button>
        </div>";
    }
}

/**
 * Formatea un número como moneda chilena / estándar ($ 15.990).
 *
 * @param float|int|string $monto
 * @return string
 */
function formatearMoneda($monto): string {
    return '$ ' . number_format((float)$monto, 0, ',', '.');
}

/**
 * Verifica si el usuario ha iniciado sesión. Si no, redirige al login.
 */
function verificarAutenticacion(): void {
    if (!isset($_SESSION['usuario'])) {
        setFlashMessage('warning', 'Debe iniciar sesión para acceder a este módulo.');
        header('Location: ' . base_url('modules/auth/login.php'));
        exit;
    }
}

/**
 * Retorna los datos del usuario logueado en sesión.
 *
 * @return array|null
 */
function usuarioActual(): ?array {
    return $_SESSION['usuario'] ?? null;
}
