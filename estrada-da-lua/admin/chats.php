<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);
$pageTitle = 'Atendimento';
$status = $_GET['status'] ?? '';

$sql = "SELECT c.*, cl.nome cliente_nome, cl.email cliente_email, p.codigo_acompanhamento,
        (SELECT mensagem FROM mensagens_chat m WHERE m.conversa_id=c.id ORDER BY m.id DESC LIMIT 1) ultima_mensagem,
        (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id=c.id AND m.remetente_tipo='Cliente' AND m.lida_em IS NULL) nao_lidas
        FROM conversas c
        JOIN clientes cl ON cl.id=c.cliente_id
        LEFT JOIN pedidos p ON p.id=c.pedido_id";
$params = [];
if (in_array($status, ['Aberta','Em atendimento','Resolvida'], true)) {
    $sql .= " WHERE c.status=?";
    $params[] = $status;
}
$sql .= " ORDER BY (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id=c.id AND m.remetente_tipo='Cliente' AND m.lida_em IS NULL) DESC,
                 COALESCE(c.ultima_mensagem_em,c.criado_em) DESC";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
$totalNaoLidas = adminUnreadCount($pdo);
require __DIR__ . '/includes/header.php';
?>
<div class="admin-top admin-top-refined">
    <div>
        <span class="section-kicker">ATENDIMENTO</span>
        <h1>Conversas</h1>
        <p>Cada conversa mantém seu próprio contexto e sua própria permissão de mídia.</p>
    </div>
    <div class="chat-admin-summary"><strong><?= $totalNaoLidas ?></strong><span>mensagem(ns)<br>não lida(s)</span></div>
</div>

<div class="admin-filters filter-row">
    <a class="filter-pill <?= $status === '' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/chats.php">Todas</a>
    <?php foreach(['Aberta','Em atendimento','Resolvida'] as $s): ?>
        <a class="filter-pill <?= $status === $s ? 'active' : '' ?>" href="?status=<?= urlencode($s) ?>"><?= e($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-chat-list">
    <?php foreach($rows as $c): ?>
        <a class="admin-chat-row <?= (int)$c['nao_lidas'] ? 'unread' : '' ?>" href="<?= BASE_URL ?>/admin/chat.php?id=<?= (int)$c['id'] ?>">
            <div class="admin-chat-avatar"><?= e(clientInitials($c['cliente_nome'])) ?><?php if((int)$c['nao_lidas']): ?><b><?= min(9, (int)$c['nao_lidas']) ?></b><?php endif; ?></div>
            <div class="admin-chat-main">
                <div><strong><?= e($c['cliente_nome']) ?></strong><small><?= e($c['cliente_email']) ?></small></div>
                <h3><?= e($c['assunto']) ?></h3>
                <p><?= e(excerpt($c['ultima_mensagem'] ?? 'Conversa iniciada.', 130)) ?></p>
            </div>
            <div class="admin-chat-meta">
                <span class="<?= conversationStatusClass($c['status']) ?>"><?= e($c['status']) ?></span>
                <span class="media-permission-chip <?= !empty($c['cliente_pode_enviar_midia']) ? 'enabled' : '' ?>"><?= !empty($c['cliente_pode_enviar_midia']) ? 'Mídia liberada' : 'Mídia bloqueada' ?></span>
                <small><?= $c['ultima_mensagem_em'] ? date('d/m H:i', strtotime($c['ultima_mensagem_em'])) : '' ?></small>
                <?php if($c['codigo_acompanhamento']): ?><em><?= e($c['codigo_acompanhamento']) ?></em><?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
    <?php if(!$rows): ?><div class="empty-state"><h2>Nenhuma conversa neste filtro.</h2></div><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
