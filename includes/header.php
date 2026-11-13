<?php
/**
 * Header común HTML5 + Bootstrap 5
 * Proyecto: TecnoStock - Sistema de Gestión de Inventario
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/includes/helpers.php';

$tituloPagina = $tituloPagina ?? 'Sistema de Inventario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($tituloPagina) ?> | TecnoStock</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
