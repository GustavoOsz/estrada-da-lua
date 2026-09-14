<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Categorias';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $nome = trim($_POST['nome'] ?? '');

        if ($nome !== '') {
            $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
            $stmt->execute([$nome]);
            $mensagem = 'Categoria criada.';
        }
    }

    if ($acao === 'editar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');

        if ($id && $nome !== '') {
            $stmt = $pdo->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
            $stmt->execute([$nome, $id]);
            $mensagem = 'Categoria atualizada.';
        }
    }

    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
            $stmt->execute([$id]);

            if ((int)$stmt->fetchColumn() > 0) {
                $erro = 'Não é possível excluir uma categoria que possui produtos.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                $mensagem = 'Categoria excluída.';
            }
        }
    }
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="admin-top">
    <h1>Categorias</h1>
</div>

<?php if ($mensagem): ?><div class="alert"><?= e($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alert"><?= e($erro) ?></div><?php endif; ?>

<div class="form-card" style="margin-bottom: 24px;">
    <form method="post">
        <input type="hidden" name="acao" value="criar">
        <div class="form-group">
            <label for="nome">Nova categoria</label>
            <input id="nome" name="nome" required>
        </div>
        <button class="btn" type="submit">Adicionar</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="acao" value="editar">
                            <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                            <input name="nome" value="<?= e($categoria['nome']) ?>" required>
                            <button class="btn btn-secondary" type="submit">Salvar</button>
                        </form>
                    </td>
                    <td>
                        <form method="post" data-confirm="Excluir esta categoria?">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                            <button class="btn btn-danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
