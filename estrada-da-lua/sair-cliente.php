<?php
require_once __DIR__ . '/config/app.php';
session_start();unset($_SESSION['cliente_id']);header('Location: '.BASE_URL.'/index.php');exit;
