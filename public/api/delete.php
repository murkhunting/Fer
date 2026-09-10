<?php
require __DIR__ . '/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_check();

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_POST['slug'] ?? ''));
if ($slug === '') json_out(['ok' => false, 'error' => 'Slug inválido.'], 400);

$all = json_decode(@file_get_contents(DATA_FILE) ?: '[]', true) ?: [];
$all = array_values(array_filter($all, fn($p) => ($p['slug'] ?? '') !== $slug));
@copy(DATA_FILE, DATA_DIR . '/proyectos-' . date('Y-m-d') . '.json.bak');
file_put_contents(DATA_FILE, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Borra carpeta de imágenes del proyecto
$dir = DATA_DIR . '/' . $slug;
if (is_dir($dir) && str_contains(realpath($dir) ?: '', realpath(DATA_DIR) ?: 'x')) {
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
  );
  foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
  @rmdir($dir);
}
json_out(['ok' => true]);
