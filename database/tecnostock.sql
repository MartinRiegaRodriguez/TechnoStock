-- =============================================================================
-- BASE DE DATOS: TecnoStock - Sistema de Gestión de Inventario
-- Compatible con: MySQL 8.0+ / MariaDB 10.4+ (XAMPP)
-- Motor: InnoDB (Garantiza claves foráneas y transacciones ACID)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `tecnostock` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `tecnostock`;

-- -----------------------------------------------------------------------------
-- 1. Tabla: usuario
-- Almacena los encargados de inventario y administradores con clave segura.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `movimiento`;
DROP TABLE IF EXISTS `producto`;
DROP TABLE IF EXISTS `categoria`;
DROP TABLE IF EXISTS `usuario`;

CREATE TABLE `usuario` (
  `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `clave` VARCHAR(255) NOT NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Tabla: categoria
-- Permite clasificar los accesorios tecnológicos de TecnoStock.
-- -----------------------------------------------------------------------------
CREATE TABLE `categoria` (
  `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` TEXT NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabla: producto
-- Catálogo central. Reglas de negocio:
-- - Código único.
-- - Precios y stock no negativos (CHECK).
-- - Desactivación lógica (estado = 'inactivo'), no eliminación física.
-- -----------------------------------------------------------------------------
CREATE TABLE `producto` (
  `id_producto` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT NULL,
  `precio` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `stock_actual` INT NOT NULL DEFAULT 0,
  `stock_minimo` INT NOT NULL DEFAULT 0,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `id_categoria` INT NOT NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `chk_producto_precio` CHECK (`precio` >= 0),
  CONSTRAINT `chk_producto_stock_actual` CHECK (`stock_actual` >= 0),
  CONSTRAINT `chk_producto_stock_minimo` CHECK (`stock_minimo` >= 0),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`)
    REFERENCES `categoria` (`id_categoria`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Tabla: movimiento
-- Registro de auditoría de cada entrada o salida de inventario.
-- Reglas de negocio:
-- - Cantidad estrictamente mayor a 0.
-- - Vinculado al usuario responsable en sesión y al producto involucrado.
-- -----------------------------------------------------------------------------
CREATE TABLE `movimiento` (
  `id_movimiento` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` ENUM('entrada', 'salida') NOT NULL,
  `cantidad` INT NOT NULL,
  `observacion` TEXT NULL,
  `id_producto` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  CONSTRAINT `chk_movimiento_cantidad` CHECK (`cantidad` > 0),
  CONSTRAINT `fk_movimiento_producto` FOREIGN KEY (`id_producto`)
    REFERENCES `producto` (`id_producto`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_movimiento_usuario` FOREIGN KEY (`id_usuario`)
    REFERENCES `usuario` (`id_usuario`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DATOS SEMILLA (SEEDS) PARA PRUEBAS Y EVALUACIÓN
-- =============================================================================

-- Usuario inicial: admin@tecnostock.cl / admin123
INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `clave`, `estado`) VALUES
(1, 'Administrador TecnoStock', 'admin@tecnostock.cl', '$2y$12$kNWeGmSlUjqnJdddevQGjuPlcM59XXMTx2RvjLlmf3DL9.Xh3w8Pa', 'activo'),
(2, 'Carlos Inventario', 'carlos@tecnostock.cl', '$2y$12$kNWeGmSlUjqnJdddevQGjuPlcM59XXMTx2RvjLlmf3DL9.Xh3w8Pa', 'activo');

-- Categorías iniciales
INSERT INTO `categoria` (`id_categoria`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Cables y Adaptadores', 'Cables USB-C, Lightning, HDMI y convertidores', 'activo'),
(2, 'Cargadores y Baterías', 'Cargadores de pared, de auto y Power Banks', 'activo'),
(3, 'Audífonos y Audio', 'Audífonos Bluetooth, TWS y parlantes portátiles', 'activo'),
(4, 'Fundas y Protección', 'Carcasas, láminas de hidrogel y vidrio templado', 'activo'),
(5, 'Soportes y Periféricos', 'Soportes para laptop, celular, mouse pads y teclados', 'activo');

-- Productos iniciales (algunos con stock bajo para verificar alertas en el MVP)
INSERT INTO `producto` (`id_producto`, `codigo`, `nombre`, `descripcion`, `precio`, `stock_actual`, `stock_minimo`, `estado`, `id_categoria`) VALUES
(1, 'CAB-001', 'Cable USB-C a Lightning 1m Trenzado', 'Cable reforzado compatible con carga rápida Power Delivery', 9990.00, 25, 10, 'activo', 1),
(2, 'CAR-002', 'Cargador Rápido 20W USB-C', 'Adaptador de corriente compacto con protección contra sobrecargas', 14990.00, 18, 5, 'activo', 2),
(3, 'AUD-003', 'Audífonos TWS Bluetooth 5.3 Pro', 'Cancelación de ruido pasiva, estuche con display digital', 29990.00, 3, 8, 'activo', 3), -- ALERTA: Stock bajo (3 < 8)
(4, 'SOP-004', 'Soporte Metálico Plegable para Laptop', 'Aluminio anodizado con 6 niveles de inclinación ergonómica', 18990.00, 2, 5, 'activo', 5), -- ALERTA: Stock bajo (2 < 5)
(5, 'FUN-005', 'Funda Silicona Magnética iPhone 15', 'Funda tipo MagSafe anti-impacto con tacto suave', 12990.00, 15, 6, 'activo', 4),
(6, 'HUB-006', 'Hub USB-C 6 en 1 4K HDMI', 'Adaptador multipuerto con lector SD y 3 puertos USB 3.0', 34990.00, 0, 4, 'activo', 1); -- ALERTA: Stock crítico cero (0 < 4)

-- Movimientos iniciales de ejemplo
INSERT INTO `movimiento` (`id_movimiento`, `fecha_hora`, `tipo`, `cantidad`, `observacion`, `id_producto`, `id_usuario`) VALUES
(1, NOW() - INTERVAL 5 DAY, 'entrada', 30, 'Carga inicial de inventario', 1, 1),
(2, NOW() - INTERVAL 4 DAY, 'salida', 5, 'Venta en mostrador factura #101', 1, 2),
(3, NOW() - INTERVAL 3 DAY, 'entrada', 20, 'Recepción de pedido mayorista', 2, 1),
(4, NOW() - INTERVAL 2 DAY, 'salida', 2, 'Venta boleta #402', 2, 2),
(5, NOW() - INTERVAL 2 DAY, 'entrada', 10, 'Inventario inicial', 3, 1),
(6, NOW() - INTERVAL 1 DAY, 'salida', 7, 'Venta especial empresas', 3, 2),
(7, NOW() - INTERVAL 1 DAY, 'entrada', 5, 'Ingreso inicial', 4, 1),
(8, NOW() - INTERVAL 12 HOUR, 'salida', 3, 'Venta tienda web', 4, 2);
