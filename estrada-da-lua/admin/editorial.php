<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);
$pageTitle='Caderno & Podcast';$msg='';$erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $acao=$_POST['acao']??'';
  if($acao==='contato'){
   $stmt=$pdo->prepare("INSERT INTO conteudos_site (chave,grupo,rotulo,valor) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)");
   $stmt->execute(['whatsapp_numero','contato','WhatsApp da loja',trim($_POST['whatsapp_numero']??'')]);
   $stmt->execute(['whatsapp_mensagem','contato','Mensagem inicial do WhatsApp',trim($_POST['whatsapp_mensagem']??'')]);
   $msg='Configuração de atendimento atualizada.';
  }
  if($acao==='editorial'){
   $tipo=in_array($_POST['tipo']??'', ['Blog','Podcast'],true)?$_POST['tipo']:'Blog';$categoria=trim($_POST['categoria']??'');$titulo=trim($_POST['titulo']??'');$resumo=trim($_POST['resumo']??'');$conteudo=trim($_POST['conteudo']??'');$tempo=max(0,(int)($_POST['tempo_leitura']??0));$ctaTexto=trim($_POST['cta_texto']??'');$ctaUrl=trim($_POST['cta_url']??'');$status=in_array($_POST['status']??'', ['Rascunho','Pronto','Publicado'],true)?$_POST['status']:'Rascunho';
   if($titulo==='')throw new RuntimeException('Dê um título ao conteúdo.');
   $capa=null;if(!empty($_FILES['capa']['name']))$capa=uploadImagem($_FILES['capa'],'conteudos');$slug=slugify($titulo);
   $stmt=$pdo->prepare("INSERT INTO conteudos_editoriais (tipo,categoria,titulo,slug,resumo,tempo_leitura,conteudo,cta_texto,cta_url,capa,status,publicado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
   $stmt->execute([$tipo,$categoria,$titulo,$slug,$resumo,$tempo?:null,$conteudo,$ctaTexto,$ctaUrl,$capa,$status,$status==='Publicado'?date('Y-m-d H:i:s'):null]);$msg='Conteúdo salvo no acervo editorial.';
  }
  if($acao==='excluir_editorial'){$id=(int)($_POST['id']??0);if($id){$pdo->prepare("DELETE FROM conteudos_editoriais WHERE id=?")->execute([$id]);$msg='Conteúdo removido.';}}
 }catch(Throwable $e){$erro=$e->getMessage();}
}
$drafts=$pdo->query("SELECT * FROM conteudos_editoriais ORDER BY atualizado_em DESC LIMIT 30")->fetchAll();
$whatsappNumero=siteContent($pdo,'whatsapp_numero','');$whatsappMensagem=siteContent($pdo,'whatsapp_mensagem','Olá! Vim pelo site da Estrada da Lua.');
require __DIR__ . '/includes/header.php';
?>
<div class="admin-top admin-top-refined"><div><span class="section-kicker">BASE EDITORIAL</span><h1>Caderno & Podcast</h1><p>Crie conteúdos com capa, narrativa, CTA e prévia antes de publicar.</p></div><a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/conteudos.php">Páginas & textos →</a></div>
<?php if($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?><?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>
<div class="admin-editor-grid">
<section class="admin-editor-panel editorial-composer"><form method="post" enctype="multipart/form-data" id="editorialForm"><input type="hidden" name="acao" value="editorial"><div class="form-grid two"><div class="form-group"><label>Formato</label><select name="tipo" data-editorial="type"><option>Blog</option><option>Podcast</option></select></div><div class="form-group"><label>Status</label><select name="status"><option>Rascunho</option><option>Pronto</option><option>Publicado</option></select></div><div class="form-group"><label>Categoria / tema</label><input name="categoria" data-editorial="category" placeholder="Ex.: Bastidores, cuidado, conversa"></div><div class="form-group"><label>Tempo estimado</label><div class="input-suffix"><input type="number" min="0" name="tempo_leitura" data-editorial="time" placeholder="5"><span>min</span></div></div></div><div class="form-group"><label>Título</label><input name="titulo" data-editorial="title" required></div><div class="form-group"><label>Resumo</label><textarea name="resumo" data-editorial="summary"></textarea></div><div class="form-group"><label>Conteúdo / roteiro</label><textarea name="conteudo" data-editorial="body" class="content-editor-textarea"></textarea><small class="character-helper"><span data-editorial-count>0</span> caracteres</small></div><div class="form-grid two"><div class="form-group"><label>Texto do CTA</label><input name="cta_texto" data-editorial="cta"></div><div class="form-group"><label>Destino do CTA</label><input name="cta_url" placeholder="/loja.php"></div></div><div class="form-group"><label>Capa</label><input type="file" name="capa" accept=".jpg,.jpeg,.png,.webp" data-image-preview="editorialImage"></div><button class="btn btn-dark">Salvar no acervo</button></form></section>
<aside class="admin-preview-panel editorial-preview-panel"><div class="admin-preview-head"><span>PRÉVIA EDITORIAL</span><span>ao vivo</span></div><div class="editorial-live-card"><div class="preview-image-box editorial-cover" id="editorialImage"><span>Sua capa aparece aqui</span></div><div class="editorial-preview-meta"><span class="preview-chip" id="editorialType">Blog</span><small id="editorialCategory">Estrada da Lua</small><small id="editorialTime"></small></div><h2 id="editorialTitle">Seu título começa aqui.</h2><p id="editorialSummary">O resumo aparece enquanto você escreve.</p><div class="editorial-body-preview" id="editorialBody">Um trecho do conteúdo aparece aqui.</div><span class="preview-cta" id="editorialCta">Continuar a experiência →</span></div></aside>
</div>
<section class="admin-contact-config"><div><span class="section-kicker">ATENDIMENTO EXTERNO</span><h2>WhatsApp</h2><p>Configure o canal alternativo ao chat interno.</p></div><form method="post"><input type="hidden" name="acao" value="contato"><div class="form-grid two"><div class="form-group"><label>Número com DDI e DDD</label><input name="whatsapp_numero" value="<?= e($whatsappNumero) ?>"></div><div class="form-group"><label>Mensagem inicial</label><input name="whatsapp_mensagem" value="<?= e($whatsappMensagem) ?>"></div></div><button class="btn btn-secondary">Salvar WhatsApp</button></form></section>
<?php if($drafts): ?><div class="admin-section-head"><div><span class="section-kicker">ACERVO</span><h2>Conteúdos salvos</h2></div></div><div class="draft-list editorial-draft-list"><?php foreach($drafts as $d): ?><article class="draft-item"><div><span class="preview-chip"><?= e($d['tipo']) ?></span><strong><?= e($d['titulo']) ?></strong><small><?= e($d['categoria']??'Sem categoria') ?> · <?= e($d['status']) ?></small></div><div class="actions"><a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/conteudo-editar.php?id=<?= (int)$d['id'] ?>">Editar</a><form method="post" data-confirm="Remover este conteúdo do acervo?"><input type="hidden" name="acao" value="excluir_editorial"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="danger-link">Excluir</button></form></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
