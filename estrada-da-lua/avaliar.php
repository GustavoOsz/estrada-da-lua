<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle = 'Deixar feedback';
$sucesso=false; $erro='';
$codigo=strtoupper(trim($_GET['codigo'] ?? $_POST['codigo'] ?? ''));

if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        $contato=trim($_POST['contato']??'');
        $nome=trim($_POST['nome']??'');
        $texto=trim($_POST['texto']??'');
        $nota=max(1,min(5,(int)($_POST['nota']??5)));
        if ($codigo===''||$nome===''||$texto==='') throw new RuntimeException('Preencha o código, seu nome e o feedback.');
        $origem='Geral'; $ok=false;
        if (strpos($codigo,'BAR-')===0) {
            $st=$pdo->prepare("SELECT * FROM leituras WHERE codigo_acompanhamento=?"); $st->execute([$codigo]); $r=$st->fetch();
            $ok=$r && contactMatches($r,$contato); $origem='Baralho Cigano';
        } else {
            $st=$pdo->prepare("SELECT * FROM pedidos WHERE codigo_acompanhamento=?"); $st->execute([$codigo]); $r=$st->fetch();
            $ok=$r && contactMatches($r,$contato);
            $origem='Guia personalizada';
            if ($r) {
                $it=$pdo->prepare("SELECT COUNT(*) FROM pedido_itens WHERE pedido_id=?");
                $it->execute([$r['id']]);
                if ((int)$it->fetchColumn() > 0) $origem='Loja';
            }
        }
        if (!$ok) throw new RuntimeException('Não conseguimos validar esse código com o contato informado.');
        $dup=$pdo->prepare("SELECT COUNT(*) FROM feedbacks WHERE codigo_referencia=?");
        $dup->execute([$codigo]);
        if ((int)$dup->fetchColumn() > 0) throw new RuntimeException('Já recebemos um feedback relacionado a este atendimento.');
        if (mb_strlen($texto) < 8) throw new RuntimeException('Conte um pouco mais sobre sua experiência para conseguirmos registrar seu feedback.');
        insertFeedbackCompat($pdo, ['nome_cliente'=>$nome,'texto'=>$texto,'nota'=>$nota,'origem'=>$origem,'codigo_referencia'=>$codigo,'aprovado'=>0]);
        $sucesso=true;
    } catch(Throwable $e){$erro=$e->getMessage();}
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero feedback-hero"><div class="container page-hero-grid"><div><span class="eyebrow">SUA VOZ TAMBÉM FAZ PARTE</span><h1>Quando a experiência fica,<br><em>ela merece ser contada.</em></h1></div><p>Seu relato só aparece publicamente depois de revisão no painel administrativo. Assim mantemos os depoimentos reais, respeitosos e ligados a atendimentos verdadeiros.</p></div></section>
<section class="section"><div class="container narrow-container">
<?php if($sucesso): ?><div class="success-panel"><span class="success-mark">✓</span><div><strong>Recebemos seu feedback.</strong><p>Obrigado por dividir sua experiência com a Estrada da Lua.</p></div></div><?php else: ?>
<?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
<form method="post" class="request-form standalone-form" novalidate>
<div class="form-grid two"><div class="form-group"><label>Código do pedido/leitura</label><input name="codigo" value="<?= e($codigo) ?>" required></div><div class="form-group"><label>Telefone ou e-mail usado</label><input name="contato" value="<?= e($_POST['contato']??'') ?>" required></div><div class="form-group"><label>Seu nome</label><input name="nome" maxlength="120" value="<?= e($_POST['nome']??'') ?>" required></div><div class="form-group"><label>Nota</label><select name="nota"><option value="5">5 — Excelente</option><option value="4">4 — Muito bom</option><option value="3">3 — Bom</option><option value="2">2 — Pode melhorar</option><option value="1">1 — Ruim</option></select></div><div class="form-group full"><label>Conte como foi</label><textarea name="texto" minlength="8" required placeholder="O que te marcou? Como foi o atendimento, a peça ou a leitura?"><?= e($_POST['texto']??'') ?></textarea></div></div>
<button class="btn btn-dark">Enviar feedback</button>
</form><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
