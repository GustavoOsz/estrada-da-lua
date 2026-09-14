<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
startClientSession();
if (empty($_SESSION['usuario_id'])) { http_response_code(403); exit('Prévia disponível apenas no painel administrativo.'); }
$pageTitle='Prévia do atendimento';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero chat-hero refined-chat-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow" data-content-key="chat_hero_eyebrow"><?= e(siteContent($pdo,'chat_hero_eyebrow','ATENDIMENTO')) ?></span>
            <h1 data-content-key="chat_hero_titulo"><?= nl2br(e(siteContent($pdo,'chat_hero_titulo',"Uma conversa que fica\njunto do seu caminho."))) ?></h1>
        </div>
        <p data-content-key="chat_hero_texto"><?= e(siteContent($pdo,'chat_hero_texto','Tire dúvidas, converse sobre sua encomenda e acompanhe decisões sem perder mensagens no meio de outros aplicativos.')) ?></p>
    </div>
</section>
<section class="section chat-page">
    <div class="container chat-dashboard-grid">
        <aside class="chat-new-card refined-chat-card">
            <span class="section-kicker" data-content-key="chat_nova_kicker"><?= e(siteContent($pdo,'chat_nova_kicker','NOVA CONVERSA')) ?></span>
            <h2 data-content-key="chat_nova_titulo"><?= e(siteContent($pdo,'chat_nova_titulo','Como podemos ajudar?')) ?></h2>
            <div class="form-group"><label>Assunto</label><input value="Dúvida sobre minha guia" readonly></div>
            <div class="form-group"><label>Mensagem</label><textarea readonly>Gostaria de conversar sobre os detalhes da minha encomenda.</textarea></div>
            <span class="btn btn-dark btn-wide">Iniciar conversa</span>
        </aside>
        <div class="chat-list-panel">
            <div class="section-heading">
                <span class="section-kicker" data-content-key="chat_lista_kicker"><?= e(siteContent($pdo,'chat_lista_kicker','SUAS CONVERSAS')) ?></span>
                <h2 class="display-title" data-content-key="chat_lista_titulo"><?= e(siteContent($pdo,'chat_lista_titulo','Atendimento em um só lugar.')) ?></h2>
            </div>
            <div class="conversation-list refined-conversation-list">
                <div class="conversation-item has-unread">
                    <div class="conversation-icon"><span></span><b>1</b></div>
                    <div><div class="conversation-top"><strong>Dúvida sobre minha guia</strong><small>agora</small></div><p>Claro! Podemos ajustar todos os detalhes por aqui.</p><div class="conversation-bottom"><span class="status status-production">Em atendimento</span><small>EDL-EXEMPLO</small></div></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
