<?php

// Copy this file to security-config.local.php and fill in values for the
// current environment. The .local.php file is ignored by Git.
return array(
	'base_url' => 'https://www.example.com/',
	'db_host' => 'localhost',
	'db_port' => 3306,
	'db_name' => 'database_name',
	'db_user' => 'database_user',
	'db_password' => 'database_password',
	'recaptcha_site_key' => '',
	'recaptcha_secret_key' => '',
	'trust_cloudflare_proxy' => false,
);
