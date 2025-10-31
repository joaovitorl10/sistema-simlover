<?php
require_once __DIR__ . '/auth.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new_user = trim($_POST['new_user'] ?? '');
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($new_user)) {
        $error = 'O nome de usuário não pode estar vazio.';
    } elseif (strlen($new) < 8) {
        $error = 'A nova senha deve ter pelo menos 8 caracteres.';
    } elseif ($new !== $confirm) {
        $error = 'Confirmação de senha não confere.';
    } else {
        $conn = new mysqli('localhost', 'root', '', 'simpress_requisicoes');
        if ($conn->connect_error) {
            $error = 'Falha ao conectar ao banco.';
        } else {
            $user = $_SESSION['admin_user'];
            $stmt = $conn->prepare('SELECT id, username, password_hash FROM admins WHERE username=? LIMIT 1');
            $stmt->bind_param('s', $user);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (!password_verify($current, $row['password_hash'])) {
                    $error = 'Senha atual incorreta.';
                } else {
                    // Verificar se o novo usuário já existe (se diferente do atual)
                    if ($new_user !== $row['username']) {
                        $check = $conn->prepare('SELECT id FROM admins WHERE username=? LIMIT 1');
                        $check->bind_param('s', $new_user);
                        $check->execute();
                        $check->store_result();
                        if ($check->num_rows > 0) {
                            $error = 'Este nome de usuário já está em uso.';
                            $check->close();
                        } else {
                            $check->close();
                        }
                    }
                    
                    if (!$error) {
                        $hash = password_hash($new, PASSWORD_DEFAULT);
                        $upd = $conn->prepare('UPDATE admins SET username=?, password_hash=?, must_change=0 WHERE id=?');
                        $upd->bind_param('ssi', $new_user, $hash, $row['id']);
                        if ($upd->execute()) {
                            $_SESSION['must_change'] = false;
                            $_SESSION['admin_user'] = $new_user;
                            $success = 'Usuário e senha alterados com sucesso!';
                        } else {
                            $error = 'Não foi possível atualizar.';
                        }
                        $upd->close();
                    }
                }
            } else {
                $error = 'Usuário não encontrado.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Alterar senha • Admin</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
        .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
        .topbar .container{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:700}
        .card{background:#fff;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08);padding:1rem;max-width:520px;margin:0 auto}
        .form-group{margin-bottom:.9rem}
        input[type=password],input[type=text]{width:100%;padding:.6rem;border:1px solid #dfe6e9;border-radius:6px}
        .btn-submit{background:var(--primary-color);color:#fff;border:none;padding:.6rem 1rem;border-radius:6px;cursor:pointer}
        .error{color:#c0392b;margin:.5rem 0}
        .success{color:#138f75;margin:.5rem 0}
        a.back{color:#fff;text-decoration:underline}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Alterar senha</div>
            <div><a class="back" href="admin-logout.php">Sair</a></div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-top:0">Personalize seu acesso</h2>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <p><a href="admin.php" class="btn-submit" style="display:inline-block;text-decoration:none">Ir para o painel</a></p>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="new_user">Nome de usuário</label>
                    <input type="text" id="new_user" name="new_user" value="<?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'admin'); ?>" required>
                    <small style="color:#666;font-size:.85rem">Você está usando: <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></small>
                </div>
                <div class="form-group">
                    <label for="current">Senha atual</label>
                    <input type="password" id="current" name="current" required>
                </div>
                <div class="form-group">
                    <label for="new">Nova senha</label>
                    <input type="password" id="new" name="new" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirmar nova senha</label>
                    <input type="password" id="confirm" name="confirm" required minlength="8">
                </div>
                <button type="submit" class="btn-submit">Salvar alterações</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
