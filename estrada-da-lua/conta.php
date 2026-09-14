<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
startClientSession();
if (!empty($_SESSION['cliente_id'])) { header('Location: ' . BASE_URL . '/perfil.php'); exit; }
$pageTitle = 'Minha conta';
$erro = '';
$modo = $_POST['modo'] ?? ($_GET['modo'] ?? 'entrar');
$voltar = $_GET['voltar'] ?? $_POST['voltar'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($modo === 'cadastrar') {
            $nome = trim($_POST['nome'] ?? '');
            $email = mb_strtolower(trim($_POST['email'] ?? ''));
            $telefone = trim($_POST['telefone'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $marketing = !empty($_POST['aceita_marketing']) ? 1 : 0;
            $interesses = [];
            foreach (['guias'=>'Guias', 'baralho'=>'Baralho Cigano', 'conteudos'=>'Conteúdos'] as $k => $v) {
                if (!empty($_POST['interesse_' . $k])) $interesses[] = $v;
            }
            if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
                throw new RuntimeException('Preencha nome, e-mail válido e uma senha com pelo menos 6 caracteres.');
            }
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email=?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) throw new RuntimeException('Já existe uma conta com este e-mail.');
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO clientes (nome,email,telefone,senha,aceita_marketing,marketing_consentido_em,interesses,ultimo_acesso) VALUES (?,?,?,?,?,?,?,NOW())");
            $stmt->execute([$nome, $email, $telefone, $hash, $marketing, $marketing ? date('Y-m-d H:i:s') : null, implode(', ', $interesses)]);
            session_regenerate_id(true);
            $_SESSION['cliente_id'] = (int)$pdo->lastInsertId();
            setFlash('Seu perfil foi criado. A partir de agora, pedidos e atendimentos ficam guardados aqui.', 'success');
        } else {
            $email = mb_strtolower(trim($_POST['email'] ?? ''));
            $senha = $_POST['senha'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $cliente = $stmt->fetch();
            if (!$cliente || !password_verify($senha, $cliente['senha'])) throw new RuntimeException('E-mail ou senha inválidos.');
            session_regenerate_id(true);
            $_SESSION['cliente_id'] = (int)$cliente['id'];
            $pdo->prepare("UPDATE clientes SET ultimo_acesso=NOW() WHERE id=?")->execute([$cliente['id']]);
            setFlash('Que bom ter você de volta.', 'success');
        }
        $destino = safeReturnUrl($voltar, BASE_URL . '/perfil.php');
        header('Location: ' . $destino);
        exit;
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="account-hero account-hero-v5">
    <div class="container account-hero-grid">
        <div>
            <span class="eyebrow light" data-content-key="conta_hero_eyebrow"><?= e(siteContent($pdo, 'conta_hero_eyebrow', 'SUA ESTRADA, GUARDADA AQUI')) ?></span>
            <h1 data-content-key="conta_hero_titulo"><?= nl2br(e(siteContent($pdo, 'conta_hero_titulo', "Uma conta para deixar\na experiência mais sua."))) ?></h1>
        </div>
        <p data-content-key="conta_hero_texto"><?= e(siteContent($pdo, 'conta_hero_texto', 'Para comprar, pedir uma peça, solicitar uma leitura ou conversar com a equipe, você entra na sua conta. Navegar e conhecer a Estrada da Lua continua livre.')) ?></p>
    </div>
</section>

<section class="section account-auth-section">
    <div class="container auth-shell">
        <div class="auth-tabs">
            <a class="<?= $modo !== 'cadastrar' ? 'active' : '' ?>" href="?modo=entrar<?= $voltar ? '&voltar=' . urlencode($voltar) : '' ?>">Já tenho conta</a>
            <a class="<?= $modo === 'cadastrar' ? 'active' : '' ?>" href="?modo=cadastrar<?= $voltar ? '&voltar=' . urlencode($voltar) : '' ?>">Criar meu perfil</a>
        </div>

        <?php if($erro): ?><div class="alert alert-error"><?= e($erro) ?></div><?php endif; ?>

        <?php if($modo === 'cadastrar'): ?>
            <div class="auth-layout">
                <form method="post" class="form-card account-form">
                    <input type="hidden" name="modo" value="cadastrar">
                    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">

                    <span class="section-kicker">CRIAR PERFIL</span>
                    <h2 data-content-key="conta_cadastro_titulo"><?= e(siteContent($pdo, 'conta_cadastro_titulo', 'Comece com o essencial.')) ?></h2>

                    <div class="form-group"><label>Seu nome</label><input name="nome" value="<?= e($_POST['nome'] ?? '') ?>" autocomplete="name" required></div>
                    <div class="form-group"><label>E-mail</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required></div>
                    <div class="form-group"><label>WhatsApp / telefone</label><input name="telefone" value="<?= e($_POST['telefone'] ?? '') ?>" autocomplete="tel"></div>
                    <div class="form-group"><label>Senha</label><input type="password" name="senha" minlength="6" autocomplete="new-password" required></div>

                    <div class="preference-block refined-preferences">
                        <div class="preference-block-head">
                            <span data-content-key="conta_preferencias_titulo"><?= e(siteContent($pdo, 'conta_preferencias_titulo', 'Se quiser, conte o que mais te chama atenção')) ?></span>
                            <small data-content-key="conta_preferencias_texto"><?= e(siteContent($pdo, 'conta_preferencias_texto', 'Opcional. Isso ajuda a personalizar o perfil sem alongar o cadastro.')) ?></small>
                        </div>
                        <div class="signup-interest-grid refined-grid">
                            <?php foreach(['guias'=>'Guias','baralho'=>'Baralho Cigano','conteudos'=>'Conteúdos'] as $k => $v): ?>
                                <label class="interest-card selectable-card">
                                    <input type="checkbox" name="interesse_<?= $k ?>" <?= !empty($_POST['interesse_'.$k]) ? 'checked' : '' ?>>
                                    <span class="interest-card-icon"><?= $k === 'guias' ? '✦' : ($k === 'baralho' ? '☾' : '✎') ?></span>
                                    <span class="interest-card-copy"><strong><?= e($v) ?></strong><small><?= $k === 'guias' ? 'Peças e pronta entrega' : ($k === 'baralho' ? 'Leituras e símbolos' : 'Textos e novidades') ?></small></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <label class="consent-row consent-card">
                        <input type="checkbox" name="aceita_marketing" value="1" <?= !empty($_POST['aceita_marketing']) ? 'checked' : '' ?>>
                        <span>
                            <strong data-content-key="conta_marketing_titulo"><?= e(siteContent($pdo, 'conta_marketing_titulo', 'Quero receber novidades da Estrada da Lua')) ?></strong>
                            <small data-content-key="conta_marketing_texto"><?= e(siteContent($pdo, 'conta_marketing_texto', 'Ofertas, novos trabalhos e conteúdos. Você pode mudar isso no perfil quando quiser.')) ?></small>
                        </span>
                    </label>

                    <button class="btn btn-dark btn-wide">Criar minha conta</button>
                </form>

                <aside class="account-promise">
                    <span class="account-thread-art"><i></i><b></b></span>
                    <h3 data-content-key="conta_lateral_titulo"><?= e(siteContent($pdo, 'conta_lateral_titulo', 'Não é só um login.')) ?></h3>
                    <p data-content-key="conta_lateral_texto"><?= e(siteContent($pdo, 'conta_lateral_texto', 'Seu perfil reúne compras, encomendas, leituras, atendimento e preferências. A base já fica pronta para recomendações e conteúdos mais pessoais no futuro.')) ?></p>
                    <ul>
                        <li>Histórico em um só lugar</li>
                        <li>Chat direto com a equipe</li>
                        <li>Preferências sob seu controle</li>
                    </ul>
                </aside>
            </div>
        <?php else: ?>
            <div class="auth-layout">
                <form method="post" class="form-card account-form">
                    <input type="hidden" name="modo" value="entrar">
                    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
                    <span class="section-kicker">BEM-VINDO DE VOLTA</span>
                    <h2 data-content-key="conta_login_titulo"><?= e(siteContent($pdo, 'conta_login_titulo', 'Entre na sua estrada.')) ?></h2>
                    <div class="form-group"><label>E-mail</label><input type="email" name="email" autocomplete="email" required></div>
                    <div class="form-group"><label>Senha</label><input type="password" name="senha" autocomplete="current-password" required></div>
                    <button class="btn btn-dark btn-wide">Entrar</button>
                </form>

                <aside class="account-promise">
                    <span class="account-thread-art"><i></i><b></b></span>
                    <h3 data-content-key="conta_login_lateral_titulo"><?= e(siteContent($pdo, 'conta_login_lateral_titulo', 'Primeira vez por aqui?')) ?></h3>
                    <p data-content-key="conta_login_lateral_texto"><?= e(siteContent($pdo, 'conta_login_lateral_texto', 'Crie sua conta em poucos campos. Você continua escolhendo o que deseja receber e pode alterar suas preferências depois.')) ?></p>
                    <a class="text-link" href="?modo=cadastrar<?= $voltar ? '&voltar=' . urlencode($voltar) : '' ?>">Criar meu perfil →</a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
