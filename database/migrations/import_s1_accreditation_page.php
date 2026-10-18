<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>Program Studi Sastra Inggris pada Program Sarjana STBA Prayoga Padang memperoleh peringkat akreditasi <strong>Baik</strong> dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT).</p>
<p><img src="{{BASE_URL}}assets/uploads/akreditasi-s1-sastra-inggris-stba-prayoga.png" alt="Sertifikat Akreditasi Program Studi S1 Sastra Inggris STBA Prayoga"></p>

<h2>Informasi Akreditasi Program Studi</h2>
<table>
<tbody>
<tr><th>Informasi</th><th>Detail</th></tr>
<tr><td>Program Studi</td><td><strong>Sastra Inggris</strong></td></tr>
<tr><td>Program</td><td>Sarjana (S1)</td></tr>
<tr><td>Perguruan Tinggi</td><td>Sekolah Tinggi Bahasa Asing Prayoga, Kota Padang</td></tr>
<tr><td>Peringkat Akreditasi</td><td><strong>Baik</strong></td></tr>
<tr><td>Nomor Keputusan</td><td>4846/SK/BAN-PT/Ak/S/VII/2024</td></tr>
<tr><td>Periode Berlaku</td><td>2 Juli 2024–2 Juli 2029</td></tr>
<tr><td>Lembaga Penerbit</td><td>Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT)</td></tr>
</tbody>
</table>
HTML;
$content = str_replace('{{BASE_URL}}', BASE_URL, $content);
$content = sanitize_rich_html($content);

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('akreditasi-s1-sastra-inggris'));
    $pageId = $find->fetchColumn();
    $values = array(
        'Akreditasi S1 Sastra Inggris', $content, 'Full Width Page Layout', '', 'Active',
        'Akreditasi S1 Sastra Inggris STBA Prayoga',
        'akreditasi S1 Sastra Inggris, BAN-PT, STBA Prayoga',
        'Sertifikat dan informasi akreditasi Program Studi S1 Sastra Inggris STBA Prayoga periode 2024–2029.'
    );
    if ($pageId) {
        $update = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,banner=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_id=?");
        $update->execute(array_merge($values, array($pageId)));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?, 'akreditasi-s1-sastra-inggris', ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute($values);
        $pageId = (int) $pdo->lastInsertId();
    }

    $menuFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=5 AND menu_name='Akreditasi S1 Sastra Inggris' LIMIT 1");
    $menuFind->execute();
    $menuId = $menuFind->fetchColumn();
    if ($menuId) {
        $updateMenu = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page',page_id=?,menu_url='',menu_order=2 WHERE menu_id=?");
        $updateMenu->execute(array($pageId, $menuId));
    } else {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=5 AND menu_order>=2");
        $insertMenu = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'Akreditasi S1 Sastra Inggris','',2,5)");
        $insertMenu->execute(array($pageId));
    }

    $infoFind = $pdo->prepare("SELECT page_content FROM tbl_page WHERE page_slug='informasi-umum' LIMIT 1");
    $infoFind->execute();
    $infoContent = $infoFind->fetchColumn();
    if ($infoContent) {
        $infoContent = preg_replace(
            '#<tr><td>Program Studi Sastra Inggris</td><td>.*?</td></tr>#s',
            '<tr><td>Program Studi Sastra Inggris</td><td>S1: Terakreditasi Baik — SK No. 4846/SK/BAN-PT/Ak/S/VII/2024, berlaku 2 Juli 2024–2 Juli 2029</td></tr>',
            $infoContent
        );
        $infoUpdate = $pdo->prepare("UPDATE tbl_page SET page_content=? WHERE page_slug='informasi-umum'");
        $infoUpdate->execute(array($infoContent));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
