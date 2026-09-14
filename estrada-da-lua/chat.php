<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$cliente = requireClient($pdo);
$pageTitle = 'Atendimento';
$erro = '';
$pedidoSelecionado = (int)($_GET['pedido'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $assunto = trim($_POST['assunto'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        $pedidoId = (int)($_POST['pedido_id'] ?? 0);
        if ($assunto === '' || $mensagem === '') throw new RuntimeException('Conte o assunto e escreva uma primeira mensagem.');
        if ($pedidoId) {
            $v = $pdo->prepare("SELECT id FROM pedidos WHERE id=? AND cliente_id=?");
            $v->execute([$pedidoId, $cliente['id']]);
            if (!$v->fetchColumn()) $pedidoId = 0;
        }
        $pdo->beginTransaction();
        $s = $pdo->prepare("INSERT INTO conversas (cliente_id,pedido_id,assunto,status,ultima_mensagem_em) VALUES (?,?,?,'Aberta',NOW())");
        $s->execute([$cliente['id'], $pedidoId ?: null, $assunto]);
        $cid = (int)$pdo->lastInsertId();
        $s = $pdo->prepare("INSERT INTO mensagens_chat (conversa_id,remetente_tipo,remetente_id,mensagem) VALUES (?,'Cliente',?,?)");
        $s->execute([$cid, $cliente['id'], $mensagem]);
        $pdo->commit();
        setFlash('Sua conversa foi iniciada. A equipe vai responder por aqui.', 'success');
        header('Location: ' . BASE_URL . '/conversa.php?id=' . $cid);
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getMessage();
    }
}

$st = $pdo->prepare("SELECT c.*, p.codigo_acompanhamento,
                    (SELECT mensagem FROM mensagens_chat m WHERE m.conversa_id=c.id ORDER BY m.id DESC LIMIT 1) ultima_mensagem,
                    (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id=c.id AND m.remetente_tipo='Admin' AND m.lida_em IS NULL) nao_lidas
                    FROM conversas c
                    LEFT JOIN pedidos p ON p.id=c.pedido_id
                    WHERE c.cliente_id=?
                    ORDER BY COALESCE(c.ultima_mensagem_em,c.criado_em) DESC");
$st->execute([$cliente['id']]);
$conversas = $st->fetchAll();
$pedidos = $pdo->prepare("SELECT id,codigo_acompanhamento,tipo_pedido FROM pedidos WHERE cliente_id=? ORDER BY data_pedido DESC LIMIT 20");
$pedidos->execute([$cliente['id']]);
$pedidos = $pedidos->fetchAll();
$whatsapp = whatsappUrl($pdo, 'Olá! Vim pelo atendimento do site da Estrada da Lua e gostaria de continuar a conversa por WhatsApp.');
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero chat-hero refined-chat-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow" data-content-key="chat_hero_eyebrow"><?= e(siteContent($pdo, 'chat_hero_eyebrow', 'ATENDIMENTO')) ?></span>
            <h1 data-content-key="chat_hero_titulo"><?= nl2br(e(siteContent($pdo, 'chat_hero_titulo', "Uma conversa que fica\njunto do seu caminho."))) ?></h1>
        </div>
        <p data-content-key="chat_hero_texto"><?= e(siteContent($pdo, 'chat_hero_texto', 'Tire dúvidas, converse sobre sua encomenda e acompanhe decisões sem perder mensagens no meio de outros aplicativos.')) ?></p>
    </div>
</section>

<section class="section chat-page">
    <div class="container chat-dashboard-grid">
        <aside class="chat-new-card refined-chat-card">
            <span class="section-kicker" data-content-key="chat_nova_kicker"><?= e(siteContent($pdo, 'chat_nova_kicker', 'NOVA CONVERSA')) ?></span>
            <h2 data-content-key="chat_nova_titulo"><?= e(siteContent($pdo, 'chat_nova_titulo', 'Como podemos ajudar?')) ?></h2>
            <?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group"><label>Assunto</label><input name="assunto" placeholder="Ex.: dúvida sobre minha guia" required></div>
                <?php if($pedidos): ?>
                    <div class="form-group">
                        <label>Relacionar a um pedido <span class="optional">opcional</span></label>
                        <select name="pedido_id">
                            <option value="">Conversa geral</option>
                            <?php foreach($pedidos as $p): ?>
                                <option value="<?= (int)$p['id'] ?>" <?= $pedidoSelecionado === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['codigo_acompanhamento'] . ' · ' . ($p['tipo_pedido'] ?: 'Pedido')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="form-group"><label>Mensagem</label><textarea name="mensagem" placeholder="Conte o que você precisa. Não precisa escrever de forma formal." required></textarea></div>
                <button class="btn btn-dark btn-wide">Iniciar conversa</button>
            </form>
            <?php if($whatsapp): ?>
                <div class="whatsapp-fallback">
                    <span>Prefere continuar pelo WhatsApp?</span>
                    <a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Abrir WhatsApp ↗</a>
                </div>
            <?php endif; ?>
        </aside>

        <div class="chat-list-panel">
            <div class="section-heading">
                <span class="section-kicker" data-content-key="chat_lista_kicker"><?= e(siteContent($pdo, 'chat_lista_kicker', 'SUAS CONVERSAS')) ?></span>
                <h2 class="display-title" data-content-key="chat_lista_titulo"><?= e(siteContent($pdo, 'chat_lista_titulo', 'Atendimento em um só lugar.')) ?></h2>
            </div>
            <?php if($conversas): ?>
                <div class="conversation-list refined-conversation-list">
                    <?php foreach($conversas as $c): ?>
                        <a class="conversation-item <?= (int)$c['nao_lidas'] ? 'has-unread' : '' ?>" href="<?= BASE_URL ?>/conversa.php?id=<?= (int)$c['id'] ?>">
                            <div class="conversation-icon"><span></span><?php if((int)$c['nao_lidas']): ?><b><?= min(9, (int)$c['nao_lidas']) ?></b><?php endif; ?></div>
                            <div>
                                <div class="conversation-top"><strong><?= e($c['assunto']) ?></strong><small><?= $c['ultima_mensagem_em'] ? date('d/m H:i', strtotime($c['ultima_mensagem_em'])) : '' ?></small></div>
                                <p><?= e(excerpt($c['ultima_mensagem'] ?? 'Conversa iniciada.', 120)) ?></p>
                                <div class="conversation-bottom"><span class="<?= conversationStatusClass($c['status']) ?>"><?= e($c['status']) ?></span><?php if($c['codigo_acompanhamento']): ?><small><?= e($c['codigo_acompanhamento']) ?></small><?php endif; ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="chat-empty">
                    <span class="chat-bubble-large"></span>
                    <h3>Você ainda não iniciou nenhuma conversa.</h3>
                    <p>Quando precisar, este espaço fica entre você e a equipe — com contexto, histórico e proximidade.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
