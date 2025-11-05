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
    .topbar{background:linear-gradient(135deg,var(--primary-color),#138f75,#0f7b6c);color:#fff;padding:1rem 0;margin-bottom:2rem;box-shadow:0 4px 15px rgba(0,0,0,0.1)}
    .topbar .container{display:flex;justify-content:space-between;align-items:center}
    .brand{font-weight:700;font-size:1.3rem;text-shadow:0 1px 3px rgba(0,0,0,0.2)}
    .brand::before{content:'📊 ';margin-right:0.5rem}
    .user{opacity:.95;display:flex;gap:1rem}
    .user a{color:#fff;text-decoration:none;padding:0.5rem 1rem;border-radius:6px;background:rgba(255,255,255,0.15);transition:all 0.3s ease;backdrop-filter:blur(10px)}
    .user a:hover{background:rgba(255,255,255,0.25);transform:translateY(-1px)}
    .card{background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);padding:1.5rem;border-top:4px solid var(--primary-color)}
    .card h2{color:var(--primary-color);margin-bottom:1.5rem;font-size:1.6rem;display:flex;align-items:center;gap:0.5rem}
    .card h2::before{content:'⏳';font-size:1.2rem}
    table{width:100%;border-collapse:collapse;margin-top:1rem}
    th,td{padding:1rem 0.8rem;border-bottom:2px solid #f1f3f4;text-align:left}
    th{background:linear-gradient(135deg,#f8f9fa,#e9ecef);color:#2c3e50;font-weight:600;text-transform:uppercase;font-size:0.85rem;letter-spacing:0.5px}
    tr:hover{background:#f8fffe;transform:scale(1.01);transition:all 0.2s ease}
    .actions a{color:#fff;background:linear-gradient(135deg,var(--primary-color),#138f75);padding:.6rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;transition:all 0.3s ease;display:inline-block}
    .actions a:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(22,160,133,0.3)}
    .toolbar{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
    .toolbar a{font-size:.95rem;padding:0.7rem 1.2rem;background:linear-gradient(135deg,#6c757d,#5a6268);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;transition:all 0.3s ease}
    .toolbar a:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(108,117,125,0.3)}
    .empty-state{text-align:center;padding:3rem;color:#6c757d}
    .empty-state::before{content:'📋';font-size:3rem;display:block;margin-bottom:1rem}
    </style>
    </head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">🚀 SimLover • Dashboard</div>
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