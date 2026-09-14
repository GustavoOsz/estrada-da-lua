<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/functions.php';
$clienteHeader = null;
$chatUnread = 0;
$cartCount = 0;
$flashes = [];
if (isset($pdo)) {
    require_once __DIR__ . '/../config/schema.php';
    ensureFullSchema($pdo);
    $clienteHeader = currentClient($pdo);
    if ($clienteHeader) $chatUnread = clientUnreadCount($pdo, (int)$clienteHeader['id']);
    $cartCount = cartCount();
    $flashes = pullFlashes();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17130f">
    <meta name="description" content="Estrada da Lua — guias, peças autorais e leituras de Baralho Cigano com cuidado, escuta e presença.">
    <title><?= e($pageTitle ?? APP_NAME) ?> | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=7.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/v5.css?v=7.1">
</head>
<body data-base-url="<?= e(BASE_URL) ?>">
<div class="top-ritual">
    <div class="container top-ritual-inner">
        <span>Feito com tempo, intenção e cuidado</span>
        <div class="top-links">
            <button type="button" class="top-future" data-coming-soon="Caderno da Lua" data-coming-text="Textos, processos, símbolos e histórias da Estrada da Lua. Estamos preparando este espaço.">Caderno</button>
            <button type="button" class="top-future" data-coming-soon="Vozes da Estrada" data-coming-text="O podcast da Estrada da Lua está sendo construído para virar companhia em outros momentos do seu dia.">Podcast</button>
            <?php if($clienteHeader): ?>
                <a href="<?= BASE_URL ?>/meus-pedidos.php">Meus pedidos</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/conta.php?modo=cadastrar">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container navbar">
        <a class="brand-symbol" href="<?= BASE_URL ?>/index.php" aria-label="Estrada da Lua — voltar ao início" title="Estrada da Lua">
            <img src="<?= BASE_URL . LOGO_PATH ?>" alt="" aria-hidden="true">
        </a>
        <nav class="main-nav" aria-label="Navegação principal">
            <a href="<?= BASE_URL ?>/index.php">Início</a>
            <a href="<?= BASE_URL ?>/portfolio.php">Memórias</a>
            <a href="<?= BASE_URL ?>/loja.php">Loja</a>
            <a href="<?= BASE_URL ?>/pedido.php">Faça a sua</a>
            <a href="<?= BASE_URL ?>/baralho-cigano.php">Baralho Cigano</a>
        </nav>
        <div class="header-actions">
            <a class="header-icon-link header-cart-link" href="<?= BASE_URL ?>/carrinho.php" aria-label="Sacola<?= $cartCount ? ' com '.$cartCount.' item(ns)' : '' ?>" title="Sacola">
                <span class="header-icon-shell" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="icon-svg" focusable="false" aria-hidden="true">
                        <path d="M7 8.5h10l-.8 10H7.8L7 8.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9 9V7a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <?php if($cartCount): ?><b><?= min(99,$cartCount) ?></b><?php endif; ?>
            </a>
            <?php if($clienteHeader): ?>
                <a class="header-icon-link header-chat-link" href="<?= BASE_URL ?>/chat.php" aria-label="Atendimento" title="Atendimento">
                    <span class="header-icon-shell" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="icon-svg" focusable="false" aria-hidden="true">
                            <path d="M6.4 16.2 5 19l3.7-1.5h6.1a5.2 5.2 0 0 0 0-10.4H8.6a5.2 5.2 0 1 0 0 10.4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M9 11.8h.01M12 11.8h.01M15 11.8h.01" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <?php if($chatUnread): ?><b><?= min(99,$chatUnread) ?></b><?php endif; ?>
                </a>
            <?php endif; ?>
            <a class="profile-entry" href="<?= BASE_URL ?>/<?= $clienteHeader ? 'perfil.php' : 'conta.php' ?>" aria-label="<?= $clienteHeader ? 'Abrir meu perfil' : 'Entrar ou criar conta' ?>">
                <span class="profile-icon" aria-hidden="true"></span>
                <span class="profile-entry-text">
                    <small><?= $clienteHeader ? 'Meu perfil' : 'Minha conta' ?></small>
                    <strong><?= $clienteHeader ? e(explode(' ', $clienteHeader['nome'])[0]) : 'Entrar' ?></strong>
                </span>
            </a>
        </div>
    </div>
</header>
<nav class="mobile-bottom-nav" aria-label="Navegação móvel">
    <a href="<?= BASE_URL ?>/index.php"><span>⌂</span>Início</a>
    <a href="<?= BASE_URL ?>/loja.php"><span>◇</span>Loja</a>
    <a href="<?= BASE_URL ?>/pedido.php"><span>✦</span>Faça a sua</a>
    <a href="<?= BASE_URL ?>/baralho-cigano.php"><span>☾</span>Baralho</a>
    <a href="<?= BASE_URL ?>/<?= $clienteHeader ? 'perfil.php' : 'conta.php' ?>"><span class="mobile-user-dot">◎<?php if($chatUnread): ?><b><?= min(9,$chatUnread) ?></b><?php endif; ?></span>Perfil</a>
</nav>
<?php if($flashes): ?><div class="server-flashes" hidden><?php foreach($flashes as $flash): ?><div data-flash-type="<?= e($flash['tipo']??'success') ?>"><?= e($flash['mensagem']??'') ?></div><?php endforeach; ?></div><?php endif; ?>
<main>
