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

	$allowed_tags = array('p','br','strong','b','em','i','u','ul','ol','li','blockquote','h1','h2','h3','h4','h5','h6','a','img','table','thead','tbody','tr','th','td','iframe');
	$allowed_attributes = array(
		'a' => array('href','title','target'),
		'img' => array('src','alt','title','width','height'),
		'table' => array('border','cellpadding','cellspacing'),
		'th' => array('colspan','rowspan'), 'td' => array('colspan','rowspan'),
		'iframe' => array('src','width','height','frameborder','allowfullscreen')
	);

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
		$tag = strtolower($node->nodeName);
		if (!in_array($tag, $allowed_tags, true)) {
			$node->parentNode->removeChild($node);
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
