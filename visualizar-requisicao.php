<?php
require_once __DIR__ . '/auth.php';
$forceChange = !empty($_SESSION['must_change']);
if ($forceChange) {
    header('Location: admin-change-password.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "simpress_requisicoes");
if ($conn->connect_error) die('Conexão falhou: ' . $conn->connect_error);

if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM requisicoes WHERE id = $id";
$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) {
    echo "Requisição não encontrada.";
    exit();
}
$r = $res->fetch_assoc();

// Processar ações de aprovação/rejeição/conclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    $aprovador = $conn->real_escape_string($_POST['aprovador'] ?? '');
    $observacoes = $conn->real_escape_string($_POST['observacoes'] ?? '');
    if ($acao === 'aprovar') {
        $upd = "UPDATE requisicoes SET status='Aprovado', data_aprovacao=CURRENT_DATE, aprovado_por='$aprovador', observacoes='$observacoes' WHERE id=$id";
    } elseif ($acao === 'rejeitar') {
        $upd = "UPDATE requisicoes SET status='Rejeitado', data_aprovacao=CURRENT_DATE, aprovado_por='$aprovador', observacoes='$observacoes' WHERE id=$id";
    } elseif ($acao === 'concluir') {
        $upd = "UPDATE requisicoes SET status='Concluído', data_conclusao=CURRENT_DATE, observacoes=CONCAT(IFNULL(observacoes,''),'\n', '$observacoes') WHERE id=$id";
    }
    if (isset($upd) && $conn->query($upd) === TRUE) {
        header("Location: visualizar-requisicao.php?id=$id&updated=1");
        exit();
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Visualizar Requisição</title>
    <link rel="stylesheet" href="simlover-style.css">
    <style>
    .topbar{background:linear-gradient(90deg,var(--primary-color),#138f75);color:#fff;padding:.75rem 0;margin-bottom:1.25rem}
    .topbar .container{display:flex;justify-content:space-between;align-items:center}
    .brand{font-weight:700}
    .detail {background:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;box-shadow:0 8px 22px rgba(0,0,0,0.08)}
    dt{font-weight:600;color:#333}
    .btn-submit{background:var(--primary-color);color:#fff;border:none;padding:.55rem .9rem;border-radius:6px;cursor:pointer}
    .btn-clean{background:#c0392b;color:#fff;border:none;padding:.55rem .9rem;border-radius:6px;cursor:pointer}
    a.back{color:var(--primary-color)}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="brand">SimLover • Requisição</div>
            <div><a style="color:#fff;text-decoration:underline" href="admin.php">Voltar ao painel</a></div>
        </div>
    </div>
    <div class="container">
        <h1>Requisição #<?php echo $r['id']; ?></h1>
        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green">Requisição atualizada com sucesso.</p>
        <?php endif; ?>

        <div class="detail">
            <dl>
                <dt>Solicitante</dt>
                <dd><?php echo htmlspecialchars($r['nome_solicitante']); ?></dd>

                <dt>E-mail</dt>
                <dd><?php echo htmlspecialchars($r['email']); ?></dd>

                <dt>Telefone</dt>
                <dd><?php echo htmlspecialchars($r['telefone']); ?></dd>

                <dt>Data</dt>
                <dd><?php echo htmlspecialchars($r['data_solicitacao']); ?></dd>

                <dt>Localização</dt>
                <dd><?php echo htmlspecialchars($r['localizacao']); if ($r['outro_local']) echo ' — ' . htmlspecialchars($r['outro_local']); ?></dd>

                <dt>Empresa / Responsável</dt>
                <dd><?php echo htmlspecialchars($r['empresa']); ?></dd>

                <dt>Endereço</dt>
                <dd><?php echo htmlspecialchars($r['endereco']); ?></dd>

                <dt>Departamento</dt>
                <dd><?php echo htmlspecialchars($r['departamento']); ?></dd>

                <dt>Serviço</dt>
                <dd><?php echo htmlspecialchars($r['servico']); ?></dd>

                <dt>Código (SKU)</dt>
                <dd><?php echo htmlspecialchars($r['codigo_peca']); ?></dd>

                <dt>Quantidade</dt>
                <dd><?php echo htmlspecialchars($r['quantidade']); ?></dd>

                <dt>POD</dt>
                <dd><?php echo htmlspecialchars($r['pod']); ?></dd>

                <dt>Informações adicionais</dt>
                <dd><?php echo nl2br(htmlspecialchars($r['justificativa'])); ?></dd>

                <dt>Status</dt>
                <dd><?php echo htmlspecialchars($r['status']); ?></dd>

                <?php if ($r['aprovado_por']): ?>
                    <dt>Aprovado por</dt>
                    <dd><?php echo htmlspecialchars($r['aprovado_por']); ?></dd>
                    <dt>Data aprovação</dt>
                    <dd><?php echo htmlspecialchars($r['data_aprovacao']); ?></dd>
                <?php endif; ?>

            </dl>
        </div>

        <?php if ($r['status'] === 'Pendente'): ?>
        <div class="detail">
            <h3>Ações</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="aprovador">Seu nome (aprovador):</label>
                    <input type="text" id="aprovador" name="aprovador" required>
                </div>
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="acao" value="aprovar" class="btn-submit">Aprovar</button>
                    <button type="submit" name="acao" value="rejeitar" class="btn-clean">Rejeitar</button>
                </div>
            </form>
        </div>
        <?php elseif ($r['status'] === 'Aprovado'): ?>
        <div class="detail">
            <h3>Concluir requisição</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="observacoes">Observações da conclusão:</label>
                    <textarea id="observacoes" name="observacoes" rows="3" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="acao" value="concluir" class="btn-submit">Marcar como Concluído</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <p><a class="back" href="admin.php">← Voltar para painel</a></p>
    </div>
</body>
</html>
<?php $conn->close(); ?>