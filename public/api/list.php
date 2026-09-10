<?php
require __DIR__ . '/bootstrap.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');
echo @file_get_contents(DATA_FILE) ?: '[]';
