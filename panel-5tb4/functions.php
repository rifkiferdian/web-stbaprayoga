<?php

/**
 * Return the CSRF token for the current session, creating it when necessary.
 */
function csrf_token()
{
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	return $_SESSION['csrf_token'];
}

/**
 * Compare a submitted CSRF token without leaking timing information.
 */
function csrf_token_is_valid($token)
{
	return is_string($token)
		&& isset($_SESSION['csrf_token'])
		&& hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Stop a state-changing request when its CSRF token is missing or invalid.
 */
function require_valid_csrf_token($token)
{
	if (!csrf_token_is_valid($token)) {
		http_response_code(403);
		header('Content-Type: text/plain; charset=UTF-8');
		exit('Permintaan ditolak karena token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.');
	}
}

function reject_bad_request($message)
{
	http_response_code(400);
	header('Content-Type: text/plain; charset=UTF-8');
	exit($message);
}

/** Validate uploaded content from its actual MIME signature, not its filename. */
function validate_admin_uploads(array &$files)
{
	$mime_extensions = array(
		'image/jpeg' => array('jpg', 'jpeg'),
		'image/png' => array('png'),
		'image/gif' => array('gif'),
		'image/webp' => array('webp'),
		'application/pdf' => array('pdf'),
		'application/zip' => array('zip', 'docx', 'xlsx', 'pptx'),
		'application/x-rar-compressed' => array('rar'),
		'application/vnd.rar' => array('rar'),
		'application/msword' => array('doc'),
		'application/vnd.ms-excel' => array('xls'),
		'application/vnd.ms-powerpoint' => array('ppt'),
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('docx'),
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('xlsx'),
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('pptx')
	);

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	foreach ($files as &$file) {
		if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
			continue;
		}
		if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
			reject_bad_request('Upload file tidak valid.');
		}
		if ($file['size'] < 1 || $file['size'] > 10 * 1024 * 1024) {
			reject_bad_request('Ukuran file harus antara 1 byte dan 10 MB.');
		}

		$mime = $finfo->file($file['tmp_name']);
		$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!isset($mime_extensions[$mime]) || !in_array($extension, $mime_extensions[$mime], true)) {
			reject_bad_request('Jenis atau isi file tidak diizinkan.');
		}
		if (strpos($mime, 'image/') === 0 && @getimagesize($file['tmp_name']) === false) {
			reject_bad_request('Isi file gambar tidak valid.');
		}

		// Handlers use the extension from this value. Replace the client-supplied
		// basename so double extensions and path characters cannot propagate.
		$file['name'] = 'upload.' . $extension;
	}
}

