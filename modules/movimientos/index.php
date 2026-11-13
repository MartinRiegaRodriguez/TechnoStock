<?php
/**
 * Historial y Auditoría de Movimientos de Inventario
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

verificarAutenticacion();

$pdo = getDBConnection();

// Filtros opcionales
$filtroTipo     = $_GET['tipo'] ?? 'todos';
$filtroProducto = (int)($_GET['id_producto'] ?? 0);

$sql = "
    SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
           p.id_producto, p.codigo, p.nombre AS producto, p.stock_actual,
           u.nombre AS usuario, u.correo
    FROM movimiento m
    INNER JOIN producto p ON m.id_producto = p.id_producto
    INNER JOIN usuario u ON m.id_usuario = u.id_usuario
    WHERE 1=1
";
$params = [];

if (in_array($filtroTipo, ['entrada', 'salida'])) {
    $sql .= " AND m.tipo = :tipo";
    $params[':tipo'] = $filtroTipo;
}

if ($filtroProducto > 0) {
    $sql .= " AND m.id_producto = :id_producto";
    $params[':id_producto'] = $filtroProducto;
}

$sql .= " ORDER BY m.fecha_hora DESC, m.id_movimiento DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

// Listado de productos para el selector de filtro
$stmtProds = $pdo->query("SELECT id_producto, codigo, nombre FROM producto ORDER BY nombre ASC");
$listaProductos = $stmtProds->fetchAll();

$tituloPagina = 'Historial de Movimientos';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<main class="container-fluid px-lg-4 py-4 flex-grow-1">
    <?php renderFlashAlert(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Movimientos de Inventario</h1>
            <p class="text-secondary small mb-0">Registro y trazabilidad de todas las entradas y salidas de existencias.</p>
        </div>
        <a href="<?= base_url('modules/movimientos/registrar.php') ?>" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-in-down-right"></i> Registrar Nuevo Movimiento
        </a>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('modules/movimientos/index.php') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <select name="id_producto" class="form-select" onchange="this.form.submit()">
                        <option value="0">-- Todos los productos --</option>
                        <?php foreach ($listaProductos as $lp): ?>
                            <option value="<?= $lp['id_producto'] ?>" <?= ($filtroProducto === (int)$lp['id_producto']) ? 'selected' : '' ?>>
                                [<?= e($lp['codigo']) ?>] <?= e($lp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <select name="tipo" class="form-select" onchange="this.form.submit()">
                        <option value="todos" <?= $filtroTipo === 'todos' ? 'selected' : '' ?>>Todos los Tipos (Entradas y Salidas)</option>
                        <option value="entrada" <?= $filtroTipo === 'entrada' ? 'selected' : '' ?>>Solo Entradas (+)</option>
                        <option value="salida" <?= $filtroTipo === 'salida' ? 'selected' : '' ?>>Solo Salidas (-)</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">Aplicar</button>
                    <?php if ($filtroTipo !== 'todos' || $filtroProducto > 0): ?>
                        <a href="<?= base_url('modules/movimientos/index.php') ?>" class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Historial -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-clock-history me-2"></i>Auditoría de Movimientos (<?= count($movimientos) ?> registros)</span>
            <input type="text" id="filtroRapidoTabla" class="form-control form-control-sm w-auto d-none d-sm-inline-block" placeholder="Filtrar movimientos...">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha y Hora</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th>Usuario Responsable</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-arrow-left-right fs-1 d-block mb-2 text-secondary"></i>
                                    No se registran movimientos con los filtros aplicados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td class="text-secondary small font-monospace">#<?= $m['id_movimiento'] ?></td>
                                <td class="text-secondary small">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i', strtotime($m['fecha_hora'])) ?>
                                </td>
                                <td>
                                    <?php if ($m['tipo'] === 'entrada'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="bi bi-box-arrow-in-down-right me-1"></i>Entrada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Salida
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-dark font-monospace"><?= e($m['codigo']) ?></span>
                                    <span class="fw-semibold ms-1"><?= e($m['producto']) ?></span>
                                </td>
                                <td class="text-center fw-bold fs-6 <?= $m['tipo'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                                    <?= $m['tipo'] === 'entrada' ? '+' : '-' ?><?= $m['cantidad'] ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><i class="bi bi-person me-1"></i><?= e($m['usuario']) ?></div>
                                    <div class="text-secondary" style="font-size: 0.75rem;"><?= e($m['correo']) ?></div>
                                </td>
                                <td class="small text-secondary">
                                    <?= e($m['observacion'] ?: 'Sin observación') ?>
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
