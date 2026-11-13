<?php
/**
 * Procesamiento de Actualización de Producto (PHP + PDO)
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;
}

$id_producto  = (int)($_POST['id_producto'] ?? 0);
$nombre       = trim($_POST['nombre'] ?? '');
$id_categoria = (int)($_POST['id_categoria'] ?? 0);
$precio       = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
$stock_minimo = filter_var($_POST['stock_minimo'] ?? null, FILTER_VALIDATE_INT);
$estado       = in_array($_POST['estado'] ?? '', ['activo', 'inactivo']) ? $_POST['estado'] : 'activo';
$descripcion  = trim($_POST['descripcion'] ?? '');

$errores = [];

if ($id_producto <= 0) {
    $errores[] = 'ID de producto inválido.';
}
if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio.';
}
if ($id_categoria <= 0) {
    $errores[] = 'Debe seleccionar una categoría válida.';
}
if ($precio === false || $precio < 0) {
    $errores[] = 'El precio no puede ser negativo.';
}
if ($stock_minimo === false || $stock_minimo < 0) {
    $errores[] = 'El stock mínimo no puede ser negativo.';
}

if (!empty($errores)) {
    setFlashMessage('danger', implode(' ', $errores));
    header('Location: ' . base_url('modules/productos/editar.php?id=' . $id_producto));
    exit;
}

try {
    $pdo = getDBConnection();

    $stmtUpdate = $pdo->prepare("
        UPDATE producto
        SET nombre = :nombre,
            descripcion = :descripcion,
            precio = :precio,
            stock_minimo = :stock_minimo,
            estado = :estado,
            id_categoria = :id_categoria
        WHERE id_producto = :id_producto
    ");

    $stmtUpdate->execute([
        ':nombre'       => $nombre,
        ':descripcion'  => $descripcion ?: null,
        ':precio'       => $precio,
        ':stock_minimo' => $stock_minimo,
        ':estado'       => $estado,
        ':id_categoria' => $id_categoria,
        ':id_producto'  => $id_producto
    ]);

    setFlashMessage('success', "Producto '{$nombre}' actualizado con éxito.");
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;

} catch (PDOException $e) {
    setFlashMessage('danger', 'Error al actualizar el producto: ' . $e->getMessage());
    header('Location: ' . base_url('modules/productos/editar.php?id=' . $id_producto));
    exit;
}
