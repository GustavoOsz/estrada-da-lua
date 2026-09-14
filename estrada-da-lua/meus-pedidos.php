<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle = 'Acompanhar pedidos';
$erro = '';
$cliente = requireClient($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    $contato = trim($_POST['contato'] ?? '');
    $encontrado = false;
    if (strpos($codigo, 'BAR-') === 0) {
        $stmt = $pdo->prepare("SELECT * FROM leituras WHERE codigo_acompanhamento=? LIMIT 1");
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();
        if ($row && contactMatches($row, $contato)) {
            rememberTracking('leitura', $codigo); $encontrado = true;
            if ($cliente) attachClientToOrder($pdo, 'leitura', (int)$row['id'], (int)$cliente['id']);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE codigo_acompanhamento=? LIMIT 1");
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();
        if ($row && contactMatches($row, $contato)) {
            rememberTracking('pedido', $codigo); $encontrado = true;
            if ($cliente) attachClientToOrder($pdo, 'pedido', (int)$row['id'], (int)$cliente['id']);
        }
    }
    if (!$encontrado) $erro = 'Não encontramos esse código com o telefone/e-mail informado.';
}

$registros = [];$vistos=[];
if($cliente){
    $st=$pdo->prepare("SELECT * FROM pedidos WHERE cliente_id=? ORDER BY data_pedido DESC");$st->execute([$cliente['id']]);
    foreach($st->fetchAll() as $row){$k='pedido:'.$row['codigo_acompanhamento'];$vistos[$k]=1;$registros[]=['tipo'=>'pedido','dados'=>$row];}
    $st=$pdo->prepare("SELECT l.*,s.nome servico_nome FROM leituras l LEFT JOIN servicos_baralho s ON s.id=l.servico_id WHERE l.cliente_id=? ORDER BY l.criado_em DESC");$st->execute([$cliente['id']]);
    foreach($st->fetchAll() as $row){$k='leitura:'.$row['codigo_acompanhamento'];$vistos[$k]=1;$registros[]=['tipo'=>'leitura','dados'=>$row];}
}
foreach (rememberedTracking() as $item) {
    [$tipo, $codigo] = array_pad(explode(':', $item, 2), 2, '');$key=$tipo.':'.$codigo;if(isset($vistos[$key]))continue;
    if ($tipo === 'pedido') {$stmt=$pdo->prepare("SELECT * FROM pedidos WHERE codigo_acompanhamento=? LIMIT 1");$stmt->execute([$codigo]);if($row=$stmt->fetch())$registros[]=['tipo'=>'pedido','dados'=>$row];}
    elseif ($tipo === 'leitura') {$stmt=$pdo->prepare("SELECT l.*,s.nome AS servico_nome FROM leituras l LEFT JOIN servicos_baralho s ON s.id=l.servico_id WHERE l.codigo_acompanhamento=? LIMIT 1");$stmt->execute([$codigo]);if($row=$stmt->fetch())$registros[]=['tipo'=>'leitura','dados'=>$row];}
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero tracking-hero"><div class="container page-hero-grid"><div><span class="eyebrow">SEU CAMINHO CONTINUA AQUI</span><h1>Veja onde está<br><em>o que você confiou a nós.</em></h1></div><p>Como você está conectado, seus pedidos e leituras aparecem aqui automaticamente. Use o código abaixo apenas para vincular um pedido antigo ao seu perfil.</p></div></section>
<section class="section tracking-page"><div class="container tracking-layout"><aside class="tracking-search"><span class="section-kicker">LOCALIZAR OUTRO PEDIDO</span><h2>Tenho um código</h2><?php if($cliente): ?><div class="profile-link-note"><strong>Você está conectado como <?= e(explode(' ',$cliente['nome'])[0]) ?>.</strong><small>Ao localizar um pedido antigo pelo código, ele também fica associado ao seu perfil.</small></div><?php endif; ?><?php if ($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?><form method="post" class="compact-form"><div class="form-group"><label for="codigo">Código de acompanhamento</label><input id="codigo" name="codigo" placeholder="EDL-... ou BAR-..." required></div><div class="form-group"><label for="contato">Telefone ou e-mail usado no pedido</label><input id="contato" name="contato" required></div><button class="btn btn-dark btn-wide">Adicionar aos meus pedidos</button></form></aside>
<div class="tracking-list"><div class="tracking-title"><span class="section-kicker">MEUS PEDIDOS</span><h2><?= count($registros) ? 'Acompanhe sem precisar perguntar.' : 'Ainda não há pedidos por aqui.' ?></h2></div>
<?php foreach ($registros as $registro): $d=$registro['dados']; ?>
<?php if ($registro['tipo']==='pedido'): $ready=($d['tipo_pedido']??'')==='Guia pronta entrega'; ?><article class="tracking-card <?= $ready?'ready-track':'' ?>"><div class="tracking-card-head"><div><small><?= $ready?'PRONTA ENTREGA':'GUIA / PEDIDO' ?></small><strong>#<?= (int)$d['id'] ?> · <?= e($d['codigo_acompanhamento']) ?></strong></div><span class="<?= pedidoStatusClass($d['status']) ?>"><?= e($d['status']) ?></span></div><h3><?= e($d['tipo_pedido'] ?: 'Pedido personalizado') ?></h3><?php if($ready && $d['linha_referencia']): ?><p class="tracking-product-name"><?= e($d['linha_referencia']) ?></p><?php endif; ?><div class="tracking-meta"><span>Recebido em <strong><?= date('d/m/Y', strtotime($d['data_pedido'])) ?></strong></span><?php if($d['tipo_entrega']): ?><span>Entrega <strong><?= e($d['tipo_entrega']) ?></strong></span><?php endif; ?><?php if($d['valor_fechado']): ?><span>Valor <strong><?= dinheiro($d['valor_fechado']) ?></strong></span><?php endif; ?></div><div class="timeline <?= $d['status']==='Cancelado'?'cancelled':'' ?>"><?php foreach (['Novo','Em Produção','Pronto','Entregue'] as $step):$ord=['Novo'=>1,'Em Produção'=>2,'Pronto'=>3,'Entregue'=>4];$active=$d['status']!=='Cancelado'&&($ord[$d['status']]??0)>=$ord[$step];?><div class="timeline-step <?= $active?'active':'' ?>"><i></i><span><?= $ready ? ['Novo'=>'Recebido','Em Produção'=>'Separando','Pronto'=>'Pronto','Entregue'=>'Entregue'][$step] : e($step) ?></span></div><?php endforeach; ?></div><div class="tracking-actions"><a class="text-link dark" href="<?= BASE_URL ?>/chat.php?pedido=<?= (int)$d['id'] ?>">Falar sobre este pedido →</a><?php if ($d['status']==='Entregue'): ?><a class="text-link dark" href="<?= BASE_URL ?>/avaliar.php?codigo=<?= urlencode($d['codigo_acompanhamento']) ?>">Como foi sua experiência? Conte para nós →</a><?php endif; ?></div></article>
<?php else: ?><article class="tracking-card oracle-track"><div class="tracking-card-head"><div><small>BARALHO CIGANO</small><strong>#<?= (int)$d['id'] ?> · <?= e($d['codigo_acompanhamento']) ?></strong></div><span class="<?= leituraStatusClass($d['status']) ?>"><?= e($d['status']) ?></span></div><h3><?= e($d['servico_nome'] ?: 'Leitura') ?></h3><div class="tracking-meta"><span>Solicitada em <strong><?= date('d/m/Y', strtotime($d['criado_em'])) ?></strong></span><?php if($d['data_agendada']): ?><span>Agendada para <strong><?= date('d/m/Y H:i', strtotime($d['data_agendada'])) ?></strong></span><?php endif; ?></div><div class="timeline oracle-timeline"><?php foreach (['Nova','Confirmada','Agendada','Concluída'] as $step):$ord=['Nova'=>1,'Confirmada'=>2,'Agendada'=>3,'Concluída'=>4];$active=$d['status']!=='Cancelada'&&($ord[$d['status']]??0)>=$ord[$step];?><div class="timeline-step <?= $active?'active':'' ?>"><i></i><span><?= e($step) ?></span></div><?php endforeach; ?></div><?php if ($d['status']==='Concluída'): ?><a class="text-link dark" href="<?= BASE_URL ?>/avaliar.php?codigo=<?= urlencode($d['codigo_acompanhamento']) ?>">Deixar um feedback sobre a leitura →</a><?php endif; ?></article><?php endif; ?>
<?php endforeach; ?>
<?php if (!$registros): ?><div class="tracking-empty"><span class="moon-small"></span><p>Quando você fizer uma encomenda, comprar uma guia pronta ou solicitar uma leitura, ela aparecerá aqui.</p><div class="actions"><a class="btn btn-dark" href="<?= BASE_URL ?>/guias-prontas.php">Ver guias prontas</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/pedido.php">Criar uma guia</a></div></div><?php endif; ?></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
