<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$sourceFile = isset($argv[1]) ? $argv[1] : '';
if ($sourceFile === '' || !is_file($sourceFile)) {
    fwrite(STDERR, "Gunakan: php import_s1_english_literature_page.php <file-html>\n");
    exit(1);
}

$source = file_get_contents($sourceFile);
$document = new DOMDocument();
$previous = libxml_use_internal_errors(true);
$document->loadHTML('<?xml encoding="utf-8" ?><div id="import-root">' . $source . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($previous);
$xpath = new DOMXPath($document);
$tables = $xpath->query("//*[@id='import-root']//table");
if ($tables->length < 1) {
    fwrite(STDERR, "Tabel kurikulum tidak ditemukan.\n");
    exit(1);
}

$content = '<p>Program Studi S1 Sastra Inggris STBA Prayoga membekali mahasiswa dengan kompetensi bahasa, linguistik, penerjemahan, penelitian, dan kajian sastra Inggris.</p>';
foreach ($tables as $tableIndex => $table) {
    $rows = array();
    foreach ($xpath->query('.//tr', $table) as $row) {
        $cells = array();
        foreach ($xpath->query('./th|./td', $row) as $cell) {
            $text = trim(preg_replace('/\s+/u', ' ', $cell->textContent));
            $cells[] = normalize_course_text($text);
        }
        if (count(array_filter($cells, static fn($value) => $value !== '')) > 0) $rows[] = $cells;
    }
    if (!$rows) continue;

    $title = $tableIndex === 0 ? 'Kurikulum 2017 Program Strata 1' : 'Kurikulum 2014 Program Strata 1';
    if (isset($rows[0][0]) && stripos($rows[0][0], 'Kurikulum') !== false) array_shift($rows);
    $content .= '<h2>' . import_escape($title) . '</h2><table><thead><tr><th>Kode Mata Kuliah</th><th>Nama Mata Kuliah</th><th>Semester</th><th>SKS</th></tr></thead><tbody>';

    if ($rows && isset($rows[0][0]) && (stripos($rows[0][0], 'Kode') !== false || stripos(implode(' ', $rows[0]), 'Mata Kuliah') !== false)) array_shift($rows);
    foreach ($rows as $cells) {
        $first = $cells[0] ?? '';
        if (strcasecmp($first, 'Jumlah') === 0 || stripos($first, 'TOTAL SKS') !== false) {
            $total = '';
            foreach (array_reverse($cells) as $cell) { if ($cell !== '') { $total = $cell; break; } }
            $label = stripos($first, 'TOTAL SKS') !== false ? 'Total SKS' : 'Jumlah';
            $content .= '<tr><td><strong>' . import_escape($label) . '</strong></td><td></td><td></td><td><strong>' . import_escape($total) . '</strong></td></tr>';
            continue;
        }
        $cells = array_pad($cells, 4, '');
        $content .= '<tr><td>' . import_escape($cells[0]) . '</td><td>' . import_escape($cells[1]) . '</td><td>' . import_escape($cells[2]) . '</td><td>' . import_escape($cells[3]) . '</td></tr>';
    }
    $content .= '</tbody></table>';
}

$content = sanitize_rich_html($content);
$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('s1-sastra-inggris'));
    $pageId = $find->fetchColumn();
    $values = array(
        'S1 Sastra Inggris', $content, 'Full Width Page Layout', '', 'Active',
        'Program Studi S1 Sastra Inggris STBA Prayoga',
        'S1 Sastra Inggris, kurikulum Sastra Inggris, STBA Prayoga',
        'Profil dan kurikulum Program Studi S1 Sastra Inggris STBA Prayoga Padang.'
    );
    if ($pageId) {
        $update = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,banner=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_id=?");
        $update->execute(array_merge($values, array($pageId)));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?, 's1-sastra-inggris', ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute($values);
        $pageId = (int) $pdo->lastInsertId();
    }

    $menu = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page',page_id=?,menu_name='S1 Sastra Inggris',menu_url='',menu_order=1 WHERE menu_parent=3 AND menu_name='S1 Sastra Inggris'");
    $menu->execute(array($pageId));
    if ($menu->rowCount() < 1) {
        $insertMenu = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'S1 Sastra Inggris','',1,3)");
        $insertMenu->execute(array($pageId));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}, curriculum_tables={$tables->length}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

function import_escape($value) { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function normalize_course_text($value)
{
    return str_replace(
        array('Pre-Intermediet', 'Advance Listening', 'Advance Grammar', 'English-Indonesia Translation', 'English Correspondance', 'Literary Criticsm', 'Literary Criticesm', 'Praktek Kerja lapangan', 'Secretarial Practica', 'Dasar- Dasar', 'Matakuliah'),
        array('Pre-Intermediate', 'Advanced Listening', 'Advanced Grammar', 'English-Indonesian Translation', 'English Correspondence', 'Literary Criticism', 'Literary Criticism', 'Praktik Kerja Lapangan', 'Secretarial Practice', 'Dasar-Dasar', 'Mata Kuliah'),
        $value
    );
}
