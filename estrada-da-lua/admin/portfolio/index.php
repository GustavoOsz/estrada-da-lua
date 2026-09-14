<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Trabalhos';

$trabalhos = $pdo->query("
    SELECT * FROM portfolio
    ORDER BY data_cadastro DESC
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-top">
    <h1>Trabalhos realizados</h1>
    <a class="btn" href="<?= BASE_URL ?>/admin/portfolio/novo.php">Novo trabalho</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Imagem</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trabalhos as $item): ?>
                <tr>
                    <td>
                        <?php if ($item['imagem']): ?>
                            <img class="thumb" src="<?= BASE_URL . '/' . e($item['imagem']) ?>" alt="">
                        <?php endif; ?>
                    </td>
                    <td><?= e($item['titulo']) ?></td>
                    <td><?= e($item['descricao']) ?></td>
                    <td class="actions">
                        <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/portfolio/editar.php?id=<?= (int)$item['id'] ?>">Editar</a>
                        <a class="btn btn-danger"
                           href="<?= BASE_URL ?>/admin/portfolio/excluir.php?id=<?= (int)$item['id'] ?>"
                           data-confirm="Excluir este trabalho?">
                           Excluir
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
