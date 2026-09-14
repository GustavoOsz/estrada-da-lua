<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$cliente = requireClient($pdo);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$st = $pdo->prepare("SELECT c.*, p.codigo_acompanhamento, p.tipo_pedido FROM conversas c LEFT JOIN pedidos p ON p.id=c.pedido_id WHERE c.id=? AND c.cliente_id=? LIMIT 1");
$st->execute([$id, $cliente['id']]);
$conversa = $st->fetch();
if (!$conversa) {
    http_response_code(404);
    exit('Conversa não encontrada.');
}

$pageTitle = 'Atendimento';
$erro = '';
$midiaClienteLiberada = !empty($conversa['cliente_pode_enviar_midia']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mensagem = trim($_POST['mensagem'] ?? '');
        $anexo = null;
        if (!empty($_FILES['anexo']['name'])) {
            if (!$midiaClienteLiberada) throw new RuntimeException('No momento, apenas a equipe pode liberar o envio de mídias neste chat.');
            $anexo = uploadImagem($_FILES['anexo'], 'chat');
        }
        if ($mensagem === '' && !$anexo) throw new RuntimeException('Escreva uma mensagem ou envie uma imagem.');
        $st = $pdo->prepare("INSERT INTO mensagens_chat (conversa_id,remetente_tipo,remetente_id,mensagem,anexo) VALUES (?,'Cliente',?,?,?)");
        $st->execute([$id, $cliente['id'], $mensagem, $anexo]);
        $pdo->prepare("UPDATE conversas SET status=IF(status='Resolvida','Aberta',status), ultima_mensagem_em=NOW() WHERE id=?")->execute([$id]);
        header('Location: ' . BASE_URL . '/conversa.php?id=' . $id . '#fim');
        exit;
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

$pdo->prepare("UPDATE mensagens_chat SET lida_em=NOW() WHERE conversa_id=? AND remetente_tipo='Admin' AND lida_em IS NULL")->execute([$id]);
$st = $pdo->prepare("SELECT * FROM mensagens_chat WHERE conversa_id=? ORDER BY id ASC");
$st->execute([$id]);
$mensagens = $st->fetchAll();
$whatsapp = whatsappUrl($pdo, 'Olá! Estou falando pelo chat do site da Estrada da Lua sobre: ' . $conversa['assunto']);
require __DIR__ . '/includes/header.php';
?>
<section class="chat-thread-page refined-thread-page">
    <div class="container chat-thread-shell refined-thread-shell">
        <header class="chat-thread-head refined-chat-head">
            <a href="<?= BASE_URL ?>/chat.php">← Conversas</a>
            <div>
                <small><?= e(siteContent($pdo, 'chat_thread_kicker', 'ATENDIMENTO')) ?></small>
                <h1><?= e($conversa['assunto']) ?></h1>
                <?php if($conversa['codigo_acompanhamento']): ?><span>Pedido <?= e($conversa['codigo_acompanhamento']) ?></span><?php endif; ?>
            </div>
            <span class="<?= conversationStatusClass($conversa['status']) ?>"><?= e($conversa['status']) ?></span>
        </header>

        <div class="chat-thread refined-chat-thread">
            <div class="chat-day-note">Esta conversa fica vinculada ao seu perfil e pode ser retomada quando você precisar.</div>
            <?php foreach($mensagens as $m): ?>
                <article class="message-bubble <?= $m['remetente_tipo'] === 'Cliente' ? 'mine' : 'theirs' ?>">
                    <div>
                        <small><?= $m['remetente_tipo'] === 'Cliente' ? 'Você' : 'Estrada da Lua' ?></small>
                        <?php if(trim((string)$m['mensagem']) !== ''): ?><p><?= nl2br(e($m['mensagem'])) ?></p><?php endif; ?>
                        <?php if($m['anexo']): ?><a class="chat-attachment" href="<?= BASE_URL . '/' . e($m['anexo']) ?>" target="_blank"><img src="<?= BASE_URL . '/' . e($m['anexo']) ?>" alt="Imagem enviada na conversa"></a><?php endif; ?>
                        <time><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></time>
                    </div>
                </article>
            <?php endforeach; ?>
            <span id="fim"></span>
        </div>

        <?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>

        <form class="chat-composer refined-chat-composer" method="post" enctype="multipart/form-data">
            <?php if($midiaClienteLiberada): ?>
                <label class="chat-file" title="Enviar imagem">
                    <input type="file" name="anexo" accept=".jpg,.jpeg,.png,.webp">
                    <span>＋</span>
                </label>
            <?php else: ?>
                <button class="chat-file chat-file-disabled" type="button" aria-disabled="true" title="Somente a equipe pode liberar o envio de mídias">
                    <span>🔒</span>
                </button>
            <?php endif; ?>
            <textarea name="mensagem" rows="1" placeholder="Escreva sua mensagem..."></textarea>
            <button class="btn btn-dark">Enviar</button>
        </form>

        <div class="chat-channel-choice">
            <span><?= $midiaClienteLiberada ? 'Envio de imagem liberado para esta fase do atendimento.' : 'O envio de mídia está bloqueado por padrão. Se precisar, a equipe pode liberar esse recurso.' ?></span>
            <?php if($whatsapp): ?><a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Prefere continuar pelo WhatsApp? ↗</a><?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
