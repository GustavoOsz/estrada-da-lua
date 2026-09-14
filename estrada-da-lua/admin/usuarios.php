<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
    exit('Acesso restrito ao administrador.');
}

$pageTitle = 'Usuários';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $nivel = in_array($_POST['nivel'] ?? '', ['admin', 'funcionario'], true)
            ? $_POST['nivel']
            : 'funcionario';

        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            $erro = 'Informe nome, e-mail válido e senha com pelo menos 6 caracteres.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ((int)$stmt->fetchColumn() > 0) {
                $erro = 'Já existe um usuário com esse e-mail.';
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nome, email, senha, nivel)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$nome, $email, $hash, $nivel]);
                $mensagem = 'Usuário criado.';
            }
        }
    }

    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id && $id !== (int)$_SESSION['usuario_id']) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $mensagem = 'Usuário excluído.';
        } else {
            $erro = 'Você não pode excluir o usuário que está conectado.';
        }
    }

    if ($acao === 'senha') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $senha = $_POST['nova_senha'] ?? '';

        if ($id && strlen($senha) >= 6) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->execute([$hash, $id]);
            $mensagem = 'Senha alterada.';
        } else {
            $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
        }
    }
}

$usuarios = $pdo->query("
    SELECT id, nome, email, nivel, criado_em
    FROM usuarios
    ORDER BY nome
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="admin-top">
    <h1>Usuários</h1>
</div>

<?php if ($mensagem): ?><div class="alert"><?= e($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alert"><?= e($erro) ?></div><?php endif; ?>

<div class="form-card" style="margin-bottom: 24px;">
    <h2>Novo usuário</h2>
    <form method="post">
        <input type="hidden" name="acao" value="criar">

        <div class="form-group">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha" minlength="6" required>
        </div>

        <div class="form-group">
            <label for="nivel">Nível</label>
            <select id="nivel" name="nivel">
                <option value="funcionario">Funcionário</option>
                <option value="admin">Administrador</option>
            </select>
        </div>

        <button class="btn" type="submit">Criar usuário</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Nível</th>
                <th>Senha</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= e($usuario['nome']) ?></td>
                    <td><?= e($usuario['email']) ?></td>
                    <td><?= e($usuario['nivel']) ?></td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="acao" value="senha">
                            <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                            <input type="password" name="nova_senha" minlength="6" placeholder="Nova senha" required>
                            <button class="btn btn-secondary" type="submit">Alterar</button>
                        </form>
                    </td>
                    <td>
                        <?php if ((int)$usuario['id'] !== (int)$_SESSION['usuario_id']): ?>
                            <form method="post" data-confirm="Excluir este usuário?">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                                <button class="btn btn-danger" type="submit">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
