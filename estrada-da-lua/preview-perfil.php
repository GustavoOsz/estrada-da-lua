<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
startClientSession();
if (empty($_SESSION['usuario_id'])) { http_response_code(403); exit('Prévia disponível apenas no painel administrativo.'); }
$pageTitle='Prévia do perfil';
require __DIR__ . '/includes/header.php';
?>
<section class="profile-hero profile-hero-v5">
<div class="container profile-hero-v5-grid">
    <div class="profile-hero-copy">
        <span class="eyebrow light" data-content-key="perfil_hero_eyebrow"><?= e(siteContent($pdo,'perfil_hero_eyebrow','MEU PERFIL')) ?></span>
        <h1>Olá, Cliente.<br><em data-content-key="perfil_hero_destaque"><?= e(siteContent($pdo,'perfil_hero_destaque','Que bom ter você por aqui.')) ?></em></h1>
        <p data-content-key="perfil_hero_texto"><?= e(siteContent($pdo,'perfil_hero_texto','Este espaço é seu: pedidos, leituras, preferências e atendimento em um só lugar.')) ?></p>
    </div>
    <div class="profile-identity-card"><div class="profile-avatar-large"><span>CL</span></div><div><small>SEU ESPAÇO NA ESTRADA</small><strong>Cliente de exemplo</strong><span>cliente@exemplo.com</span></div></div>
</div>
</section>
<section class="profile-overview"><div class="container profile-overview-grid"><div><small>Pedidos e leituras</small><strong>4</strong></div><div><small>Em andamento</small><strong>1</strong></div><div><small>Atendimento</small><strong>Abrir chat</strong></div></div></section>
<section class="section"><div class="container"><div class="section-heading"><span class="section-kicker">PRÉVIA DO PERFIL</span><h2 class="display-title">Um espaço que vai ficando mais seu.</h2></div></div></section>
<section class="account-hero account-hero-v5 preview-account-block"><div class="container account-hero-grid"><div><span class="eyebrow light" data-content-key="conta_hero_eyebrow"><?= e(siteContent($pdo,'conta_hero_eyebrow','SUA ESTRADA, GUARDADA AQUI')) ?></span><h1 data-content-key="conta_hero_titulo"><?= nl2br(e(siteContent($pdo,'conta_hero_titulo',"Uma conta para deixar\na experiência mais sua."))) ?></h1></div><p data-content-key="conta_hero_texto"><?= e(siteContent($pdo,'conta_hero_texto','Para comprar, pedir uma peça, solicitar uma leitura ou conversar com a equipe, você entra na sua conta. Navegar e conhecer a Estrada da Lua continua livre.')) ?></p></div></section>
<section class="section account-auth-section"><div class="container"><div class="form-card account-form" style="max-width:760px"><span class="section-kicker">CRIAR PERFIL</span><h2 data-content-key="conta_cadastro_titulo"><?= e(siteContent($pdo,'conta_cadastro_titulo','Comece com o essencial.')) ?></h2><div class="preference-block refined-preferences"><div class="preference-block-head"><span data-content-key="conta_preferencias_titulo"><?= e(siteContent($pdo,'conta_preferencias_titulo','Se quiser, conte o que mais te chama atenção')) ?></span><small data-content-key="conta_preferencias_texto"><?= e(siteContent($pdo,'conta_preferencias_texto','Opcional. Isso ajuda a personalizar o perfil sem alongar o cadastro.')) ?></small></div><div class="signup-interest-grid refined-grid"><div class="selectable-card"><span class="interest-card-icon">✦</span><span class="interest-card-copy"><strong>Guias</strong><small>Peças e pronta entrega</small></span></div><div class="selectable-card"><span class="interest-card-icon">☾</span><span class="interest-card-copy"><strong>Baralho</strong><small>Leituras e símbolos</small></span></div><div class="selectable-card"><span class="interest-card-icon">✎</span><span class="interest-card-copy"><strong>Conteúdos</strong><small>Textos e novidades</small></span></div></div></div><div class="consent-card"><strong data-content-key="conta_marketing_titulo"><?= e(siteContent($pdo,'conta_marketing_titulo','Quero receber novidades da Estrada da Lua')) ?></strong><small data-content-key="conta_marketing_texto"><?= e(siteContent($pdo,'conta_marketing_texto','Ofertas, novos trabalhos e conteúdos. Você pode mudar isso no perfil quando quiser.')) ?></small></div><div class="account-promise" style="margin-top:18px"><h3 data-content-key="conta_lateral_titulo"><?= e(siteContent($pdo,'conta_lateral_titulo','Não é só um login.')) ?></h3><p data-content-key="conta_lateral_texto"><?= e(siteContent($pdo,'conta_lateral_texto','Seu perfil reúne compras, encomendas, leituras, atendimento e preferências.')) ?></p><h3 data-content-key="conta_login_titulo"><?= e(siteContent($pdo,'conta_login_titulo','Entre na sua estrada.')) ?></h3><h3 data-content-key="conta_login_lateral_titulo"><?= e(siteContent($pdo,'conta_login_lateral_titulo','Primeira vez por aqui?')) ?></h3><p data-content-key="conta_login_lateral_texto"><?= e(siteContent($pdo,'conta_login_lateral_texto','Crie sua conta em poucos campos e mantenha suas preferências sob controle.')) ?></p></div></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
