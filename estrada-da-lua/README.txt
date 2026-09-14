ESTRADA DA LUA — V7

BANCO DE DADOS
- O projeto continua usando: estrada_da_lua
- Não crie outro banco.
- A migração é não destrutiva e foi reforçada para bancos criados por versões antigas do projeto.

COMO ATUALIZAR
1. Faça backup da pasta atual e do banco estrada_da_lua.
2. Substitua os arquivos pela V7.
3. Acesse no localhost: /admin/setup.php
4. Clique em “Atualizar banco / redefinir acesso”.
5. Entre no painel normalmente.

OBSERVAÇÃO IMPORTANTE SOBRE FEEDBACKS
- A V7 corrige especificamente bancos antigos em que feedbacks não possuía colunas como nome_cliente, texto ou origem.
- config/schema.php adiciona os campos que faltarem e reaproveita dados de nomes antigos quando possível.
- O cadastro também usa uma rotina compatível com colunas legadas, evitando que uma coluna antiga obrigatória provoque outro Fatal Error.

COMPRA DE PRONTA ENTREGA
- Produtos com pronta_entrega=1 e estoque>0 usam comprar-pronto.php.
- O fluxo é: peça pronta > identificação/login > carrinho/checkout > pedido.
- Produtos sob encomenda continuam usando pedido.php.
- pedido.php possui uma proteção extra: se receber o ID de uma peça pronta, redireciona para a compra direta.

CHAT
- Permissão de mídia é individual por conversa.
- Abra Admin > Atendimento > uma conversa.
- Use “Mídia nesta conversa” para liberar ou bloquear o upload do cliente.
- O PHP valida a permissão no envio; não é apenas uma trava visual.

PÁGINAS E TEXTOS
- Admin > Páginas & textos.
- Cada página possui seu próprio espaço de edição e prévia.
- A prévia carrega a própria página do site e vários campos atualizam enquanto você digita.
- O editor de Blog/Podcast permanece em Admin > Caderno & Podcast.

BARALHO CIGANO
- A carta é revelada após o embaralhamento.
- O significado só aparece ao clicar em “Entender o significado”.
- O modal é temático e termina com CTA para solicitar uma leitura completa.

LOGO
- O caminho continua: /img/logo-estrada-da-lua.png
- A constante continua em config/app.php como LOGO_PATH.

VALIDAÇÃO
- PHP validado com php -l.
- JavaScript validado com node --check.
- Rotas PHP estáticas conferidas.
- Nenhum alert(), confirm() ou prompt() nativo permanece no projeto.
- Na finalização da compra, o estoque é revalidado e baixado dentro da mesma transação do pedido para reduzir risco de venda duplicada da última unidade.
