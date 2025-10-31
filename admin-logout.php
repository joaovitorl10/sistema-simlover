<?php
// admin-logout.php - encerra sessão do admin
session_start();
$_SESSION = [];
session_destroy();
// redireciona para a página de login
header('Location: admin-login.php');
exit();
?>