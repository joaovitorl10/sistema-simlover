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
        .topbar{background:linear-gradient(135deg,var(--primary-color),#138f75,#0f7b6c);color:#fff;padding:1rem 0;margin-bottom:2rem;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
        .topbar .container{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:700;font-size:1.4rem;text-shadow:0 1px 3px rgba(0,0,0,0.2)}
        .brand::before{content:'🔐 ';margin-right:0.5rem}
        .login-box{max-width:450px;margin:3rem auto;background:#fff;padding:2rem;border-radius:12px;box-shadow:0 15px 35px rgba(0,0,0,0.1);border-top:4px solid var(--primary-color)}
        .login-box h2{color:var(--primary-color);margin-bottom:1.5rem;text-align:center;font-size:1.8rem;font-weight:600}
        .login-box h2::after{content:'';display:block;width:60px;height:3px;background:linear-gradient(90deg,var(--primary-color),#ff6b00);margin:0.5rem auto}
        .form-group{margin-bottom:1.2rem}
        .form-group label{font-weight:600;color:#2c3e50;margin-bottom:0.5rem;display:block}
        input[type="text"],input[type="password"]{width:100%;padding:0.8rem;border:2px solid #e0e6ed;border-radius:8px;font-size:1rem;transition:all 0.3s ease}
        input[type="text"]:focus,input[type="password"]:focus{border-color:var(--primary-color);outline:none;box-shadow:0 0 0 3px rgba(22,160,133,0.1)}
        .error{color:#e74c3c;margin-bottom:1rem;padding:0.75rem;background:#fdf2f2;border:1px solid #fecaca;border-radius:6px;text-align:center}
        .btn-submit{background:linear-gradient(135deg,var(--primary-color),#138f75);color:#fff;border:none;padding:0.8rem 2rem;border-radius:8px;cursor:pointer;font-size:1rem;font-weight:600;width:100%;transition:all 0.3s ease;text-transform:uppercase;letter-spacing:0.5px}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(22,160,133,0.3)}
        .helper{font-size:.9rem;color:#666;margin-top:1rem;text-align:center;line-height:1.5}
        .helper a{color:var(--primary-color);text-decoration:none;font-weight:600}
        .helper a:hover{text-decoration:underline}
        .form-actions{margin-top:1.5rem}
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
            <h2>🚀 Painel Administrativo</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="user">E-mail ou usuário</label>
                    <input type="text" id="user" name="user" required placeholder="seuemail@exemplo.com">
                </div>
                <div class="form-group">
                    <label for="pass">Senha</label>
                    <input type="password" id="pass" name="pass" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Entrar</button>
                </div>
            </form>
            <p class="helper">Primeiro acesso: <strong>admin</strong> / <strong>Senha123!</strong><br>
            No primeiro login, você será solicitado a trocar para seu e-mail e senha pessoal.</p>
            <p class="helper"><a href="admin-forgot-password.php">Esqueci minha senha</a></p>
        </div>
    </div>
</body>
</html>