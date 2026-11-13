# Guía de Base de Datos &mdash; TecnoStock

Este documento describe la estructura relacional, restricciones y pasos de instalación de la base de datos `tecnostock`.

---

## 1. Instrucciones de Importación en phpMyAdmin / MySQL

### Opción A: Desde phpMyAdmin (XAMPP)
1. Inicie **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2. Abra su navegador e ingrese a `http://localhost/phpmyadmin`.
3. Haga clic en la pestaña **Importar** (en el menú superior).
4. Presione **Seleccionar archivo** y elija `database/tecnostock.sql`.
5. Presione el botón **Importar** al final de la página.
6. La base de datos `tecnostock`, sus 4 tablas y los datos semilla se crearán automáticamente.

### Opción B: Desde la Consola de MySQL
```bash
mysql -u root -p < database/tecnostock.sql
```

---

## 2. Modelo Relacional y Entidades

El modelo relacional cumple con las 3 Formas Normales (3FN) y utiliza el motor transaccional **InnoDB**:

```
+---------------+       1:N       +---------------+
|   categoria   | <-------------- |   producto    |
+---------------+                 +---------------+
| id_categoria  |                 | id_producto   |
| nombre (UQ)   |                 | codigo (UQ)   |
| descripcion   |                 | nombre        |
| estado        |                 | precio (>= 0) |
+---------------+                 | stock_actual  |
                                  | stock_minimo  |
                                  | estado        |
                                  | id_categoria  |
                                  +---------------+
                                          |
                                          | 1:N
                                          v
+---------------+       1:N       +---------------+
|    usuario    | <-------------- |  movimiento   |
+---------------+                 +---------------+
| id_usuario    |                 | id_movimiento |
| nombre        |                 | fecha_hora    |
| correo (UQ)   |                 | tipo (E / S)  |
| clave (hash)  |                 | cantidad (>0) |
| estado        |                 | observacion   |
+---------------+                 | id_producto   |
                                  | id_usuario    |
                                  +---------------+
```

---

## 3. Integridad y Restricciones de Negocio Implementadas

1. **Claves Primarias y Foráneas:**
   - Todas las tablas poseen PK autoincrementales.
   - Claves foráneas con `ON UPDATE CASCADE ON DELETE RESTRICT`: impide el borrado físico de categorías o productos que tengan dependencias o movimientos asociados (Regla de negocio #5).

2. **Unicidad:**
   - `producto.codigo`: `UNIQUE` (Regla #1).
   - `usuario.correo`: `UNIQUE`.
   - `categoria.nombre`: `UNIQUE`.

3. **Restricciones CHECK:**
   - `producto.precio >= 0`
   - `producto.stock_actual >= 0`
   - `producto.stock_minimo >= 0`
   - `movimiento.cantidad > 0`

4. **Datos Semilla (Seeds):**
   - Usuario Administrador: `admin@tecnostock.cl` / `admin123`
   - Categorías tecnológicas reales.
   - Productos con stock normal y productos en situación de **alerta de stock bajo** (`stock_actual < stock_minimo`) para verificación inmediata.
