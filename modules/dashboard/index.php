<?php
/**
 * Dashboard Principal
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$pdo = getDBConnection();

// Estadísticas generales para las tarjetas KPI
$totalProductos   = $pdo->query("SELECT COUNT(*) FROM producto WHERE estado = 'activo'")->fetchColumn();
$totalCategorias  = $pdo->query("SELECT COUNT(*) FROM categoria WHERE estado = 'activo'")->fetchColumn();
$totalMovimientos = $pdo->query("SELECT COUNT(*) FROM movimiento")->fetchColumn();
$stockBajoCount   = $pdo->query("SELECT COUNT(*) FROM producto WHERE estado = 'activo' AND stock_actual < stock_minimo")->fetchColumn();

// Listado de productos con stock bajo (Regla de negocio: stock_actual < stock_minimo)
$stmtCriticos = $pdo->query("
    SELECT p.id_producto, p.codigo, p.nombre, p.stock_actual, p.stock_minimo, p.precio, c.nombre AS categoria
    FROM producto p
    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
    WHERE p.estado = 'activo' AND p.stock_actual < p.stock_minimo
    ORDER BY p.stock_actual ASC
");
$productosCriticos = $stmtCriticos->fetchAll();

// Últimos 5 movimientos registrados
$stmtUltimosMov = $pdo->query("
    SELECT m.fecha_hora, m.tipo, m.cantidad, m.observacion, p.nombre AS producto, p.codigo, u.nombre AS usuario
    FROM movimiento m
    INNER JOIN producto p ON m.id_producto = p.id_producto
    INNER JOIN usuario u ON m.id_usuario = u.id_usuario
    ORDER BY m.fecha_hora DESC
    LIMIT 5
");
$ultimosMovimientos = $stmtUltimosMov->fetchAll();

$tituloPagina = 'Dashboard';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container-fluid px-lg-4 py-4 flex-grow-1">
    <?php renderFlashAlert(); ?>

    <!-- Encabezado de sección con acciones rápidas -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Panel de Control &mdash; TecnoStock</h1>
            <p class="text-secondary small mb-0">Monitoreo de existencias, catálogo y flujo de inventario en tiempo real.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('modules/productos/crear.php') ?>" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
            <a href="<?= base_url('modules/movimientos/registrar.php') ?>" class="btn btn-dark d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-right"></i> Registrar Movimiento
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold">Productos Activos</div>
                        <div class="fs-3 fw-bold mt-1"><?= $totalProductos ?></div>
                        <a href="<?= base_url('modules/productos/index.php') ?>" class="text-decoration-none small text-primary">Ver catálogo &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-3 p-3 fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold">Categorías</div>
                        <div class="fs-3 fw-bold mt-1"><?= $totalCategorias ?></div>
                        <span class="text-secondary small">Rubro de tecnología</span>
                    </div>
                    <div class="bg-secondary-subtle text-secondary rounded-3 p-3 fs-3">
                        <i class="bi bi-tags"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold">Total Movimientos</div>
                        <div class="fs-3 fw-bold mt-1"><?= $totalMovimientos ?></div>
                        <a href="<?= base_url('modules/movimientos/index.php') ?>" class="text-decoration-none small text-dark">Ver historial &rarr;</a>
                    </div>
                    <div class="bg-info-subtle text-info-emphasis rounded-3 p-3 fs-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 <?= $stockBajoCount > 0 ? 'border-danger border-2' : '' ?>">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-danger small text-uppercase fw-bold">Alertas Stock Bajo</div>
                        <div class="fs-3 fw-bold text-danger mt-1"><?= $stockBajoCount ?></div>
                        <span class="small text-danger fw-semibold">
                            <?= $stockBajoCount > 0 ? '¡Requiere reposición!' : 'Nivel óptimo' ?>
                        </span>
                    </div>
                    <div class="bg-danger text-white rounded-3 p-3 fs-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Alertas de Stock Bajo -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2 text-danger">
                <i class="bi bi-bell-fill fs-5"></i>
                <h2 class="h5 mb-0 fw-bold">Alerta: Productos con Stock Menor al Mínimo</h2>
            </div>
            <span class="badge bg-danger rounded-pill"><?= count($productosCriticos) ?> producto(s) crítico(s)</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($productosCriticos)): ?>
                <div class="p-4 text-center text-success">
                    <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                    <p class="mb-0 fw-semibold">Excelente: Todos los productos se encuentran por sobre el nivel de existencias mínimas.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Stock Actual</th>
                                <th class="text-center">Stock Mínimo</th>
                                <th class="text-center">Déficit</th>
                                <th class="text-end">Acción Recomendada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosCriticos as $prod): 
                                $deficit = $prod['stock_minimo'] - $prod['stock_actual'];
                            ?>
                            <tr class="table-row-stock-critico">
                                <td><span class="badge bg-dark font-monospace"><?= e($prod['codigo']) ?></span></td>
                                <td class="fw-semibold"><?= e($prod['nombre']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-dark"><?= e($prod['categoria']) ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-danger fs-6"><?= $prod['stock_actual'] ?></span>
                                </td>
                                <td class="text-center text-secondary fw-semibold"><?= $prod['stock_minimo'] ?></td>
                                <td class="text-center text-danger fw-bold">-<?= $deficit ?> u.</td>
                                <td class="text-end">
                                    <a href="<?= base_url('modules/movimientos/registrar.php?id_producto=' . $prod['id_producto'] . '&tipo=entrada') ?>" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-box-arrow-in-down-right"></i> Ingresar Stock
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección de Últimos Movimientos -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h2 class="h5 mb-0 fw-bold text-dark"><i class="bi bi-activity me-2"></i>Actividad Reciente de Inventario</h2>
            <a href="<?= base_url('modules/movimientos/index.php') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th>Responsable</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimosMovimientos)): ?>
                            <tr><td colspan="6" class="text-center py-3 text-muted">No se registran movimientos recientes.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ultimosMovimientos as $mov): ?>
                            <tr>
                                <td class="text-secondary small"><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                                <td>
                                    <?php if ($mov['tipo'] === 'entrada'): ?>
                                        <span class="badge bg-success"><i class="bi bi-arrow-down-left me-1"></i>Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-right me-1"></i>Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-dark font-monospace me-1"><?= e($mov['codigo']) ?></span>
                                    <?= e($mov['producto']) ?>
                                </td>
                                <td class="text-center fw-bold <?= $mov['tipo'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                                    <?= $mov['tipo'] === 'entrada' ? '+' : '-' ?><?= $mov['cantidad'] ?>
                                </td>
                                <td class="small text-secondary"><i class="bi bi-person me-1"></i><?= e($mov['usuario']) ?></td>
                                <td class="small text-muted"><?= e($mov['observacion'] ?: 'Sin observación') ?></td>
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
