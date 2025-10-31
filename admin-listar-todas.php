<?php
require_once __DIR__ . '/auth.php';
$forceChange = !empty($_SESSION['must_change']);
if ($forceChange) {
    header('Location: admin-change-password.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "simpress_requisicoes");
if ($conn->connect_error) die('Conexão falhou: ' . $conn->connect_error);

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_data_inicio = $_GET['data_inicio'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';
$filtro_busca = $_GET['busca'] ?? '';

// Montar query
$where = [];
$params = [];
$types = '';

if (!empty($filtro_status)) {
    $where[] = "status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

if (!empty($filtro_data_inicio)) {
    $where[] = "data_solicitacao >= ?";
    $params[] = $filtro_data_inicio;
    $types .= 's';
}

if (!empty($filtro_data_fim)) {
    $where[] = "data_solicitacao <= ?";
    $params[] = $filtro_data_fim;
    $types .= 's';
}

if (!empty($filtro_busca)) {
    $where[] = "(nome_solicitante LIKE ? OR email LIKE ? OR codigo_peca LIKE ?)";
    $busca = '%' . $filtro_busca . '%';
    $params[] = $busca;
    $params[] = $busca;
    $params[] = $busca;
    $types .= 'sss';
}

$sql = "SELECT id, nome_solicitante, email, data_solicitacao, servico, codigo_peca, status, created_at FROM requisicoes";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Todas as requisições • Admin</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
    .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
    .topbar .container{display:flex;justify-content:space-between;align-items:center}
    .brand{font-weight:700}
    .user{opacity:.9}
    .card{background:#fff;border-radius:8px;box-shadow:0 8px 22px rgba(0,0,0,0.08);padding:1rem;margin-bottom:1rem}
    table{width:100%;border-collapse:collapse}
    th,td{padding:0.65rem;border-bottom:1px solid #eef2f3;text-align:left;font-size:.9rem}
    th{background:#f9fbfb;color:#34495e}
    tr:hover{background:#fafafa}
    .actions a{color:#fff;background:var(--primary-color);padding:.35rem .55rem;border-radius:6px;text-decoration:none;font-size:.85rem}
    .toolbar{display:flex;gap:.6rem;margin-bottom:.8rem;flex-wrap:wrap}
    .toolbar a{font-size:.9rem}
    .status{padding:.25rem .5rem;border-radius:4px;font-size:.85rem;font-weight:600}
    .status-pendente{background:#f39c12;color:#fff}
    .status-aprovado{background:#27ae60;color:#fff}
    .status-rejeitado{background:#c0392b;color:#fff}
    .status-concluido{background:#34495e;color:#fff}
    .filtros{background:#f9fbfb;padding:1rem;border-radius:6px;margin-bottom:1rem}
    .filtros form{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;align-items:end}
    .filtros input,.filtros select{padding:.5rem;border:1px solid #dfe6e9;border-radius:6px}
    .filtros button{background:var(--primary-color);color:#fff;border:none;padding:.5rem 1rem;border-radius:6px;cursor:pointer}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Todas as requisições</div>
            <div class="user">Olá, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'admin'); ?> • <a style="color:#fff;text-decoration:underline;margin-right:.6rem" href="admin-change-password.php">Alterar senha</a> <a style="color:#fff;text-decoration:underline" href="admin-logout.php">Sair</a></div>
        </div>
    </div>
    <div class="container">
        <div class="toolbar">
            <a class="actions" href="admin.php">← Voltar ao painel</a>
            <a class="actions" href="simlover-requisicao.html" style="background:#ff6b00">Novo formulário</a>
        </div>

        <div class="card filtros">
            <h3 style="margin-top:0;margin-bottom:.75rem">Filtros</h3>
            <form method="GET">
                <div>
                    <label style="display:block;font-size:.85rem;margin-bottom:.25rem">Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="Pendente" <?php echo $filtro_status === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="Aprovado" <?php echo $filtro_status === 'Aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                        <option value="Rejeitado" <?php echo $filtro_status === 'Rejeitado' ? 'selected' : ''; ?>>Rejeitado</option>
                        <option value="Concluído" <?php echo $filtro_status === 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;margin-bottom:.25rem">Data início</label>
                    <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;margin-bottom:.25rem">Data fim</label>
                    <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;margin-bottom:.25rem">Buscar</label>
                    <input type="text" name="busca" placeholder="Nome, email ou SKU" value="<?php echo htmlspecialchars($filtro_busca); ?>">
                </div>
                <div>
                    <button type="submit">Filtrar</button>
                    <a href="admin-listar-todas.php" style="display:inline-block;margin-left:.5rem;padding:.5rem 1rem;background:#7f8c8d;color:#fff;border-radius:6px;text-decoration:none;font-size:.9rem">Limpar</a>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h1 style="margin-top:0">Requisições (<?php echo $res->num_rows; ?>)</h1>
            <?php if ($res && $res->num_rows > 0): ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Data</th><th>Solicitante</th><th>E-mail</th><th>Serviço / SKU</th><th>Status</th><th>Ações</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): 
                        $statusClass = 'status-' . strtolower(preg_replace('/[^a-z]/i', '', $row['status']));
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['data_solicitacao'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nome_solicitante']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['servico']) . ' / ' . htmlspecialchars($row['codigo_peca']); ?></td>
                        <td><span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td class="actions"><a href="visualizar-requisicao.php?id=<?php echo $row['id']; ?>">Abrir</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Nenhuma requisição encontrada com os filtros aplicados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>
