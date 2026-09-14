<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo); startClientSession();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: '.BASE_URL.'/loja.php'); exit; }
$id = filter_input(INPUT_POST,'produto_id',FILTER_VALIDATE_INT);
$acao = $_POST['acao'] ?? 'adicionar';
$q = max(1,(int)($_POST['quantidade']??1));
$voltar = safeReturnUrl($_POST['voltar']??'', BASE_URL.'/carrinho.php');
if (!$id) { setFlash('Não encontramos esta peça.', 'error'); header('Location: '.$voltar); exit; }
$stmt=$pdo->prepare("SELECT id,nome,estoque,pronta_entrega FROM produtos WHERE id=? LIMIT 1");$stmt->execute([$id]);$p=$stmt->fetch();
if(!$p || empty($p['pronta_entrega']) || (int)$p['estoque']<1){setFlash('Esta peça não está disponível para pronta entrega agora.','error');header('Location: '.$voltar);exit;}
$_SESSION['carrinho'] = is_array($_SESSION['carrinho']??null) ? $_SESSION['carrinho'] : [];
if($acao==='remover'){unset($_SESSION['carrinho'][$id]);setFlash('Peça removida da sacola.','success');}
elseif($acao==='atualizar'){$_SESSION['carrinho'][$id]=min($q,(int)$p['estoque']);setFlash('Sacola atualizada.','success');}
else{$_SESSION['carrinho'][$id]=min((int)$p['estoque'],(int)($_SESSION['carrinho'][$id]??0)+$q);setFlash($p['nome'].' foi para a sua sacola.','success');}
header('Location: '.$voltar);exit;
