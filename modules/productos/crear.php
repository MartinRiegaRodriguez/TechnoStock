<?php
/**
 * Formulario de Registro de Nuevo Producto
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$pdo = getDBConnection();

// Obtener categorías activas para el select
$stmtCat = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 'activo' ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll();

$tituloPagina = 'Registrar Producto';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container py-4 flex-grow-1" style="max-width: 820px;">
    <?php renderFlashAlert(); ?>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Registrar Nuevo Producto</h1>
            <p class="text-secondary small mb-0">Complete la ficha técnica para incorporar el producto al catálogo.</p>
        </div>
        <a href="<?= base_url('modules/productos/index.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('modules/productos/guardar.php') ?>" method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <!-- Código Único -->
                    <div class="col-12 col-md-4">
                        <label for="codigo" class="form-label fw-semibold">Código del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase font-monospace" id="codigo" name="codigo" placeholder="Ej: CAB-007" required maxlength="50">
                        <div class="form-text small">Debe ser único en el sistema.</div>
                        <div class="invalid-feedback">Por favor ingrese un código único válido.</div>
                    </div>

                    <!-- Nombre del Producto -->
                    <div class="col-12 col-md-8">
                        <label for="nombre" class="form-label fw-semibold">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Cable USB-C a USB-C 2 metros" required maxlength="150">
                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                    </div>

                    <!-- Categoría -->
                    <div class="col-12 col-md-6">
                        <label for="id_categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <option value="">-- Seleccionar categoría --</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= e($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione una categoría.</div>
                    </div>

                    <!-- Precio -->
                    <div class="col-12 col-md-6">
                        <label for="precio" class="form-label fw-semibold">Precio de Venta ($ CLP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="precio" name="precio" placeholder="0" min="0" step="1" required>
                            <div class="invalid-feedback">El precio debe ser un número mayor o igual a 0.</div>
                        </div>
                    </div>

                    <!-- Stock Inicial -->
                    <div class="col-12 col-md-6">
                        <label for="stock_actual" class="form-label fw-semibold">Stock Inicial <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock_actual" name="stock_actual" value="0" min="0" step="1" required>
                        <div class="form-text small">Existencias físicas disponibles al momento del registro.</div>
                        <div class="invalid-feedback">El stock actual no puede ser negativo.</div>
                    </div>

                    <!-- Stock Mínimo -->
                    <div class="col-12 col-md-6">
                        <label for="stock_minimo" class="form-label fw-semibold">Stock Mínimo de Alerta <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="5" min="0" step="1" required>
                        <div class="form-text small">Nivel de inventario que disparará la alerta de reposición.</div>
                        <div class="invalid-feedback">El stock mínimo no puede ser negativo.</div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-semibold">Descripción Detallada</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Características técnicas, compatibilidad, color o garantía..."></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('modules/productos/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-save"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
