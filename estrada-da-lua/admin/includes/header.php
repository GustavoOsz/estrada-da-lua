<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/schema.php';
$adminUnread = 0;
$adminFlashes = [];
if (isset($pdo)) {
    ensureFullSchema($pdo);
    $adminUnread = adminUnreadCount($pdo);
    $adminFlashes = pullFlashes();
}
$currentAdminPath = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
$currentAdminUri = $_SERVER['REQUEST_URI'] ?? '';
$navActive = static function(array $needles) use ($currentAdminUri): string {
    foreach ($needles as $needle) if (str_contains($currentAdminUri, $needle)) return 'active';
    return '';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17130f">
    <title><?= e($pageTitle ?? 'Painel') ?> | Estrada da Lua</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=7.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/v5.css?v=7.1">
</head>
<body class="admin-body" data-base-url="<?= e(BASE_URL) ?>">
<div class="admin-layout admin-layout-v7">
    <aside class="sidebar sidebar-v7">
        <a class="admin-logo admin-wordmark" href="<?= BASE_URL ?>/admin/dashboard.php">
            <span class="admin-moon-mark"></span>
            <span><small>PAINEL</small><strong>Estrada da Lua</strong></span>
        </a>

        <div class="admin-user admin-user-v7">
            <span><?= e(mb_strtoupper(mb_substr($_SESSION['usuario_nome'] ?? 'A', 0, 1))) ?></span>
            <div><strong><?= e($_SESSION['usuario_nome'] ?? 'Administrador') ?></strong><small><?= e($_SESSION['usuario_nivel'] ?? 'admin') ?></small></div>
        </div>

        <nav class="admin-nav-v7">
            <span class="sidebar-label">VISÃO GERAL</span>
            <a class="<?= $navActive(['/admin/dashboard.php']) ?>" href="<?= BASE_URL ?>/admin/dashboard.php"><i>⌂</i><span>Dashboard</span></a>
            <a class="sidebar-chat-link <?= $navActive(['/admin/chats.php','/admin/chat.php']) ?>" href="<?= BASE_URL ?>/admin/chats.php"><i>◍</i><span>Atendimento</span><?php if($adminUnread): ?><b><?= min(99,$adminUnread) ?></b><?php endif; ?></a>
            <a class="<?= $navActive(['/admin/pedidos.php','/admin/pedido.php']) ?>" href="<?= BASE_URL ?>/admin/pedidos.php"><i>◌</i><span>Pedidos & guias</span></a>
            <a class="<?= $navActive(['/admin/leituras.php','/admin/leitura.php']) ?>" href="<?= BASE_URL ?>/admin/leituras.php"><i>✧</i><span>Leituras</span></a>
            <a class="<?= $navActive(['/admin/financeiro.php','/admin/venda.php']) ?>" href="<?= BASE_URL ?>/admin/financeiro.php"><i>↗</i><span>Financeiro</span></a>

            <span class="sidebar-label">PREÇOS & OFERTA</span>
            <a class="<?= $navActive(['/admin/precos.php']) ?>" href="<?= BASE_URL ?>/admin/precos.php"><i>R$</i><span>Precificação</span></a>
            <a class="<?= $navActive(['/admin/servicos-baralho.php']) ?>" href="<?= BASE_URL ?>/admin/servicos-baralho.php"><i>☾</i><span>Serviços de baralho</span></a>
            <a class="<?= $navActive(['/admin/feedbacks.php']) ?>" href="<?= BASE_URL ?>/admin/feedbacks.php"><i>★</i><span>Feedbacks</span></a>

            <span class="sidebar-label">CONTEÚDO & PÚBLICO</span>
            <a class="<?= $navActive(['/admin/conteudos.php']) ?>" href="<?= BASE_URL ?>/admin/conteudos.php"><i>✎</i><span>Páginas & textos</span></a>
            <a class="<?= $navActive(['/admin/editorial.php','/admin/conteudo-editar.php']) ?>" href="<?= BASE_URL ?>/admin/editorial.php"><i>▤</i><span>Caderno & Podcast</span></a>
            <a class="<?= $navActive(['/admin/historia.php']) ?>" href="<?= BASE_URL ?>/admin/historia.php"><i>✦</i><span>História</span></a>
            <a class="<?= $navActive(['/admin/portfolio/']) ?>" href="<?= BASE_URL ?>/admin/portfolio/index.php"><i>▦</i><span>Trabalhos</span></a>
            <a class="<?= $navActive(['/admin/produtos/']) ?>" href="<?= BASE_URL ?>/admin/produtos/index.php"><i>◇</i><span>Produtos</span></a>
            <a class="<?= $navActive(['/admin/categorias.php']) ?>" href="<?= BASE_URL ?>/admin/categorias.php"><i>≡</i><span>Categorias</span></a>
            <a class="<?= $navActive(['/admin/clientes.php']) ?>" href="<?= BASE_URL ?>/admin/clientes.php"><i>♙</i><span>Clientes & perfis</span></a>
            <?php if(($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?><a class="<?= $navActive(['/admin/usuarios.php']) ?>" href="<?= BASE_URL ?>/admin/usuarios.php"><i>◎</i><span>Usuários</span></a><?php endif; ?>
        </nav>

        <div class="sidebar-bottom sidebar-bottom-v7">
            <a href="<?= BASE_URL ?>/index.php" target="_blank">Ver site ↗</a>
            <a href="<?= BASE_URL ?>/admin/logout.php">Sair</a>
        </div>
    </aside>

    <section class="admin-content admin-content-v7">
        <?php if($adminFlashes): ?><div class="server-flashes" hidden><?php foreach($adminFlashes as $flash): ?><div data-flash-type="<?= e($flash['tipo'] ?? 'success') ?>"><?= e($flash['mensagem'] ?? '') ?></div><?php endforeach; ?></div><?php endif; ?>
