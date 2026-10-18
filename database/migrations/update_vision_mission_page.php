<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<h2>Visi Institusi</h2>
<blockquote><p>Menjadi Komunitas Pendidikan Tinggi Bereputasi Unggul di LLDIKTI Wilayah X.</p></blockquote>

<h2>Misi Institusi</h2>
<ol>
<li>Menyelenggarakan pendidikan yang unggul dalam keterampilan bahasa asing.</li>
<li>Melaksanakan penelitian dan publikasi ilmiah di bidang bahasa asing.</li>
<li>Menyelenggarakan pengabdian kepada masyarakat secara terencana dan berkelanjutan sesuai dengan kebutuhan masyarakat di bidang bahasa asing.</li>
<li>Menyelenggarakan tata kelola sekolah tinggi yang efektif dan efisien sesuai dengan prinsip <em>Good University Governance</em>.</li>
<li>Menjalin kemitraan dan kerja sama yang baik dengan pemangku kepentingan untuk meningkatkan mutu sekolah tinggi.</li>
</ol>

<h2>Tujuan Institusi</h2>
<ol>
<li>Menghasilkan lulusan yang mampu bersaing dalam dunia kerja yang menuntut keterampilan berbahasa asing yang baik.</li>
<li>Menghasilkan karya penelitian yang dipublikasikan secara nasional dan internasional.</li>
<li>Menghasilkan karya pengabdian kepada masyarakat dalam bentuk transformasi ilmu dan keterampilan sebagai penerapan penelitian di bidang bahasa asing.</li>
<li>Menciptakan sekolah tinggi yang memiliki tata kelola efektif dan efisien.</li>
<li>Menghasilkan kerja sama dalam dan luar negeri untuk mendukung pelaksanaan Tridharma Perguruan Tinggi.</li>
</ol>

<h2>Visi Program Studi S1 Sastra Inggris</h2>
<blockquote><p>Menghasilkan lulusan bidang Ilmu Bahasa dan Sastra Inggris yang unggul, berkarakter cerdas, dan dapat dipercaya di LLDIKTI Wilayah X tahun 2035.</p></blockquote>

<h2>Misi Program Studi S1 Sastra Inggris</h2>
<ol>
<li>Menyelenggarakan pendidikan yang unggul dalam bidang bahasa dan sastra Inggris.</li>
<li>Melaksanakan penelitian dan publikasi ilmiah di bidang bahasa dan sastra Inggris.</li>
<li>Menyelenggarakan pengabdian kepada masyarakat secara terencana dan berkelanjutan sesuai dengan kebutuhan masyarakat di bidang keterampilan bahasa dan sastra Inggris.</li>
<li>Menyelenggarakan evaluasi berkelanjutan untuk meningkatkan mutu program studi.</li>
<li>Meningkatkan dan memperluas kerja sama Tridharma Perguruan Tinggi.</li>
</ol>

<h2>Tujuan Program Studi S1 Sastra Inggris</h2>
<ol>
<li>Menghasilkan lulusan yang unggul dan mampu bersaing dalam dunia kerja yang menuntut keterampilan berbahasa Inggris yang baik.</li>
<li>Menghasilkan lulusan yang mampu menerjemahkan bahasa Inggris secara lisan maupun tulisan dengan baik.</li>
<li>Menghasilkan lulusan yang mampu menganalisis dan mengapresiasi karya sastra dalam bahasa Inggris.</li>
<li>Menghasilkan lulusan yang mampu melaksanakan penelitian dalam bidang kebahasaan dan kesusastraan.</li>
<li>Menghasilkan lulusan yang menguasai ilmu bahasa dan sastra Inggris serta mampu menerapkannya di lingkungan kerja dan masyarakat.</li>
</ol>

<h2>Sasaran dan Strategi Institusi dan Program Studi</h2>

<h3>Fase II: Penguatan Tata Kelola dan Pemantapan Tridharma (2018–2021)</h3>
<p><strong>Strategi pencapaian:</strong></p>
<ol>
<li>UPPS memiliki struktur organisasi yang jelas dan lengkap.</li>
<li>Program Studi bersama Sekolah Tinggi menyepakati Rencana Induk Pengembangan sepuluh tahun (2015–2025).</li>
<li>Program Studi ikut serta merevisi Statuta bersama Sekolah Tinggi.</li>
<li>Mengembangkan budaya penelitian untuk menghasilkan luaran berupa materi publikasi, jurnal, seminar, dan buku.</li>
<li>Memanfaatkan dana penelitian dan pengabdian kepada masyarakat yang dialokasikan oleh Sekolah Tinggi.</li>
<li>Menjalin kerja sama dengan program studi sejenis dalam melaksanakan penelitian dan pengabdian kepada masyarakat.</li>
<li>Meningkatkan dan memperluas kerja sama Tridharma Perguruan Tinggi tingkat nasional.</li>
</ol>

