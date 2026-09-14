-- Estrada da Lua V5.1 — correção de compatibilidade da tabela feedbacks
-- Execute somente se quiser corrigir manualmente pelo phpMyAdmin.
-- A forma recomendada continua sendo abrir /admin/setup.php depois de substituir os arquivos.

USE estrada_da_lua;

-- Em MariaDB/MySQL recentes, ADD COLUMN IF NOT EXISTS evita erro caso o campo já exista.
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS nota TINYINT UNSIGNED NOT NULL DEFAULT 5;
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS origem VARCHAR(60) NOT NULL DEFAULT 'Geral';
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS codigo_referencia VARCHAR(24) NULL;
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS aprovado TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS destaque TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE feedbacks SET origem='Geral' WHERE origem IS NULL OR TRIM(origem)='';
