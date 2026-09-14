<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../includes/functions.php';
ensurePedidoSchema($pdo);
$pageTitle = 'Pedidos';
$statusPermitidos = ['Novo', 'Em Produção', 'Pronto', 'Entregue', 'Cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if ($id && in_array($status, $statusPermitidos, true)) {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        if ($status === 'Entregue') {
            $q = $pdo->prepare("SELECT * FROM pedidos WHERE id=?");
            $q->execute([$id]);
            if ($pedidoSync = $q->fetch()) syncVendaPedido($pdo, $pedidoSync);
        }
    }
    header('Location: ' . BASE_URL . '/admin/pedidos.php'); exit;
}

$filtro = $_GET['status'] ?? '';
$sql = "SELECT * FROM pedidos";
$params = [];
if (in_array($filtro, $statusPermitidos, true)) { $sql .= " WHERE status = ?"; $params[] = $filtro; }
$sql .= " ORDER BY data_pedido DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $pedidos = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="admin-top"><div><span class="section-kicker">SOLICITAÇÕES</span><h1>Pedidos</h1><p>Pedidos da loja e requisições de guias personalizadas em um só lugar.</p></div></div>
<div class="filter-row admin-filters"><a class="filter-pill <?= !$filtro ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/pedidos.php">Todos</a><?php foreach ($statusPermitidos as $status): ?><a class="filter-pill <?= $filtro === $status ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/pedidos.php?status=<?= urlencode($status) ?>"><?= e($status) ?></a><?php endforeach; ?></div>
<div class="table-wrap modern-table"><table><thead><tr><th>Cliente</th><th>Solicitação</th><th>Preferências</th><th>Recebido</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($pedidos as $pedido): ?><tr>
<td><strong><?= e($pedido['nome_cliente']) ?></strong><small><?= e($pedido['telefone']) ?></small></td>
<td><strong><?= e($pedido['tipo_pedido'] ?: 'Pedido') ?></strong><small><?= e(excerpt($pedido['observacoes'], 55)) ?></small></td>
<td><?= e($pedido['cores'] ?: '—') ?><small><?= e($pedido['tamanho'] ?: '') ?></small></td>
<td><?= date('d/m/Y', strtotime($pedido['data_pedido'])) ?><small><?= date('H:i', strtotime($pedido['data_pedido'])) ?></small></td>
<td><form method="post"><input type="hidden" name="id" value="<?= (int)$pedido['id'] ?>"><select class="status-select" name="status" onchange="this.form.submit()"><?php foreach ($statusPermitidos as $status): ?><option value="<?= e($status) ?>" <?= $pedido['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></form></td>
<td><a class="table-link" href="<?= BASE_URL ?>/admin/pedido.php?id=<?= (int)$pedido['id'] ?>">Detalhes →</a></td>
</tr><?php endforeach; ?>
<?php if (!$pedidos): ?><tr><td colspan="6" class="empty-cell">Nenhum pedido encontrado.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
