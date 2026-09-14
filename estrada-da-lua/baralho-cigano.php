<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/schema.php';
require_once __DIR__ . '/includes/functions.php';
ensureFullSchema($pdo);
$pageTitle = 'Baralho Cigano';
$servicos = $pdo->query("SELECT * FROM servicos_baralho WHERE ativo=1 ORDER BY destaque DESC, ordem, preco")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="oracle-hero oracle-hero-v5">
    <div class="oracle-stars"></div>
    <div class="container oracle-hero-grid">
        <div class="oracle-copy">
            <span class="eyebrow light" data-content-key="baralho_hero_eyebrow"><?= e(siteContent($pdo, 'baralho_hero_eyebrow', 'BARALHO CIGANO • ESCUTA • REFLEXÃO')) ?></span>
            <h1>
                <span data-content-key="baralho_hero_titulo_linha1"><?= e(siteContent($pdo, 'baralho_hero_titulo_linha1', 'Algumas perguntas não pedem pressa.')) ?></span><br>
                <em data-content-key="baralho_hero_titulo_destaque" data-typing data-typing-speed="58"><?= e(siteContent($pdo, 'baralho_hero_titulo_destaque', 'Pedem espaço.')) ?></em>
            </h1>
            <p data-content-key="baralho_hero_texto"><?= e(siteContent($pdo, 'baralho_hero_texto', 'Uma leitura pode ser esse intervalo: um momento para organizar o que você sente, enxergar uma situação por outros ângulos e sair com perguntas melhores — não com promessas vazias.')) ?></p>
            <div class="hero-actions">
                <a class="btn btn-brand-gold" data-content-key="baralho_hero_btn_primario" href="#leituras"><?= e(siteContent($pdo, 'baralho_hero_btn_primario', 'Escolher minha leitura')) ?></a>
                <a class="text-link" data-content-key="baralho_hero_btn_secundario" href="#carta-do-caminho"><?= e(siteContent($pdo, 'baralho_hero_btn_secundario', 'Tirar uma carta simbólica →')) ?></a>
            </div>
        </div>

        <div id="carta-do-caminho" class="oracle-deck-stage oracle-stage-refined" data-oracle-deck>
            <div class="deck-aura"></div>
            <div class="deck-table handmade-deck reveal-spread">
                <div class="shuffle-card card-back back-one"><i></i></div>
                <div class="shuffle-card card-back back-two"><i></i></div>
                <div class="shuffle-card card-back back-three"><i></i></div>
                <div class="drawn-card handmade-card" data-card-reveal aria-live="polite">
                    <div class="card-paper artisan-card-paper">
                        <span class="card-number" data-card-number>32</span>
                        <span class="card-sigil"></span>
                        <div class="card-illustration" data-card-art data-theme="lua"><i></i><b data-card-symbol>☾</b></div>
                        <strong data-card-name>A Lua</strong>
                        <small>CARTA DO CAMINHO</small>
                    </div>
                </div>
                <button type="button" class="deck-action" data-draw-card><span>↻</span> <span data-content-key="baralho_deck_botao"><?= e(siteContent($pdo, 'baralho_deck_botao', 'Embaralhar e tirar uma carta')) ?></span></button>
                <small class="deck-note" data-content-key="baralho_deck_nota"><?= e(siteContent($pdo, 'baralho_deck_nota', 'Uma experiência simbólica para explorar o baralho. A leitura completa acontece no atendimento.')) ?></small>
            </div>
        </div>
    </div>
</section>

<section class="oracle-reveal-invite" data-card-reveal-invite hidden>
    <div class="container oracle-reveal-invite-inner">
        <div>
            <span class="section-kicker">A CARTA SE REVELOU</span>
            <h2><span data-card-invite-name>A Lua</span> apareceu no seu caminho.</h2>
            <p>O símbolo é só o começo. Abra a leitura da carta para entender a mensagem, a reflexão e o convite que ela traz.</p>
        </div>
        <button type="button" class="btn btn-dark" data-open-card-meaning>Entender o significado</button>
    </div>
</section>

<section class="oracle-whisper"><div class="container"><p data-content-key="baralho_frase"><?= e(siteContent($pdo, 'baralho_frase', '“Nem toda resposta precisa prever o futuro. Às vezes, ela só precisa devolver presença ao agora.”')) ?></p></div></section>

