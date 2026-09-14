-- Estrada da Lua V7.1: instalação nova. Gerado em 02/09/2026.

-- Tabelas-base reconstruídas a partir das consultas do projeto; extensões extraídas de config/schema.php.

-- Estrutura e configurações iniciais, sem os dados do computador antigo.

-- Use em banco novo. Para migrar seus cadastros, importe o backup do computador antigo em vez deste arquivo.

-- Depois abra /estrada-da-lua/admin/setup.php e defina seu acesso.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS estrada_da_lua CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE estrada_da_lua;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin','funcionario') NOT NULL DEFAULT 'funcionario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NULL,
    nome VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0,
    estoque INT NOT NULL DEFAULT 0,
    imagem_principal VARCHAR(255) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produtos_categoria (categoria_id),
    tipo_produto VARCHAR(60) NOT NULL DEFAULT 'Guia',
    pronta_entrega TINYINT(1) NOT NULL DEFAULT 0,
    custo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    INDEX idx_imagens_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    imagem VARCHAR(255) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    texto LONGTEXT NULL,
    imagem VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(120) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NULL,
    observacoes TEXT NULL,
    status ENUM('Novo','Em Produção','Pronto','Entregue','Cancelado') NOT NULL DEFAULT 'Novo',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_pedido VARCHAR(100) NULL,
    linha_referencia VARCHAR(180) NULL,
    tamanho VARCHAR(80) NULL,
    cores VARCHAR(255) NULL,
    materiais VARCHAR(255) NULL,
    quantidade INT NOT NULL DEFAULT 1,
    faixa_orcamento VARCHAR(100) NULL,
    prazo_desejado DATE NULL,
    preferencia_contato VARCHAR(50) NULL,
    arquivo_referencia VARCHAR(255) NULL,
    codigo_acompanhamento VARCHAR(24) NULL,
    comprimento_cm DECIMAL(10,2) NULL,
    horas_trabalho DECIMAL(10,2) NULL,
    custo_materiais_calculado DECIMAL(10,2) NULL,
    custo_tempo_calculado DECIMAL(10,2) NULL,
    custo_comprimento_calculado DECIMAL(10,2) NULL,
    custo_envio_calculado DECIMAL(10,2) NULL,
    custo_embalagem_calculado DECIMAL(10,2) NULL,
    preco_sugerido DECIMAL(10,2) NULL,
    valor_fechado DECIMAL(10,2) NULL,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cliente_id INT NULL,
    tipo_entrega VARCHAR(50) NULL,
    cep VARCHAR(20) NULL,
    frete_estimado DECIMAL(10,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    INDEX idx_itens_pedido (pedido_id),
    INDEX idx_itens_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversas (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensagens_chat (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conteudos_site (
    chave VARCHAR(100) PRIMARY KEY,
    grupo VARCHAR(60) NOT NULL DEFAULT 'geral',
    rotulo VARCHAR(140) NOT NULL,
    valor TEXT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conteudos_editoriais (
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
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    categoria VARCHAR(100) NULL,
    tempo_leitura INT NULL,
    cta_texto VARCHAR(120) NULL,
    cta_url VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracao_precos (
    id TINYINT UNSIGNED PRIMARY KEY,
    valor_cm DECIMAL(10,2) NOT NULL DEFAULT 0.35,
    valor_hora DECIMAL(10,2) NOT NULL DEFAULT 18.00,
    custo_embalagem DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    frete_padrao DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    margem_percentual DECIMAL(6,2) NOT NULL DEFAULT 35.00,
    taxa_pagamento_percentual DECIMAL(6,2) NOT NULL DEFAULT 5.00,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materiais_preco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    unidade VARCHAR(40) NOT NULL DEFAULT 'unidade',
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(120) NOT NULL,
    texto TEXT NOT NULL,
    nota TINYINT UNSIGNED NOT NULL DEFAULT 5,
    origem VARCHAR(60) NOT NULL DEFAULT 'Geral',
    codigo_referencia VARCHAR(24) NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 0,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracao_baralho (
    id TINYINT UNSIGNED PRIMARY KEY,
    valor_hora DECIMAL(10,2) NOT NULL DEFAULT 25.00,
    custo_preparacao DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    taxa_plataforma_percentual DECIMAL(6,2) NOT NULL DEFAULT 5.00,
    margem_percentual DECIMAL(6,2) NOT NULL DEFAULT 45.00,
    adicional_urgencia_percentual DECIMAL(6,2) NOT NULL DEFAULT 25.00,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicos_baralho (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leituras (
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
    CONSTRAINT fk_leituras_servico FOREIGN KEY (servico_id) REFERENCES servicos_baralho(id) ON DELETE SET NULL,
    cliente_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vendas (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracao_precos (id) VALUES (1);

INSERT IGNORE INTO configuracao_baralho (id) VALUES (1);

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_hero_eyebrow','home','Sobretítulo da abertura','ANCESTRALIDADE • FEITO À MÃO • PRESENÇA');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_hero_titulo','home','Título principal','Há coisas que a gente não veste.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_hero_destaque','home','Trecho em destaque','A gente carrega.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_hero_texto','home','Texto da abertura','A Estrada da Lua nasce do encontro entre memória, cuidado e criação. Cada peça é construída para ter presença — do primeiro fio ao último detalhe.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_process_kicker','home','Chamada da experiência','SUA GUIA, SEU CAMINHO');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_process_titulo','home','Título da experiência','Uma peça feita para pertencer à sua história.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_process_texto','home','Texto da experiência','A criação começa na escuta: referências, intenção, cores e detalhes. O orçamento é justo e transparente, mas o centro da experiência é o cuidado com aquilo que você quer receber.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_closing_titulo','home','CTA final','Talvez a sua peça ainda não exista. E isso é a melhor parte.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('home_closing_texto','home','Texto do CTA final','Conte sua ideia. A gente transforma referência em matéria, com acompanhamento do começo ao fim.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('baralho_teaser_titulo','home','Chamada do Baralho','Há perguntas que não pedem pressa. Pedem presença.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('baralho_teaser_texto','home','Texto do Baralho','Um espaço de escuta e reflexão para olhar uma questão por outros ângulos, sem entregar sua autonomia para as cartas.');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('whatsapp_numero','contato','WhatsApp da loja','');

INSERT IGNORE INTO conteudos_site (chave,grupo,rotulo,valor) VALUES ('whatsapp_mensagem','contato','Mensagem inicial do WhatsApp','Olá! Vim pelo site da Estrada da Lua e gostaria de continuar meu atendimento por aqui.');

INSERT IGNORE INTO servicos_baralho (id,nome,subtitulo,descricao,duracao_minutos,preco,destaque,ordem) VALUES (1,'Caminho Aberto','Uma questão, um ponto de partida','Uma leitura objetiva para olhar com mais clareza para uma pergunta ou situação específica.',30,45.0,0,1);

INSERT IGNORE INTO servicos_baralho (id,nome,subtitulo,descricao,duracao_minutos,preco,destaque,ordem) VALUES (2,'Encruzilhada','Quando existem caminhos demais','Uma leitura mais ampla para compreender possibilidades, tensões, escolhas e aquilo que merece atenção agora.',50,75.0,1,2);

INSERT IGNORE INTO servicos_baralho (id,nome,subtitulo,descricao,duracao_minutos,preco,destaque,ordem) VALUES (3,'Lua Inteira','Tempo para olhar o todo','Uma leitura aprofundada para atravessar diferentes áreas da vida com mais espaço, contexto e reflexão.',75,110.0,0,3);
