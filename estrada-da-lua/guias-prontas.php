<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle='Guias à pronta entrega';
$stmt=$pdo->query("SELECT p.*,c.nome categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.tipo_produto='Guia' AND p.pronta_entrega=1 AND p.estoque>0 ORDER BY p.id DESC");
$guias=$stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero ready-hero"><div class="container page-hero-grid"><div><span class="eyebrow" data-content-key="pronta_eyebrow"><?= e(siteContent($pdo,'pronta_eyebrow','PRONTA ENTREGA')) ?></span><h1 data-content-key="pronta_titulo"><?= nl2br(e(siteContent($pdo,'pronta_titulo',"Algumas peças já fizeram\no caminho até aqui."))) ?></h1></div><p data-content-key="pronta_texto"><?= e(siteContent($pdo,'pronta_texto','Guias disponíveis agora, com fotos reais e estoque visível. Você escolhe a peça e segue direto para o fluxo de compra.')) ?></p></div></section>
<section class="section"><div class="container">
<div class="ready-intro-note"><span>✦</span><div><b data-content-key="pronta_nota_titulo"><?= e(siteContent($pdo,'pronta_nota_titulo','Pronta entrega não significa impessoal.')) ?></b> <span data-content-key="pronta_nota_texto"><?= e(siteContent($pdo,'pronta_nota_texto','Cada peça continua sendo preparada com cuidado antes de seguir para você. Aqui, a diferença é só que ela já existe e pode começar o próximo caminho mais rápido.')) ?></span></div></div>
<?php if($guias): ?><div class="ready-guide-grid">
<?php foreach($guias as $g): ?><article class="ready-guide-card"><a href="<?= BASE_URL ?>/produto.php?id=<?= (int)$g['id'] ?>"><div class="ready-guide-image"><?php if($g['imagem_principal']): ?><img src="<?= BASE_URL.'/'.$g['imagem_principal'] ?>" alt="<?= e($g['nome']) ?>"><?php else: ?><div class="image-placeholder"><span>Estrada<br>da Lua</span></div><?php endif; ?><span class="ready-badge">Disponível agora</span></div></a><div class="ready-guide-body"><small><?= e($g['categoria']??'Guia') ?></small><h3><?= e($g['nome']) ?></h3><p><?= e(excerpt($g['descricao'],120)) ?></p><div class="ready-guide-bottom"><div><small><?= (int)$g['estoque'] ?> em estoque</small><strong><?= dinheiro($g['preco']) ?></strong></div><a class="btn btn-dark" href="<?= BASE_URL ?>/comprar-pronto.php?id=<?= (int)$g['id'] ?>">Comprar</a></div></div></article><?php endforeach; ?>
</div><?php else: ?><div class="empty-state"><span class="moon-small"></span><h2>As guias prontas encontraram seus caminhos por enquanto.</h2><p>Você ainda pode criar uma peça pensada desde o início para você.</p><a class="btn btn-dark" href="<?= BASE_URL ?>/pedido.php">Criar minha guia personalizada</a></div><?php endif; ?>
</div></section>
<?php $feedbackOrigem='Loja';$feedbackLimit=4;require __DIR__.'/includes/feedbacks-section.php'; ?>
<section class="closing-cta"><div class="container closing-grid"><div><span class="section-kicker light" data-content-key="pronta_close_kicker"><?= e(siteContent($pdo,'pronta_close_kicker','NÃO ENCONTROU A SUA?')) ?></span><h2 data-content-key="pronta_close_titulo"><?= nl2br(e(siteContent($pdo,'pronta_close_titulo',"Quando a peça certa ainda não existe,\na gente começa pela sua ideia."))) ?></h2></div><div><p data-content-key="pronta_close_texto"><?= e(siteContent($pdo,'pronta_close_texto','Conte cores, referências, tamanho e o que você deseja sentir quando receber a peça.')) ?></p><a class="btn btn-brand-gold" href="<?= BASE_URL ?>/pedido.php">Criar uma personalizada</a></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