<section id="como-funciona" class="section oracle-intro">
    <div class="container story-layout">
        <div><span class="section-kicker" data-content-key="baralho_intro_kicker"><?= e(siteContent($pdo, 'baralho_intro_kicker', 'UM ORÁCULO, NÃO UMA SENTENÇA')) ?></span></div>
        <div>
            <h2 class="display-title" data-content-key="baralho_intro_titulo"><?= nl2br(e(siteContent($pdo, 'baralho_intro_titulo', "A leitura abre símbolos.\nVocê continua dono do caminho."))) ?></h2>
            <p class="large-copy" data-content-key="baralho_intro_texto"><?= e(siteContent($pdo, 'baralho_intro_texto', 'O Baralho Cigano é usado aqui como instrumento de reflexão e conversa. A proposta é acolher uma questão, observar padrões e possibilidades e construir uma leitura cuidadosa, sem substituir sua autonomia.')) ?></p>
            <div class="oracle-values">
                <div><span>01</span><strong data-content-key="baralho_valor_1_titulo"><?= e(siteContent($pdo, 'baralho_valor_1_titulo', 'Você chega com uma questão')) ?></strong><p data-content-key="baralho_valor_1_texto"><?= e(siteContent($pdo, 'baralho_valor_1_texto', 'Não precisa estar perfeitamente formulada. A gente organiza junto.')) ?></p></div>
                <div><span>02</span><strong data-content-key="baralho_valor_2_titulo"><?= e(siteContent($pdo, 'baralho_valor_2_titulo', 'A leitura cria um mapa')) ?></strong><p data-content-key="baralho_valor_2_texto"><?= e(siteContent($pdo, 'baralho_valor_2_texto', 'Cartas, símbolos e relações ajudam a olhar para o cenário com outras lentes.')) ?></p></div>
                <div><span>03</span><strong data-content-key="baralho_valor_3_titulo"><?= e(siteContent($pdo, 'baralho_valor_3_titulo', 'Você leva reflexão, não dependência')) ?></strong><p data-content-key="baralho_valor_3_texto"><?= e(siteContent($pdo, 'baralho_valor_3_texto', 'O objetivo é sair com mais clareza para decidir por si.')) ?></p></div>
            </div>
        </div>
    </div>
</section>

<section id="leituras" class="section oracle-services">
    <div class="container">
        <div class="section-heading split-heading">
            <div>
                <span class="section-kicker light" data-content-key="baralho_servicos_kicker"><?= e(siteContent($pdo, 'baralho_servicos_kicker', 'ESCOLHA O TEMPO DA SUA PERGUNTA')) ?></span>
                <h2 class="display-title light" data-content-key="baralho_servicos_titulo"><?= nl2br(e(siteContent($pdo, 'baralho_servicos_titulo', "Uma leitura para o tamanho\ndo que você precisa olhar."))) ?></h2>
            </div>
            <p data-content-key="baralho_servicos_texto"><?= e(siteContent($pdo, 'baralho_servicos_texto', 'Cada opção tem duração e valor definidos com transparência. Para solicitar uma leitura, você entra na sua conta e o atendimento fica guardado no seu perfil.')) ?></p>
        </div>
        <div class="oracle-service-grid">
            <?php foreach($servicos as $s): ?>
                <article class="oracle-service-card <?= $s['destaque'] ? 'featured' : '' ?>">
                    <?php if($s['destaque']): ?><span class="service-badge" data-content-key="baralho_servico_badge"><?= e(siteContent($pdo, 'baralho_servico_badge', 'MAIS ESCOLHIDA')) ?></span><?php endif; ?>
                    <small><?= (int)$s['duracao_minutos'] ?> MINUTOS</small>
                    <h3><?= e($s['nome']) ?></h3>
                    <em><?= e($s['subtitulo']) ?></em>
                    <p><?= e($s['descricao']) ?></p>
                    <div class="service-price"><span>a partir de</span><strong><?= dinheiro($s['preco']) ?></strong></div>
                    <a class="btn <?= $s['destaque'] ? 'btn-brand-gold' : 'btn-outline-light' ?> btn-wide" href="<?= BASE_URL ?>/agendar-leitura.php?servico=<?= (int)$s['id'] ?>" data-content-key="baralho_servico_cta"><?= e(siteContent($pdo, 'baralho_servico_cta', 'Quero esta leitura')) ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section oracle-emotion">
    <div class="container oracle-emotion-grid">
        <div>
            <span class="section-kicker" data-content-key="baralho_emocao_kicker"><?= e(siteContent($pdo, 'baralho_emocao_kicker', 'QUANDO UMA LEITURA PODE FAZER SENTIDO')) ?></span>
            <h2 class="display-title" data-content-key="baralho_emocao_titulo"><?= nl2br(e(siteContent($pdo, 'baralho_emocao_titulo', "Quando a cabeça repete a mesma pergunta\ne nenhuma resposta parece suficiente."))) ?></h2>
        </div>
        <div class="emotion-list">
            <div><strong data-content-key="baralho_emocao_1_titulo"><?= e(siteContent($pdo, 'baralho_emocao_1_titulo', 'Quando existe uma decisão')) ?></strong><p data-content-key="baralho_emocao_1_texto"><?= e(siteContent($pdo, 'baralho_emocao_1_texto', 'e você quer enxergar melhor o que pesa em cada caminho.')) ?></p></div>
            <div><strong data-content-key="baralho_emocao_2_titulo"><?= e(siteContent($pdo, 'baralho_emocao_2_titulo', 'Quando algo mudou')) ?></strong><p data-content-key="baralho_emocao_2_texto"><?= e(siteContent($pdo, 'baralho_emocao_2_texto', 'e você ainda está tentando entender o lugar que isso ocupa dentro de você.')) ?></p></div>
            <div><strong data-content-key="baralho_emocao_3_titulo"><?= e(siteContent($pdo, 'baralho_emocao_3_titulo', 'Quando você quer se escutar')) ?></strong><p data-content-key="baralho_emocao_3_texto"><?= e(siteContent($pdo, 'baralho_emocao_3_texto', 'sem transformar o oráculo em uma autoridade sobre sua vida.')) ?></p></div>
        </div>
    </div>
