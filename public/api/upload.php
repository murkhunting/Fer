<?php
require __DIR__ . '/bootstrap.php';
require_admin();

function slugify(string $s): string {
  $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
  $s = strtolower($s);
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  return trim($s ?? '', '-') ?: ('proyecto-' . date('Ymd-His'));
}

function save_image(array $file, string $dest, int $maxSide = 1920): bool {
  $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  if (!isset($allowed[$mime])) return false;
  if ($file['size'] > 8 * 1024 * 1024) return false;

  $src = match ($mime) {
    'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
    'image/png' => imagecreatefrompng($file['tmp_name']),
    'image/webp' => imagecreatefromwebp($file['tmp_name']),
    default => null,
  };
  if (!$src) return false;

  $w = imagesx($src); $h = imagesy($src);
  $k = min(1, $maxSide / max($w, $h));
  if ($k < 1) {
    $nw = (int)($w * $k); $nh = (int)($h * $k);
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime === 'image/png' || $mime === 'image/webp') {
      imagealphablending($dst, false); imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($src); $src = $dst;
  }
  $ok = match ($mime) {
    'image/jpeg' => imagejpeg($src, $dest, 85),
    'image/png' => imagepng($src, $dest, 6),
    'image/webp' => imagewebp($src, $dest, 85),
    default => false,
  };
  imagedestroy($src);
  return (bool)$ok;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'Método inválido.'], 405);
csrf_check();

$title = trim($_POST['title'] ?? '');
$location = trim($_POST['location'] ?? '');
$client = trim($_POST['client'] ?? '');
$year = (int)($_POST['year'] ?? date('Y'));
$description = trim($_POST['description'] ?? '');
$videoUrl = trim($_POST['videoUrl'] ?? '');
$tags = $_POST['tags'] ?? [];
if (is_string($tags)) $tags = explode(',', $tags);
$tags = array_values(array_intersect(array_map('trim', (array)$tags), ['fotografia', 'video', 'arquitectura']));
$slug = slugify($_POST['slug'] ?? $title);
$editSlug = slugify($_POST['editSlug'] ?? '');

$errs = [];
if ($title === '') $errs[] = 'Falta el título.';
if (!in_array('fotografia', $tags, true) && !in_array('video', $tags, true))
  $errs[] = 'La etiqueta "arquitectura" sola no vale: añade foto o video.';
if (in_array('video', $tags, true) && $videoUrl === '') $errs[] = 'Si es de video, la URL del video es obligatoria.';
if ($errs) json_out(['ok' => false, 'error' => implode(' ', $errs)], 400);

$all = json_decode(@file_get_contents(DATA_FILE) ?: '[]', true) ?: [];
$existing = null;
foreach ($all as $p) { if (($p['slug'] ?? '') === ($editSlug !== '' ? $editSlug : $slug)) { $existing = $p; break; } }

$dir = DATA_DIR . '/' . $slug;
if (!is_dir($dir)) @mkdir($dir, 0755, true);
if (!is_dir($dir . '/galeria')) @mkdir($dir . '/galeria', 0755, true);

// Portada
$coverRel = $existing['cover'] ?? '';
if (!empty($_FILES['cover']['tmp_name'])) {
  $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
  $dest = $dir . '/cover.' . ($ext !== '' ? strtolower($ext) : 'jpg');
  // normalizamos a la extensión real según mime dentro de save_image
  $tmp = $dir . '/cover.upload';
  if (!save_image($_FILES['cover'], $tmp)) json_out(['ok' => false, 'error' => 'Portada inválida (JPG/PNG/WebP, max 8MB).'], 400);
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
  $real = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
  $dest = $dir . '/cover.' . $real;
  @unlink($dest); rename($tmp, $dest);
  $coverRel = '/proyectos-data/' . $slug . '/cover.' . $real;
}
if ($coverRel === '') json_out(['ok' => false, 'error' => 'Falta la imagen principal.'], 400);

// Galería (se añade a la existente en edición; con ?replace=1 se sustituye)
$gallery = ($editSlug !== '' && empty($_POST['replaceGallery'])) ? ($existing['gallery'] ?? []) : [];
if (!empty($_FILES['gallery'])) {
  $files = [];
  if (is_array($_FILES['gallery']['tmp_name'])) {
    foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
      if ($tmp !== '' && is_uploaded_file($tmp)) {
        $files[] = ['tmp_name' => $tmp, 'name' => $_FILES['gallery']['name'][$i], 'size' => $_FILES['gallery']['size'][$i]];
      }
    }
  }
  if (count($gallery) + count($files) > 20) json_out(['ok' => false, 'error' => 'Máximo 20 fotos por proyecto.'], 400);
  foreach ($files as $f) {
    $n = count($gallery) + 1;
    $tmp = $dir . '/galeria/foto' . $n . '.upload';
    if (!save_image($f, $tmp)) continue;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $real = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $dest = $dir . '/galeria/foto' . $n . '.' . $real;
    @unlink($dest); rename($tmp, $dest);
    $gallery[] = '/proyectos-data/' . $slug . '/galeria/foto' . $n . '.' . $real;
  }
}
if (in_array('fotografia', $tags, true) && count($gallery) === 0)
  json_out(['ok' => false, 'error' => 'Si es de fotografía, la galería es obligatoria.'], 400);

$entry = [
  'slug' => $slug,
  'title' => $title,
  'location' => $location,
  'client' => $client,
  'year' => $year,
  'tags' => $tags,
  'cover' => $coverRel,
  'gallery' => array_values($gallery),
  'videoUrl' => $videoUrl,
  'description' => $description,
];

// backup diario
@copy(DATA_FILE, DATA_DIR . '/proyectos-' . date('Y-m-d') . '.json.bak');

$found = false;
foreach ($all as $i => $p) {
  if (($p['slug'] ?? '') === ($editSlug !== '' ? $editSlug : $slug)) { $all[$i] = $entry; $found = true; break; }
}
if (!$found) $all[] = $entry;

file_put_contents(DATA_FILE, json_encode(array_values($all), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
json_out(['ok' => true, 'slug' => $slug]);
