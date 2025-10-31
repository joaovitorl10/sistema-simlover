<?php
// admin-login.php - login com senha hasheada em tabela admins
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    // Conecta ao banco
    $conn = new mysqli('localhost', 'root', '', 'simpress_requisicoes');
    if ($conn->connect_error) {
        $error = 'Falha ao conectar ao banco de dados.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, nome, password_hash FROM admins WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($pass, $row['password_hash'])) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_user'] = $row['username'];
                $_SESSION['admin_name'] = $row['nome'] ?: $row['username'];
                $_SESSION['must_change'] = (int)($row['must_change'] ?? 0) === 1;
                if (!empty($_SESSION['must_change'])) {
                    header('Location: admin-change-password.php');
                } else {
                    header('Location: admin.php');
                }
                exit();
            }
        }
        $error = 'Usuário ou senha incorretos.';
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Admin</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
        .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
        .topbar .container{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:700;font-size:1.15rem}
        .login-box{max-width:420px;margin:3rem auto;background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08)}
        .login-box h2{color:var(--primary-color);margin-bottom:1rem}
        .form-group{margin-bottom:0.9rem}
        input[type="text"],input[type="password"]{width:100%;padding:0.6rem;border:1px solid #dfe6e9;border-radius:6px}
        .error{color:#c0392b;margin-bottom:0.75rem}
        .btn-submit{background:var(--primary-color);color:#fff;border:none;padding:0.6rem 1rem;border-radius:6px;cursor:pointer}
        .helper{font-size:.85rem;color:#666;margin-top:.75rem}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Admin</div>
        </div>
    </div>
    <div class="container">
        <div class="login-box">
            <h2>Painel administrativo</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="user">Usuário</label>
                    <input type="text" id="user" name="user" required>
                </div>
                <div class="form-group">
                    <label for="pass">Senha</label>
                    <input type="password" id="pass" name="pass" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Entrar</button>
                </div>
            </form>
            <p class="helper">Usuário padrão: <strong>admin</strong> • Senha padrão: <strong>Senha123!</strong><br>
            Dica: você pode criar novos administradores na tabela <code>admins</code> (colunas: username, nome, password_hash).</p>
        </div>
    </div>
</body>
</html>