</section>

<?php $feedbackOrigem='Baralho Cigano'; $feedbackLimit=4; require __DIR__ . '/includes/feedbacks-section.php'; ?>
<section class="oracle-close">
    <div class="container closing-grid">
        <div>
            <span class="section-kicker light" data-content-key="baralho_close_kicker"><?= e(siteContent($pdo, 'baralho_close_kicker', 'SE A PERGUNTA CONTINUA VOLTANDO')) ?></span>
            <h2 data-content-key="baralho_close_titulo"><?= nl2br(e(siteContent($pdo, 'baralho_close_titulo', "Talvez seja hora de dar a ela\num lugar para ser escutada."))) ?></h2>
        </div>
        <div>
            <p data-content-key="baralho_close_texto"><?= e(siteContent($pdo, 'baralho_close_texto', 'Escolha uma leitura, conte brevemente o que você deseja olhar e acompanhe tudo dentro do seu perfil.')) ?></p>
            <a class="btn btn-brand-gold" data-content-key="baralho_close_cta" href="<?= BASE_URL ?>/agendar-leitura.php"><?= e(siteContent($pdo, 'baralho_close_cta', 'Solicitar uma leitura')) ?></a>
            <small class="ethical-note" data-content-key="baralho_close_etica"><?= e(siteContent($pdo, 'baralho_close_etica', 'Leituras não substituem orientação médica, psicológica, jurídica ou financeira e não prometem resultados futuros.')) ?></small>
        </div>
    </div>
</section>
<div class="card-meaning-modal" id="cardMeaningModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cardMeaningTitle">
    <button type="button" class="card-meaning-backdrop" data-close-card-meaning aria-label="Fechar significado"></button>
    <article class="card-meaning-dialog" data-card-modal-theme="lua">
        <button type="button" class="card-meaning-close" data-close-card-meaning aria-label="Fechar">×</button>
        <div class="card-modal-art">
            <span class="card-modal-number" data-modal-card-number>32</span>
            <span class="card-modal-orbit orbit-one"></span>
            <span class="card-modal-orbit orbit-two"></span>
            <span class="card-modal-symbol" data-modal-card-symbol>☾</span>
            <small>UMA CARTA, UM ESPELHO</small>
        </div>
        <div class="card-modal-copy">
            <span class="section-kicker">SUA CARTA</span>
            <h2 id="cardMeaningTitle" data-modal-card-title>A Lua</h2>
            <strong data-modal-card-keyword>Intuição · sensibilidade · ciclos</strong>
            <p data-modal-card-meaning>Convite para observar o que é sentido antes de ser explicado.</p>
            <blockquote data-modal-card-reflection>O que dentro de você já sabe a resposta, mesmo que ainda não consiga colocá-la em palavras?</blockquote>
            <div class="card-modal-cta">
                <span>Quer olhar sua pergunta com mais profundidade?</span>
                <a class="btn btn-brand-gold" href="<?= BASE_URL ?>/agendar-leitura.php">Quero minha leitura completa</a>
            </div>
        </div>
    </article>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
