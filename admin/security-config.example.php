<?php

// Copy this file to security-config.local.php and fill in the values.
// security-config.local.php is excluded by .gitignore.
return array(
	'recaptcha_site_key' => 'YOUR_RECAPTCHA_V2_SITE_KEY',
	'recaptcha_secret_key' => 'YOUR_RECAPTCHA_V2_SECRET_KEY',

	// Enable only when the public origin accepts traffic exclusively through
	// Cloudflare; otherwise CF-Connecting-IP can be spoofed by direct clients.
	'trust_cloudflare_proxy' => false,
);
