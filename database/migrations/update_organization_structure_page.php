<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>Struktur organisasi STBA Prayoga Padang terdiri atas unsur pimpinan, unit pelaksana akademik, staf pengajar, serta tenaga kependidikan.</p>

<h2>Pimpinan STBA Prayoga</h2>
<table>
<tbody>
<tr><th>Jabatan</th><th>Nama</th></tr>
<tr><td>Ketua</td><td><strong>Melania Priska M., S.S., M.Hum.</strong></td></tr>
<tr><td>Wakil Ketua Bidang Akademik</td><td><strong>Yusmalinda, S.Pd., M.Pd.</strong></td></tr>
<tr><td>Wakil Ketua Bidang Nonakademik</td><td><strong>Yohanes Tuaderu, S.S., M.Hum.</strong></td></tr>
</tbody>
</table>

<h2>Unit Penelitian dan Pengabdian kepada Masyarakat</h2>
<table>
<tbody>
<tr><th>Jabatan</th><th>Nama</th></tr>
<tr><td>Koordinator</td><td><strong>Dian Purnama Sari, M.Pd.</strong></td></tr>
</tbody>
</table>

<h2>Unit Penjaminan Mutu Internal</h2>
<table>
<tbody>
<tr><th>Jabatan</th><th>Nama</th></tr>
<tr><td>Koordinator</td><td><strong>Dian Eka Sari, S.S., M.A.</strong></td></tr>
</tbody>
</table>

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
    'Struktur Organisasi', $content, 'Full Width Page Layout', 'Active',
    'Struktur Organisasi STBA Prayoga Padang',
    'struktur organisasi STBA Prayoga, pimpinan, dosen, tenaga kependidikan',
    'Struktur organisasi, pimpinan, unit, staf pengajar, dan tenaga kependidikan STBA Prayoga Padang.',
    'struktur-organisasi'
));
if ($statement->rowCount() < 1) {
    fwrite(STDERR, "Halaman struktur organisasi tidak ditemukan atau tidak berubah.\n");
    exit(1);
}
echo "Halaman struktur organisasi berhasil diperbarui.\n";
