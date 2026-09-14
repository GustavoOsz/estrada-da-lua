<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$st = $pdo->prepare("SELECT c.*, cl.nome cliente_nome, cl.email cliente_email, cl.telefone cliente_telefone, p.codigo_acompanhamento, p.tipo_pedido
                     FROM conversas c
                     JOIN clientes cl ON cl.id=c.cliente_id
                     LEFT JOIN pedidos p ON p.id=c.pedido_id
                     WHERE c.id=? LIMIT 1");
$st->execute([$id]);
$conversa = $st->fetch();
if (!$conversa) { http_response_code(404); exit('Conversa não encontrada.'); }

$pageTitle = 'Conversa';
$erro = '';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $acao = $_POST['acao'] ?? 'mensagem';
        if ($acao === 'status') {
            $novo = $_POST['status'] ?? '';
            if (!in_array($novo, ['Aberta','Em atendimento','Resolvida'], true)) throw new RuntimeException('Status inválido.');
            $pdo->prepare("UPDATE conversas SET status=? WHERE id=?")->execute([$novo, $id]);
            setFlash('Status da conversa atualizado.', 'success');
        } elseif ($acao === 'midia') {
            $liberado = !empty($_POST['cliente_pode_enviar_midia']) ? 1 : 0;
            $pdo->prepare("UPDATE conversas SET cliente_pode_enviar_midia=? WHERE id=?")->execute([$liberado, $id]);
            setFlash($liberado ? 'Envio de mídia liberado apenas nesta conversa.' : 'Envio de mídia bloqueado nesta conversa.', 'success');
        } else {
            $mensagem = trim($_POST['mensagem'] ?? '');
            $anexo = null;
            if (!empty($_FILES['anexo']['name'])) $anexo = uploadImagem($_FILES['anexo'], 'chat');
            if ($mensagem === '' && !$anexo) throw new RuntimeException('Escreva uma resposta ou envie uma imagem.');
            $st = $pdo->prepare("INSERT INTO mensagens_chat (conversa_id,remetente_tipo,remetente_id,mensagem,anexo) VALUES (?,'Admin',?,?,?)");
            $st->execute([$id, $_SESSION['usuario_id'] ?? null, $mensagem, $anexo]);
            $pdo->prepare("UPDATE conversas SET status=IF(status='Aberta','Em atendimento',status), ultima_mensagem_em=NOW() WHERE id=?")->execute([$id]);
        }
        header('Location: ' . BASE_URL . '/admin/chat.php?id=' . $id . '#fim');
        exit;
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

$pdo->prepare("UPDATE mensagens_chat SET lida_em=NOW() WHERE conversa_id=? AND remetente_tipo='Cliente' AND lida_em IS NULL")->execute([$id]);
$st = $pdo->prepare("SELECT * FROM mensagens_chat WHERE conversa_id=? ORDER BY id ASC");
$st->execute([$id]);
$mensagens = $st->fetchAll();
$st = $pdo->prepare("SELECT status, cliente_pode_enviar_midia FROM conversas WHERE id=?");
$st->execute([$id]);
$estado = $st->fetch() ?: [];
$conversa['status'] = $estado['status'] ?? $conversa['status'];
$conversa['cliente_pode_enviar_midia'] = (int)($estado['cliente_pode_enviar_midia'] ?? 0);
require __DIR__ . '/includes/header.php';
?>
<div class="admin-chat-thread-layout">
    <section class="admin-chat-thread">
        <header class="admin-chat-thread-head refined-thread-head">
            <div>
                <a class="back-link" href="<?= BASE_URL ?>/admin/chats.php">← Voltar às conversas</a>
                <h1><?= e($conversa['assunto']) ?></h1>
                <p><?= e($conversa['cliente_nome']) ?> · <?= e($conversa['cliente_email']) ?></p>
            </div>
            <form method="post" class="chat-status-form">
                <input type="hidden" name="acao" value="status">
                <select name="status" onchange="this.form.submit()">
                    <?php foreach(['Aberta','Em atendimento','Resolvida'] as $s): ?><option <?= $conversa['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                </select>
            </form>
        </header>

        <?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
        <div class="chat-thread admin-thread refined-chat-thread">
            <div class="chat-day-note">Histórico completo do atendimento.</div>
            <?php foreach($mensagens as $m): ?>
                <article class="message-bubble <?= $m['remetente_tipo'] === 'Admin' ? 'mine' : 'theirs' ?>">
                    <div><small><?= $m['remetente_tipo'] === 'Admin' ? 'Equipe' : 'Cliente' ?></small><?php if(trim((string)$m['mensagem']) !== ''): ?><p><?= nl2br(e($m['mensagem'])) ?></p><?php endif; ?><?php if($m['anexo']): ?><a class="chat-attachment" href="<?= BASE_URL . '/' . e($m['anexo']) ?>" target="_blank"><img src="<?= BASE_URL . '/' . e($m['anexo']) ?>" alt="Anexo"></a><?php endif; ?><time><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></time></div>
                </article>
            <?php endforeach; ?>
            <span id="fim"></span>
        </div>

        <form class="chat-composer admin-composer" method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="mensagem">
            <label class="chat-file"><input type="file" name="anexo" accept=".jpg,.jpeg,.png,.webp"><span>＋</span></label>
            <textarea name="mensagem" rows="1" placeholder="Responder ao cliente..."></textarea>
            <button class="btn btn-dark">Enviar</button>
        </form>
    </section>

    <aside class="admin-chat-context refined-context-card">
        <span class="section-kicker">CONTEXTO</span>
        <h2><?= e($conversa['cliente_nome']) ?></h2>
        <div class="detail-list">
            <div><small>E-mail</small><strong><?= e($conversa['cliente_email']) ?></strong></div>
            <div><small>Telefone</small><strong><?= e($conversa['cliente_telefone']) ?></strong></div>
            <?php if($conversa['codigo_acompanhamento']): ?><div><small>Pedido</small><strong><?= e($conversa['codigo_acompanhamento']) ?></strong><a class="table-link" href="<?= BASE_URL ?>/admin/pedido.php?id=<?= (int)$conversa['pedido_id'] ?>">Abrir pedido →</a></div><?php endif; ?>
            <div><small>Status</small><strong><?= e($conversa['status']) ?></strong></div>
        </div>

        <form method="post" class="conversation-media-control">
            <input type="hidden" name="acao" value="midia">
            <span class="section-kicker">MÍDIA NESTA CONVERSA</span>
            <label class="switch-card <?= !empty($conversa['cliente_pode_enviar_midia']) ? 'is-on' : '' ?>">
                <input type="checkbox" name="cliente_pode_enviar_midia" value="1" <?= !empty($conversa['cliente_pode_enviar_midia']) ? 'checked' : '' ?>>
                <span class="switch-visual"><i></i></span>
                <span class="switch-copy"><strong><?= !empty($conversa['cliente_pode_enviar_midia']) ? 'Cliente pode enviar mídia' : 'Envio de mídia bloqueado' ?></strong><small>Esta escolha vale somente para esta conversa.</small></span>
            </label>
            <button class="btn btn-secondary btn-wide">Salvar permissão</button>
        </form>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
