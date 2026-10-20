<?php
// Log errors without exposing paths, credentials, or stack traces to visitors.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Setting up the time zone
date_default_timezone_set('Asia/Jakarta');

$security_config_file = __DIR__ . '/security-config.local.php';
$security_config = file_exists($security_config_file)
	? require $security_config_file
	: array();

define('RECAPTCHA_SITE_KEY', isset($security_config['recaptcha_site_key']) ? $security_config['recaptcha_site_key'] : '');
define('RECAPTCHA_SECRET_KEY', isset($security_config['recaptcha_secret_key']) ? $security_config['recaptcha_secret_key'] : '');
define('TRUST_CLOUDFLARE_PROXY', !empty($security_config['trust_cloudflare_proxy']));

// Database connection. Production values belong in security-config.local.php.
$dbhost = isset($security_config['db_host']) ? $security_config['db_host'] : 'localhost';
$dbport = isset($security_config['db_port']) ? (int) $security_config['db_port'] : 3306;
$dbname = isset($security_config['db_name']) ? $security_config['db_name'] : 'web-stbaprayoga';
$dbuser = isset($security_config['db_user']) ? $security_config['db_user'] : 'root';
$dbpass = isset($security_config['db_password']) ? $security_config['db_password'] : '';

// Defining base url
define("BASE_URL", "http://localhost/web-stbaprayoga/");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "panel-5tb4" . "/");

try {
	$pdo = new PDO(
		"mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4",
		$dbuser,
		$dbpass,
		array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_TIMEOUT => 5
		)
	);
}
catch( PDOException $ex ) {
	error_log('Database connection failed: ' . $ex->getMessage());
	if (!headers_sent()) {
		http_response_code(503);
		header('Content-Type: text/html; charset=UTF-8');
		header('Retry-After: 60');
	}
	echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Layanan sementara tidak tersedia</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f6f5ed;color:#173a2d;font-family:Arial,sans-serif}.box{width:min(90%,560px);padding:40px;border-radius:24px;background:#fff;box-shadow:0 20px 60px rgba(11,59,41,.12);text-align:center}.mark{display:grid;width:64px;height:64px;margin:0 auto 24px;place-items:center;border-radius:50%;background:#e9f6ee;color:#187449;font-size:30px}h1{margin:0;font-size:28px}p{margin:16px 0 0;color:#64748b;line-height:1.7}.hint{font-size:13px}</style></head><body><main class="box"><div class="mark">!</div><h1>Layanan sementara tidak tersedia</h1><p>Website sedang mengalami gangguan koneksi. Silakan coba kembali beberapa saat lagi.</p><p class="hint">Jika masalah berlanjut, hubungi pengelola website.</p></main></body></html>';
	exit;
}
