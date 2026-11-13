<?php
/**
 * Barra de Navegación Responsiva Bootstrap 5
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */
$usuario = usuarioActual();
$currentScript = $_SERVER['SCRIPT_NAME'];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= base_url('modules/dashboard/index.php') ?>">
            <span class="bg-primary text-white rounded-3 px-2 py-1 fs-5">
                <i class="bi bi-cpu-fill"></i>
            </span>
            <span>Tecno<span class="text-primary">Stock</span></span>
        </a>

        <?php if ($usuario): ?>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentScript, 'dashboard') !== false ? 'active fw-semibold' : '' ?>" href="<?= base_url('modules/dashboard/index.php') ?>">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($currentScript, 'productos') !== false ? 'active fw-semibold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-box-seam me-1"></i> Productos
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="<?= base_url('modules/productos/index.php') ?>"><i class="bi bi-list-ul me-2"></i>Catálogo de Productos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('modules/productos/crear.php') ?>"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Producto</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($currentScript, 'movimientos') !== false ? 'active fw-semibold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-arrow-left-right me-1"></i> Movimientos
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="<?= base_url('modules/movimientos/index.php') ?>"><i class="bi bi-clock-history me-2"></i>Historial de Movimientos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('modules/movimientos/registrar.php') ?>"><i class="bi bi-box-arrow-in-down-right me-2"></i>Registrar Entrada / Salida</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-light small d-none d-md-block text-end">
                    <div class="fw-semibold text-white"><i class="bi bi-person-circle me-1"></i><?= e($usuario['nombre']) ?></div>
                    <div class="text-secondary" style="font-size: 0.75rem;"><?= e($usuario['correo']) ?></div>
                </div>
                <a href="<?= base_url('modules/auth/logout.php') ?>" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Salir</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</nav>
