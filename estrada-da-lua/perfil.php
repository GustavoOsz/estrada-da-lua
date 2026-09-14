<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$cliente = requireClient($pdo);
$pageTitle = 'Meu perfil';
$msg='';$erro='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $acao=$_POST['acao']??'perfil';
        if($acao==='perfil'){
            $nome=trim($_POST['nome']??'');$telefone=trim($_POST['telefone']??'');$cidade=trim($_POST['cidade']??'');$marketing=!empty($_POST['aceita_marketing'])?1:0;
            $interesses=[];foreach(['guias'=>'Guias','baralho'=>'Baralho Cigano','conteudos'=>'Conteúdos','ofertas'=>'Ofertas'] as $k=>$v){if(!empty($_POST['interesse_'.$k]))$interesses[]=$v;}
            if($nome==='')throw new RuntimeException('Informe seu nome.');
            $stmt=$pdo->prepare("UPDATE clientes SET nome=?,telefone=?,cidade=?,aceita_marketing=?,marketing_consentido_em=?,interesses=? WHERE id=?");
            $stmt->execute([$nome,$telefone,$cidade,$marketing,$marketing?($cliente['marketing_consentido_em']?:date('Y-m-d H:i:s')):null,implode(', ',$interesses),$cliente['id']]);
            $msg='Seu perfil foi atualizado.';
        } elseif($acao==='avatar'){
            if(empty($_FILES['avatar']['name'])) throw new RuntimeException('Escolha uma imagem para o perfil.');
            $avatar=uploadImagem($_FILES['avatar'],'avatars');
            $pdo->prepare("UPDATE clientes SET avatar=? WHERE id=?")->execute([$avatar,$cliente['id']]);
            $msg='Sua foto de perfil foi atualizada.';
        } elseif($acao==='senha'){
            $atual=$_POST['senha_atual']??'';$nova=$_POST['nova_senha']??'';
            if(!password_verify($atual,$cliente['senha'])) throw new RuntimeException('A senha atual não confere.');
            if(strlen($nova)<6) throw new RuntimeException('A nova senha precisa ter pelo menos 6 caracteres.');
            $pdo->prepare("UPDATE clientes SET senha=? WHERE id=?")->execute([password_hash($nova,PASSWORD_DEFAULT),$cliente['id']]);
            $msg='Senha alterada com sucesso.';
        }
        $cliente=currentClient($pdo);
    }catch(Throwable $e){$erro=$e->getMessage();}
}
$pedidos=$pdo->prepare("SELECT * FROM pedidos WHERE cliente_id=? ORDER BY data_pedido DESC LIMIT 12");$pedidos->execute([$cliente['id']]);$pedidos=$pedidos->fetchAll();
$leituras=$pdo->prepare("SELECT l.*,s.nome servico_nome FROM leituras l LEFT JOIN servicos_baralho s ON s.id=l.servico_id WHERE l.cliente_id=? ORDER BY l.criado_em DESC LIMIT 12");$leituras->execute([$cliente['id']]);$leituras=$leituras->fetchAll();
$conversas=$pdo->prepare("SELECT c.*, (SELECT mensagem FROM mensagens_chat m WHERE m.conversa_id=c.id ORDER BY m.id DESC LIMIT 1) ultima_mensagem FROM conversas c WHERE c.cliente_id=? ORDER BY COALESCE(c.ultima_mensagem_em,c.criado_em) DESC LIMIT 3");$conversas->execute([$cliente['id']]);$conversas=$conversas->fetchAll();
$interessesAtuais=array_map('trim',explode(',',(string)$cliente['interesses']));
$andamento=count(array_filter($pedidos,fn($p)=>!in_array($p['status'],['Entregue','Cancelado'],true)))+count(array_filter($leituras,fn($r)=>!in_array($r['status'],['Concluída','Cancelada'],true)));
$chatUnread=clientUnreadCount($pdo,(int)$cliente['id']);
require __DIR__ . '/includes/header.php';
?>
<section class="profile-hero profile-hero-v5">
<div class="container profile-hero-v5-grid">
    <div class="profile-hero-copy"><span class="eyebrow light" data-content-key="perfil_hero_eyebrow"><?= e(siteContent($pdo,'perfil_hero_eyebrow','MEU PERFIL')) ?></span><h1>Olá, <?= e(explode(' ',$cliente['nome'])[0]) ?>.<br><em data-content-key="perfil_hero_destaque"><?= e(siteContent($pdo,'perfil_hero_destaque','Que bom ter você por aqui.')) ?></em></h1><p data-content-key="perfil_hero_texto"><?= e(siteContent($pdo,'perfil_hero_texto','Este espaço é seu: pedidos, leituras, preferências e atendimento em um só lugar.')) ?></p></div>
    <div class="profile-identity-card">
        <div class="profile-avatar-large"><?php if(!empty($cliente['avatar'])): ?><img src="<?= BASE_URL.'/'.e($cliente['avatar']) ?>" alt="Foto de perfil de <?= e($cliente['nome']) ?>"><?php else: ?><span><?= e(clientInitials($cliente['nome'])) ?></span><?php endif; ?></div>
        <div><small>SEU ESPAÇO NA ESTRADA</small><strong><?= e($cliente['nome']) ?></strong><span><?= e($cliente['email']) ?></span></div>
    </div>
