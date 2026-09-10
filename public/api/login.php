<?php
require __DIR__ . '/bootstrap.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }

// Rate-limit: 5 intentos / 10 min por sesión+IP
$now = time();
$_SESSION['login_trials'] = array_filter($_SESSION['login_trials'] ?? [], fn($t) => $now - $t < 600);
if (count($_SESSION['login_trials']) >= 5) {
  http_response_code(429);
  echo json_encode(['ok' => false, 'error' => 'Demasiados intentos. Espera 10 minutos.']);
  exit;
}

$pass = $_POST['password'] ?? '';
if ($pass !== '' && password_verify($pass, ADMIN_PASS_HASH)) {
  session_regenerate_id(true);
  $_SESSION['admin'] = true;
  $_SESSION['login_trials'] = [];
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
  echo json_encode(['ok' => true, 'csrf' => $_SESSION['csrf']]);
} else {
  $_SESSION['login_trials'][] = $now;
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Contraseña incorrecta.']);
}
