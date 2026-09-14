</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="footer-wordmark" href="<?= BASE_URL ?>/index.php"><span class="footer-moon-mark"></span><div><small>ATELIÊ & ORÁCULO</small><strong>Estrada da Lua</strong></div></a>
            <p class="footer-copy">Peças, encontros e leituras que carregam cuidado, memória e presença.</p>
            <!-- A logo original continua em LOGO_PATH. No header usamos o arquivo como símbolo; basta substituir a imagem mantendo o mesmo nome/caminho. -->
        </div>
        <div><span class="footer-label">Escolha seu caminho</span><a href="<?= BASE_URL ?>/loja.php#pronta-entrega">Pronta entrega</a><a href="<?= BASE_URL ?>/pedido.php">Feita para você</a><a href="<?= BASE_URL ?>/baralho-cigano.php">Baralho Cigano</a><a href="<?= BASE_URL ?>/portfolio.php">Memórias</a></div>
        <div><span class="footer-label">Sua experiência</span><a href="<?= BASE_URL ?>/carrinho.php">Minha sacola<?= $cartCount ? ' · '.$cartCount : '' ?></a><a href="<?= BASE_URL ?>/<?= $clienteHeader ? 'perfil.php' : 'conta.php' ?>"><?= $clienteHeader ? 'Meu perfil' : 'Criar meu perfil' ?></a><?php if($clienteHeader): ?><a href="<?= BASE_URL ?>/chat.php">Atendimento pelo site<?= $chatUnread ? ' · '.$chatUnread.' nova(s)' : '' ?></a><a href="<?= BASE_URL ?>/meus-pedidos.php">Meus pedidos</a><?php endif; ?><a href="<?= BASE_URL ?>/avaliar.php">Deixar um feedback</a></div>
        <div><span class="footer-label">Em construção</span><button type="button" class="footer-future" data-coming-soon="Caderno da Lua" data-coming-text="Um espaço para histórias, processos, cuidados e símbolos. Estamos preparando o primeiro capítulo.">Caderno da Lua <small>em breve</small></button><button type="button" class="footer-future" data-coming-soon="Vozes da Estrada" data-coming-text="Conversas e episódios para levar a Estrada da Lua para além da tela. Em produção.">Vozes da Estrada <small>em breve</small></button><a class="admin-entry" href="<?= BASE_URL ?>/admin/login.php">Área administrativa ↗</a></div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> Estrada da Lua.</span><span>Feito para permanecer, não apenas passar.</span></div>
</footer>

<div class="coming-modal" id="comingModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="comingTitle">
    <button class="coming-backdrop" type="button" data-close-coming aria-label="Fechar"></button>
    <div class="coming-card">
        <button class="coming-close" type="button" data-close-coming aria-label="Fechar">×</button>
        <span class="coming-orbit"><i></i></span>
        <small>UM NOVO CAMINHO ESTÁ SENDO ABERTO</small>
        <h2 id="comingTitle">Em produção</h2>
        <p id="comingText">Estamos preparando esta experiência com o mesmo cuidado do restante da Estrada da Lua.</p>
        <button class="btn btn-dark" type="button" data-close-coming>Continuar explorando</button>
    </div>
</div>

<div class="ui-modal" id="confirmModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <button class="ui-modal-backdrop" type="button" data-confirm-cancel aria-label="Cancelar"></button>
    <div class="ui-modal-card">
        <span class="modal-symbol">✦</span>
        <small>CONFIRMAÇÃO</small>
        <h2 id="confirmTitle">Confirmar ação?</h2>
        <p id="confirmText">Esta ação precisa da sua confirmação.</p>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-confirm-cancel>Voltar</button><button class="btn btn-dark" type="button" data-confirm-ok>Confirmar</button></div>
    </div>
</div>
<div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true"></div>
<script src="<?= BASE_URL ?>/assets/js/site.js?v=7.1"></script>
<script src="<?= BASE_URL ?>/assets/js/site-v5.js?v=7.1"></script>
</body>
</html>
