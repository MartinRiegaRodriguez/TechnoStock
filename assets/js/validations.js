/**
 * Validaciones en Cliente con JavaScript
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 * 
 * Reglas cubiertas en cliente (se repiten obligatoriamente en PHP):
 * - Campos requeridos no vacíos.
 * - Precio, stock actual y stock mínimo >= 0 (no negativos).
 * - Cantidad de movimiento > 0.
 * - Validación inmediata de salida: que la cantidad no supere el stock disponible.
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Integración con validación nativa de Bootstrap 5
    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            let esValido = true;

            // Validación específica para formularios de producto
            const inputPrecio = form.querySelector('input[name="precio"]');
            if (inputPrecio) {
                const precio = parseFloat(inputPrecio.value);
                if (isNaN(precio) || precio < 0) {
                    inputPrecio.setCustomValidity('El precio no puede ser negativo.');
                    esValido = false;
                } else {
                    inputPrecio.setCustomValidity('');
                }
            }

            const inputStockActual = form.querySelector('input[name="stock_actual"]');
            if (inputStockActual) {
                const stock = parseInt(inputStockActual.value, 10);
                if (isNaN(stock) || stock < 0) {
                    inputStockActual.setCustomValidity('El stock actual no puede ser negativo.');
                    esValido = false;
                } else {
                    inputStockActual.setCustomValidity('');
                }
            }

            const inputStockMinimo = form.querySelector('input[name="stock_minimo"]');
            if (inputStockMinimo) {
                const stockMin = parseInt(inputStockMinimo.value, 10);
                if (isNaN(stockMin) || stockMin < 0) {
                    inputStockMinimo.setCustomValidity('El stock mínimo no puede ser negativo.');
                    esValido = false;
                } else {
                    inputStockMinimo.setCustomValidity('');
                }
            }

            // Validación específica para registro de movimientos (Entrada / Salida)
            const selectProducto = form.querySelector('select[name="id_producto"]');
            const selectTipo = form.querySelector('select[name="tipo"]');
            const inputCantidad = form.querySelector('input[name="cantidad"]');

            if (selectProducto && selectTipo && inputCantidad) {
                const cantidad = parseInt(inputCantidad.value, 10);
                const tipo = selectTipo.value;
                const optionSeleccionada = selectProducto.options[selectProducto.selectedIndex];
                const stockDisponible = optionSeleccionada ? parseInt(optionSeleccionada.getAttribute('data-stock') || '0', 10) : 0;

                if (isNaN(cantidad) || cantidad <= 0) {
                    inputCantidad.setCustomValidity('La cantidad debe ser mayor a cero.');
                    esValido = false;
                } else if (tipo === 'salida' && cantidad > stockDisponible) {
                    // Regla de negocio #4: Una salida no puede dejar el stock bajo cero
                    inputCantidad.setCustomValidity(`Stock insuficiente. El producto solo cuenta con ${stockDisponible} unidades disponibles.`);
                    esValido = false;
                } else {
                    inputCantidad.setCustomValidity('');
                }
            }

            // Si falla la validación nativa o personalizada, detener envío
            if (!form.checkValidity() || !esValido) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
});
