<?php
// inc/auth.php - Sistema de autenticação

session_start();

/**
 * Verifica se o usuário está autenticado
 */
function is_logged_in() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email']);
}

/**
 * Redireciona para login se não autenticado
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /public/login.php');
        exit;
    }
}

/**
 * Realiza login do usuário
 * @return bool|string true se sucesso, string com mensagem de erro
 */
function fazer_login($email, $senha) {
    $db = db_connect();
    if (!$db) {
        return 'Erro ao conectar ao banco de dados.';
    }

    $query = "SELECT id_usuario, nome, email, senha FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        return 'Erro ao preparar consulta.';
    }

    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
        return 'Erro ao executar consulta.';
    }

    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        return 'Email ou senha incorretos.';
    }

    // Verifica a senha
    if (!password_verify($senha, $usuario['senha'])) {
        return 'Email ou senha incorretos.';
    }

    // Define a sessão
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_nome'] = $usuario['nome'];

    return true;
}

/**
 * Realiza cadastro de novo usuário
 * @return bool|string true se sucesso, string com mensagem de erro
 */
function fazer_registro($email, $senha, $nome) {
    $db = db_connect();
    if (!$db) {
        return 'Erro ao conectar ao banco de dados.';
    }

    // Valida entrada
    if (empty($email) || empty($senha) || empty($nome)) {
        return 'Todos os campos são obrigatórios.';
    }

    if (strlen($senha) < 6) {
        return 'A senha deve ter no mínimo 6 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Email inválido.';
    }

    // Verifica se email já existe
    $check_query = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    if (!$check_stmt) {
        return 'Erro ao preparar consulta.';
    }

    $check_stmt->bind_param('s', $email);
    if (!$check_stmt->execute()) {
        return 'Erro ao executar consulta.';
    }

    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $check_stmt->close();
        return 'Email já cadastrado.';
    }
    $check_stmt->close();

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere usuário
    $insert_query = "INSERT INTO usuarios (email, senha, nome, ativo) VALUES (?, ?, ?, 1)";
    $insert_stmt = $db->prepare($insert_query);
    if (!$insert_stmt) {
        return 'Erro ao preparar inserção.';
    }

    $insert_stmt->bind_param('sss', $email, $senha_hash, $nome);
    if (!$insert_stmt->execute()) {
        $insert_stmt->close();
        return 'Erro ao cadastrar usuário.';
    }
    $insert_stmt->close();

    return true;
}

/**
 * Faz logout do usuário
 */
function fazer_logout() {
    session_destroy();
    $_SESSION = [];
    header('Location: /public/login.php');
    exit;
}

/**
 * Retorna dados do usuário logado
 */
function get_usuario_logado() {
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'email' => $_SESSION['usuario_email'],
        'nome' => $_SESSION['usuario_nome'],
    ];
}
