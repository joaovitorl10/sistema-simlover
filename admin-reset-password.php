<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Redefinir senha • SimLover</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
        .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
        .topbar .container{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:700;font-size:1.15rem}
        .login-box{max-width:420px;margin:3rem auto;background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08)}
        .login-box h2{color:var(--primary-color);margin-bottom:1rem}
        .form-group{margin-bottom:0.9rem}
        input[type="password"]{width:100%;padding:0.6rem;border:1px solid #dfe6e9;border-radius:6px}
        .error{color:#c0392b;margin-bottom:0.75rem}
        .success{color:#27ae60;margin-bottom:0.75rem}
        .btn-submit{background:var(--primary-color);color:#fff;border:none;padding:0.6rem 1rem;border-radius:6px;cursor:pointer}
        a.back{color:var(--primary-color)}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Redefinir senha</div>
        </div>
    </div>
    <div class="container">
        <div class="login-box">
            <?php
            session_start();
            $error = '';
            $success = '';
            $token = $_GET['token'] ?? '';
            $validToken = false;
            $userEmail = '';
            
            if (empty($token)) {
                $error = 'Link inválido ou expirado.';
            } else {
                $conn = new mysqli('localhost', 'root', '', 'simpress_requisicoes');
                if ($conn->connect_error) {
                    $error = 'Falha ao conectar ao banco de dados.';
                } else {
                    // Verificar token
                    $stmt = $conn->prepare('SELECT id, username, reset_expires FROM admins WHERE reset_token=? LIMIT 1');
                    $stmt->bind_param('s', $token);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        $expires = strtotime($row['reset_expires']);
                        if ($expires < time()) {
                            $error = 'Este link expirou. Solicite um novo link de recuperação.';
                        } else {
                            $validToken = true;
                            $userEmail = $row['username'];
                            $userId = $row['id'];
                        }
                    } else {
                        $error = 'Link inválido ou já utilizado.';
                    }
                    $stmt->close();
                    
                    // Processar nova senha
                    if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $newPass = $_POST['new_pass'] ?? '';
                        $confirmPass = $_POST['confirm_pass'] ?? '';
                        
                        if (strlen($newPass) < 8) {
                            $error = 'A senha deve ter pelo menos 8 caracteres.';
                        } elseif ($newPass !== $confirmPass) {
                            $error = 'As senhas não conferem.';
                        } else {
                            $hash = password_hash($newPass, PASSWORD_DEFAULT);
                            $upd = $conn->prepare('UPDATE admins SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?');
                            $upd->bind_param('si', $hash, $userId);
                            if ($upd->execute()) {
                                $success = 'Senha redefinida com sucesso! Você já pode fazer login.';
                                $validToken = false; // Esconder o formulário
                            } else {
                                $error = 'Não foi possível atualizar a senha. Tente novamente.';
                            }
                            $upd->close();
                        }
                    }
                    
                    $conn->close();
                }
            }
            ?>
            
            <h2>Redefinir senha</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <p><a href="admin-forgot-password.php" class="back">← Solicitar novo link</a> | <a href="admin-login.php" class="back">Login</a></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <p><a href="admin-login.php" class="btn-submit" style="display:inline-block;text-decoration:none">Fazer login</a></p>
            <?php elseif ($validToken): ?>
                <p style="color:#666;font-size:.9rem">Defina uma nova senha para: <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
                <form method="POST">
                    <div class="form-group">
                        <label for="new_pass">Nova senha</label>
                        <input type="password" id="new_pass" name="new_pass" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_pass">Confirmar nova senha</label>
                        <input type="password" id="confirm_pass" name="confirm_pass" required minlength="8">
                    </div>
                    <button type="submit" class="btn-submit">Redefinir senha</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
