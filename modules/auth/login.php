<?php
/**
 * Pantalla de Inicio de Sesión
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/includes/helpers.php';

// Si ya tiene sesión activa, redirigir al Dashboard
if (isset($_SESSION['usuario'])) {
    header('Location: ' . base_url('modules/dashboard/index.php'));
    exit;
}

$tituloPagina = 'Iniciar Sesión';
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100 py-5">
    <div class="card shadow-lg p-4 p-md-5 w-100" style="max-width: 440px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 58px; height: 58px;">
                <i class="bi bi-cpu-fill fs-2"></i>
            </div>
            <h2 class="h4 fw-bold mb-1">Tecno<span class="text-primary">Stock</span></h2>
            <p class="text-muted small">Sistema de Control de Inventario</p>
        </div>

        <?php renderFlashAlert(); ?>

        <form action="<?= base_url('modules/auth/process_login.php') ?>" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="correo" class="form-label fw-semibold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@tecnostock.cl" required autocomplete="email">
                    <div class="invalid-feedback">Por favor ingrese un correo electrónico válido.</div>
                </div>
            </div>

            <div class="mb-4">
                <label for="clave" class="form-label fw-semibold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password" class="form-control" id="clave" name="clave" placeholder="••••••••" required autocomplete="current-password">
                    <div class="invalid-feedback">Por favor ingrese su contraseña.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-in-right"></i>
                Ingresar al Sistema
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center">
            <div class="alert alert-secondary py-2 px-3 small text-start mb-0">
                <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i>Credenciales de prueba:</div>
                <div><strong>Usuario:</strong> <code>admin@tecnostock.cl</code></div>
                <div><strong>Clave:</strong> <code>admin123</code></div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
