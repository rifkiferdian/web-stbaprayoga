<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>STBA Prayoga didukung oleh staf pengajar dan tenaga kependidikan yang berkomitmen memberikan layanan pendidikan terbaik bagi mahasiswa.</p>

<h2>Staf Pengajar STBA Prayoga</h2>
<ol>
<li>Prof. Dr. Oktavianus, M.Hum.</li>
<li>Dr. Maizufri, M.S.</li>
<li>Drs. Nasrul HS, M.Ag.</li>
<li>Dr. Wa’özisökhi Nazara, M.Hum.</li>
<li>Dra. Irma, M.Hum.</li>
<li>Dra. Emidar, M.Pd.</li>
<li>Yusmalinda, S.Pd., M.Pd.</li>
<li>Yohanes Tuaderu, S.S., M.Hum.</li>
<li>Tumpal Manurung, S.Kom., M.Kom.</li>
<li>Danel Aditia Situngkir, S.H., M.H.</li>
<li>Dian Purnama Sari, M.Pd.</li>
<li>Andry Azhari, M.Pd.</li>
<li>Melania Priska M., S.S., M.Hum.</li>
</ol>

<h2>Tenaga Kependidikan</h2>
<h3>Staf Tata Usaha</h3>
<ul>
<li>Sonywaty, S.E.</li>
<li>Widodo, S.Kom.</li>
</ul>

<h3>Staf Perpustakaan</h3>
<ul>
<li>Silvia Manda Sari, S.Sos.</li>
</ul>
HTML;

$content = sanitize_rich_html($content);
$statement = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_slug=?");
$statement->execute(array(
    'Dosen dan Tenaga Kependidikan', $content, 'Full Width Page Layout', 'Active',
    'Dosen dan Tenaga Kependidikan STBA Prayoga',
    'dosen STBA Prayoga, staf pengajar, tenaga kependidikan, staf perpustakaan',
    'Daftar staf pengajar dan tenaga kependidikan STBA Prayoga Padang.',
    'profil-dosen-dan-tenaga-kependidikan'
));
if ($statement->rowCount() < 1) {
    fwrite(STDERR, "Halaman dosen dan tenaga kependidikan tidak ditemukan atau tidak berubah.\n");
    exit(1);
}

$pageId = $pdo->query("SELECT page_id FROM tbl_page WHERE page_slug='profil-dosen-dan-tenaga-kependidikan'")->fetchColumn();
$menu = $pdo->prepare("UPDATE tbl_menu SET menu_name='Dosen & Tenaga Kependidikan' WHERE menu_type='Page' AND page_id=?");
$menu->execute(array($pageId));
echo "Halaman dosen dan tenaga kependidikan berhasil diperbarui.\n";
