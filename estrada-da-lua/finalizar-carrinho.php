<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);

$cliente = requireClient($pdo);
$itens = cartRows($pdo);
if (!$itens) {
    header('Location: ' . BASE_URL . '/carrinho.php');
    exit;
}

$config = getConfigPrecos($pdo);
$fretePadrao = (float)($config['frete_padrao'] ?? 0);
$subtotal = array_sum(array_column($itens, '_subtotal'));
$pageTitle = 'Finalizar compra';
$erro = '';
$sucesso = false;
$codigo = '';
$pedidoId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $entrega = in_array($_POST['tipo_entrega'] ?? '', ['Envio','Retirada'], true) ? $_POST['tipo_entrega'] : 'Envio';
        $cep = trim($_POST['cep'] ?? '');
        $obs = trim($_POST['observacoes'] ?? '');

        if ($nome === '' || $telefone === '') throw new RuntimeException('Informe seu nome e telefone.');
        if ($entrega === 'Envio' && $cep === '') throw new RuntimeException('Informe o CEP para o envio.');

        startClientSession();
        $cart = $_SESSION['carrinho'] ?? [];
        if (!is_array($cart) || !$cart) throw new RuntimeException('Sua sacola ficou sem itens disponíveis.');

        $pdo->beginTransaction();

        /* Revalida preço e estoque dentro da transação. Isso evita vender a mesma
         * peça duas vezes se dois pedidos forem finalizados quase ao mesmo tempo. */
        $locked = [];
        $subtotal = 0.0;
        $custoMateriais = 0.0;
        $qTotal = 0;
        $nomes = [];
        $selectProduto = $pdo->prepare("SELECT id,nome,preco,estoque,custo_unitario,pronta_entrega FROM produtos WHERE id=? FOR UPDATE");

        foreach ($cart as $produtoId => $quantidadeBruta) {
            $produtoId = (int)$produtoId;
            $quantidade = max(1, (int)$quantidadeBruta);
            if (!$produtoId) continue;

            $selectProduto->execute([$produtoId]);
            $produto = $selectProduto->fetch();
            if (!$produto || empty($produto['pronta_entrega'])) {
                throw new RuntimeException('Uma das peças da sacola deixou de estar disponível para pronta entrega.');
            }
            if ((int)$produto['estoque'] < $quantidade) {
                throw new RuntimeException('O estoque de “' . $produto['nome'] . '” mudou. Revise sua sacola antes de concluir.');
            }

            $preco = (float)$produto['preco'];
            $subtotal += $preco * $quantidade;
            $custoMateriais += (float)$produto['custo_unitario'] * $quantidade;
            $qTotal += $quantidade;
            $nomes[] = $produto['nome'] . ' × ' . $quantidade;
            $locked[] = ['id'=>$produtoId,'nome'=>$produto['nome'],'preco'=>$preco,'quantidade'=>$quantidade];
        }

        if (!$locked) throw new RuntimeException('Sua sacola ficou sem itens disponíveis.');

        $frete = $entrega === 'Envio' ? $fretePadrao : 0.0;
        $total = $subtotal + $frete;
        $codigo = generateTrackingCode('EDL');

        $st = $pdo->prepare("INSERT INTO pedidos
            (cliente_id,nome_cliente,telefone,email,tipo_pedido,linha_referencia,quantidade,preferencia_contato,tipo_entrega,cep,frete_estimado,observacoes,codigo_acompanhamento,valor_fechado,custo_materiais_calculado,custo_envio_calculado,custo_embalagem_calculado)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $st->execute([
            $cliente['id'], $nome, $telefone, $email, 'Compra da loja', implode(', ', $nomes), $qTotal,
            'Chat / WhatsApp', $entrega, $cep ?: null, $frete, $obs, $codigo, $total,
            $custoMateriais, $frete, (float)($config['custo_embalagem'] ?? 0)
        ]);
        $pedidoId = (int)$pdo->lastInsertId();

        $insertItem = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,valor) VALUES (?,?,?,?)");
        $baixarEstoque = $pdo->prepare("UPDATE produtos SET estoque=estoque-? WHERE id=? AND estoque>=?");
        foreach ($locked as $item) {
            $insertItem->execute([$pedidoId, $item['id'], $item['quantidade'], $item['preco']]);
            $baixarEstoque->execute([$item['quantidade'], $item['id'], $item['quantidade']]);
            if ($baixarEstoque->rowCount() !== 1) {
                throw new RuntimeException('O estoque de “' . $item['nome'] . '” mudou durante a compra. Tente novamente.');
            }
        }

        $pdo->commit();
        $_SESSION['carrinho'] = [];
        rememberTracking('pedido', $codigo);
        $sucesso = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getMessage();
        $itens = cartRows($pdo);
        $subtotal = array_sum(array_column($itens, '_subtotal'));
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero ready-hero">
    <div class="container page-hero-grid">
        <div><span class="eyebrow">FINALIZAR COMPRA</span><h1>Quase no seu caminho.<br><em>Só falta combinar a chegada.</em></h1></div>
        <p>Revise seus dados e escolha envio ou retirada. A compra fica registrada no seu perfil e a equipe confirma pagamento e entrega pelo atendimento.</p>
    </div>