<h3>Fase III: Pembenahan Mutu SDM dan Peningkatan Daya Saing Lulusan (2022–2025)</h3>
<p><strong>Strategi pencapaian:</strong></p>
<ol>
<li>Mendorong dan memfasilitasi dosen untuk memiliki jabatan fungsional minimal Lektor.</li>
<li>Mendorong dosen melanjutkan studi S3 sehingga Program Studi S1 Sastra Inggris memiliki minimal dua dosen berkualifikasi Doktor.</li>
<li>Mengadakan pelatihan, seminar, dan lokakarya untuk meningkatkan profesionalisme dosen dan tenaga kependidikan.</li>
<li>Membentuk karakter positif mahasiswa dengan mengintegrasikan pendidikan karakter pada semua mata kuliah.</li>
<li>Meningkatkan kualitas program studi dalam menghasilkan lulusan dengan rata-rata predikat kelulusan sangat memuaskan.</li>
<li>Memberdayakan dan memaksimalkan fungsi laboratorium untuk menunjang pendidikan dan praktik keterampilan berbahasa.</li>
<li>Meningkatkan dan memperluas kerja sama Tridharma Perguruan Tinggi tingkat internasional.</li>
<li>Ikut serta melaksanakan program MBKM guna meningkatkan mutu lulusan.</li>
</ol>

<h3>Fase IV: Peningkatan Kerja Sama dan Kegiatan MBKM (2026–2030)</h3>
<p><strong>Sasaran:</strong> peningkatan kerja sama serta kegiatan MBKM Kemdikbud dan Mandiri.</p>
<p><strong>Strategi pencapaian:</strong></p>
<ol>
<li>Menjalin kerja sama dengan program studi sejenis di perguruan tinggi lain untuk program pertukaran mahasiswa.</li>
<li>Menjalin kerja sama dengan dunia industri dan dunia usaha untuk menempatkan mahasiswa dalam program magang mandiri.</li>
<li>Menjalin kerja sama dengan instansi pemerintah dan swasta untuk menempatkan mahasiswa dalam program magang.</li>
<li>Mendorong mahasiswa mengikuti KKN Tematik atau Proyek Kemanusiaan melalui kerja sama dengan pemerintah desa.</li>
<li>Menjalin kerja sama dengan sekolah mitra untuk melaksanakan program Kampus Mengajar Mandiri.</li>
</ol>

<h3>Fase V: Peningkatan Luaran Tridharma Perguruan Tinggi (2031–2035)</h3>
<p><strong>Strategi pencapaian:</strong></p>
<ol>
<li>Mendorong dan memfasilitasi dosen melakukan penelitian dengan hibah dari Kementerian.</li>
<li>Mendorong dan memfasilitasi dosen mempublikasikan artikel penelitian pada jurnal nasional terakreditasi SINTA 1 atau SINTA 2 serta jurnal internasional bereputasi dan terindeks Scopus minimal Q2.</li>
<li>Mendorong dan memfasilitasi dosen menerbitkan buku ber-ISBN dan memiliki HAKI.</li>
<li>Mengikutsertakan mahasiswa dalam penelitian dan pengabdian dosen.</li>
<li>Mendorong dan memfasilitasi dosen mempublikasikan hasil pengabdian kepada masyarakat.</li>
<li>Mengikutsertakan mahasiswa dalam perlombaan tingkat nasional dan internasional.</li>
</ol>
HTML;

$content = sanitize_rich_html($content);
$pdo->beginTransaction();
$statement = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_slug=?");
$statement->execute(array(
    'Visi, Misi, dan Tujuan', $content, 'Full Width Page Layout', 'Active',
    'Visi, Misi, dan Tujuan STBA Prayoga',
    'visi misi STBA Prayoga, tujuan institusi, S1 Sastra Inggris',
    'Visi, misi, tujuan, sasaran, dan strategi STBA Prayoga serta Program Studi S1 Sastra Inggris.',
    'visi-misi'
));
if ($statement->rowCount() < 1) {
    fwrite(STDERR, "Halaman visi-misi tidak ditemukan atau tidak berubah.\n");
    exit(1);
}

$menu = $pdo->prepare("UPDATE tbl_menu SET menu_name='Visi, Misi & Tujuan' WHERE menu_type='Page' AND page_id=(SELECT page_id FROM tbl_page WHERE page_slug='visi-misi')");
$menu->execute();
$pdo->commit();
echo "Halaman visi dan misi berhasil diperbarui.\n";
