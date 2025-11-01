<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Esqueci minha senha • SimLover</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
        .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
        .topbar .container{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:700;font-size:1.15rem}
        .login-box{max-width:420px;margin:3rem auto;background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08)}
        .login-box h2{color:var(--primary-color);margin-bottom:1rem}
        .form-group{margin-bottom:0.9rem}
        input[type="email"]{width:100%;padding:0.6rem;border:1px solid #dfe6e9;border-radius:6px}
        .error{color:#c0392b;margin-bottom:0.75rem}
        .success{color:#27ae60;margin-bottom:0.75rem}
        .btn-submit{background:var(--primary-color);color:#fff;border:none;padding:0.6rem 1rem;border-radius:6px;cursor:pointer}
        .helper{font-size:.85rem;color:#666;margin-top:.75rem}
        a.back{color:var(--primary-color)}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Recuperar senha</div>
        </div>
    </div>
    <div class="container">
        <div class="login-box">
            <h2>Esqueci minha senha</h2>
            <p style="color:#666;font-size:.9rem">Informe o e-mail cadastrado. Você receberá um link para redefinir sua senha.</p>
            
            <?php
            session_start();
            $error = '';
            $success = '';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = trim($_POST['email'] ?? '');
                
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Por favor, informe um e-mail válido.';
                } else {
                    $conn = new mysqli('localhost', 'root', '', 'simpress_requisicoes');
                    if ($conn->connect_error) {
                        $error = 'Falha ao conectar ao banco de dados.';
                    } else {
                        // Verificar se email existe
                        $stmt = $conn->prepare('SELECT id, username FROM admins WHERE username = ? LIMIT 1');
                        $stmt->bind_param('s', $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($row = $result->fetch_assoc()) {
                            // Gerar token único
                            $token = bin2hex(random_bytes(32));
                            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                            
                            // Salvar token no banco
                            $upd = $conn->prepare('UPDATE admins SET reset_token=?, reset_expires=? WHERE id=?');
                            $upd->bind_param('ssi', $token, $expires, $row['id']);
                            $upd->execute();
                            $upd->close();
                            
                            // Enviar email com link
                            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/admin-reset-password.php?token=" . $token;
                            
                            require_once __DIR__ . '/notify.php';
                            $assunto = "Redefinição de senha - SimLover";
                            $mensagem = "Olá,\n\nVocê solicitou a redefinição de senha.\nClique no link abaixo (válido por 1 hora):\n\n$resetLink\n\nSe não foi você, ignore este e-mail.";
                            $html = "<h3>Redefinição de senha</h3>"
                                  . "<p>Olá,</p>"
                                  . "<p>Você solicitou a redefinição de senha no sistema SimLover.</p>"
                                  . "<p><a href='$resetLink' style='background:#16a085;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block'>Redefinir minha senha</a></p>"
                                  . "<p style='color:#666;font-size:0.9rem'>Ou copie e cole este link: <br>$resetLink</p>"
                                  . "<p style='color:#666;font-size:0.85rem'>Este link expira em 1 hora. Se não foi você, ignore este e-mail.</p>";
                            
                            // Tentar enviar email
                            send_password_reset_email($email, $assunto, $mensagem, $html);
                            
                            $success = "Se o e-mail existir em nossa base, você receberá um link para redefinir sua senha. Verifique sua caixa de entrada.";
                        } else {
                            // Por segurança, mostramos a mesma mensagem mesmo se email não existir
                            $success = "Se o e-mail existir em nossa base, você receberá um link para redefinir sua senha. Verifique sua caixa de entrada.";
                        }
                        
                        $stmt->close();
                        $conn->close();
                    }
                }
            }
            ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <p><a href="admin-login.php" class="back">← Voltar para o login</a></p>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">E-mail cadastrado</label>
                    <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
                </div>
                <button type="submit" class="btn-submit">Enviar link de recuperação</button>
            </form>
            <p class="helper"><a href="admin-login.php" class="back">← Voltar para o login</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
