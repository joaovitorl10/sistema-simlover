<?php
// auth.php - incluir no topo de páginas administrativas
session_start();

// Verifica se admin está logado
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    // redireciona para o login
    header('Location: admin-login.php');
    exit();
}
?>