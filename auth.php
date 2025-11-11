<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost'; 
$dbname = 'pixelteca_db';
$username_db = 'root'; 
$password_db = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Falha na ligação à base de dados.']);
    exit;
}

function send_response($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

if (!isset($_POST['action'])) {
    send_response(false, 'Ação não definida.');
}

$action = $_POST['action'];

if ($action === 'register') {
    if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        send_response(false, 'Campos em falta.');
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        send_response(false, 'Todos os campos são obrigatórios.');
    }
    if (strlen($password) < 6) {
        send_response(false, 'A senha deve ter pelo menos 6 caracteres.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_response(false, 'Formato de e-mail inválido.');
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        send_response(false, 'Utilizador ou e-mail já registado.');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);
        send_response(true, 'Registo efetuado com sucesso!');
    } catch (PDOException $e) {
        send_response(false, 'Erro ao registar: ' . $e->getMessage());
    }
}
elseif ($action === 'login') {
    if (!isset($_POST['emailOrUsername'], $_POST['password'])) {
        send_response(false, 'Campos em falta.');
    }

    $emailOrUsername = trim($_POST['emailOrUsername']);
    $password = $_POST['password'];

    if (empty($emailOrUsername) || empty($password)) {
        send_response(false, 'Todos os campos são obrigatórios.');
    }

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch();

    if (!$user) {
        send_response(false, 'Utilizador ou senha inválidos.');
    }

    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        send_response(true, 'Login efetuado com sucesso!');
    } else {
        send_response(false, 'Utilizador ou senha inválidos.');
    }
}
?>
