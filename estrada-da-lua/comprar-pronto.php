<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('Não encontramos a peça selecionada.', 'error');
    header('Location: ' . BASE_URL . '/loja.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id,nome,estoque,pronta_entrega FROM produtos WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    setFlash('Esta peça não foi encontrada.', 'error');
    header('Location: ' . BASE_URL . '/loja.php');
    exit;
}

if (empty($produto['pronta_entrega']) || (int)$produto['estoque'] < 1) {
    setFlash('Esta peça não está disponível para compra imediata. Você pode solicitar uma criação personalizada.', 'error');
    header('Location: ' . BASE_URL . '/pedido.php?produto=' . $id);
    exit;
}

/* Compra direta exige identificação, mas navegar pela loja continua livre. */
requireClient($pdo);
startClientSession();
$_SESSION['carrinho'] = is_array($_SESSION['carrinho'] ?? null) ? $_SESSION['carrinho'] : [];
$_SESSION['carrinho'][$id] = max(1, (int)($_SESSION['carrinho'][$id] ?? 0));
$_SESSION['carrinho'][$id] = min((int)$produto['estoque'], $_SESSION['carrinho'][$id]);

header('Location: ' . BASE_URL . '/finalizar-carrinho.php');
exit;
