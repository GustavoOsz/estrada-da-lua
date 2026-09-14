<?php
$feedbackLimit = $feedbackLimit ?? 6;
$feedbackOrigem = $feedbackOrigem ?? null;
$feedbacksPublicos = getFeedbacks($pdo, $feedbackLimit, $feedbackOrigem);
?>
<?php if ($feedbacksPublicos): ?>
<section class="section feedback-section">
    <div class="container">
        <div class="section-heading split-heading feedback-heading">
            <div>
                <span class="section-kicker">QUEM JÁ PASSOU POR AQUI</span>
                <h2 class="display-title">Experiências que viraram<br>palavra e memória.</h2>
            </div>
            <p>Os relatos abaixo são feedbacks aprovados de clientes da Estrada da Lua. Nada de depoimento inventado: a confiança precisa nascer de experiências reais.</p>
        </div>
        <div class="feedback-grid">
            <?php foreach ($feedbacksPublicos as $feedback): ?>
                <article class="feedback-card <?= $feedback['destaque'] ? 'featured-feedback' : '' ?>">
                    <div class="feedback-stars" aria-label="<?= (int)$feedback['nota'] ?> de 5 estrelas"><?= str_repeat('★', max(1, min(5, (int)$feedback['nota']))) ?></div>
                    <blockquote>“<?= e($feedback['texto']) ?>”</blockquote>
                    <footer><strong><?= e($feedback['nome_cliente']) ?></strong><span><?= e($feedback['origem']) ?></span></footer>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
