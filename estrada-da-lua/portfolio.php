<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle = 'Memórias';
$trabalhos = $pdo->query("SELECT * FROM portfolio ORDER BY data_cadastro DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow" data-content-key="portfolio_eyebrow"><?= e(siteContent($pdo,'portfolio_eyebrow','TRABALHOS REALIZADOS')) ?></span>
            <h1 data-content-key="portfolio_titulo"><?= nl2br(e(siteContent($pdo,'portfolio_titulo',"O que já foi feito\ntambém conta a nossa história."))) ?></h1>
        </div>
        <p data-content-key="portfolio_texto"><?= e(siteContent($pdo,'portfolio_texto','Cada trabalho guarda decisões, encontros e referências diferentes. Esta é a memória visual de peças que já seguiram seus próprios caminhos.')) ?></p>
    </div>
</section>

<section class="section portfolio-page">
    <div class="container editorial-grid">
        <?php foreach ($trabalhos as $i => $item): ?>
            <article class="editorial-card <?= $i % 5 === 0 ? 'featured' : '' ?>">
                <div class="editorial-image">
                    <?php if ($item['imagem']): ?>
                        <img src="<?= BASE_URL . '/' . e($item['imagem']) ?>" alt="<?= e($item['titulo']) ?>">
                    <?php else: ?>
                        <div class="image-placeholder"><span>Estrada<br>da Lua</span></div>
                    <?php endif; ?>
                    <span class="editorial-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                </div>
                <h3><?= e($item['titulo']) ?></h3>
                <p><?= nl2br(e($item['descricao'])) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="closing-cta compact">
    <div class="container closing-grid">
        <div><span class="section-kicker light" data-content-key="portfolio_close_kicker"><?= e(siteContent($pdo,'portfolio_close_kicker','AGORA É A SUA VEZ')) ?></span><h2 data-content-key="portfolio_close_titulo"><?= nl2br(e(siteContent($pdo,'portfolio_close_titulo',"Quer criar algo\nsó seu?"))) ?></h2></div>
        <div><p data-content-key="portfolio_close_texto"><?= e(siteContent($pdo,'portfolio_close_texto','Conte o que você imaginou. Pode mandar cores, medidas e até uma imagem de referência.')) ?></p><a class="btn btn-gold" href="<?= BASE_URL ?>/pedido.php">Solicitar minha guia</a></div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
