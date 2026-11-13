<?php
/**
 * Catálogo de Productos y Búsqueda
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$pdo = getDBConnection();

// Parámetros de búsqueda y filtros
$busqueda = trim($_GET['q'] ?? '');
$filtroEstado = $_GET['estado'] ?? 'activo'; // Por defecto muestra activos

// Construcción dinámica de consulta preparada
$sql = "
    SELECT p.id_producto, p.codigo, p.nombre, p.descripcion, p.precio, 
           p.stock_actual, p.stock_minimo, p.estado, 
           c.id_categoria, c.nombre AS categoria,
           (SELECT COUNT(*) FROM movimiento m WHERE m.id_producto = p.id_producto) AS total_movimientos
    FROM producto p
    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
    WHERE 1=1
";
$params = [];

if ($filtroEstado !== 'todos') {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE :busq1 OR p.codigo LIKE :busq2 OR c.nombre LIKE :busq3)";
    $term = "%{$busqueda}%";
    $params[':busq1'] = $term;
    $params[':busq2'] = $term;
    $params[':busq3'] = $term;
}

$sql .= " ORDER BY p.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$tituloPagina = 'Catálogo de Productos';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container-fluid px-lg-4 py-4 flex-grow-1">
    <?php renderFlashAlert(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Catálogo de Productos</h1>
            <p class="text-secondary small mb-0">Gestión de existencias, precios y control de estados de comercialización.</p>
        </div>
        <a href="<?= base_url('modules/productos/crear.php') ?>" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i> Registrar Nuevo Producto
        </a>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('modules/productos/index.php') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-secondary"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Buscar por código, nombre o categoría..." value="<?= e($busqueda) ?>">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-3">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="activo" <?= $filtroEstado === 'activo' ? 'selected' : '' ?>>Solo Activos</option>
                        <option value="inactivo" <?= $filtroEstado === 'inactivo' ? 'selected' : '' ?>>Solo Inactivos (Desactivados)</option>
                        <option value="todos" <?= $filtroEstado === 'todos' ? 'selected' : '' ?>>Todos los Estados</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                    <?php if (!empty($busqueda) || $filtroEstado !== 'activo'): ?>
                        <a href="<?= base_url('modules/productos/index.php') ?>" class="btn btn-outline-secondary" title="Limpiar Filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla Responsiva de Productos -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-boxes me-2"></i>Listado de Existencias (<?= count($productos) ?> encontrados)</span>
            <input type="text" id="filtroRapidoTabla" class="form-control form-control-sm w-auto d-none d-sm-inline-block" placeholder="Filtrado rápido en vista...">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Producto / Descripción</th>
                            <th>Categoría</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock Actual</th>
                            <th class="text-center">Stock Mínimo</th>
                            <th class="text-center">Alerta Stock</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    No se encontraron productos que coincidan con la búsqueda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $p): 
                                $esBajoStock = ($p['stock_actual'] < $p['stock_minimo']);
                            ?>
                            <tr class="<?= ($esBajoStock && $p['estado'] === 'activo') ? 'table-row-stock-critico' : '' ?>">
                                <td>
                                    <span class="badge bg-dark font-monospace"><?= e($p['codigo']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($p['nombre']) ?></div>
                                    <small class="text-secondary d-block text-truncate" style="max-width: 280px;">
                                        <?= e($p['descripcion'] ?: 'Sin descripción') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark"><?= e($p['categoria']) ?></span>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= formatearMoneda($p['precio']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="fs-6 fw-bold <?= $esBajoStock ? 'text-danger' : 'text-dark' ?>">
                                        <?= $p['stock_actual'] ?>
                                    </span>
                                </td>
                                <td class="text-center text-secondary">
                                    <?= $p['stock_minimo'] ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($esBajoStock && $p['estado'] === 'activo'): ?>
                                        <span class="badge badge-stock-bajo">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Bajo Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-check-circle me-1"></i>Óptimo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['estado'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Botón rápido para movimiento de entrada/salida -->
                                        <a href="<?= base_url('modules/movimientos/registrar.php?id_producto=' . $p['id_producto']) ?>" class="btn btn-outline-secondary" title="Registrar movimiento">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </a>

                                        <!-- Modificar precio, descripción, categoría y stock -->
                                        <a href="<?= base_url('modules/productos/editar.php?id=' . $p['id_producto']) ?>" class="btn btn-outline-primary" title="Editar producto">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Desactivar producto (Soft Delete según Regla de Negocio #5) -->
                                        <?php if ($p['estado'] === 'activo'): ?>
                                            <a href="<?= base_url('modules/productos/desactivar.php?id=' . $p['id_producto'] . '&accion=desactivar') ?>" 
                                               class="btn btn-outline-danger btn-desactivar-producto" 
                                               data-nombre="<?= e($p['nombre']) ?>"
                                               title="Desactivar producto (baja lógica)">
                                                <i class="bi bi-slash-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('modules/productos/desactivar.php?id=' . $p['id_producto'] . '&accion=activar') ?>" 
                                               class="btn btn-outline-success" 
                                               title="Reactivar producto">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
