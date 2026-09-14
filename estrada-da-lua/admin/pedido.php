<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$load=function()use($pdo,$id){$s=$pdo->prepare("SELECT * FROM pedidos WHERE id=?");$s->execute([$id]);return $s->fetch();};
$pedido=$load();if(!$pedido)exit('Pedido não encontrado.');
$it=$pdo->prepare("SELECT pi.*,p.nome produto_nome,p.imagem_principal FROM pedido_itens pi LEFT JOIN produtos p ON p.id=pi.produto_id WHERE pi.pedido_id=?");$it->execute([$id]);$itens=$it->fetchAll();
$pageTitle='Pedido #'.$id;$permit=['Novo','Em Produção','Pronto','Entregue','Cancelado'];$msg='';$ready=($pedido['tipo_pedido']??'')==='Guia pronta entrega';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $a=$_POST['acao']??'status';
 if($a==='status'){$st=$_POST['status']??'';$valor=parseMoneyInput($_POST['valor_fechado']??0);if(in_array($st,$permit,true)){$s=$pdo->prepare("UPDATE pedidos SET status=?,valor_fechado=? WHERE id=?");$s->execute([$st,$valor?:null,$id]);$pedido=$load();if($st==='Entregue')syncVendaPedido($pdo,$pedido);$msg='Pedido atualizado'.($st==='Entregue'?' e sincronizado com o financeiro.':'.');}}
 if($a==='sync'){$pedido=$load();syncVendaPedido($pdo,$pedido);$msg='Financeiro atualizado.';}
 $pedido=$load();
}
require __DIR__ . '/includes/header.php';
?>
<div class="admin-top"><div><a class="back-link" href="<?= BASE_URL ?>/admin/pedidos.php">← Voltar aos pedidos</a><h1><?= $ready?'Pronta entrega':'Pedido' ?> #<?= (int)$pedido['id'] ?></h1><p><?= e($pedido['codigo_acompanhamento']) ?> · recebido em <?= date('d/m/Y \à\s H:i',strtotime($pedido['data_pedido'])) ?></p></div><span class="<?= pedidoStatusClass($pedido['status']) ?>"><?= e($pedido['status']) ?></span></div>
<?php if($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
<div class="pedido-detail-grid"><section class="detail-card wide"><span class="section-kicker"><?= $ready?'COMPRA / RESERVA':'A SOLICITAÇÃO' ?></span><h2><?= e($pedido['tipo_pedido']?:'Pedido personalizado') ?></h2>
<?php if($ready): ?>
<div class="detail-list two-col"><?php if($itens): foreach($itens as $item): ?><div><small>Peça escolhida</small><strong><?= e($item['produto_nome']?:$pedido['linha_referencia']) ?></strong></div><div><small>Quantidade</small><strong><?= (int)$item['quantidade'] ?></strong></div><div><small>Valor da peça</small><strong><?= dinheiro($item['valor']) ?></strong></div><?php endforeach; endif; ?><div><small>Entrega</small><strong><?= e($pedido['tipo_entrega']?:'A combinar') ?></strong></div><div><small>CEP</small><strong><?= e($pedido['cep']?:'Não informado') ?></strong></div><div><small>Frete estimado</small><strong><?= dinheiro($pedido['frete_estimado']??0) ?></strong></div></div>
<?php else: ?>
<div class="detail-list two-col"><div><small>Cores</small><strong><?= e($pedido['cores']?:'Não informado') ?></strong></div><div><small>Tamanho</small><strong><?= e($pedido['tamanho']?:'Não informado') ?></strong></div><div><small>Linha / referência</small><strong><?= e($pedido['linha_referencia']?:'Não informado') ?></strong></div><div><small>Materiais</small><strong><?= e($pedido['materiais']?:'Não informado') ?></strong></div><div><small>Quantidade</small><strong><?= (int)($pedido['quantidade']?:1) ?></strong></div><div><small>Orçamento inicial</small><strong><?= e($pedido['faixa_orcamento']?:'Aberto a proposta') ?></strong></div></div>
<?php endif; ?>
<div class="detail-note"><small>Observações do cliente</small><p><?= nl2br(e($pedido['observacoes']?:'Nenhuma observação adicional.')) ?></p></div><?php if($pedido['arquivo_referencia']): ?><a class="reference-image" href="<?= BASE_URL.'/'.$pedido['arquivo_referencia'] ?>" target="_blank"><img src="<?= BASE_URL.'/'.$pedido['arquivo_referencia'] ?>" alt="Referência"><span>Abrir imagem ↗</span></a><?php endif; ?>
<div class="pricing-summary"><div><small><?= $ready?'Valor do pedido':'Preço sugerido' ?></small><strong><?= $ready&&$pedido['valor_fechado']?dinheiro($pedido['valor_fechado']):($pedido['preco_sugerido']!==null?dinheiro($pedido['preco_sugerido']):'Ainda não calculado') ?></strong></div><div><small>Valor fechado</small><strong><?= $pedido['valor_fechado']?dinheiro($pedido['valor_fechado']):'—' ?></strong></div><?php if(!$ready): ?><a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/precos.php?pedido=<?= (int)$pedido['id'] ?>">Abrir calculadora de preço →</a><?php endif; ?></div></section>
<aside><section class="detail-card"><span class="section-kicker">CLIENTE</span><h3><?= e($pedido['nome_cliente']) ?></h3><div class="contact-stack"><a href="tel:<?= e($pedido['telefone']) ?>"><?= e($pedido['telefone']) ?></a><?php if($pedido['email']): ?><a href="mailto:<?= e($pedido['email']) ?>"><?= e($pedido['email']) ?></a><?php endif; ?></div><?php if($pedido['cliente_id']): ?><a class="text-link dark" href="<?= BASE_URL ?>/admin/clientes.php?q=<?= urlencode($pedido['email']?:$pedido['telefone']) ?>">Ver perfil na base →</a><?php endif; ?></section>
<section class="detail-card"><span class="section-kicker">ANDAMENTO & VENDA</span><form method="post"><input type="hidden" name="acao" value="status"><div class="form-group"><label>Status atual</label><select name="status"><?php foreach($permit as $st): $label=$ready?(['Novo'=>'Recebido','Em Produção'=>'Separando','Pronto'=>'Pronto para envio/retirada','Entregue'=>'Entregue','Cancelado'=>'Cancelado'][$st]??$st):$st; ?><option value="<?= e($st) ?>" <?= $pedido['status']===$st?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Valor final fechado</label><input name="valor_fechado" value="<?= e((string)$pedido['valor_fechado']) ?>" placeholder="Ex.: 149.90"></div><button class="btn btn-dark btn-wide">Atualizar pedido</button></form><form method="post"><input type="hidden" name="acao" value="sync"><button class="btn btn-secondary btn-wide">Sincronizar financeiro</button></form><small class="calc-help">Ao marcar como Entregue, a venda é enviada automaticamente ao financeiro usando os custos cadastrados.</small></section></aside></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
