<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle='Agendar leitura';$cliente=requireClient($pdo);
$servicos=$pdo->query("SELECT * FROM servicos_baralho WHERE ativo=1 ORDER BY destaque DESC, ordem, preco")->fetchAll();
$config=$pdo->query("SELECT * FROM configuracao_baralho WHERE id=1")->fetch();
$selected=(int)($_GET['servico']??$_POST['servico_id']??0);
$sucesso=false;$erro='';$codigo='';$precoFinal=0;

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $nome=trim($_POST['nome']??'');$telefone=trim($_POST['telefone']??'');$email=trim($_POST['email']??'');
        $sid=(int)($_POST['servico_id']??0);$tema=trim($_POST['tema']??'');$pergunta=trim($_POST['pergunta']??'');$formato=trim($_POST['formato']??'');$data=trim($_POST['data_preferida']??'');$urgencia=!empty($_POST['urgencia']);$obs=trim($_POST['observacoes']??'');
        if($nome===''||$telefone===''||!$sid) throw new RuntimeException('Preencha seu nome, telefone e escolha uma leitura.');
        $st=$pdo->prepare("SELECT * FROM servicos_baralho WHERE id=? AND ativo=1");$st->execute([$sid]);$servico=$st->fetch();if(!$servico) throw new RuntimeException('A leitura escolhida não está disponível.');
        $precoFinal=(float)$servico['preco'];if($urgencia)$precoFinal*=1+((float)$config['adicional_urgencia_percentual']/100);
        $codigo=generateTrackingCode('BAR');
        $st=$pdo->prepare("INSERT INTO leituras (cliente_id,nome_cliente,telefone,email,servico_id,tema,pergunta,formato,data_preferida,urgencia,observacoes,preco_estimado,codigo_acompanhamento) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $st->execute([$cliente['id']??null,$nome,$telefone,$email,$sid,$tema,$pergunta,$formato,$data?:null,$urgencia?1:0,$obs,$precoFinal,$codigo]);
        rememberTracking('leitura',$codigo);$sucesso=true;
    }catch(Throwable $e){$erro=$e->getMessage();}
}
require __DIR__ . '/includes/header.php';
?>
<section class="request-hero oracle-request-hero"><div class="container request-hero-grid"><div><span class="eyebrow">SOLICITAR UMA LEITURA</span><h1>Chegue com a pergunta.<br><em>O resto pode ser construído na conversa.</em></h1></div><p>Escolha o tipo de leitura e conte apenas o necessário. Depois da solicitação, o atendimento confirma formato, horário e pagamento.</p></div></section>
<section class="section request-section"><div class="container request-layout"><aside class="request-aside oracle-aside"><span class="section-kicker">ANTES DE ENVIAR</span><h2>Você não precisa se explicar por inteiro.</h2><p>Uma frase já pode ser suficiente para indicar o tema. Detalhes pessoais só precisam ser compartilhados se você quiser.</p><div class="request-steps"><li><span>1</span><div><strong>Escolha a leitura</strong><small>Tempo e valor aparecem de forma transparente.</small></div></li><li><span>2</span><div><strong>Conte o tema</strong><small>Amor, trabalho, decisão, ciclo ou outra questão.</small></div></li><li><span>3</span><div><strong>Acompanhe</strong><small>Você recebe um código para ver confirmação e agendamento.</small></div></li></div></aside><div class="request-form-wrap">
<?php if($sucesso): ?><div class="oracle-confirm"><span class="success-mark">✓</span><div><strong>Sua solicitação foi recebida.</strong><p>Guarde este código: <b><?= e($codigo) ?></b>. Valor estimado: <b><?= dinheiro($precoFinal) ?></b>.</p><a class="btn btn-dark" href="<?= BASE_URL ?>/meus-pedidos.php">Acompanhar minha leitura</a></div></div><?php else: ?>
<?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
<form method="post" class="request-form" id="readingForm"><div class="form-section-title"><span>01</span><div><strong>Qual leitura faz sentido agora?</strong><small>Os valores abaixo são controlados pelo painel administrativo.</small></div></div>
<div class="reading-options"><?php foreach($servicos as $s): ?><label class="reading-option"><input type="radio" name="servico_id" value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['preco'] ?>" <?= $selected===(int)$s['id']?'checked':'' ?> required><span><small><?= (int)$s['duracao_minutos'] ?> min</small><strong><?= e($s['nome']) ?></strong><em><?= dinheiro($s['preco']) ?></em></span></label><?php endforeach; ?></div>
<div class="form-section-title"><span>02</span><div><strong>Quem chega para a leitura?</strong><small>Dados usados para contato e acompanhamento.</small></div></div><div class="form-grid two"><div class="form-group"><label>Nome *</label><input name="nome" value="<?= e($cliente['nome']??'') ?>" required></div><div class="form-group"><label>WhatsApp / telefone *</label><input name="telefone" value="<?= e($cliente['telefone']??'') ?>" required></div><div class="form-group"><label>E-mail</label><input type="email" name="email" value="<?= e($cliente['email']??'') ?>"></div><div class="form-group"><label>Formato preferido</label><select name="formato"><option>WhatsApp — texto/áudio</option><option>Chamada de vídeo</option><option>Presencial — se disponível</option></select></div></div>
<div class="form-section-title"><span>03</span><div><strong>O que está pedindo atenção?</strong><small>Compartilhe só o que se sentir confortável.</small></div></div><div class="form-grid two"><div class="form-group"><label>Tema</label><select name="tema"><option>Decisões e caminhos</option><option>Amor e relações</option><option>Trabalho e projetos</option><option>Espiritualidade</option><option>Ciclos e mudanças</option><option>Outro</option></select></div><div class="form-group"><label>Data preferida</label><input type="date" name="data_preferida"></div><div class="form-group full"><label>Sua pergunta ou contexto</label><textarea name="pergunta" placeholder="Ex.: Estou entre dois caminhos e queria compreender melhor o que cada escolha movimenta..."></textarea></div><div class="form-group full"><label>Algo mais que deseja contar?</label><textarea name="observacoes"></textarea></div></div>
<label class="urgency-row"><input id="urgencia" type="checkbox" name="urgencia" value="1" data-percent="<?= (float)$config['adicional_urgencia_percentual'] ?>"><span><strong>Prioridade, se houver disponibilidade</strong><small>Acrescenta <?= number_format((float)$config['adicional_urgencia_percentual'],0) ?>% ao valor. Não garante encaixe; a equipe confirma antes.</small></span></label>
<div class="reading-total"><span>Estimativa da solicitação</span><strong id="readingTotal">Selecione uma leitura</strong></div><button class="btn btn-dark btn-submit">Enviar solicitação →</button><p class="form-footnote">O envio não confirma horário automaticamente. Você recebe a confirmação depois da análise.</p></form>
<script>
(function(){const radios=[...document.querySelectorAll('[name="servico_id"]')], urgent=document.getElementById('urgencia'), out=document.getElementById('readingTotal');function update(){const r=radios.find(x=>x.checked);if(!r){out.textContent='Selecione uma leitura';return;}let v=parseFloat(r.dataset.price||0);if(urgent.checked)v*=1+(parseFloat(urgent.dataset.percent||0)/100);out.textContent=v.toLocaleString('pt-BR',{style:'currency',currency:'BRL'});}radios.forEach(r=>r.addEventListener('change',update));urgent.addEventListener('change',update);update();})();
</script><?php endif; ?>
</div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
