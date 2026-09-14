<?php
/**
 * Migrações não destrutivas da Estrada da Lua.
 * Este arquivo SEMPRE usa o banco estrada_da_lua já existente.
 */
function tableColumns(PDO $pdo, string $table): array
{
    $cols = [];
    foreach ($pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll() as $col) {
        $cols[$col['Field']] = true;
    }
    return $cols;
}

function ensureColumns(PDO $pdo, string $table, array $columns): void
{
    $existentes = tableColumns($pdo, $table);
    foreach ($columns as $nome => $definicao) {
        if (!isset($existentes[$nome])) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$nome}` {$definicao}");
        }
    }
}

function ensureFullSchema(PDO $pdo): void
{
    static $done = false;
    if ($done) return;
    /* PEDIDOS / GUIAS */
    ensureColumns($pdo, 'pedidos', [
        'tipo_pedido'                 => "VARCHAR(100) NULL AFTER email",
        'linha_referencia'            => "VARCHAR(180) NULL AFTER tipo_pedido",
        'tamanho'                     => "VARCHAR(80) NULL AFTER linha_referencia",
        'cores'                       => "VARCHAR(255) NULL AFTER tamanho",
        'materiais'                   => "VARCHAR(255) NULL AFTER cores",
        'quantidade'                  => "INT NOT NULL DEFAULT 1 AFTER materiais",
        'faixa_orcamento'             => "VARCHAR(100) NULL AFTER quantidade",
        'prazo_desejado'              => "DATE NULL AFTER faixa_orcamento",
        'preferencia_contato'         => "VARCHAR(50) NULL AFTER prazo_desejado",
        'arquivo_referencia'          => "VARCHAR(255) NULL AFTER preferencia_contato",
        'codigo_acompanhamento'       => "VARCHAR(24) NULL AFTER arquivo_referencia",
        'comprimento_cm'              => "DECIMAL(10,2) NULL AFTER codigo_acompanhamento",
        'horas_trabalho'              => "DECIMAL(10,2) NULL AFTER comprimento_cm",
        'custo_materiais_calculado'   => "DECIMAL(10,2) NULL AFTER horas_trabalho",
        'custo_tempo_calculado'       => "DECIMAL(10,2) NULL AFTER custo_materiais_calculado",
        'custo_comprimento_calculado' => "DECIMAL(10,2) NULL AFTER custo_tempo_calculado",
        'custo_envio_calculado'       => "DECIMAL(10,2) NULL AFTER custo_comprimento_calculado",
        'custo_embalagem_calculado'   => "DECIMAL(10,2) NULL AFTER custo_envio_calculado",
        'preco_sugerido'              => "DECIMAL(10,2) NULL AFTER custo_embalagem_calculado",
        'valor_fechado'               => "DECIMAL(10,2) NULL AFTER preco_sugerido",
        'atualizado_em'               => "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER valor_fechado",
    ]);

    /* PRODUTOS / PRONTA ENTREGA */
    ensureColumns($pdo, 'produtos', [
        'tipo_produto'  => "VARCHAR(60) NOT NULL DEFAULT 'Guia' AFTER categoria_id",
        'pronta_entrega'=> "TINYINT(1) NOT NULL DEFAULT 0 AFTER estoque",
        'custo_unitario'=> "DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER pronta_entrega",
    ]);

    /* PERFIS DE CLIENTES */
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        telefone VARCHAR(30) NULL,
        avatar VARCHAR(255) NULL,
        cidade VARCHAR(120) NULL,
        senha VARCHAR(255) NOT NULL,
        aceita_marketing TINYINT(1) NOT NULL DEFAULT 0,
        marketing_consentido_em DATETIME NULL,
        interesses VARCHAR(255) NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ultimo_acesso DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'clientes', [
        'avatar' => "VARCHAR(255) NULL AFTER telefone",
        'cidade' => "VARCHAR(120) NULL AFTER avatar",
    ]);

    /* ATENDIMENTO / CHAT */
    $pdo->exec("CREATE TABLE IF NOT EXISTS conversas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        pedido_id INT NULL,
        leitura_id INT NULL,
        assunto VARCHAR(180) NOT NULL,
        status ENUM('Aberta','Em atendimento','Resolvida') NOT NULL DEFAULT 'Aberta',
        cliente_pode_enviar_midia TINYINT(1) NOT NULL DEFAULT 0,
        ultima_mensagem_em DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_conversas_cliente (cliente_id),
        INDEX idx_conversas_status (status),
        INDEX idx_conversas_ultima (ultima_mensagem_em)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'conversas', [
        'cliente_pode_enviar_midia' => "TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS mensagens_chat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversa_id INT NOT NULL,
        remetente_tipo ENUM('Cliente','Admin') NOT NULL,
        remetente_id INT NULL,
        mensagem TEXT NOT NULL,
        anexo VARCHAR(255) NULL,
        lida_em DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_chat_conversa (conversa_id),
        INDEX idx_chat_nao_lida (conversa_id, remetente_tipo, lida_em)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'pedidos', [
        'cliente_id'      => "INT NULL AFTER id",
        'tipo_entrega'    => "VARCHAR(50) NULL AFTER preferencia_contato",
        'cep'             => "VARCHAR(20) NULL AFTER tipo_entrega",
        'frete_estimado'  => "DECIMAL(10,2) NULL AFTER cep",
    ]);

    /* CONTEÚDO EDITÁVEL / BASE PARA BLOG E PODCAST */
    $pdo->exec("CREATE TABLE IF NOT EXISTS conteudos_site (
        chave VARCHAR(100) PRIMARY KEY,
        grupo VARCHAR(60) NOT NULL DEFAULT 'geral',
        rotulo VARCHAR(140) NOT NULL,
        valor TEXT NULL,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'conteudos_site', [
        'grupo'        => "VARCHAR(60) NOT NULL DEFAULT 'geral' AFTER chave",
        'rotulo'       => "VARCHAR(140) NOT NULL DEFAULT '' AFTER grupo",
        'valor'        => "TEXT NULL AFTER rotulo",
        'atualizado_em'=> "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ]);

    $conteudosPadrao = [
        ['home_hero_eyebrow','home','Sobretítulo da abertura','ANCESTRALIDADE • FEITO À MÃO • PRESENÇA'],
        ['home_hero_titulo','home','Título principal','Há coisas que a gente não veste.'],
        ['home_hero_destaque','home','Trecho em destaque','A gente carrega.'],
        ['home_hero_texto','home','Texto da abertura','A Estrada da Lua nasce do encontro entre memória, cuidado e criação. Cada peça é construída para ter presença — do primeiro fio ao último detalhe.'],
        ['home_process_kicker','home','Chamada da experiência','SUA GUIA, SEU CAMINHO'],
        ['home_process_titulo','home','Título da experiência','Uma peça feita para pertencer à sua história.'],
        ['home_process_texto','home','Texto da experiência','A criação começa na escuta: referências, intenção, cores e detalhes. O orçamento é justo e transparente, mas o centro da experiência é o cuidado com aquilo que você quer receber.'],
        ['home_closing_titulo','home','CTA final','Talvez a sua peça ainda não exista. E isso é a melhor parte.'],
        ['home_closing_texto','home','Texto do CTA final','Conte sua ideia. A gente transforma referência em matéria, com acompanhamento do começo ao fim.'],
        ['baralho_teaser_titulo','home','Chamada do Baralho','Há perguntas que não pedem pressa. Pedem presença.'],
        ['baralho_teaser_texto','home','Texto do Baralho','Um espaço de escuta e reflexão para olhar uma questão por outros ângulos, sem entregar sua autonomia para as cartas.'],
        ['whatsapp_numero','contato','WhatsApp da loja',''],
        ['whatsapp_mensagem','contato','Mensagem inicial do WhatsApp','Olá! Vim pelo site da Estrada da Lua e gostaria de continuar meu atendimento por aqui.'],
    ];
    $insConteudo = $pdo->prepare("INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES (?,?,?,?)");
    foreach ($conteudosPadrao as $conteudo) $insConteudo->execute($conteudo);

    $pdo->exec("CREATE TABLE IF NOT EXISTS conteudos_editoriais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('Blog','Podcast') NOT NULL,
        titulo VARCHAR(180) NOT NULL,
        slug VARCHAR(190) NULL,
        resumo TEXT NULL,
        conteudo LONGTEXT NULL,
        capa VARCHAR(255) NULL,
        status ENUM('Rascunho','Pronto','Publicado') NOT NULL DEFAULT 'Rascunho',
        publicado_em DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'conteudos_editoriais', [
        'categoria' => "VARCHAR(100) NULL AFTER tipo",
        'tempo_leitura' => "INT NULL AFTER resumo",
        'cta_texto' => "VARCHAR(120) NULL AFTER conteudo",
        'cta_url' => "VARCHAR(255) NULL AFTER cta_texto",
    ]);

    /* CONFIGURAÇÃO DE PREÇOS */
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracao_precos (
        id TINYINT UNSIGNED PRIMARY KEY,
        valor_cm DECIMAL(10,2) NOT NULL DEFAULT 0.35,
        valor_hora DECIMAL(10,2) NOT NULL DEFAULT 18.00,
        custo_embalagem DECIMAL(10,2) NOT NULL DEFAULT 5.00,
        frete_padrao DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        margem_percentual DECIMAL(6,2) NOT NULL DEFAULT 35.00,
        taxa_pagamento_percentual DECIMAL(6,2) NOT NULL DEFAULT 5.00,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT IGNORE INTO configuracao_precos (id) VALUES (1)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS materiais_preco (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        unidade VARCHAR(40) NOT NULL DEFAULT 'unidade',
        valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    /* FEEDBACKS REAIS */
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_cliente VARCHAR(120) NOT NULL,
        texto TEXT NOT NULL,
        nota TINYINT UNSIGNED NOT NULL DEFAULT 5,
        origem VARCHAR(60) NOT NULL DEFAULT 'Geral',
        codigo_referencia VARCHAR(24) NULL,
        aprovado TINYINT(1) NOT NULL DEFAULT 0,
        destaque TINYINT(1) NOT NULL DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    /*
     * Compatibilidade com bancos criados por versões anteriores.
     * CREATE TABLE IF NOT EXISTS não adiciona colunas a uma tabela que já existe,
     * então garantimos explicitamente os campos usados pela V5.
     */
    ensureColumns($pdo, 'feedbacks', [
        /* Os dois campos abaixo existiam com outros nomes em builds antigas.
         * Defaults seguros permitem migrar tabelas que já possuem registros. */
        'nome_cliente'      => "VARCHAR(120) NOT NULL DEFAULT 'Cliente'",
        'texto'             => "TEXT NULL",
        'nota'              => "TINYINT UNSIGNED NOT NULL DEFAULT 5",
        'origem'            => "VARCHAR(60) NOT NULL DEFAULT 'Geral'",
        'codigo_referencia' => "VARCHAR(24) NULL",
        'aprovado'          => "TINYINT(1) NOT NULL DEFAULT 0",
        'destaque'          => "TINYINT(1) NOT NULL DEFAULT 0",
        'criado_em'         => "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
    ]);

    /* Aproveita conteúdo de schemas legados quando os nomes eram diferentes. */
    $feedbackCols = tableColumns($pdo, 'feedbacks');
    foreach (['nome','cliente_nome','cliente'] as $legacyName) {
        if (isset($feedbackCols[$legacyName])) {
            $pdo->exec("UPDATE feedbacks SET nome_cliente=`{$legacyName}` WHERE (nome_cliente IS NULL OR TRIM(nome_cliente)='' OR nome_cliente='Cliente') AND `{$legacyName}` IS NOT NULL AND TRIM(`{$legacyName}`)<>''");
            break;
        }
    }
    foreach (['feedback','mensagem','comentario','depoimento','relato','descricao'] as $legacyText) {
        if (isset($feedbackCols[$legacyText])) {
            $pdo->exec("UPDATE feedbacks SET texto=`{$legacyText}` WHERE (texto IS NULL OR TRIM(texto)='') AND `{$legacyText}` IS NOT NULL AND TRIM(`{$legacyText}`)<>''");
            break;
        }
    }
    if (isset($feedbackCols['publicado'])) {
        $pdo->exec("UPDATE feedbacks SET aprovado=IF(COALESCE(publicado,0)<>0,1,aprovado)");
    }
    if (isset($feedbackCols['avaliacao'])) {
        $pdo->exec("UPDATE feedbacks SET nota=LEAST(5,GREATEST(1,COALESCE(avaliacao,nota,5)))");
    }
    foreach (['tipo','categoria'] as $legacyOrigin) {
        if (isset($feedbackCols[$legacyOrigin])) {
            $pdo->exec("UPDATE feedbacks SET origem=`{$legacyOrigin}` WHERE (origem IS NULL OR TRIM(origem)='' OR origem='Geral') AND `{$legacyOrigin}` IS NOT NULL AND TRIM(`{$legacyOrigin}`)<>''");
            break;
        }
    }
    $pdo->exec("UPDATE feedbacks SET nome_cliente='Cliente' WHERE nome_cliente IS NULL OR TRIM(nome_cliente)=''");
    $pdo->exec("UPDATE feedbacks SET texto='' WHERE texto IS NULL");
    $pdo->exec("UPDATE feedbacks SET origem='Geral' WHERE origem IS NULL OR TRIM(origem)=''");

    /* BARALHO CIGANO */
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracao_baralho (
        id TINYINT UNSIGNED PRIMARY KEY,
        valor_hora DECIMAL(10,2) NOT NULL DEFAULT 25.00,
        custo_preparacao DECIMAL(10,2) NOT NULL DEFAULT 5.00,
        taxa_plataforma_percentual DECIMAL(6,2) NOT NULL DEFAULT 5.00,
        margem_percentual DECIMAL(6,2) NOT NULL DEFAULT 45.00,
        adicional_urgencia_percentual DECIMAL(6,2) NOT NULL DEFAULT 25.00,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT IGNORE INTO configuracao_baralho (id) VALUES (1)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS servicos_baralho (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        subtitulo VARCHAR(180) NULL,
        descricao TEXT NULL,
        duracao_minutos INT NOT NULL DEFAULT 30,
        preco DECIMAL(10,2) NOT NULL DEFAULT 0,
        destaque TINYINT(1) NOT NULL DEFAULT 0,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        ordem INT NOT NULL DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ((int)$pdo->query("SELECT COUNT(*) FROM servicos_baralho")->fetchColumn() === 0) {
        $stmt = $pdo->prepare("INSERT INTO servicos_baralho (nome, subtitulo, descricao, duracao_minutos, preco, destaque, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $defaults = [
            ['Caminho Aberto', 'Uma questão, um ponto de partida', 'Uma leitura objetiva para olhar com mais clareza para uma pergunta ou situação específica.', 30, 45.00, 0, 1],
            ['Encruzilhada', 'Quando existem caminhos demais', 'Uma leitura mais ampla para compreender possibilidades, tensões, escolhas e aquilo que merece atenção agora.', 50, 75.00, 1, 2],
            ['Lua Inteira', 'Tempo para olhar o todo', 'Uma leitura aprofundada para atravessar diferentes áreas da vida com mais espaço, contexto e reflexão.', 75, 110.00, 0, 3],
        ];
        foreach ($defaults as $row) { $stmt->execute($row); }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS leituras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_cliente VARCHAR(120) NOT NULL,
        telefone VARCHAR(30) NOT NULL,
        email VARCHAR(150) NULL,
        servico_id INT NULL,
        tema VARCHAR(100) NULL,
        pergunta TEXT NULL,
        formato VARCHAR(80) NULL,
        data_preferida DATE NULL,
        urgencia TINYINT(1) NOT NULL DEFAULT 0,
        observacoes TEXT NULL,
        status ENUM('Nova','Confirmada','Agendada','Concluída','Cancelada') NOT NULL DEFAULT 'Nova',
        preco_estimado DECIMAL(10,2) NULL,
        valor_fechado DECIMAL(10,2) NULL,
        outros_custos DECIMAL(10,2) NOT NULL DEFAULT 0,
        codigo_acompanhamento VARCHAR(24) NULL,
        data_agendada DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_leituras_servico FOREIGN KEY (servico_id) REFERENCES servicos_baralho(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureColumns($pdo, 'leituras', [
        'cliente_id' => "INT NULL AFTER id",
    ]);

    /* FINANCEIRO */
    $pdo->exec("CREATE TABLE IF NOT EXISTS vendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        origem VARCHAR(60) NOT NULL,
        referencia_id INT NULL,
        cliente VARCHAR(120) NULL,
        descricao VARCHAR(255) NOT NULL,
        receita DECIMAL(10,2) NOT NULL DEFAULT 0,
        custo_materiais DECIMAL(10,2) NOT NULL DEFAULT 0,
        custo_tempo DECIMAL(10,2) NOT NULL DEFAULT 0,
        custo_envio DECIMAL(10,2) NOT NULL DEFAULT 0,
        outros_custos DECIMAL(10,2) NOT NULL DEFAULT 0,
        data_venda DATE NOT NULL,
        observacoes TEXT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_vendas_data (data_venda),
        INDEX idx_vendas_origem_ref (origem, referencia_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    /* Códigos de acompanhamento para pedidos antigos */
    $faltando = $pdo->query("SELECT id FROM pedidos WHERE codigo_acompanhamento IS NULL OR codigo_acompanhamento = ''")->fetchAll();
    if ($faltando) {
        $up = $pdo->prepare("UPDATE pedidos SET codigo_acompanhamento = ? WHERE id = ?");
        foreach ($faltando as $row) {
            $up->execute(['EDL-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10)), $row['id']]);
        }
    }

    $faltandoLeituras = $pdo->query("SELECT id FROM leituras WHERE codigo_acompanhamento IS NULL OR codigo_acompanhamento = ''")->fetchAll();
    if ($faltandoLeituras) {
        $up = $pdo->prepare("UPDATE leituras SET codigo_acompanhamento = ? WHERE id = ?");
        foreach ($faltandoLeituras as $row) {
            $up->execute(['BAR-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10)), $row['id']]);
        }
    }

    /* Só considera a migração concluída depois de todas as etapas passarem. */
    $done = true;
}

/* Compatibilidade com os arquivos da versão anterior */
function ensurePedidoSchema(PDO $pdo): void
{
    ensureFullSchema($pdo);
}
