<?php
require_once __DIR__ . '/auth.php';
$forceChange = !empty($_SESSION['must_change']);
if ($forceChange) {
    header('Location: admin-change-password.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "simpress_requisicoes");
if ($conn->connect_error) die('Conexão falhou: ' . $conn->connect_error);

// Listar requisições pendentes
$sql = "SELECT id, nome_solicitante, email, data_solicitacao, servico, codigo_peca FROM requisicoes WHERE status='Pendente' ORDER BY created_at DESC";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Painel Admin - Requisições Pendentes</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
    .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
    .topbar .container{display:flex;justify-content:space-between;align-items:center}
    .brand{font-weight:700}
    .user{opacity:.9}
    .card{background:#fff;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08);padding:1rem}
    table{width:100%;border-collapse:collapse}
    th,td{padding:0.65rem;border-bottom:1px solid #eef2f3;text-align:left}
    th{background:#f9fbfb;color:#34495e}
    tr:hover{background:#fafafa}
    .actions a{color:#fff;background:var(--primary-color);padding:.4rem .6rem;border-radius:6px;text-decoration:none}
    .toolbar{display:flex;gap:.6rem;margin-bottom:.8rem}
    .toolbar a{font-size:.9rem}
    </style>
    </head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Painel</div>
            <div class="user">Olá, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'admin'); ?> • <a style="color:#fff;text-decoration:underline;margin-right:.6rem" href="admin-change-password.php">Alterar senha</a> <a style="color:#fff;text-decoration:underline" href="admin-logout.php">Sair</a></div>
        </div>
    </div>
    <div class="container">
        <div class="toolbar">
            <a class="actions" href="simlover-requisicao.html" style="background:#ff6b00">Novo formulário</a>
            <a class="actions" href="admin-listar-todas.php">Ver todas as requisições</a>
            <a class="actions" href="init_db.php" title="Recriar estruturas se necessário">Init DB</a>
        </div>
        <div class="card">
        <h1 style="margin-top:0">Requisições pendentes</h1>
        <?php if ($res && $res->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>ID</th><th>Data</th><th>Solicitante</th><th>E-mail</th><th>Serviço / SKU</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['data_solicitacao'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_solicitante']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['servico']) . ' / ' . htmlspecialchars($row['codigo_peca']); ?></td>
                    <td class="actions"><a href="visualizar-requisicao.php?id=<?php echo $row['id']; ?>">Abrir</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="card"><p>Nenhuma requisição pendente no momento.</p></div>
        <?php endif; ?>
        
    </div>
</body>
</html>
<?php $conn->close(); ?>