</section>
<section class="section">
    <div class="container purchase-layout">
        <div class="purchase-form">
            <?php if($sucesso): ?>
                <div class="success-panel big-success">
                    <span class="success-mark">✓</span>
                    <div>
                        <strong>Seu pedido foi registrado.</strong>
                        <p>Código: <b><?= e($codigo) ?></b>. A peça já ficou vinculada ao seu pedido e você pode acompanhar tudo pelo perfil.</p>
                        <div class="actions"><a class="btn btn-dark" href="<?= BASE_URL ?>/meus-pedidos.php">Acompanhar pedido</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/chat.php?pedido=<?= (int)$pedidoId ?>">Falar com a equipe</a></div>
                    </div>
                </div>
            <?php else: ?>
                <?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
                <form method="post" novalidate>
                    <div class="form-grid two">
                        <div class="form-group"><label>Nome</label><input name="nome" value="<?= e($_POST['nome'] ?? $cliente['nome']) ?>" required></div>
                        <div class="form-group"><label>Telefone</label><input name="telefone" value="<?= e($_POST['telefone'] ?? $cliente['telefone']) ?>" required></div>
                        <div class="form-group full"><label>E-mail</label><input type="email" name="email" value="<?= e($_POST['email'] ?? $cliente['email']) ?>"></div>
                    </div>
                    <div class="form-group">
                        <label>Como prefere receber?</label>
                        <div class="delivery-options">
                            <label><input type="radio" name="tipo_entrega" value="Envio" <?= ($_POST['tipo_entrega'] ?? 'Envio') === 'Envio' ? 'checked' : '' ?>><span><b>Envio</b><small>Frete padrão: <?= dinheiro($fretePadrao) ?></small></span></label>
                            <label><input type="radio" name="tipo_entrega" value="Retirada" <?= ($_POST['tipo_entrega'] ?? '') === 'Retirada' ? 'checked' : '' ?>><span><b>Retirada</b><small>Sem frete</small></span></label>
                        </div>
                    </div>
                    <div class="form-group"><label>CEP <span class="optional">para envio</span></label><input name="cep" value="<?= e($_POST['cep'] ?? '') ?>" placeholder="00000-000"></div>
                    <div class="form-group"><label>Observações</label><textarea name="observacoes" placeholder="Alguma informação para a entrega ou atendimento?"><?= e($_POST['observacoes'] ?? '') ?></textarea></div>
                    <button class="btn btn-dark btn-wide">Confirmar meu pedido</button>
                </form>
            <?php endif; ?>
        </div>
        <aside class="purchase-summary">
            <span class="section-kicker light">SUA SELEÇÃO</span>
            <?php foreach($itens as $i): ?><div class="cart-mini-line"><span><?= e($i['nome']) ?> × <?= (int)$i['_quantidade'] ?></span><strong><?= dinheiro($i['_subtotal']) ?></strong></div><?php endforeach; ?>
            <div class="purchase-price-line total"><span>Subtotal</span><strong><?= dinheiro($subtotal) ?></strong></div>
            <small>O frete depende da opção escolhida e aparece no valor registrado do pedido.</small>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
