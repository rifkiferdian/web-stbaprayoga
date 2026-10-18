<?php
// Error Reporting Turn On
// ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
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

// Host Name
$dbhost = 'localhost';

// Database Name
$dbname = 'web-stbaprayoga';

// Database Username
$dbuser = 'root';

// Database Password
$dbpass = '';

// Defining base url
define("BASE_URL", "http://localhost/web-stbaprayoga/");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "panel-5tb4" . "/");

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $ex ) {
    echo "Connection error :" . $ex->getMessage();
}
