<?php
/**
 * Configuración y conexión segura a la Base de Datos con PHP 8 + PDO
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */

// Parámetros de conexión para entorno XAMPP
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'tecnostock');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtiene la instancia única de conexión PDO.
 *
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            // Lanza excepciones en caso de error (crucial para transacciones seguras)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Retorna resultados como arrays asociativos limpios
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Desactiva emulación de consultas preparadas para mayor seguridad nativa
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Mensaje amigable para desarrollo local si la base aún no se ha importado
            $errorMsg = "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 20px; border: 1px solid #f5c6cb; background: #f8d7da; color: #721c24; border-radius: 8px;'>";
            $errorMsg .= "<h3 style='margin-top:0;'>❌ Error de conexión a la Base de Datos</h3>";
            $errorMsg .= "<p>No fue posible conectar con MySQL en <code>" . DB_HOST . "</code>.</p>";
            $errorMsg .= "<p><strong>Causa probable:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            $errorMsg .= "<hr style='border:0; border-top:1px solid #f5c6cb; margin:15px 0;'>";
            $errorMsg .= "<strong>Pasos para solucionar en XAMPP:</strong>";
            $errorMsg .= "<ol style='margin-bottom:0; padding-left: 20px;'>";
            $errorMsg .= "<li>Abre el <strong>Panel de Control de XAMPP</strong> y asegúrate de que <strong>Apache</strong> y <strong>MySQL</strong> estén en verde (iniciados).</li>";
            $errorMsg .= "<li>Abre <code>http://localhost/phpmyadmin</code>.</li>";
            $errorMsg .= "<li>Crea o importa el script ubicado en <code>database/tecnostock.sql</code>.</li>";
            $errorMsg .= "</ol>";
            $errorMsg .= "</div>";
            die($errorMsg);
        }
    }

    return $pdo;
}
