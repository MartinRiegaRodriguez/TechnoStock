<?php
/**
 * Procesamiento de Inicio de Sesión
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('modules/auth/login.php'));
    exit;
}

$correo = trim($_POST['correo'] ?? '');
$clave  = trim($_POST['clave'] ?? '');

// Validación en el servidor (PHP)
if (empty($correo) || empty($clave) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('danger', 'Debe ingresar un correo válido y su contraseña.');
    header('Location: ' . base_url('modules/auth/login.php'));
    exit;
}

try {
    $pdo = getDBConnection();

    // Consulta preparada segura contra inyección SQL
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, correo, clave, estado FROM usuario WHERE correo = :correo LIMIT 1");
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($clave, $usuario['clave'])) {
        // Verificar si la cuenta está activa
        if ($usuario['estado'] !== 'activo') {
            setFlashMessage('danger', 'Su cuenta de usuario se encuentra inactiva. Contacte al administrador.');
            header('Location: ' . base_url('modules/auth/login.php'));
            exit;
        }

        // Regenerar ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'nombre'     => $usuario['nombre'],
            'correo'     => $usuario['correo']
        ];

        setFlashMessage('success', '¡Bienvenido(a) a TecnoStock, ' . $usuario['nombre'] . '!');
        header('Location: ' . base_url('modules/dashboard/index.php'));
        exit;
    } else {
        setFlashMessage('danger', 'Credenciales incorrectas. Verifique su correo y contraseña.');
        header('Location: ' . base_url('modules/auth/login.php'));
        exit;
    }

} catch (PDOException $e) {
    setFlashMessage('danger', 'Error interno al procesar el ingreso: ' . $e->getMessage());
    header('Location: ' . base_url('modules/auth/login.php'));
    exit;
}
