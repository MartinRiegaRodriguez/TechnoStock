<?php
/**
 * Footer común HTML5 + Bootstrap 5
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */
?>
<footer class="bg-white border-top py-3 mt-auto text-secondary text-center small">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <span>&copy; <?= date('Y') ?> <strong>TecnoStock</strong> &mdash; Sistema de Inventario</span>
        <span>Desarrollado con <strong>PHP 8 + PDO, MySQL y Bootstrap 5</strong></span>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle con Popper JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Script de Validaciones del Cliente (JavaScript) -->
<script src="<?= base_url('assets/js/validations.js') ?>"></script>

<!-- Script Principal de Interacción UI -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
