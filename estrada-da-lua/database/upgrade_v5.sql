-- Estrada da Lua · referência de migração V5
-- O caminho recomendado é abrir /admin/setup.php uma vez no localhost.
-- Esse setup usa config/schema.php e faz as alterações de modo não destrutivo.
-- Banco mantido: estrada_da_lua
USE estrada_da_lua;

CREATE TABLE IF NOT EXISTS conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    pedido_id INT NULL,
    leitura_id INT NULL,
    assunto VARCHAR(180) NOT NULL,
    status ENUM('Aberta','Em atendimento','Resolvida') NOT NULL DEFAULT 'Aberta',
    ultima_mensagem_em DATETIME NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversas_cliente (cliente_id),
    INDEX idx_conversas_status (status),
    INDEX idx_conversas_ultima (ultima_mensagem_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- As colunas abaixo são adicionadas automaticamente pelo setup somente quando faltarem:
-- clientes.avatar VARCHAR(255)
-- clientes.cidade VARCHAR(120)
-- conteudos_editoriais.categoria VARCHAR(100)
-- conteudos_editoriais.tempo_leitura INT
-- conteudos_editoriais.cta_texto VARCHAR(120)
-- conteudos_editoriais.cta_url VARCHAR(255)
-- conteudos_site: whatsapp_numero e whatsapp_mensagem
