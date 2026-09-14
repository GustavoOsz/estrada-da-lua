<?php
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function dinheiro($valor): string
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function excerpt(?string $texto, int $limite = 125): string
{
    $texto = trim(strip_tags((string)$texto));
    if (mb_strlen($texto) <= $limite) return $texto;
    return rtrim(mb_substr($texto, 0, $limite - 1)) . '…';
}

function generateTrackingCode(string $prefixo = 'EDL'): string
{
    return strtoupper($prefixo . '-' . substr(bin2hex(random_bytes(5)), 0, 10));
}

function normalizePhone(string $valor): string
{
    return preg_replace('/\D+/', '', $valor) ?: '';
}

function contactMatches(array $row, string $contato): bool
{
    $contato = trim($contato);
    if ($contato === '') return false;
    if (!empty($row['email']) && mb_strtolower(trim($row['email'])) === mb_strtolower($contato)) return true;
    if (!empty($row['telefone']) && normalizePhone($row['telefone']) === normalizePhone($contato) && normalizePhone($contato) !== '') return true;
    return false;
}

function rememberTracking(string $tipo, string $codigo): void
{
    if (headers_sent()) return;
    $atual = [];
    if (!empty($_COOKIE['estrada_rastros'])) {
        $decoded = json_decode($_COOKIE['estrada_rastros'], true);
        if (is_array($decoded)) $atual = $decoded;
    }
    $chave = $tipo . ':' . $codigo;
    $atual = array_values(array_filter($atual, fn($item) => $item !== $chave));
    array_unshift($atual, $chave);
    $atual = array_slice($atual, 0, 12);
    setcookie('estrada_rastros', json_encode($atual), [
        'expires' => time() + 31536000,
        'path' => BASE_URL ?: '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['estrada_rastros'] = json_encode($atual);
}

function rememberedTracking(): array
{
    if (empty($_COOKIE['estrada_rastros'])) return [];
    $items = json_decode($_COOKIE['estrada_rastros'], true);
    return is_array($items) ? $items : [];
}

function uploadImagem(array $arquivo, string $pasta): ?string
{
    if (!isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($arquivo['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
    if ($arquivo['size'] > 5 * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo 5 MB.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($arquivo['tmp_name']);
    $extensoes = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($extensoes[$mime])) throw new RuntimeException('Formato inválido. Use JPG, PNG ou WEBP.');

    $destinoPasta = APP_ROOT . '/uploads/' . trim($pasta, '/');
    if (!is_dir($destinoPasta)) mkdir($destinoPasta, 0775, true);
    $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
    if (!move_uploaded_file($arquivo['tmp_name'], $destinoPasta . '/' . $nome)) throw new RuntimeException('Não foi possível salvar a imagem.');
    return 'uploads/' . trim($pasta, '/') . '/' . $nome;
}

function pedidoStatusClass(string $status): string
{
    return match ($status) {
        'Em Produção' => 'status status-production',
        'Pronto' => 'status status-ready',
        'Entregue' => 'status status-done',
        'Cancelado' => 'status status-cancelled',
        default => 'status status-new',
    };
}

function leituraStatusClass(string $status): string
{
    return match ($status) {
        'Confirmada' => 'status status-production',
        'Agendada' => 'status status-ready',
        'Concluída' => 'status status-done',
        'Cancelada' => 'status status-cancelled',
        default => 'status status-new',
    };
}

function insertFeedbackCompat(PDO $pdo, array $data): int
{
    $meta = $pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll();
    if (!$meta) throw new RuntimeException('A tabela de feedbacks não está disponível.');

    $present = [];
    foreach ($meta as $col) $present[$col['Field']] = $col;

    $name = trim((string)($data['nome_cliente'] ?? $data['nome'] ?? ''));
    $text = trim((string)($data['texto'] ?? ''));
    $rating = max(1, min(5, (int)($data['nota'] ?? 5)));
    $origin = trim((string)($data['origem'] ?? 'Geral')) ?: 'Geral';
    $reference = trim((string)($data['codigo_referencia'] ?? '')) ?: null;
    $approved = !empty($data['aprovado']) ? 1 : 0;
    $featured = !empty($data['destaque']) ? 1 : 0;

    $values = [];
    $put = static function(string $column, mixed $value) use (&$values, $present): void {
        if (isset($present[$column])) $values[$column] = $value;
    };

    foreach (['nome_cliente','nome','cliente_nome','cliente'] as $col) $put($col, $name);
    foreach (['texto','feedback','mensagem','comentario','depoimento','relato','descricao'] as $col) $put($col, $text);
    foreach (['nota','avaliacao'] as $col) $put($col, $rating);
    foreach (['origem','tipo','categoria'] as $col) $put($col, $origin);
    foreach (['codigo_referencia','codigo'] as $col) $put($col, $reference);
    foreach (['aprovado','publicado'] as $col) $put($col, $approved);
    $put('destaque', $featured);

    if (!$values) throw new RuntimeException('A estrutura de feedbacks não possui campos compatíveis.');

    $columns = array_keys($values);
    $sql = 'INSERT INTO feedbacks (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($values));
    return (int)$pdo->lastInsertId();
}

function normalizeFeedbackRow(array $row): array
{
    $row['nome_cliente'] = $row['nome_cliente'] ?? $row['nome'] ?? $row['cliente_nome'] ?? $row['cliente'] ?? 'Cliente';
    $row['texto'] = $row['texto'] ?? $row['feedback'] ?? $row['mensagem'] ?? $row['comentario'] ?? $row['depoimento'] ?? $row['relato'] ?? $row['descricao'] ?? '';
    $row['nota'] = (int)($row['nota'] ?? $row['avaliacao'] ?? 5);
    $row['origem'] = $row['origem'] ?? $row['tipo'] ?? $row['categoria'] ?? 'Geral';
    $row['destaque'] = (int)($row['destaque'] ?? 0);
    $row['aprovado'] = (int)($row['aprovado'] ?? $row['publicado'] ?? 0);
    return $row;
}

function getFeedbacks(PDO $pdo, int $limite = 6, ?string $origem = null): array
{
    /*
     * A prova social aparece em várias páginas. Por isso ela nunca deve ser capaz
     * de derrubar o site caso o banco esteja alguns passos atrás da versão do código.
     */
    $limite = max(1, min(24, $limite));

    try {
        $colunas = [];
        foreach ($pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll() as $coluna) {
            $colunas[$coluna['Field']] = true;
        }

        if (!$colunas) return [];

        $where = [];
        $params = [];

        if (isset($colunas['aprovado'])) {
            $where[] = 'aprovado = 1';
        }

        if ($origem && isset($colunas['origem'])) {
            $where[] = "(origem = ? OR origem = 'Geral')";
            $params[] = $origem;
        }

        $ordem = [];
        if (isset($colunas['destaque'])) $ordem[] = 'destaque DESC';
        if (isset($colunas['criado_em'])) $ordem[] = 'criado_em DESC';
        elseif (isset($colunas['id'])) $ordem[] = 'id DESC';

        $sql = 'SELECT * FROM feedbacks';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        if ($ordem) $sql .= ' ORDER BY ' . implode(', ', $ordem);
        $sql .= ' LIMIT ' . $limite;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row = normalizeFeedbackRow($row);
        }
        unset($row);

        return $rows;
    } catch (Throwable $e) {
        /* Feedback é conteúdo complementar: em caso de incompatibilidade, não quebra a página. */
        return [];
    }
}

function getConfigPrecos(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM configuracao_precos WHERE id=1")->fetch() ?: [];
}

function calcularPrecoGuia(array $config, float $comprimento, float $materiais, float $horas, float $envio): array
{
    $custoComprimento = max(0, $comprimento) * (float)($config['valor_cm'] ?? 0);
    $custoMateriais = max(0, $materiais);
    $custoTempo = max(0, $horas) * (float)($config['valor_hora'] ?? 0);
    $custoEmbalagem = (float)($config['custo_embalagem'] ?? 0);
    $custoEnvio = max(0, $envio);
    $custoBase = $custoComprimento + $custoMateriais + $custoTempo + $custoEmbalagem + $custoEnvio;
    $margem = max(0, min(90, (float)($config['margem_percentual'] ?? 0))) / 100;
    $taxa = max(0, min(50, (float)($config['taxa_pagamento_percentual'] ?? 0))) / 100;
    $divisor = max(.05, 1 - $margem - $taxa);
    $sugerido = $custoBase / $divisor;
    return compact('custoComprimento','custoMateriais','custoTempo','custoEmbalagem','custoEnvio','custoBase','sugerido');
}

function precoSugeridoBaralho(array $config, int $duracaoMinutos): array
{
    $custoTempo = ((float)$duracaoMinutos / 60) * (float)($config['valor_hora'] ?? 0);
    $preparo = (float)($config['custo_preparacao'] ?? 0);
    $base = $custoTempo + $preparo;
    $margem = max(0, min(90, (float)($config['margem_percentual'] ?? 0))) / 100;
    $taxa = max(0, min(50, (float)($config['taxa_plataforma_percentual'] ?? 0))) / 100;
    $preco = $base / max(.05, 1 - $margem - $taxa);
    return ['custoTempo'=>$custoTempo,'preparo'=>$preparo,'base'=>$base,'sugerido'=>$preco];
}

function upsertVenda(PDO $pdo, array $dados): void
{
    $origem = $dados['origem'];
    $ref = $dados['referencia_id'] ?? null;
    $id = null;
    if ($ref) {
        $stmt = $pdo->prepare("SELECT id FROM vendas WHERE origem=? AND referencia_id=? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$origem, $ref]);
        $id = $stmt->fetchColumn();
    }
    $vals = [
        $dados['cliente'] ?? null,
        $dados['descricao'] ?? 'Venda',
        $dados['receita'] ?? 0,
        $dados['custo_materiais'] ?? 0,
        $dados['custo_tempo'] ?? 0,
        $dados['custo_envio'] ?? 0,
        $dados['outros_custos'] ?? 0,
        $dados['data_venda'] ?? date('Y-m-d'),
        $dados['observacoes'] ?? null,
    ];
    if ($id) {
        $stmt = $pdo->prepare("UPDATE vendas SET cliente=?, descricao=?, receita=?, custo_materiais=?, custo_tempo=?, custo_envio=?, outros_custos=?, data_venda=?, observacoes=? WHERE id=?");
        $stmt->execute(array_merge($vals, [$id]));
    } else {
        $stmt = $pdo->prepare("INSERT INTO vendas (origem, referencia_id, cliente, descricao, receita, custo_materiais, custo_tempo, custo_envio, outros_custos, data_venda, observacoes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute(array_merge([$origem, $ref], $vals));
    }
}

function syncVendaPedido(PDO $pdo, array $pedido): void
{
    $receita = (float)($pedido['valor_fechado'] ?: $pedido['preco_sugerido']);
    if ($receita <= 0) return;
    $it = $pdo->prepare("SELECT COUNT(*) FROM pedido_itens WHERE pedido_id=?");
    $it->execute([$pedido['id']]);
    $origem = (int)$it->fetchColumn() > 0 ? 'Produto' : 'Guia personalizada';
    upsertVenda($pdo, [
        'origem'=>$origem,
        'referencia_id'=>(int)$pedido['id'],
        'cliente'=>$pedido['nome_cliente'],
        'descricao'=>($pedido['tipo_pedido'] ?: 'Pedido') . ' #' . $pedido['id'],
        'receita'=>$receita,
        'custo_materiais'=>(float)($pedido['custo_materiais_calculado'] ?? 0) + (float)($pedido['custo_comprimento_calculado'] ?? 0),
        'custo_tempo'=>(float)($pedido['custo_tempo_calculado'] ?? 0),
        'custo_envio'=>(float)($pedido['custo_envio_calculado'] ?? 0),
        'outros_custos'=>(float)($pedido['custo_embalagem_calculado'] ?? 0),
        'data_venda'=>date('Y-m-d'),
        'observacoes'=>'Gerado automaticamente pelo pedido ' . ($pedido['codigo_acompanhamento'] ?? ''),
    ]);
}

function syncVendaLeitura(PDO $pdo, array $leitura): void
{
    $receita = (float)($leitura['valor_fechado'] ?: $leitura['preco_estimado']);
    if ($receita <= 0) return;
    $config = $pdo->query("SELECT * FROM configuracao_baralho WHERE id=1")->fetch();
    $duracao = (int)($leitura['duracao_minutos'] ?? 0);
    $custoTempo = ($duracao / 60) * (float)($config['valor_hora'] ?? 0);
    $taxa = $receita * ((float)($config['taxa_plataforma_percentual'] ?? 0) / 100);
    $outros = (float)($config['custo_preparacao'] ?? 0) + (float)($leitura['outros_custos'] ?? 0) + $taxa;
    upsertVenda($pdo, [
        'origem'=>'Baralho Cigano',
        'referencia_id'=>(int)$leitura['id'],
        'cliente'=>$leitura['nome_cliente'],
        'descricao'=>($leitura['servico_nome'] ?? 'Leitura') . ' #' . $leitura['id'],
        'receita'=>$receita,
        'custo_tempo'=>$custoTempo,
        'outros_custos'=>$outros,
        'data_venda'=>date('Y-m-d'),
        'observacoes'=>'Gerado automaticamente pela leitura ' . ($leitura['codigo_acompanhamento'] ?? ''),
    ]);
}

function startClientSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function currentClient(PDO $pdo): ?array
{
    startClientSession();
    $id = (int)($_SESSION['cliente_id'] ?? 0);
    if (!$id) return null;
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    if (!$cliente) unset($_SESSION['cliente_id']);
    return $cliente ?: null;
}

function requireClient(PDO $pdo): array
{
    $cliente = currentClient($pdo);
    if (!$cliente) {
        header('Location: ' . BASE_URL . '/conta.php?voltar=' . urlencode($_SERVER['REQUEST_URI'] ?? BASE_URL . '/perfil.php'));
        exit;
    }
    return $cliente;
}

function siteContent(PDO $pdo, string $chave, string $padrao = ''): string
{
    static $cache = [];
    if (array_key_exists($chave, $cache)) return $cache[$chave];
    $stmt = $pdo->prepare("SELECT valor FROM conteudos_site WHERE chave=? LIMIT 1");
    $stmt->execute([$chave]);
    $valor = $stmt->fetchColumn();
    $cache[$chave] = $valor !== false && $valor !== null ? (string)$valor : $padrao;
    return $cache[$chave];
}

function siteContentBool(PDO $pdo, string $chave, bool $padrao = false): bool
{
    $valor = mb_strtolower(trim(siteContent($pdo, $chave, $padrao ? "1" : "0")));
    return in_array($valor, ["1", "true", "sim", "yes", "on"], true);
}


function slugify(string $texto): string
{
    $texto = trim(mb_strtolower($texto));
    $trans = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($trans !== false) $texto = $trans;
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?: '';
    return trim($texto, '-');
}

function attachClientToOrder(PDO $pdo, string $tipo, int $id, int $clienteId): void
{
    if ($tipo === 'leitura') {
        $stmt = $pdo->prepare("UPDATE leituras SET cliente_id=? WHERE id=?");
    } else {
        $stmt = $pdo->prepare("UPDATE pedidos SET cliente_id=? WHERE id=?");
    }
    $stmt->execute([$clienteId, $id]);
}

function safeReturnUrl(?string $url, string $fallback): string
{
    $url = trim((string)$url);
    if ($url === '') return $fallback;
    if (str_starts_with($url, BASE_URL . '/')) return $url;
    if (BASE_URL === '' && str_starts_with($url, '/')) return $url;
    return $fallback;
}

function setFlash(string $mensagem, string $tipo = 'success'): void
{
    startClientSession();
    $_SESSION['flash'][] = ['mensagem'=>$mensagem,'tipo'=>$tipo];
}

function pullFlashes(): array
{
    startClientSession();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($flashes) ? $flashes : [];
}

function clientInitials(string $nome): string
{
    $partes = array_values(array_filter(preg_split('/\s+/', trim($nome)) ?: []));
    if (!$partes) return 'EL';
    $iniciais = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) $iniciais .= mb_substr($partes[count($partes)-1], 0, 1);
    return mb_strtoupper($iniciais);
}

function clientUnreadCount(PDO $pdo, int $clienteId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mensagens_chat m JOIN conversas c ON c.id=m.conversa_id WHERE c.cliente_id=? AND m.remetente_tipo='Admin' AND m.lida_em IS NULL");
    $stmt->execute([$clienteId]);
    return (int)$stmt->fetchColumn();
}

function adminUnreadCount(PDO $pdo): int
{
    return (int)$pdo->query("SELECT COUNT(*) FROM mensagens_chat WHERE remetente_tipo='Cliente' AND lida_em IS NULL")->fetchColumn();
}

function whatsappUrl(PDO $pdo, string $mensagem = ''): ?string
{
    $numero = preg_replace('/\D+/', '', siteContent($pdo, 'whatsapp_numero', '')) ?: '';
    if ($numero === '') return null;
    $texto = trim($mensagem) !== '' ? $mensagem : siteContent($pdo, 'whatsapp_mensagem', 'Olá! Vim pelo site da Estrada da Lua.');
    return 'https://wa.me/' . $numero . '?text=' . rawurlencode($texto);
}

function conversationStatusClass(string $status): string
{
    return match ($status) {
        'Em atendimento' => 'status status-production',
        'Resolvida' => 'status status-done',
        default => 'status status-new',
    };
}

function cartCount(): int
{
    startClientSession();
    $cart = $_SESSION['carrinho'] ?? [];
    if (!is_array($cart)) return 0;
    return array_sum(array_map('intval', $cart));
}

function cartRows(PDO $pdo): array
{
    startClientSession();
    $cart = $_SESSION['carrinho'] ?? [];
    if (!is_array($cart) || !$cart) return [];
    $ids = array_values(array_filter(array_map('intval', array_keys($cart))));
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = [];
    foreach ($stmt->fetchAll() as $p) {
        if (empty($p['pronta_entrega']) || (int)$p['estoque'] < 1) continue;
        $q = max(1, min((int)$p['estoque'], (int)($cart[(string)$p['id']] ?? $cart[(int)$p['id']] ?? 1)));
        $p['_quantidade'] = $q;
        $p['_subtotal'] = (float)$p['preco'] * $q;
        $rows[] = $p;
    }
    return $rows;
}

function parseMoneyInput(mixed $valor): float
{
    $s = trim((string)$valor);
    if ($s === '') return 0.0;
    $s = preg_replace('/[^0-9,.-]/', '', $s) ?? '0';
    if (str_contains($s, ',') && str_contains($s, '.')) {
        // Padrão pt-BR: 1.234,56
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } elseif (str_contains($s, ',')) {
        $s = str_replace(',', '.', $s);
    }
    return (float)$s;
}