</div>
</section>
<section class="profile-overview"><div class="container profile-overview-grid"><div><small>Pedidos e leituras</small><strong><?= count($pedidos)+count($leituras) ?></strong></div><div><small>Em andamento</small><strong><?= $andamento ?></strong></div><a href="<?= BASE_URL ?>/chat.php"><small>Atendimento</small><strong><?= $chatUnread ? $chatUnread.' nova'.($chatUnread>1?'s':'') : 'Abrir chat' ?></strong></a></div></section>
<section class="section profile-page"><div class="container profile-grid profile-grid-v5">
<aside class="profile-nav-card"><div class="profile-mini-identity"><div class="profile-avatar-mini"><?php if(!empty($cliente['avatar'])): ?><img src="<?= BASE_URL.'/'.e($cliente['avatar']) ?>" alt=""><?php else: ?><span><?= e(clientInitials($cliente['nome'])) ?></span><?php endif; ?></div><div><strong><?= e($cliente['nome']) ?></strong><small><?= e($cliente['email']) ?></small></div></div><a href="#pedidos">Pedidos e leituras</a><a href="#atendimento">Atendimento</a><a href="#dados">Dados e preferências</a><a href="#seguranca">Segurança</a><a href="<?= BASE_URL ?>/meus-pedidos.php">Adicionar pedido antigo</a><a href="<?= BASE_URL ?>/sair-cliente.php">Sair</a></aside>
<div>
<?php if($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?><?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
<section id="pedidos" class="profile-section"><div class="section-heading split-heading"><div><span class="section-kicker">SEUS CAMINHOS</span><h2 class="display-title">O que já está acontecendo.</h2></div><a class="text-link dark" href="<?= BASE_URL ?>/meus-pedidos.php">Acompanhamento completo →</a></div><div class="profile-order-grid">
<?php foreach(array_slice($pedidos,0,4) as $p): ?><article class="profile-order-card"><small>PEDIDO · <?= e($p['codigo_acompanhamento']) ?></small><h3><?= e($p['tipo_pedido']?:'Guia') ?></h3><span class="<?= pedidoStatusClass($p['status']) ?>"><?= e($p['status']) ?></span><a href="<?= BASE_URL ?>/meus-pedidos.php">Ver andamento →</a></article><?php endforeach; ?>
<?php foreach(array_slice($leituras,0,4) as $r): ?><article class="profile-order-card oracle"><small>BARALHO · <?= e($r['codigo_acompanhamento']) ?></small><h3><?= e($r['servico_nome']?:'Leitura') ?></h3><span class="<?= leituraStatusClass($r['status']) ?>"><?= e($r['status']) ?></span><a href="<?= BASE_URL ?>/meus-pedidos.php">Ver andamento →</a></article><?php endforeach; ?>
<?php if(!$pedidos&&!$leituras): ?><div class="profile-empty"><p>Seu histórico começa quando você fizer uma compra, solicitar uma guia ou agendar uma leitura.</p><div class="actions"><a class="btn btn-dark" href="<?= BASE_URL ?>/loja.php">Explorar a loja</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/pedido.php">Criar minha guia</a></div></div><?php endif; ?>
</div></section>

<section id="atendimento" class="profile-section"><div class="section-heading split-heading"><div><span class="section-kicker">ATENDIMENTO</span><h2 class="display-title">Conversa que não se perde.</h2></div><a class="btn btn-dark" href="<?= BASE_URL ?>/chat.php">Abrir atendimento<?= $chatUnread?' · '.$chatUnread.' nova(s)':'' ?></a></div><div class="profile-chat-preview"><?php if($conversas): ?><?php foreach($conversas as $c): ?><a href="<?= BASE_URL ?>/conversa.php?id=<?= (int)$c['id'] ?>"><span class="<?= conversationStatusClass($c['status']) ?>"><?= e($c['status']) ?></span><strong><?= e($c['assunto']) ?></strong><p><?= e(excerpt($c['ultima_mensagem']??'Conversa iniciada.',95)) ?></p><small><?= $c['ultima_mensagem_em']?date('d/m/Y H:i',strtotime($c['ultima_mensagem_em'])):'Ainda sem mensagens' ?></small></a><?php endforeach; ?><?php else: ?><div class="profile-empty"><p>Quando quiser tirar uma dúvida ou conversar sobre um pedido, você pode começar por aqui.</p><a class="text-link dark" href="<?= BASE_URL ?>/chat.php">Iniciar uma conversa →</a></div><?php endif; ?></div></section>

<section id="dados" class="profile-section"><div class="section-heading"><span class="section-kicker">SEU PERFIL</span><h2 class="display-title">Informações e preferências.</h2></div><div class="profile-settings-grid">
<form method="post" enctype="multipart/form-data" class="profile-preferences avatar-settings"><input type="hidden" name="acao" value="avatar"><h3>Foto de perfil</h3><div class="avatar-editor"><div class="profile-avatar-large small"><?php if(!empty($cliente['avatar'])): ?><img src="<?= BASE_URL.'/'.e($cliente['avatar']) ?>" alt=""><?php else: ?><span><?= e(clientInitials($cliente['nome'])) ?></span><?php endif; ?></div><div><input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" required><small>JPG, PNG ou WEBP. Até 5 MB.</small></div></div><button class="btn btn-secondary">Atualizar foto</button></form>
<form method="post" class="profile-preferences"><input type="hidden" name="acao" value="perfil"><h3>Seus dados</h3><div class="form-grid two"><div class="form-group"><label>Nome</label><input name="nome" value="<?= e($cliente['nome']) ?>" required></div><div class="form-group"><label>WhatsApp / telefone</label><input name="telefone" value="<?= e($cliente['telefone']) ?>"></div><div class="form-group full"><label>Cidade</label><input name="cidade" value="<?= e($cliente['cidade']??'') ?>" placeholder="Ex.: Curitiba"></div></div><h3>O que mais te interessa?</h3><div class="interest-grid"><?php foreach(['guias'=>'Guias','baralho'=>'Baralho Cigano','conteudos'=>'Conteúdos','ofertas'=>'Ofertas'] as $k=>$v): ?><label><input type="checkbox" name="interesse_<?= $k ?>" <?= in_array($v,$interessesAtuais,true)?'checked':'' ?>><span><?= e($v) ?></span></label><?php endforeach; ?></div><label class="consent-row"><input type="checkbox" name="aceita_marketing" value="1" <?= $cliente['aceita_marketing']?'checked':'' ?>><span><strong>Quero receber novidades</strong><small>Novos trabalhos, conteúdos e ofertas. Você pode desligar quando quiser.</small></span></label><button class="btn btn-dark">Salvar meu perfil</button></form>
</div></section>

<section id="seguranca" class="profile-section"><span class="section-kicker">SEGURANÇA</span><h2 class="display-title">Sua conta, sob seu controle.</h2><form method="post" class="profile-preferences security-form"><input type="hidden" name="acao" value="senha"><div class="form-grid two"><div class="form-group"><label>Senha atual</label><input type="password" name="senha_atual" required autocomplete="current-password"></div><div class="form-group"><label>Nova senha</label><input type="password" name="nova_senha" minlength="6" required autocomplete="new-password"></div></div><button class="btn btn-secondary">Alterar senha</button></form></section>

<section class="profile-section"><div class="section-heading"><span class="section-kicker">O QUE VEM DEPOIS</span><h2 class="display-title">Seu perfil também vai guardar conteúdo.</h2></div><div class="future-grid"><button type="button" data-coming-soon="Caderno da Lua" data-coming-text="No futuro, textos salvos e recomendações poderão aparecer no seu perfil de acordo com o que você escolher acompanhar."><span>01</span><strong>Caderno da Lua</strong><small>Blog · em produção</small></button><button type="button" data-coming-soon="Vozes da Estrada" data-coming-text="Episódios, conversas e recomendações poderão fazer parte da sua área pessoal quando o podcast estiver pronto."><span>02</span><strong>Vozes da Estrada</strong><small>Podcast · em produção</small></button></div></section>
</div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
