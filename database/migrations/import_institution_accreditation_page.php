<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>Berikut adalah sertifikat akreditasi institusi Sekolah Tinggi Bahasa Asing Prayoga, Kota Padang, yang diterbitkan oleh Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT).</p>
<p><img src="{{BASE_URL}}assets/uploads/akreditasi-institusi-stba-prayoga.jpg" alt="Sertifikat Akreditasi Institusi STBA Prayoga Padang"></p>

<h2>Informasi Akreditasi</h2>
<table>
<tbody>
<tr><th>Informasi</th><th>Detail</th></tr>
<tr><td>Perguruan Tinggi</td><td><strong>Sekolah Tinggi Bahasa Asing Prayoga, Kota Padang</strong></td></tr>
<tr><td>Peringkat Akreditasi</td><td><strong>Baik</strong></td></tr>
<tr><td>Nomor Keputusan</td><td>1055/SK/BAN-PT/Akred/PT/XII/2020</td></tr>
<tr><td>Periode Berlaku</td><td>8 Desember 2020–8 Desember 2025</td></tr>
<tr><td>Lembaga Penerbit</td><td>Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT)</td></tr>
</tbody>
</table>

<blockquote><p>Sertifikat ini merupakan dokumen akreditasi untuk periode 2020–2025. Informasi status akreditasi terbaru perlu disesuaikan apabila STBA Prayoga telah menerima keputusan atau sertifikat pembaruan.</p></blockquote>
HTML;
$content = str_replace('{{BASE_URL}}', BASE_URL, $content);
$content = sanitize_rich_html($content);

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('akreditasi-institusi'));
    $pageId = $find->fetchColumn();
    $values = array(
        'Akreditasi Institusi', $content, 'Full Width Page Layout', '', 'Active',
        'Akreditasi Institusi STBA Prayoga Padang',
        'akreditasi institusi STBA Prayoga, BAN-PT, sertifikat akreditasi',
        'Sertifikat dan informasi akreditasi institusi STBA Prayoga Padang periode 2020–2025.'
    );
    if ($pageId) {
        $update = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,banner=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_id=?");
        $update->execute(array_merge($values, array($pageId)));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?, 'akreditasi-institusi', ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute($values);
        $pageId = (int) $pdo->lastInsertId();
    }

    $menuFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=5 AND menu_name='Akreditasi Institusi' LIMIT 1");
    $menuFind->execute();
    $menuId = $menuFind->fetchColumn();
    if ($menuId) {
        $updateMenu = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page',page_id=?,menu_url='',menu_order=1 WHERE menu_id=?");
        $updateMenu->execute(array($pageId, $menuId));
    } else {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=5 AND menu_order>=1");
        $insertMenu = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'Akreditasi Institusi','',1,5)");
        $insertMenu->execute(array($pageId));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
