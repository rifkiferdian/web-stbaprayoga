<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$sourceFile = __DIR__ . '/../../tmp/history-import/source.html';
if (!is_file($sourceFile)) {
    fwrite(STDERR, "Source HTML tidak ditemukan.\n");
    exit(1);
}

$source = file_get_contents($sourceFile);
$document = new DOMDocument();
$previous = libxml_use_internal_errors(true);
$document->loadHTML('<?xml encoding="utf-8" ?>' . $source, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($previous);

$xpath = new DOMXPath($document);
$container = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' gdl-page-content ')]")->item(0);
if (!$container) {
    fwrite(STDERR, "Konten utama WordPress tidak ditemukan.\n");
    exit(1);
}

$content = '<p><img src="' . BASE_URL . 'assets/uploads/stba-history-building.gif" alt="Gedung STBA Prayoga Padang"></p>';
foreach ($xpath->query('.//p', $container) as $paragraph) {
    $text = trim(preg_replace('/\s+/u', ' ', $paragraph->textContent));
    $text = str_replace(array('â€“', 'â€”', 'â€™', 'â€œ', 'â€', 'Â'), array('–', '—', '’', '“', '”', ''), $text);
    if ($text === '') continue;
    $content .= '<p>' . modern_import_escape($text) . '</p>';
}
$content .= '<p><img src="' . BASE_URL . 'assets/uploads/stba-history-yard.jpg" alt="Lingkungan kampus STBA Prayoga Padang"></p>';
$content = sanitize_rich_html($content);

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('sejarah-stba-prayoga'));
    $pageId = $find->fetchColumn();
    if ($pageId) {
        $statement = $pdo->prepare("UPDATE tbl_page SET page_name=?, page_content=?, page_layout=?, banner=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE page_id=?");
        $statement->execute(array('Sejarah STBA Prayoga', $content, 'Full Width Page Layout', 'stba-history-yard.jpg', 'Active', 'Sejarah STBA Prayoga Padang', 'sejarah STBA Prayoga, ABA Prayoga, kampus bahasa Padang', 'Sejarah perjalanan ABA Prayoga hingga menjadi STBA Prayoga Padang.', $pageId));
    } else {
        $statement = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?)");
        $statement->execute(array('Sejarah STBA Prayoga', 'sejarah-stba-prayoga', $content, 'Full Width Page Layout', 'stba-history-yard.jpg', 'Active', 'Sejarah STBA Prayoga Padang', 'sejarah STBA Prayoga, ABA Prayoga, kampus bahasa Padang', 'Sejarah perjalanan ABA Prayoga hingga menjadi STBA Prayoga Padang.'));
        $pageId = (int) $pdo->lastInsertId();
    }

    $menuFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=2 AND menu_name=? LIMIT 1");
    $menuFind->execute(array('Sejarah'));
    $menuId = $menuFind->fetchColumn();
    if ($menuId) {
        $menuUpdate = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page', page_id=?, menu_url='', menu_order=2 WHERE menu_id=?");
        $menuUpdate->execute(array($pageId, $menuId));
    } else {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=2 AND menu_order>=2");
        $menuInsert = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'Sejarah','',2,2)");
        $menuInsert->execute(array($pageId));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

function modern_import_escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