/** Allow basic editorial markup while removing scripts and active attributes. */
function sanitize_rich_html($html)
{
	if (!is_string($html) || $html === '') {
		return '';
	}

	$allowed_tags = array('p','div','span','br','hr','strong','b','em','i','u','s','strike','sub','sup','ul','ol','li','blockquote','h1','h2','h3','h4','h5','h6','a','img','figure','figcaption','table','thead','tbody','tr','th','td','iframe');
	$allowed_attributes = array(
		'a' => array('href','title','target'),
		'img' => array('src','alt','title','width','height','style','align'),
		'ol' => array('start','type','style'), 'ul' => array('style'), 'li' => array('value','style'),
		'table' => array('border','cellpadding','cellspacing','width','style','align'),
		'th' => array('colspan','rowspan','style','align'), 'td' => array('colspan','rowspan','style','align'),
		'iframe' => array('src','width','height','frameborder','allowfullscreen')
	);
	foreach (array('p','div','span','blockquote','h1','h2','h3','h4','h5','h6','figure','figcaption','strong','b','em','i','u','s','strike') as $styled_tag) {
		$allowed_attributes[$styled_tag] = array('style','align');
	}

	$sanitize_inline_style = function($style) {
		$safe = array();
		foreach (explode(';', (string) $style) as $declaration) {
			if (strpos($declaration, ':') === false) continue;
			list($property, $value) = array_map('trim', explode(':', $declaration, 2));
			$property = strtolower($property);
			$value = trim($value);
			$is_safe = false;
			switch ($property) {
				case 'text-align': $is_safe = preg_match('/^(left|center|right|justify)$/i', $value); break;
				case 'font-weight': $is_safe = preg_match('/^(normal|bold|bolder|lighter|[1-9]00)$/i', $value); break;
				case 'font-style': $is_safe = preg_match('/^(normal|italic|oblique)$/i', $value); break;
				case 'text-decoration': $is_safe = preg_match('/^(none|underline|line-through|overline)(\s+(underline|line-through|overline))*$/i', $value); break;
				case 'color':
				case 'background-color': $is_safe = preg_match('/^(#[0-9a-f]{3,8}|rgba?\([0-9.,%\s]+\)|hsla?\([0-9.,%\s]+\)|[a-z]+)$/i', $value); break;
				case 'font-size': $is_safe = preg_match('/^(?:[0-9]|[1-9][0-9]|1[0-9]{2}|200)(px|pt|%|em|rem)$/i', $value); break;
				case 'font-family': $is_safe = preg_match('/^[a-z0-9 ,\-"\']+$/i', $value); break;
				case 'line-height': $is_safe = preg_match('/^([0-9]+(?:\.[0-9]+)?)(px|pt|%|em|rem)?$/i', $value); break;
				case 'margin-left':
				case 'text-indent': $is_safe = preg_match('/^-?(?:[0-9]|[1-9][0-9]{0,2})(px|pt|%|em|rem)$/i', $value); break;
				case 'width':
				case 'height': $is_safe = preg_match('/^(auto|[0-9]+(?:\.[0-9]+)?(px|%|em|rem))$/i', $value); break;
				case 'float': $is_safe = preg_match('/^(left|right|none)$/i', $value); break;
			}
			if ($is_safe) $safe[] = $property . ':' . $value;
		}
		return $safe ? implode(';', $safe) . ';' : '';
	};

	$document = new DOMDocument();
	$previous = libxml_use_internal_errors(true);
	$document->loadHTML('<?xml encoding="utf-8" ?><div id="safe-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	libxml_clear_errors();
	libxml_use_internal_errors($previous);
	$root = $document->getElementById('safe-root');
	if (!$root) {
		return '';
	}

	$nodes = array();
	foreach ($root->getElementsByTagName('*') as $node) { $nodes[] = $node; }
	foreach ($nodes as $node) {
		if ($node === $root || !$node->parentNode) {
			continue;
		}
		$tag = strtolower($node->nodeName);
		if (!in_array($tag, $allowed_tags, true)) {
			// Remove active/embedded elements with their contents. For harmless
			// formatting wrappers (notably Word's span/font tags), unwrap the
			// element so the editor text itself is never discarded.
			if (in_array($tag, array('script','style','object','embed','applet','link','meta'), true)) {
				$node->parentNode->removeChild($node);
			} else {
				$parent = $node->parentNode;
				while ($node->firstChild) {
					$parent->insertBefore($node->firstChild, $node);
				}
				$parent->removeChild($node);
			}
			continue;
		}
		$permitted = isset($allowed_attributes[$tag]) ? $allowed_attributes[$tag] : array();
		foreach (iterator_to_array($node->attributes) as $attribute) {
			$name = strtolower($attribute->name);
			$value = trim($attribute->value);
			if (!in_array($name, $permitted, true)) {
				$node->removeAttribute($attribute->name);
				continue;
			}
			if ($name === 'style') {
				$safe_style = $sanitize_inline_style($value);
				if ($safe_style === '') {
					$node->removeAttribute($attribute->name);
				} else {
					$node->setAttribute('style', $safe_style);
				}
				continue;
			}
			if ($name === 'align') {
				$alignment = strtolower($value);
				if (!in_array($alignment, array('left','center','right','justify'), true)) {
					$node->removeAttribute($attribute->name);
				} else {
					$node->setAttribute('align', $alignment);
				}
				continue;
			}
			if (in_array($name, array('href','src'), true) && preg_match('/^(?:javascript|data|vbscript):/i', preg_replace('/\s+/', '', $value))) {
				$node->removeAttribute($attribute->name);
			}
		}
		if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
			$node->setAttribute('rel', 'noopener noreferrer');
		}
	}

	$output = '';
	foreach ($root->childNodes as $child) { $output .= $document->saveHTML($child); }
	return $output;
}

