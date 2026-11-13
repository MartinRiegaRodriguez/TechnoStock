<?php
/**
 * Formulario de Modificación de Producto
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$id_producto = (int)($_GET['id'] ?? 0);
if ($id_producto <= 0) {
    setFlashMessage('danger', 'Identificador de producto no válido.');
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;
}

$pdo = getDBConnection();

// Obtener datos del producto
$stmtProd = $pdo->prepare("SELECT * FROM producto WHERE id_producto = :id LIMIT 1");
$stmtProd->execute([':id' => $id_producto]);
$producto = $stmtProd->fetch();

if (!$producto) {
    setFlashMessage('danger', 'El producto solicitado no existe.');
    header('Location: ' . base_url('modules/productos/index.php'));
    exit;
}

// Obtener categorías para el selector
$stmtCat = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 'activo' ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll();

$tituloPagina = 'Modificar Producto: ' . $producto['codigo'];
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container py-4 flex-grow-1" style="max-width: 820px;">
    <?php renderFlashAlert(); ?>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Modificar Producto</h1>
            <p class="text-secondary small mb-0">Actualización de precio, categoría, descripción y límites de stock.</p>
        </div>
        <a href="<?= base_url('modules/productos/index.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('modules/productos/actualizar.php') ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="id_producto" value="<?= (int)$producto['id_producto'] ?>">

                <div class="row g-3">
                    <!-- Código del Producto (Identificador fijo con badge) -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Código del Producto</label>
                        <input type="text" class="form-control bg-light font-monospace" value="<?= e($producto['codigo']) ?>" readonly>
                        <div class="form-text small">El código único no es editable para asegurar la trazabilidad.</div>
                    </div>

                    <!-- Nombre del Producto -->
                    <div class="col-12 col-md-8">
                        <label for="nombre" class="form-label fw-semibold">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= e($producto['nombre']) ?>" required maxlength="150">
                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                    </div>

                    <!-- Categoría -->
                    <div class="col-12 col-md-6">
                        <label for="id_categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : '' ?>>
                                    <?= e($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione una categoría válida.</div>
                    </div>

                    <!-- Precio -->
                    <div class="col-12 col-md-6">
                        <label for="precio" class="form-label fw-semibold">Precio de Venta ($ CLP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="precio" name="precio" value="<?= (int)$producto['precio'] ?>" min="0" step="1" required>
                            <div class="invalid-feedback">El precio debe ser mayor o igual a 0.</div>
                        </div>
                    </div>

                    <!-- Stock Actual (Informativo con enlace a Movimiento) -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Stock Actual</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light fw-bold" value="<?= (int)$producto['stock_actual'] ?> unidades" readonly>
                            <a href="<?= base_url('modules/movimientos/registrar.php?id_producto=' . $producto['id_producto']) ?>" class="btn btn-outline-secondary" title="Ajustar mediante movimiento">
                                <i class="bi bi-arrow-left-right"></i>
                            </a>
                        </div>
                        <div class="form-text small">El stock se actualiza mediante registro de entradas y salidas.</div>
                    </div>

                    <!-- Stock Mínimo -->
                    <div class="col-12 col-md-4">
                        <label for="stock_minimo" class="form-label fw-semibold">Stock Mínimo de Alerta <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="<?= (int)$producto['stock_minimo'] ?>" min="0" step="1" required>
                        <div class="form-text small">Umbral para alertas visuales.</div>
                        <div class="invalid-feedback">El stock mínimo no puede ser negativo.</div>
                    </div>

                    <!-- Estado Comercial -->
                    <div class="col-12 col-md-4">
                        <label for="estado" class="form-label fw-semibold">Estado Comercial <span class="text-danger">*</span></label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo" <?= ($producto['estado'] === 'activo') ? 'selected' : '' ?>>Activo (Comercializable)</option>
                            <option value="inactivo" <?= ($producto['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo (Desactivado)</option>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= e($producto['descripcion']) ?></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('modules/productos/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i> Actualizar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
