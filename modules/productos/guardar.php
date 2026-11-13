<?php
/**
 * Procesamiento de Inserción de Producto con Validaciones Servidor (PHP)
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

$codigo       = strtoupper(trim($_POST['codigo'] ?? ''));
$nombre       = trim($_POST['nombre'] ?? '');
$id_categoria = (int)($_POST['id_categoria'] ?? 0);
$precio       = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
$stock_actual = filter_var($_POST['stock_actual'] ?? null, FILTER_VALIDATE_INT);
$stock_minimo = filter_var($_POST['stock_minimo'] ?? null, FILTER_VALIDATE_INT);
$descripcion  = trim($_POST['descripcion'] ?? '');

$errores = [];

// 1. Validaciones en PHP (Replicando y asegurando integridad de negocio)
if (empty($codigo)) {
    $errores[] = 'El código del producto es obligatorio.';
}

if (empty($nombre)) {
    $errores[] = 'El nombre del producto es obligatorio.';
}

if ($id_categoria <= 0) {
    $errores[] = 'Debe seleccionar una categoría válida.';
}

if ($precio === false || $precio < 0) {
    $errores[] = 'El precio no puede ser negativo ni inválido.';
}

if ($stock_actual === false || $stock_actual < 0) {
    $errores[] = 'El stock actual no puede ser negativo.';
}

if ($stock_minimo === false || $stock_minimo < 0) {
    $errores[] = 'El stock mínimo no puede ser negativo.';
}

if (!empty($errores)) {
    setFlashMessage('danger', implode(' ', $errores));
    header('Location: ' . base_url('modules/productos/crear.php'));
    exit;
}

try {
    $pdo = getDBConnection();

    // 2. Regla de negocio #1: El código de producto no puede repetirse
    $stmtCheck = $pdo->prepare("SELECT id_producto FROM producto WHERE codigo = :codigo LIMIT 1");
    $stmtCheck->execute([':codigo' => $codigo]);
    if ($stmtCheck->fetch()) {
        setFlashMessage('danger', "El código '{$codigo}' ya está registrado en otro producto. Debe ser único.");
        header('Location: ' . base_url('modules/productos/crear.php'));
        exit;
    }

    // 3. Inicio de transacción para garantizar consistencia
    $pdo->beginTransaction();

    $stmtInsert = $pdo->prepare("
        INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria)
        VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, 'activo', :id_categoria)
    ");
    $stmtInsert->execute([
        ':codigo'       => $codigo,
        ':nombre'       => $nombre,
        ':descripcion'  => $descripcion ?: null,
        ':precio'       => $precio,
        ':stock_actual' => $stock_actual,
        ':stock_minimo' => $stock_minimo,
        ':id_categoria' => $id_categoria
    ]);

    $idProductoNuevo = (int)$pdo->lastInsertId();

    // Si se especificó un stock inicial mayor a 0, se registra el movimiento correspondiente
    if ($stock_actual > 0) {
        $usuario = usuarioActual();
        $stmtMov = $pdo->prepare("
            INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
            VALUES ('entrada', :cantidad, 'Stock inicial al registrar producto', :id_producto, :id_usuario)
        ");
        $stmtMov->execute([
            ':cantidad'    => $stock_actual,
            ':id_producto' => $idProductoNuevo,
            ':id_usuario'  => $usuario['id_usuario']
        ]);
    }

    $pdo->commit();

    setFlashMessage('success', "Producto '{$nombre}' ({$codigo}) registrado exitosamente.");
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlashMessage('danger', 'Error en base de datos al guardar producto: ' . $e->getMessage());
    header('Location: ' . base_url('modules/productos/crear.php'));
    exit;
}
