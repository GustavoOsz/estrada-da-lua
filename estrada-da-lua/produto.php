<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Produto não encontrado.');
}

$st = $pdo->prepare("SELECT p.*, c.nome categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id=?");
$st->execute([$id]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    exit('Produto não encontrado.');
}

$fs = $pdo->prepare("SELECT * FROM produto_imagens WHERE produto_id=? ORDER BY id");
$fs->execute([$id]);
$fotos = $fs->fetchAll();

$pageTitle = $p['nome'];
require __DIR__ . '/includes/header.php';

$prontaEntrega = !empty($p['pronta_entrega']) && (int)$p['estoque'] > 0;
$compraDireta = $prontaEntrega && ($p['tipo_produto'] ?? 'Guia') === 'Guia';
?>
<section class="section product-detail-page">
    <div class="container product-detail-grid">
        <div class="product-gallery">
            <div class="product-main-image">
                <?php if($p['imagem_principal']): ?>
                    <img src="<?= BASE_URL . '/' . $p['imagem_principal'] ?>" alt="<?= e($p['nome']) ?>">
                <?php else: ?>
                    <div class="image-placeholder"><span>Estrada<br>da Lua</span></div>
                <?php endif; ?>
            </div>
            <?php if($fotos): ?>
                <div class="gallery-grid">
                    <?php foreach($fotos as $f): ?>
                        <img src="<?= BASE_URL . '/' . $f['imagem'] ?>" alt="Detalhe de <?= e($p['nome']) ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="product-info">
            <span class="section-kicker"><?= e($p['categoria'] ?? 'PEÇA ESTRADA DA LUA') ?></span>
            <h1><?= e($p['nome']) ?></h1>
            <p class="product-description"><?= nl2br(e($p['descricao'])) ?></p>
            <div class="product-price"><?= dinheiro($p['preco']) ?></div>
            <p class="stock-note">
                <?php if($prontaEntrega): ?>
                    Pronta entrega · <?= (int)$p['estoque'] ?> disponível(is).
                <?php elseif((int)$p['estoque'] > 0): ?>
                    Disponível para pedido.
                <?php else: ?>
                    Disponível sob consulta.
                <?php endif; ?>
            </p>

            <div class="product-actions">
                <?php if($prontaEntrega): ?>
                    <a class="btn btn-dark btn-wide" href="<?= BASE_URL ?>/comprar-pronto.php?id=<?= (int)$p['id'] ?>">Comprar</a>
                    <form class="add-cart-form" method="post" action="<?= BASE_URL ?>/carrinho-acao.php">
                        <input type="hidden" name="acao" value="adicionar">
                        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                        <input type="hidden" name="quantidade" value="1">
                        <input type="hidden" name="voltar" value="<?= BASE_URL ?>/carrinho.php">
                        <button class="btn btn-secondary btn-wide">Adicionar à sacola</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-dark btn-wide" href="<?= BASE_URL ?>/pedido.php?produto=<?= (int)$p['id'] ?>">Solicitar uma personalizada</a>
                <?php endif; ?>
                <a class="text-link dark" href="<?= BASE_URL ?>/pedido.php">Prefiro uma personalizada →</a>
            </div>

            <div class="product-values">
                <div><strong>Feito com cuidado</strong><span>Cada detalhe é tratado como parte da experiência.</span></div>
                <div><strong>Acompanhe online</strong><span>Depois da solicitação, seu código mostra o andamento.</span></div>
                <div><strong>Atendimento humano</strong><span>Você explica o que procura antes da produção.</span></div>
            </div>
        </aside>
    </div>
</section>
<?php $feedbackOrigem='Loja'; $feedbackLimit=3; require __DIR__ . '/includes/feedbacks-section.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
