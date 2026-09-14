<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    exit('Produto não encontrado.');
}

$pageTitle = 'Fotos do produto';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imagem = uploadImagem($_FILES['imagem'], 'produtos');

        if ($imagem) {
            $stmt = $pdo->prepare("
                INSERT INTO produto_imagens (produto_id, imagem)
                VALUES (?, ?)
            ");
            $stmt->execute([$id, $imagem]);
        }

        header('Location: ' . BASE_URL . '/admin/produtos/fotos.php?id=' . $id);
        exit;
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

$excluir = filter_input(INPUT_GET, 'excluir', FILTER_VALIDATE_INT);

if ($excluir) {
    $stmt = $pdo->prepare("
        DELETE FROM produto_imagens
        WHERE id = ? AND produto_id = ?
    ");
    $stmt->execute([$excluir, $id]);

    header('Location: ' . BASE_URL . '/admin/produtos/fotos.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM produto_imagens WHERE produto_id = ? ORDER BY id DESC");
$stmt->execute([$id]);
$fotos = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-top">
    <div>
        <h1>Fotos adicionais</h1>
        <p><?= e($produto['nome']) ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/produtos/editar.php?id=<?= $id ?>">Voltar ao produto</a>
</div>

<?php if ($erro): ?><div class="alert"><?= e($erro) ?></div><?php endif; ?>

<div class="form-card" style="margin-bottom: 24px;">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="imagem">Adicionar foto</label>
            <input id="imagem" type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp" required>
        </div>
        <button class="btn" type="submit">Adicionar foto</button>
    </form>
</div>

<div class="grid">
    <?php foreach ($fotos as $foto): ?>
        <article class="card">
            <img src="<?= BASE_URL . '/' . e($foto['imagem']) ?>" alt="">
            <div class="card-body">
                <a
                    class="btn btn-danger"
                    href="<?= BASE_URL ?>/admin/produtos/fotos.php?id=<?= $id ?>&excluir=<?= (int)$foto['id'] ?>"
                    data-confirm="Excluir esta foto?">
                    Excluir foto
                </a>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
