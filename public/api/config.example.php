<?php
// PLANTILLA — cópialo a config.php (local) o a config-fer.php (servidor, fuera de public_html).
// Genera el hash con: php -r "echo password_hash('TU-CLAVE', PASSWORD_ARGON2ID), PHP_EOL;"
// La pass real del cliente SOLO vive en el servidor. Nunca en git.

define('ADMIN_PASS_HASH', '$argon2id$CAMBIAME$genera-tu-hash');

// Opcionales (por defecto apuntan a ../proyectos-data):
// define('DATA_DIR', __DIR__ . '/../proyectos-data');
// define('DATA_FILE', DATA_DIR . '/proyectos.json');
