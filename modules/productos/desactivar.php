<?php
/**
 * Desactivación Lógica de Productos (Soft Delete)
 * Regla de negocio #5: Un producto con movimientos no se elimina físicamente; se desactiva.
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$id_producto = (int)($_GET['id'] ?? 0);
$accion      = $_GET['accion'] ?? 'desactivar';

if ($id_producto <= 0) {
    setFlashMessage('danger', 'Identificador de producto inválido.');
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;
}

try {
    $pdo = getDBConnection();

    // Obtener información del producto
    $stmt = $pdo->prepare("SELECT codigo, nombre, estado FROM producto WHERE id_producto = :id LIMIT 1");
    $stmt->execute([':id' => $id_producto]);
    $prod = $stmt->fetch();

    if (!$prod) {
        setFlashMessage('danger', 'El producto no existe.');
        header('Location: ' . base_url('modules/productos/index.php'));
        exit;
    }

    $nuevoEstado = ($accion === 'activar') ? 'activo' : 'inactivo';

    $stmtUpdate = $pdo->prepare("UPDATE producto SET estado = :estado WHERE id_producto = :id");
    $stmtUpdate->execute([
        ':estado' => $nuevoEstado,
        ':id'     => $id_producto
    ]);

    if ($nuevoEstado === 'inactivo') {
        setFlashMessage('warning', "El producto '{$prod['nombre']}' ({$prod['codigo']}) fue desactivado exitosamente. Se mantiene en la base de datos para preservar la integridad del historial de movimientos.");
    } else {
        setFlashMessage('success', "El producto '{$prod['nombre']}' ({$prod['codigo']}) ha sido reactivado en el catálogo.");
    }

} catch (PDOException $e) {
    setFlashMessage('danger', 'Error al cambiar estado del producto: ' . $e->getMessage());
}

header('Location: ' . base_url('modules/productos/index.php'));
exit;