function sanitize_admin_post(array &$input)
{
	$rich_fields = array('page_content','news_content','description','detail','faq_content','fq_content','footer_about','message','contact_map_iframe','video_iframe','code_body');
	foreach ($input as $key => &$value) {
		if (is_array($value)) {
			sanitize_admin_post($value);
		} elseif ($key !== 'password' && $key !== 're_password' && $key !== 'csrf_token') {
			$value = in_array($key, $rich_fields, true)
				? sanitize_rich_html($value)
				: htmlspecialchars(trim(strip_tags($value)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		}
	}
}

function login_client_ip()
{
	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	if (defined('TRUST_CLOUDFLARE_PROXY') && TRUST_CLOUDFLARE_PROXY && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function login_identifier($email)
{
	return hash('sha256', strtolower(trim((string) $email)));
}

function login_rate_limit_status(PDO $pdo, $email, $ip)
{
	$pdo->exec("DELETE FROM tbl_login_attempt WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
	$statement = $pdo->prepare(
		"SELECT
		 SUM(identifier = ?) AS email_failures,
		 SUM(ip_address = ?) AS ip_failures,
		 MIN(attempted_at) AS first_attempt
		 FROM tbl_login_attempt
		 WHERE attempted_at >= (NOW() - INTERVAL 15 MINUTE)"
	);
	$statement->execute(array(login_identifier($email), $ip));
	$row = $statement->fetch(PDO::FETCH_ASSOC);
	$email_failures = (int) $row['email_failures'];
	$ip_failures = (int) $row['ip_failures'];

	return array(
		'limited' => ($email_failures >= 5 || $ip_failures >= 20),
		'email_failures' => $email_failures,
		'ip_failures' => $ip_failures
	);
}

function record_login_failure(PDO $pdo, $email, $ip)
{
	$statement = $pdo->prepare("INSERT INTO tbl_login_attempt (identifier, ip_address, attempted_at) VALUES (?, ?, NOW())");
	$statement->execute(array(login_identifier($email), $ip));
}

function clear_login_failures(PDO $pdo, $email)
{
	$statement = $pdo->prepare("DELETE FROM tbl_login_attempt WHERE identifier = ?");
	$statement->execute(array(login_identifier($email)));
}

function is_local_request()
{
	$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']) : '';
	return in_array(strtolower($host), array('localhost', '127.0.0.1', '::1'), true);
}

function recaptcha_is_configured()
{
	return defined('RECAPTCHA_SITE_KEY') && defined('RECAPTCHA_SECRET_KEY')
		&& RECAPTCHA_SITE_KEY !== '' && RECAPTCHA_SECRET_KEY !== '';
}

function verify_recaptcha_response($response, $remote_ip)
{
	if (!recaptcha_is_configured()) {
		return is_local_request();
	}
	if (!is_string($response) || $response === '') {
		return false;
	}

	$curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
	curl_setopt_array($curl, array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query(array(
			'secret' => RECAPTCHA_SECRET_KEY,
			'response' => $response,
			'remoteip' => $remote_ip
		)),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_PROTOCOLS => CURLPROTO_HTTPS
	));
	$body = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);
	if ($body === false || $status !== 200) {
		return false;
	}
	$result = json_decode($body, true);
	return is_array($result) && !empty($result['success']);
}
