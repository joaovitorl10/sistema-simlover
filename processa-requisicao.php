<?php
// Processa o envio do formulário
$conn = new mysqli("localhost", "root", "", "simpress_requisicoes");
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // Campos do formulário
    $nome = $conn->real_escape_string($_POST['nome'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $telefone = $conn->real_escape_string($_POST['telefone'] ?? '');
    $data = $conn->real_escape_string($_POST['data'] ?? date('Y-m-d'));
    $localizacao = $conn->real_escape_string($_POST['localizacao'] ?? '');
    $outro_local = $conn->real_escape_string($_POST['outro-local'] ?? '');
    $empresa = $conn->real_escape_string($_POST['empresa'] ?? '');
    $endereco = $conn->real_escape_string($_POST['endereco'] ?? '');
    $contato = $conn->real_escape_string($_POST['contato'] ?? '');
    $departamento = $conn->real_escape_string($_POST['departamento'] ?? '');
    $servico = $conn->real_escape_string($_POST['servico'] ?? '');
    $codigo_peca = $conn->real_escape_string($_POST['codigo_peca'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 0);
    $pod = $conn->real_escape_string($_POST['pod'] ?? '');
    $justificativa = $conn->real_escape_string($_POST['justificativa'] ?? '');

    $sql = "INSERT INTO requisicoes (
        nome_solicitante,
        email,
        telefone,
        data_solicitacao,
        localizacao,
        outro_local,
        empresa,
        endereco,
        contato,
        departamento,
        servico,
        codigo_peca,
        quantidade,
        pod,
        justificativa,
        status
    ) VALUES (
        '$nome',
        '$email',
        '$telefone',
        '$data',
        '$localizacao',
        '$outro_local',
        '$empresa',
        '$endereco',
        '$contato',
        '$departamento',
        '$servico',
        '$codigo_peca',
        $quantidade,
        '$pod',
        '$justificativa',
        'Pendente'
    )";

    if ($conn->query($sql) === TRUE) {
        $id = $conn->insert_id;
        // Registrar notificação simples para admins (arquivo de log)
        $message = sprintf("[%s] Nova requisição ID=%d; solicitante=%s; email=%s; local=%s; servico=%s\n", date('Y-m-d H:i:s'), $id, $nome, $email, $localizacao, $servico);
        file_put_contents(__DIR__ . '/admin_notifications.log', $message, FILE_APPEND | LOCK_EX);

        // Redirecionar para página de confirmação
        header('Location: simlover-confirmacao.html');
        exit();
    } else {
        echo "Erro ao salvar requisição: " . $conn->error;
    }
}

$conn->close();
?>