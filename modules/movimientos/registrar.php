<?php
/**
 * Formulario para Registrar Entrada o Salida de Inventario
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$pdo = getDBConnection();

// Parámetros opcionales para preselección
$idProductoPre = (int)($_GET['id_producto'] ?? 0);
$tipoPre       = in_array($_GET['tipo'] ?? '', ['entrada', 'salida']) ? $_GET['tipo'] : 'entrada';

// Obtener solo productos activos para registrar movimientos
$stmtProds = $pdo->query("
    SELECT p.id_producto, p.codigo, p.nombre, p.stock_actual, p.stock_minimo, c.nombre AS categoria
    FROM producto p
    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
    WHERE p.estado = 'activo'
    ORDER BY p.nombre ASC
");
$productosActivos = $stmtProds->fetchAll();

$tituloPagina = 'Registrar Movimiento';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container py-4 flex-grow-1" style="max-width: 760px;">
    <?php renderFlashAlert(); ?>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Registrar Entrada / Salida</h1>
            <p class="text-secondary small mb-0">Controle el flujo de existencias actualizando automáticamente el stock.</p>
        </div>
        <a href="<?= base_url('modules/movimientos/index.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Historial
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('modules/movimientos/guardar.php') ?>" method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <!-- Selección de Producto -->
                    <div class="col-12">
                        <label for="select_id_producto" class="form-label fw-semibold">Producto <span class="text-danger">*</span></label>
                        <select class="form-select" id="select_id_producto" name="id_producto" required>
                            <option value="">-- Seleccionar producto --</option>
                            <?php foreach ($productosActivos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>" 
                                        data-stock="<?= $p['stock_actual'] ?>"
                                        <?= ($idProductoPre === (int)$p['id_producto']) ? 'selected' : '' ?>>
                                    [<?= e($p['codigo']) ?>] <?= e($p['nombre']) ?> &mdash; (Stock: <?= $p['stock_actual'] ?> u.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-1 d-flex justify-content-between align-items-center">
                            <span id="info_stock_actual" class="small"></span>
                            <span class="text-muted small">Solo productos en estado activo.</span>
                        </div>
                        <div class="invalid-feedback">Debe seleccionar un producto.</div>
                    </div>

                    <!-- Tipo de Movimiento -->
                    <div class="col-12 col-md-6">
                        <label for="select_tipo_movimiento" class="form-label fw-semibold">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <select class="form-select" id="select_tipo_movimiento" name="tipo" required>
                            <option value="entrada" <?= $tipoPre === 'entrada' ? 'selected' : '' ?>>➕ Entrada (Aumenta stock)</option>
                            <option value="salida" <?= $tipoPre === 'salida' ? 'selected' : '' ?>>➖ Salida (Disminuye stock)</option>
                        </select>
                        <div class="form-text small">Regla: Una salida no puede dejar el stock bajo cero.</div>
                    </div>

                    <!-- Cantidad -->
                    <div class="col-12 col-md-6">
                        <label for="input_cantidad_mov" class="form-label fw-semibold">Cantidad de Unidades <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="input_cantidad_mov" name="cantidad" min="1" step="1" placeholder="Ej: 10" required>
                        <div class="invalid-feedback">Ingrese una cantidad mayor a 0 y que no supere el stock disponible si es salida.</div>
                    </div>

                    <!-- Observación / Motivo -->
                    <div class="col-12">
                        <label for="observacion" class="form-label fw-semibold">Observación o Motivo</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="3" placeholder="Ej: Factura Proveedor #8491, Venta cliente, Devolución o Ajuste de inventario"></textarea>
                        <div class="form-text small">Quedará registrado junto al usuario responsable actual: <strong><?= e(usuarioActual()['nombre']) ?></strong></div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('modules/movimientos/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-circle"></i> Procesar Movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
