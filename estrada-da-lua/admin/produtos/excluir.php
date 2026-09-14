<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("DELETE FROM produto_imagens WHERE produto_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        exit('Não foi possível excluir o produto. Ele pode estar ligado a um pedido.');
    }
}

header('Location: ' . BASE_URL . '/admin/produtos/index.php');
exit;
