<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../config/content-pages.php';
require_once __DIR__ . '/../includes/functions.php';
ensureFullSchema($pdo);

$pageTitle = 'Páginas & textos';
$pages = contentPageDefinitions();
$pagina = $_GET['pagina'] ?? $_POST['pagina'] ?? 'home';
if (!isset($pages[$pagina])) $pagina = 'home';
$current = $pages[$pagina];
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO conteudos_site (chave,grupo,rotulo,valor) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE grupo=VALUES(grupo), rotulo=VALUES(rotulo), valor=VALUES(valor)");
        foreach ($current['fields'] as $chave => $meta) {
            $valor = trim($_POST[$chave] ?? '');
            $stmt->execute([$chave, $pagina, $meta[0], $valor]);
        }
        $msg = 'Textos de ' . $current['label'] . ' atualizados.';
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

$values = [];
$stmt = $pdo->query("SELECT chave,valor FROM conteudos_site");
foreach ($stmt->fetchAll() as $row) $values[$row['chave']] = $row['valor'];

$previewUrl = BASE_URL . $current['preview'];
if ($pagina === 'personalizada') $previewUrl = BASE_URL . '/pedido.php?admin_preview=1';
require __DIR__ . '/includes/header.php';
?>
<div class="admin-top admin-top-refined">
    <div>
        <span class="section-kicker">CONTEÚDO DO SITE</span>
        <h1>Páginas & textos</h1>
        <p>Escolha uma tela, edite apenas o que pertence a ela e acompanhe a prévia ao lado.</p>
    </div>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/editorial.php">Caderno & Podcast →</a>
</div>

<?php if($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>

<div class="page-content-editor-shell">
    <aside class="page-editor-nav">
        <span class="page-editor-nav-title">TELAS DO SITE</span>
        <?php foreach($pages as $slug => $page): ?>
            <a class="page-editor-nav-item <?= $slug === $pagina ? 'active' : '' ?>" href="?pagina=<?= e($slug) ?>">
                <span><?= str_pad((string)(array_search($slug, array_keys($pages), true) + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <div><strong><?= e($page['label']) ?></strong><small><?= e($page['description']) ?></small></div>
            </a>
        <?php endforeach; ?>
    </aside>

    <section class="page-editor-main">
        <header class="page-editor-heading">
            <div><span class="section-kicker"><?= e(mb_strtoupper($current['label'])) ?></span><h2><?= e($current['label']) ?></h2><p><?= e($current['description']) ?></p></div>
            <a class="table-link" target="_blank" href="<?= e($previewUrl) ?>">Abrir página real ↗</a>
        </header>

        <form method="post" class="page-editor-form" data-page-editor-form>
            <input type="hidden" name="pagina" value="<?= e($pagina) ?>">
            <?php foreach($current['fields'] as $chave => $meta): ?>
                <?php $value = array_key_exists($chave, $values) ? $values[$chave] : $meta[2]; ?>
                <div class="form-group page-copy-field <?= $meta[1] === 'textarea' ? 'wide' : '' ?>">
                    <label for="field_<?= e($chave) ?>"><?= e($meta[0]) ?></label>
                    <small><?= e($chave) ?></small>
                    <?php if($meta[1] === 'textarea'): ?>
                        <textarea id="field_<?= e($chave) ?>" name="<?= e($chave) ?>" data-content-editor-key="<?= e($chave) ?>"><?= e($value) ?></textarea>
                    <?php else: ?>
                        <input id="field_<?= e($chave) ?>" name="<?= e($chave) ?>" value="<?= e($value) ?>" data-content-editor-key="<?= e($chave) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="page-editor-savebar">
                <div><strong>Pronto para salvar?</strong><small>A prévia ao lado acompanha as alterações compatíveis enquanto você digita.</small></div>
                <button class="btn btn-dark">Salvar esta página</button>
            </div>
        </form>
    </section>

    <aside class="page-live-preview-panel">
        <div class="page-live-preview-head">
            <div><span>PRÉVIA DA PÁGINA</span><small>visualização integrada</small></div>
            <button type="button" data-refresh-page-preview title="Recarregar prévia">↻</button>
        </div>
        <div class="page-preview-frame-wrap">
            <iframe id="pagePreviewFrame" src="<?= e($previewUrl) ?>" title="Prévia de <?= e($current['label']) ?>"></iframe>
        </div>
        <p class="page-preview-note">A prévia usa a própria página do site. Alterações de estrutura ou conteúdos sem marcador aparecem depois de salvar/recarregar.</p>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
