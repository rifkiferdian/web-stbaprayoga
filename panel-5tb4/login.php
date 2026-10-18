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
include("config.php");
include_once("functions.php");
$error_message='';

if(isset($_POST['form1'])) {
	require_valid_csrf_token(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null);
	$email = isset($_POST['email']) ? strip_tags($_POST['email']) : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	$client_ip = login_client_ip();
	$rate_limit = login_rate_limit_status($pdo, $email, $client_ip);

	if ($rate_limit['limited']) {
		http_response_code(429);
		header('Retry-After: 900');
		$error_message = 'Terlalu banyak percobaan login. Silakan coba lagi setelah 15 menit.<br>';
	} elseif (!verify_recaptcha_response(isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '', $client_ip)) {
		record_login_failure($pdo, $email, $client_ip);
		$error_message = 'Verifikasi CAPTCHA gagal. Silakan coba lagi.<br>';
	}
        
	if ($error_message !== '') {
		// Do not continue to password verification.
    } elseif(empty($email) || empty($password)) {
		record_login_failure($pdo, $email, $client_ip);
        $error_message = 'Email and/or Password can not be empty<br>';
    } else {

    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=?");
    	$statement->execute(array($email,'Active'));
		$row = $statement->fetch(PDO::FETCH_ASSOC);

		$authenticated = false;
		$legacy_md5 = false;

		if ($row) {
			$stored_password = $row['password'];
			$authenticated = password_verify($password, $stored_password);

			// Transitional support for existing MD5 records. After the first
			// successful login the database is upgraded immediately.
			if (!$authenticated && preg_match('/^[a-f0-9]{32}$/i', $stored_password)) {
				$authenticated = hash_equals(strtolower($stored_password), md5($password));
				$legacy_md5 = $authenticated;
			}
		}

		if (!$authenticated) {
			record_login_failure($pdo, $email, $client_ip);
			// Use one generic response so the login page does not reveal whether
			// an email address exists.
			$error_message .= 'Email atau password tidak sesuai<br>';
		} else {
			clear_login_failures($pdo, $email);
			if ($legacy_md5 || password_needs_rehash($stored_password, PASSWORD_DEFAULT)) {
				$new_password_hash = password_hash($password, PASSWORD_DEFAULT);
				$update = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
				$update->execute(array($new_password_hash, $row['id']));
			}

			// Never keep a password hash in session data.
			unset($row['password']);
			session_regenerate_id(true);
			$_SESSION['user'] = $row;
			$_SESSION['last_activity'] = time();
			$_SESSION['last_regeneration'] = time();
			header("location: index.php");
			exit;
		}
    }

    
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<title>Masuk Panel — STBA Prayoga</title>
	<link rel="icon" href="../assets/uploads/favicon.png">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<?php if (recaptcha_is_configured()): ?>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<?php endif; ?>
	<style>
		:root { --navy:#0c2744; --blue:#145da0; --cyan:#39a9db; --paper:#fff; --muted:#66788a; --line:#dce5ed; --danger:#b42318; }
		* { box-sizing:border-box; }
		html, body { min-height:100%; }
		body { margin:0; color:#172b3a; font-family:"Segoe UI",Arial,sans-serif; background:#edf3f7; }
		.login-shell { min-height:100vh; display:grid; grid-template-columns:minmax(320px,1.05fr) minmax(420px,.95fr); }
		.brand-panel { position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; padding:56px clamp(38px,6vw,92px); color:#fff; background:linear-gradient(145deg,var(--navy),#104b78 62%,#1580ab); }
		.brand-panel:before,.brand-panel:after { content:""; position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.16); }
		.brand-panel:before { width:460px; height:460px; right:-220px; top:-150px; box-shadow:0 0 0 70px rgba(255,255,255,.035),0 0 0 140px rgba(255,255,255,.025); }
		.brand-panel:after { width:260px; height:260px; left:-160px; bottom:-100px; }
		.brand, .welcome, .security-note { position:relative; z-index:1; }
		.brand { display:flex; align-items:center; gap:15px; }
		.brand img { width:68px; height:68px; object-fit:contain; padding:6px; border-radius:16px; background:#fff; box-shadow:0 12px 32px rgba(0,0,0,.18); }
		.brand-name { font-size:14px; font-weight:700; letter-spacing:.13em; text-transform:uppercase; }
		.brand-subtitle { margin-top:4px; color:#bfe3f3; font-size:13px; }
		.welcome { max-width:620px; margin:70px 0; }
		.welcome .eyebrow { display:inline-block; margin-bottom:18px; color:#9edcf3; font-size:12px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; }
		.welcome h1 { max-width:560px; margin:0 0 20px; font-size:clamp(38px,5vw,66px); line-height:1.04; letter-spacing:-.04em; }
		.welcome p { max-width:510px; margin:0; color:#cfe7f1; font-size:17px; line-height:1.7; }
		.security-note { display:flex; align-items:center; gap:10px; color:#b9d9e7; font-size:13px; }
		.security-note i { color:#67d0ee; }
		.form-panel { display:flex; align-items:center; justify-content:center; padding:44px clamp(24px,6vw,86px); background:var(--paper); }
		.login-card { width:100%; max-width:440px; }
		.mobile-brand { display:none; }
		.login-card h2 { margin:0 0 10px; color:var(--navy); font-size:32px; letter-spacing:-.025em; }
		.login-intro { margin:0 0 34px; color:var(--muted); line-height:1.6; }
		.alert-error { margin-bottom:22px; padding:13px 15px; border:1px solid #f2c9c5; border-radius:10px; color:var(--danger); background:#fff4f2; font-size:14px; line-height:1.5; }
		.form-group { margin-bottom:20px; }
		.form-group label { display:block; margin-bottom:8px; color:#253d4e; font-size:13px; font-weight:700; }
		.input-wrap { position:relative; }
		.input-wrap i { position:absolute; left:15px; top:50%; color:#7890a2; transform:translateY(-50%); }
		.input-wrap input { width:100%; height:50px; padding:0 15px 0 44px; border:1px solid var(--line); border-radius:10px; color:#172b3a; background:#f9fbfc; font-size:15px; outline:none; transition:.2s ease; }
		.input-wrap input:focus { border-color:var(--cyan); background:#fff; box-shadow:0 0 0 4px rgba(57,169,219,.13); }
		.captcha-wrap { margin:4px 0 20px; overflow:hidden; }
		.config-warning { padding:12px 14px; border-radius:9px; color:#7a4b00; background:#fff5da; font-size:13px; }
		.login-button { width:100%; height:52px; border:0; border-radius:10px; color:#fff; background:linear-gradient(120deg,var(--blue),#167bb0); font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 12px 24px rgba(20,93,160,.23); transition:transform .2s ease,box-shadow .2s ease; }
		.login-button:hover { transform:translateY(-1px); box-shadow:0 15px 28px rgba(20,93,160,.3); }
		.login-button:focus { outline:3px solid rgba(57,169,219,.3); outline-offset:2px; }
		.login-footer { margin-top:26px; color:#8797a5; font-size:12px; text-align:center; }
		@media (max-width:820px) { .login-shell{display:block;} .brand-panel{display:none;} .form-panel{min-height:100vh;padding:34px 22px;} .mobile-brand{display:flex;align-items:center;gap:12px;margin-bottom:42px;color:var(--navy);font-weight:700;} .mobile-brand img{width:52px;height:52px;object-fit:contain;} }
	</style>
</head>
<body>
	<main class="login-shell">
		<section class="brand-panel" aria-label="STBA Prayoga">
			<div class="brand">
				<img src="../assets/uploads/logo.png" alt="Logo STBA Prayoga">
				<div><div class="brand-name">STBA Prayoga</div><div class="brand-subtitle">Administration Portal</div></div>
			</div>
			<div class="welcome">
				<span class="eyebrow">Ruang kerja internal</span>
				<h1>Kelola informasi kampus dengan lebih mudah.</h1>
				<p>Masuk ke panel untuk memperbarui berita, halaman, media, dan informasi akademik STBA Prayoga.</p>
			</div>
			<div class="security-note"><i class="fa fa-shield"></i><span>Akses dilindungi dengan rate limiting, CAPTCHA, dan sesi aman.</span></div>
		</section>
		<section class="form-panel">
			<div class="login-card">
				<div class="mobile-brand"><img src="../assets/uploads/logo.png" alt="Logo STBA Prayoga"><span>STBA Prayoga</span></div>
				<h2>Selamat datang</h2>
				<p class="login-intro">Gunakan akun administrator Anda untuk melanjutkan.</p>
				<?php if ($error_message !== ''): ?><div class="alert-error" role="alert"><i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?></div><?php endif; ?>
				<form action="" method="post">
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
					<div class="form-group">
						<label for="email">Alamat email</label>
						<div class="input-wrap"><i class="fa fa-envelope-o"></i><input id="email" name="email" type="email" autocomplete="username" placeholder="nama@stbaprayoga.ac.id" required autofocus></div>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<div class="input-wrap"><i class="fa fa-lock"></i><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required></div>
					</div>
					<?php if (recaptcha_is_configured()): ?><div class="captcha-wrap"><div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div></div>
					<?php elseif (!is_local_request()): ?><div class="config-warning">Google reCAPTCHA belum dikonfigurasi. Login dinonaktifkan.</div><?php endif; ?>
					<button type="submit" class="login-button" name="form1" value="1">Masuk ke panel <i class="fa fa-arrow-right"></i></button>
				</form>
				<div class="login-footer">Panel internal STBA Prayoga &middot; Akses terbatas</div>
			</div>
		</section>
	</main>
</body>
</html>
