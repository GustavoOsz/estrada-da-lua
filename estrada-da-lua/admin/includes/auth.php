<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/app.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}
