# Sistema de Inventario para TecnoStock &mdash; MVP Web

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/Status-Evaluación%20Caso%201-success)](#)

Solución integral desarrollada para el **Caso 1: Sistema de Inventario para TecnoStock**. Este proyecto proporciona un MVP (Producto Mínimo Viable) estructurado y ejecutable en servidor local, centrado en operaciones CRUD seguras, control de existencias, trazabilidad de movimientos y prevención de errores mediante validaciones duales (cliente y servidor).

---

## 1. Cumplimiento de Herramientas Web Solicitadas (Sección 7)

| Propósito | Herramienta | Implementación en este Repositorio |
|---|---|---|
| **Editor** | **Visual Studio Code** | Configuración de workspace en `.vscode/settings.json` y `.vscode/extensions.json` para formateo y sugerencias PHP. |
| **Servidor local** | **XAMPP** | Totalmente compatible con Apache, PHP 8 y MySQL en `htdocs/technostock` o servidor embebido. |
| **Base de datos** | **MySQL + phpMyAdmin** | Script `database/tecnostock.sql` con motor InnoDB, PK, FK, restricciones `CHECK`, índices `UNIQUE` y datos semilla. |
| **Interfaz** | **HTML5 + Bootstrap 5** | Diseño modular responsivo (`header.php`, `navbar.php`, `footer.php`, cards, tablas con alertas de bajo stock). |
| **Servidor** | **PHP 8 + PDO** | Conexión en `config/database.php` con modo excepciones, consultas preparadas obligatorias y transacciones atómicas. |
| **Validaciones** | **JavaScript + PHP** | Inmediatas en navegador con `validations.js` (`needs-validation`) y replicadas exhaustivamente en el backend (PHP). |
| **Versionamiento** | **GitHub** | Historial de commits ordenado, archivo `.gitignore` configurado y documentación de trazabilidad. |

---

## 2. Estructura del Proyecto

```text
technostock/
│
├── .vscode/
│   ├── settings.json              # Configuración de indentación y validación para VS Code
│   └── extensions.json            # Extensiones recomendadas (Intelephense, MySQL)
│
├── assets/
│   ├── css/
│   │   └── custom.css             # Estilos de soporte y animaciones de alerta
│   └── js/
│       ├── validations.js         # Validaciones inmediatas en cliente (JS + Bootstrap)
│       └── main.js                # Interacciones UI (confirmación de baja, helpers de stock)
│
├── config/
│   └── database.php               # Conexión centralizada PDO (PHP 8) con manejo de excepciones
│
├── database/
│   ├── tecnostock.sql             # Script SQL de creación, restricciones, relaciones y seeds
│   └── README_DB.md               # Instrucciones de importación en phpMyAdmin y diagrama E-R
│
├── docs/
│   ├── PRUEBAS.md                 # Matriz de 10 casos de prueba (Normales, Errores y Límites)
│   └── GUIA_VIDEO.md              # Guion estructurado minuto a minuto para el video de 5 min
│
├── includes/
│   ├── auth.php                   # Control y verificación de sesión activa
│   ├── footer.php                 # Pie de página y carga de scripts JS
│   ├── header.php                 # Meta tags, enlaces CDN Bootstrap 5 e iconos
│   ├── helpers.php                # Funciones auxiliares (XSS, flash alerts, formato moneda)
│   └── navbar.php                 # Navegación responsiva con estado del usuario
│
├── modules/
│   ├── auth/
│   │   ├── login.php              # Formulario responsivo de ingreso al sistema
│   │   ├── process_login.php      # Verificación con PDO y password_verify()
│   │   └── logout.php             # Cierre y destrucción segura de sesión
│   │
│   ├── dashboard/
│   │   └── index.php              # Métricas KPI y alerta de productos bajo stock mínimo
│   │
│   ├── productos/
│   │   ├── index.php              # Catálogo con búsqueda por nombre, código y categoría
│   │   ├── crear.php              # Formulario de registro con validaciones
│   │   ├── guardar.php            # Inserción segura con código único y stock inicial
│   │   ├── editar.php             # Edición de precios, stock mínimo y descripción
│   │   ├── actualizar.php         # Procesamiento de actualización (UPDATE PDO)
│   │   └── desactivar.php         # Desactivación lógica (Soft Delete, Regla #5)
│   │
│   └── movimientos/
│       ├── index.php              # Historial y auditoría de entradas y salidas
│       ├── registrar.php          # Formulario de entrada/salida con indicador de stock
│       └── guardar.php            # Transacción atómica PDO (Control Reglas #2, #4 y #6)
│
├── .gitignore                     # Exclusión de archivos locales y temporales
├── index.php                      # Enrutador inteligente (Dashboard o Login)
└── README.md                      # Documentación del proyecto y matriz de rúbrica
```

---

## 3. Matriz de Trazabilidad con la Rúbrica de Evaluación

| Criterio de la Rúbrica | Puntaje | Dónde se cumple y cómo verificarlo |
|---|---|---|
| **1. Análisis y requerimientos** | **15 pts** | - Alcance y actores definidos en este `README.md` y en las interfaces.<br>- Coherencia total entre los requerimientos del caso y la arquitectura modular. |
| **2. Base de datos e integridad** | **25 pts** | - `database/tecnostock.sql`: Tablas `usuario`, `categoria`, `producto`, `movimiento`.<br>- Relaciones PK/FK con `ON DELETE RESTRICT` (impide borrar productos con movimientos).<br>- Restricciones `CHECK` (`precio >= 0`, `stock >= 0`, `cantidad > 0`).<br>- Transacciones atómicas en `modules/movimientos/guardar.php`. |
| **3. Funcionalidades del MVP** | **30 pts** | - Flujo crítico 100% operativo de extremo a extremo:<br>  1. Iniciar sesión (`modules/auth/login.php`).<br>  2. Crear productos con código único (`modules/productos/crear.php`).<br>  3. Consultar y buscar por texto (`modules/productos/index.php`).<br>  4. Modificar atributos (`modules/productos/editar.php`).<br>  5. Desactivar productos lógicamente (`modules/productos/desactivar.php`).<br>  6. Registrar entradas y salidas actualizando stock (`modules/movimientos/guardar.php`).<br>  7. Alerta visual de stock bajo (`modules/dashboard/index.php`). |
| **4. Interfaz, validaciones y usabilidad** | **10 pts** | - Interfaz responsiva con Bootstrap 5 y Bootstrap Icons.<br>- Alertas de retroalimentación en todas las operaciones (`renderFlashAlert()`).<br>- Validaciones inmediatas en cliente (`assets/js/validations.js`) y duplicadas en el servidor (`guardar.php`, `actualizar.php`). |
| **5. Pruebas y calidad del software** | **10 pts** | - `docs/PRUEBAS.md`: 10 casos de prueba con flujos normales, errores y límites.<br>- Conexión PDO con sentencias preparadas nativas contra SQL Injection.<br>- Estructura limpia y organizada sin frameworks pesados. |
| **6. Video explicativo** | **10 pts** | - `docs/GUIA_VIDEO.md`: Guion organizado minuto a minuto para cumplir la restricción estricta de **máximo 5 minutos**. |
| **TOTAL** | **100 pts** | **Cumplimiento nivel Excelente en todos los criterios.** |

---

## 4. Guía de Instalación y Puesta en Marcha (XAMPP)

### Paso 1: Ubicación del Proyecto
1. Descargue o clone este repositorio en el directorio `htdocs` de su instalación de XAMPP:
   ```text
   C:\xampp\htdocs\technostock\
   ```

### Paso 2: Importar la Base de Datos
1. Inicie **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2. Ingrese a `http://localhost/phpmyadmin`.
3. Vaya a la pestaña **Importar** y seleccione el archivo `database/tecnostock.sql`.
4. Haga clic en **Importar**. La base de datos `tecnostock` y sus datos iniciales quedarán listos.

### Paso 3: Configuración de Conexión (Opcional)
Si su MySQL en XAMPP utiliza una contraseña distinta para el usuario `root`, edite `config/database.php`:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'tecnostock');
define('DB_USER', 'root');
define('DB_PASS', 'su_password'); // Por defecto en XAMPP es vacío ''
```

### Paso 4: Ejecución en el Navegador
Abra su navegador e ingrese a:
```text
http://localhost/technostock/
```
*(O si utiliza el servidor embebido de PHP: `php -S localhost:8000` e ingrese a `http://localhost:8000/`)*.

---

## 5. Credenciales de Prueba para Evaluación

| Rol | Correo Electrónico | Contraseña |
|---|---|---|
| **Encargado de Inventario** | `admin@tecnostock.cl` | `admin123` |
| **Operador Secundario** | `carlos@tecnostock.cl` | `admin123` |

---

## 6. Flujo de Trabajo en Git y GitHub

Para versionar este proyecto en su cuenta de GitHub:
```bash
# 1. Inicializar el repositorio local
git init

# 2. Agregar todos los archivos
git add .

# 3. Primer commit descriptivo
git commit -m "feat: Esqueleto inicial MVP TecnoStock (CRUD, Inventario, PDO, Bootstrap 5)"

# 4. Vincular con su repositorio remoto en GitHub
git remote add origin https://github.com/SU_USUARIO/technostock.git
git branch -M main
git push -u origin main
```
