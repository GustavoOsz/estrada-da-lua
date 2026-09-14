<?php
$host = 'localhost';
$db   = 'estrada_da_lua';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit(
        '<h2>Não foi possível conectar ao banco estrada_da_lua.</h2>' .
        '<p>Confirme se o MySQL está iniciado e revise <code>config/database.php</code>.</p>'
    );
}
