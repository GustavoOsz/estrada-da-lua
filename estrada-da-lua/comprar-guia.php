<?php
/* Compatibilidade com links das versões anteriores. A compra de pronta entrega
 * agora possui um fluxo único e explícito em comprar-pronto.php. */
require_once __DIR__ . '/config/app.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
$destino = BASE_URL . '/loja.php';
if ($id) $destino = BASE_URL . '/comprar-pronto.php?id=' . $id;
header('Location: ' . $destino, true, 302);
exit;
