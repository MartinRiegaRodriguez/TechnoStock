<?php
/**
 * Procesamiento de Movimientos de Inventario (PHP 8 + Transacciones PDO)
 * 
 * Reglas de negocio críticas implementadas:
 * - Regla 2: Cantidad estrictamente positiva (> 0).
 * - Regla 3: Trazabilidad completa (fecha, tipo, cantidad, producto y usuario en sesión).
 * - Regla 4: Una salida no puede dejar el stock bajo cero.
 * - Regla 6: Actualización automática y atómica del stock mediante transacciones PDO.
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('modules/movimientos/index.php'));
    exit;
}

$id_producto = (int)($_POST['id_producto'] ?? 0);
$tipo        = in_array($_POST['tipo'] ?? '', ['entrada', 'salida']) ? $_POST['tipo'] : null;
$cantidad    = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
$observacion = trim($_POST['observacion'] ?? '');

$errores = [];

if ($id_producto <= 0) {
    $errores[] = 'Debe seleccionar un producto válido.';
}

if (!$tipo) {
    $errores[] = 'El tipo de movimiento debe ser Entrada o Salida.';
}

if ($cantidad === false || $cantidad <= 0) {
    $errores[] = 'La cantidad debe ser un número entero mayor a cero.';
}

if (!empty($errores)) {
    setFlashMessage('danger', implode(' ', $errores));
    header("Location: " . base_url("modules/movimientos/registrar.php?id_producto={$id_producto}&tipo={$tipo}"));
    exit;
}

try {
    $pdo = getDBConnection();
    $usuario = usuarioActual();

    // Iniciar Transacción PDO para asegurar consistencia ACID
    $pdo->beginTransaction();

    // Bloqueo de fila (FOR UPDATE) para evitar condiciones de carrera concurrentes
    $stmtProd = $pdo->prepare("
        SELECT id_producto, codigo, nombre, stock_actual, stock_minimo, estado 
        FROM producto 
        WHERE id_producto = :id 
        FOR UPDATE
    ");
    $stmtProd->execute([':id' => $id_producto]);
    $producto = $stmtProd->fetch();

    if (!$producto) {
        $pdo->rollBack();
        setFlashMessage('danger', 'El producto seleccionado no existe.');
        header('Location: ' . base_url('modules/movimientos/registrar.php'));
        exit;
    }

    if ($producto['estado'] !== 'activo') {
        $pdo->rollBack();
        setFlashMessage('danger', "El producto '{$producto['nombre']}' se encuentra inactivo y no admite nuevos movimientos.");
        header('Location: ' . base_url('modules/movimientos/registrar.php'));
        exit;
    }

    // Regla de negocio #4: Una salida no puede dejar el stock bajo cero
    if ($tipo === 'salida') {
        if ($producto['stock_actual'] < $cantidad) {
            $pdo->rollBack();
            setFlashMessage('danger', "Stock insuficiente para '{$producto['nombre']}'. Stock disponible: {$producto['stock_actual']} u., solicitado: {$cantidad} u. Una salida no puede dejar el stock bajo cero.");
            header("Location: " . base_url("modules/movimientos/registrar.php?id_producto={$id_producto}&tipo=salida"));
            exit;
        }
        $nuevoStock = $producto['stock_actual'] - $cantidad;
    } else {
        // Entrada
        $nuevoStock = $producto['stock_actual'] + $cantidad;
    }

    // 1. Insertar registro de movimiento con usuario responsable de sesión
    $stmtMov = $pdo->prepare("
        INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
        VALUES (NOW(), :tipo, :cantidad, :observacion, :id_producto, :id_usuario)
    ");
    $stmtMov->execute([
        ':tipo'        => $tipo,
        ':cantidad'    => $cantidad,
        ':observacion' => $observacion ?: null,
        ':id_producto' => $id_producto,
        ':id_usuario'  => $usuario['id_usuario']
    ]);

    // 2. Regla de negocio #6: El stock debe actualizarse automáticamente
    $stmtUpdate = $pdo->prepare("
        UPDATE producto 
        SET stock_actual = :nuevo_stock 
        WHERE id_producto = :id_producto
    ");
    $stmtUpdate->execute([
        ':nuevo_stock' => $nuevoStock,
        ':id_producto' => $id_producto
    ]);

    // Confirmar transacción
    $pdo->commit();

    // Mensaje de éxito con advertencia de stock bajo si corresponde
    $mensajeExito = "Movimiento de {$tipo} exitoso (+{$cantidad} u. para [{$producto['codigo']}] {$producto['nombre']}). Nuevo stock: {$nuevoStock} u.";
    if ($nuevoStock < $producto['stock_minimo']) {
        $mensajeExito .= " &mdash; ⚠️ ¡Alerta! El stock actual ha quedado por debajo del mínimo permitido ({$producto['stock_minimo']} u.).";
        setFlashMessage('warning', $mensajeExito);
    } else {
        setFlashMessage('success', $mensajeExito);
    }

    header('Location: ' . base_url('modules/movimientos/index.php'));
    exit;

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlashMessage('danger', 'Error al procesar el movimiento en la base de datos: ' . $e->getMessage());
    header("Location: " . base_url("modules/movimientos/registrar.php?id_producto={$id_producto}&tipo={$tipo}"));
    exit;
}
