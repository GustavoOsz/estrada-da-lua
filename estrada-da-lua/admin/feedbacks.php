<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);

$pageTitle = 'Feedbacks';
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $acao = $_POST['acao'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        if ($acao === 'add') {
            $nome = trim($_POST['nome'] ?? '');
            $texto = trim($_POST['texto'] ?? '');
            $origem = trim($_POST['origem'] ?? 'Geral');
            $nota = (int)($_POST['nota'] ?? 5);
            $destaque = !empty($_POST['destaque']) ? 1 : 0;

            if ($nome === '') throw new RuntimeException('Informe o nome do cliente.');
            if (mb_strlen($nome) > 120) throw new RuntimeException('O nome do cliente está muito longo.');
            if (mb_strlen($texto) < 8) throw new RuntimeException('Escreva um relato com pelo menos 8 caracteres.');
            if (!in_array($origem, ['Geral','Guia personalizada','Loja','Baralho Cigano'], true)) $origem = 'Geral';
            $nota = max(1, min(5, $nota));

            insertFeedbackCompat($pdo, ['nome_cliente'=>$nome,'texto'=>$texto,'nota'=>$nota,'origem'=>$origem,'aprovado'=>1,'destaque'=>$destaque]);
            $msg = 'Feedback adicionado e publicado com sucesso.';
        }

        if ($acao === 'toggle' && $id) {
            $stmt = $pdo->prepare("UPDATE feedbacks SET aprovado=?, destaque=? WHERE id=?");
            $stmt->execute([!empty($_POST['aprovado']) ? 1 : 0, !empty($_POST['destaque']) ? 1 : 0, $id]);
            $msg = 'Feedback atualizado.';
        }

        if ($acao === 'delete' && $id) {
            $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id=?");
            $stmt->execute([$id]);
            $msg = 'Feedback removido.';
        }
    } catch (Throwable $e) {
        $erro = 'Não foi possível concluir a ação: ' . $e->getMessage();
    }
}

try {
    $rows = $pdo->query("SELECT * FROM feedbacks ORDER BY aprovado ASC, destaque DESC, criado_em DESC, id DESC")->fetchAll();
    $rows = array_map('normalizeFeedbackRow', $rows);
} catch (Throwable $e) {
    $rows = [];
    $erro = $erro ?: 'Não foi possível carregar os feedbacks. Execute o setup do projeto para atualizar a estrutura do banco.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="admin-top admin-top-refined">
    <div>
        <span class="section-kicker">PROVA SOCIAL REAL</span>
        <h1>Feedbacks</h1>
        <p>Cadastre, revise e publique relatos reais sem sair do painel.</p>
    </div>
    <div class="admin-top-badge"><strong><?= count($rows) ?></strong><span>feedback(s)</span></div>
</div>

<?php if($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>

<div class="admin-two-col feedback-admin feedback-admin-refined">
    <section class="form-card admin-panel-card">
        <span class="section-kicker">NOVO RELATO</span>
        <h2>Adicionar manualmente</h2>
        <p class="admin-helper">Use esta área apenas para feedbacks reais recebidos por outros canais.</p>
        <form method="post" novalidate>
            <input type="hidden" name="acao" value="add">
            <div class="form-group"><label>Nome do cliente</label><input name="nome" maxlength="120" required value="<?= e((($_POST['acao'] ?? '') === 'add') ? ($_POST['nome'] ?? '') : '') ?>"></div>
            <div class="form-grid two">
                <div class="form-group"><label>Origem</label><select name="origem"><option>Geral</option><option>Guia personalizada</option><option>Loja</option><option>Baralho Cigano</option></select></div>
                <div class="form-group"><label>Nota</label><select name="nota"><option value="5">5 — Excelente</option><option value="4">4 — Muito bom</option><option value="3">3 — Bom</option><option value="2">2 — Pode melhorar</option><option value="1">1 — Ruim</option></select></div>
            </div>
            <div class="form-group"><label>Relato</label><textarea name="texto" minlength="8" required placeholder="Conte o que o cliente compartilhou..."></textarea></div>
            <label class="mini-check visual-check"><input type="checkbox" name="destaque"><span>Dar destaque especial no site</span></label>
            <button class="btn btn-dark btn-wide">Adicionar feedback</button>
        </form>
    </section>

    <section class="feedback-admin-list">
        <?php if($rows): ?>
            <?php foreach($rows as $r): ?>
                <article class="detail-card feedback-review-card">
                    <div class="feedback-admin-head">
                        <div>
                            <strong><?= e($r['nome_cliente'] ?? 'Cliente') ?></strong>
                            <small><?= e($r['origem'] ?? 'Geral') ?> · <?= str_repeat('★', max(1, min(5, (int)($r['nota'] ?? 5)))) ?></small>
                        </div>
                        <span class="status <?= !empty($r['aprovado']) ? 'status-done' : 'status-new' ?>"><?= !empty($r['aprovado']) ? 'Publicado' : 'Aguardando' ?></span>
                    </div>
                    <p>“<?= e($r['texto'] ?? '') ?>”</p>
                    <form method="post" class="actions feedback-inline-actions">
                        <input type="hidden" name="acao" value="toggle">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <label class="mini-check"><input type="checkbox" name="aprovado" <?= !empty($r['aprovado']) ? 'checked' : '' ?>> publicado</label>
                        <label class="mini-check"><input type="checkbox" name="destaque" <?= !empty($r['destaque']) ? 'checked' : '' ?>> destaque</label>
                        <button class="btn btn-secondary">Salvar</button>
                    </form>
                    <form method="post" data-confirm="Excluir este feedback?">
                        <input type="hidden" name="acao" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="table-link danger-link">Excluir</button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state admin-empty-state"><h2>Nenhum feedback cadastrado ainda.</h2><p>Os relatos enviados pelo site também aparecerão aqui para revisão.</p></div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
