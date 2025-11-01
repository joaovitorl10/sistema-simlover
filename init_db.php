<?php
$servername = "localhost";
$username = "root";
$password = "";

// Criar conexão
$conn = new mysqli($servername, $username, $password);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Criar banco de dados
$sql = "CREATE DATABASE IF NOT EXISTS simpress_requisicoes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Banco de dados criado com sucesso\n";
} else {
    echo "Erro ao criar banco de dados: " . $conn->error . "\n";
}

// Selecionar o banco de dados
$conn->select_db("simpress_requisicoes");

// Criar tabela de requisições (com todas as colunas necessárias)
$sql = "CREATE TABLE IF NOT EXISTS requisicoes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome_solicitante VARCHAR(200) NOT NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(50) NULL,
    data_solicitacao DATE NOT NULL,
    localizacao VARCHAR(100) NOT NULL,
    outro_local VARCHAR(255) NULL,
    empresa VARCHAR(200) NULL,
    endereco VARCHAR(255) NULL,
    contato VARCHAR(50) NULL,
    departamento VARCHAR(100) NULL,
    servico VARCHAR(100) NULL,
    codigo_peca VARCHAR(100) NULL,
    quantidade INT NULL,
    pod VARCHAR(100) NULL,
    justificativa TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pendente',
    data_aprovacao DATE NULL,
    aprovado_por VARCHAR(100) NULL,
    data_conclusao DATE NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Tabela requisicoes criada com sucesso\n";
} else {
    echo "Erro ao criar tabela: " . $conn->error . "\n";
}

$conn->close();
// Criar tabela de administradores (com senha hasheada)
$conn = new mysqli($servername, $username, $password, "simpress_requisicoes");
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


// Garante coluna must_change (para instalações antigas)
$conn->query("ALTER TABLE admins ADD COLUMN must_change TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash");

// Garante colunas de recuperação de senha (se tabela já existe)
$conn->query("ALTER TABLE admins ADD COLUMN reset_token VARCHAR(64) NULL AFTER must_change");
$conn->query("ALTER TABLE admins ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");

// Recria a definição garantindo a coluna quando tabela não existia
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(150) NULL,
    password_hash VARCHAR(255) NOT NULL,
    must_change TINYINT(1) NOT NULL DEFAULT 0,
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($sql) === TRUE) {
    // ok
}

// Garante um usuário padrão 'admin' se não existir
$defaultUser = 'admin';
$defaultPass = 'Senha123!';

$stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $defaultUser);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO admins (username, nome, password_hash, must_change) VALUES (?,?,?,1)");
    $nome = 'Administrador';
    $ins->bind_param('sss', $defaultUser, $nome, $hash);
    $ins->execute();
}
$stmt->close();
$conn->close();

echo "Configuração concluída!";
?>