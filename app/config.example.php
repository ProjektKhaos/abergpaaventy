<?php
/*
  Site : Åberg På Äventyr | abergpaaventyr.se
  Kode and design Hasse & Klasse 2026
  config.example.php : exempelkonfiguration för Åberg På Äventyr
  Aktiv fil : /app/config.example.php
  Uppdaterad : 2026-07-04 10:56

  Kopiera denna fil till app/config.php på servern och fyll i riktiga värden.
  app/config.php ska inte commitas till GitHub.
*/

// --- Databas ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'database_name');
define('DB_USER', 'database_user');
define('DB_PASS', 'database_password');
define('DB_CHARSET', 'utf8mb4');

// --- Bas-URL ---
define('BASE_URL', 'https://abergpaaventyr.se');

// --- Uppladdningar ---
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// --- Tillåtna bildtyper ---
define('ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXT',  ['jpg', 'jpeg', 'png', 'webp']);

// --- Tidzon ---
date_default_timezone_set('Asia/Bangkok');
