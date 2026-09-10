<?php
// Bootstrap: localiza la configuración con la PASS_HASH.
// Orden: 1) fuera de public_html (producción ideal), 2) api/config.php local (dev).
// Copia api/config.example.php a api/config.php en local. NUNCA subas tu config.php con la pass real al repo.

$candidates = [
  dirname(__DIR__, 3) . '/config-fer.php',   // domains/tudominio/config-fer.php (Hostinger)
  dirname(__DIR__, 2) . '/config-fer.php',   // variante según estructura
  __DIR__ . '/config.php',                    // local dev
];

foreach ($candidates as $f) {
  if (is_file($f)) { require_once $f; break; }
}

if (!defined('ADMIN_PASS_HASH')) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'Falta configuración (config.php) en el servidor.']);
  exit;
}

if (!defined('DATA_DIR')) define('DATA_DIR', dirname(__DIR__) . '/proyectos-data');
if (!defined('DATA_FILE')) define('DATA_FILE', DATA_DIR . '/proyectos.json');

if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0755, true);
if (!is_file(DATA_FILE)) @file_put_contents(DATA_FILE, '[]');

function json_out($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function require_admin(): void {
  session_start();
  if (empty($_SESSION['admin'])) json_out(['ok' => false, 'error' => 'No autorizado.'], 401);
}

function csrf_check(): void {
  $tok = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
  if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$tok)) {
    json_out(['ok' => false, 'error' => 'CSRF inválido. Recarga el admin.'], 403);
  }
}
