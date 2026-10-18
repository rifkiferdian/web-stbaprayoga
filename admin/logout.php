<?php 
ob_start();
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params(array(
	'lifetime' => 0,
	'path' => '/',
	'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
	'httponly' => true,
	'samesite' => 'Lax'
));
session_start();
include 'config.php'; 
include_once 'functions.php';
require_valid_csrf_token(isset($_GET['csrf_token']) ? $_GET['csrf_token'] : null);
unset($_SESSION['user']);
unset($_SESSION['csrf_token']);
session_destroy();
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
header("location: login.php"); 
?>
