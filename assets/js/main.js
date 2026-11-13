/**
 * Comportamiento e Interacciones UI
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Cierre automático opcional de alertas flash tras 6 segundos
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 2. Confirmación antes de desactivar un producto (Soft Delete)
    const botonesDesactivar = document.querySelectorAll('.btn-desactivar-producto');
    botonesDesactivar.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const nombre = this.getAttribute('data-nombre') || 'este producto';
            const confirmar = confirm(`¿Está seguro de desactivar "${nombre}"?\n\nDe acuerdo a las reglas de TecnoStock, el producto pasará a estado inactivo y no se borrará físicamente.`);
            if (!confirmar) {
                e.preventDefault();
            }
        });
    });

    // 3. Ayudante visual en el formulario de Movimientos (muestra stock actual del producto elegido)
    const selectProductoMov = document.getElementById('select_id_producto');
    const badgeStockInfo = document.getElementById('info_stock_actual');
    const selectTipoMov = document.getElementById('select_tipo_movimiento');
    const inputCantidadMov = document.getElementById('input_cantidad_mov');

    function actualizarInfoStock() {
        if (!selectProductoMov || !badgeStockInfo) return;
        const opt = selectProductoMov.options[selectProductoMov.selectedIndex];
        if (opt && opt.value !== '') {
            const stock = opt.getAttribute('data-stock');
            badgeStockInfo.textContent = `Stock actual disponible: ${stock} unidades`;
            badgeStockInfo.className = parseInt(stock, 10) <= 0 ? 'text-danger fw-bold' : 'text-primary fw-semibold';
        } else {
            badgeStockInfo.textContent = '';
        }
    }

    if (selectProductoMov) {
        selectProductoMov.addEventListener('change', actualizarInfoStock);
        actualizarInfoStock();
    }

    // 4. Búsqueda rápida en cliente para tablas (filtrado en tiempo real sin recargar)
    const inputFiltroRapido = document.getElementById('filtroRapidoTabla');
    if (inputFiltroRapido) {
        inputFiltroRapido.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('tbody tr');
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(query) ? '' : 'none';
            });
        });
    }
});
