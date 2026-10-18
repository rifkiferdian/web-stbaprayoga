<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<h2>Identitas Perguruan Tinggi</h2>
<table>
<tbody>
<tr><th>Informasi</th><th>Detail</th></tr>
<tr><td>Nama Perguruan Tinggi</td><td><strong>Sekolah Tinggi Bahasa Asing Prayoga</strong>, disingkat <strong>STBA Prayoga</strong></td></tr>
<tr><td>Telepon</td><td><a href="tel:075131717">0751-31717</a> dan <a href="tel:085830305317">085830305317</a></td></tr>
<tr><td>Faksimile</td><td>0751-31717</td></tr>
<tr><td>Email</td><td><a href="mailto:info@stba-prayoga.ac.id">info@stba-prayoga.ac.id</a></td></tr>
<tr><td>Website</td><td><a href="https://www.stba-prayoga.ac.id" target="_blank">www.stba-prayoga.ac.id</a></td></tr>
<tr><td>Alamat</td><td>Jalan Veteran No. 8, Kelurahan Padang Pasir, Kecamatan Padang Barat, Kota Padang, Sumatera Barat 25116</td></tr>
</tbody>
</table>

<h2>Pimpinan</h2>
<table>
<tbody>
<tr><th>Jabatan</th><th>Nama dan Kontak</th></tr>
<tr><td>Ketua</td><td><strong>Melania Priska M., S.S., M.Hum.</strong><br>Telepon/HP: <a href="tel:083161040132">083161040132</a></td></tr>
<tr><td>Wakil Ketua Bidang Akademik</td><td><strong>Yusmalinda, S.Pd., M.Pd.</strong><br>NIDN: 1020127201<br>Telepon/HP: <a href="tel:081268233350">081268233350</a></td></tr>
<tr><td>Wakil Ketua Bidang Nonakademik</td><td><strong>Yohanes Tuaderu, S.S., M.Hum.</strong><br>NIDN: 1001016604<br>Telepon/HP: <a href="tel:08122521188">08122521188</a></td></tr>
</tbody>
</table>

<h2>Status Akreditasi</h2>
<table>
<tbody>
<tr><th>Unit</th><th>Status</th></tr>
<tr><td>STBA Prayoga</td><td>Institusi: Terakreditasi Baik — SK No. 1055/SK/BAN-PT/Akred/PT/XII/2020, berlaku sampai 8 Desember 2025</td></tr>
<tr><td>Program Studi Sastra Inggris</td><td>S1: Terakreditasi B — SK No. 26/SK/BAN-PT/Ak.S/S/I/2024</td></tr>
</tbody>
</table>

<h2>Pendirian dan Aset Kampus</h2>
<table>
<tbody>
<tr><th>Informasi</th><th>Detail</th></tr>
<tr><td>Tahun Pendirian</td><td>1 Februari 1971</td></tr>
<tr><td>Akta Pendirian Notaris</td><td>No. 31, 26 Maret 1962</td></tr>
<tr><td>Status Kepemilikan Tanah dan Bangunan</td><td>Milik sendiri</td></tr>
<tr><td>Luas Tanah</td><td>5.061 m² / SHM</td></tr>
<tr><td>Luas Bangunan</td><td>2.000 m² (gedung baru yang dibangun setelah gempa bumi 2009)</td></tr>
</tbody>
</table>

<h2>Yayasan Prayoga Padang</h2>
<table>
<tbody>
<tr><th>Informasi</th><th>Detail</th></tr>
<tr><td>Nama Yayasan</td><td><strong>Yayasan Prayoga Padang</strong></td></tr>
<tr><td>Akta Pendirian</td><td>No. 31, 26 Maret 1962</td></tr>
<tr><td>Terdaftar di Dephukham</td><td>No. AHU-AH.01.08-98, tanggal 1 Maret 2010</td></tr>
<tr><td>Alamat</td><td>Jalan Khairil Anwar No. 10, Kelurahan Belakang Tangsi, Kecamatan Padang Barat, Kota Padang, Sumatera Barat 25118</td></tr>
<tr><td>Telepon/Faksimile</td><td>(0751) 21917</td></tr>
<tr><td>Email</td><td><a href="mailto:info@prayoga-padang.or.id">info@prayoga-padang.or.id</a></td></tr>
</tbody>
</table>
HTML;

$content = sanitize_rich_html($content);
$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('informasi-umum'));
    $pageId = $find->fetchColumn();
    $values = array('Informasi Umum', $content, 'Full Width Page Layout', '', 'Active', 'Informasi Umum STBA Prayoga Padang', 'informasi umum STBA Prayoga, profil kampus, akreditasi, pimpinan', 'Informasi umum, pimpinan, akreditasi, fasilitas, dan Yayasan Prayoga Padang.');
    if ($pageId) {
        $update = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,banner=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_id=?");
        $update->execute(array_merge($values, array($pageId)));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?, 'informasi-umum', ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute($values);
        $pageId = (int) $pdo->lastInsertId();
    }

    $menuFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=2 AND menu_name=? LIMIT 1");
    $menuFind->execute(array('Informasi Umum'));
    $menuId = $menuFind->fetchColumn();
    if ($menuId) {
        $updateMenu = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page',page_id=?,menu_url='',menu_order=3 WHERE menu_id=?");
        $updateMenu->execute(array($pageId, $menuId));
    } else {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=2 AND menu_order>=3");
        $insertMenu = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'Informasi Umum','',3,2)");
        $insertMenu->execute(array($pageId));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
