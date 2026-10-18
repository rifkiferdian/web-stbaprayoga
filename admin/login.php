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
        
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Email and/or Password can not be empty<br>';
    } else {
		
		$email = strip_tags($_POST['email']);
		$password = $_POST['password'];

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
			// Use one generic response so the login page does not reveal whether
			// an email address exists.
			$error_message .= 'Email atau password tidak sesuai<br>';
		} else {
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
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Login</title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<link rel="stylesheet" href="css/bootstrap-3.4.1.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">

	<link rel="stylesheet" href="style.css">
</head>

<body class="hold-transition login-page sidebar-mini">

<div class="login-box">
	<div class="login-logo">
		<b>Admin Panel</b>
	</div>
  	<div class="login-box-body">
    	<p class="login-box-msg">Log in to start your session</p>
    
	    <?php 
	    if( (isset($error_message)) && ($error_message!='') ):
	        echo '<div class="error">'.$error_message.'</div>';
	    endif;
	    ?>

		<form action="" method="post">
			<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
			<div class="form-group has-feedback">
				<input class="form-control" placeholder="Email address" name="email" type="email" autocomplete="off" autofocus>
			</div>
			<div class="form-group has-feedback">
				<input class="form-control" placeholder="Password" name="password" type="password" autocomplete="off" value="">
			</div>
			<div class="row">
				<div class="col-xs-8"></div>
				<div class="col-xs-4">
					<input type="submit" class="btn btn-primary btn-block btn-flat login-button" name="form1" value="Log In">
				</div>
			</div>
		</form>
	</div>
</div>


<script src="js/jquery-3.7.1.min.js"></script>
<script src="js/jquery-migrate-3.6.0.min.js"></script>
<script src="js/bootstrap-3.4.1.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="js/jquery.inputmask.date.extensions.js"></script>
<script src="js/jquery.inputmask.extensions.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/icheck.min.js"></script>
<script src="js/fastclick.js"></script>
<script src="js/jquery.sparkline.min.js"></script>
<script src="js/jquery.slimscroll.min.js"></script>
<script src="js/app.min.js"></script>
<script src="js/demo.js"></script>

</body>
</html>
