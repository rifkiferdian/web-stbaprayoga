<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>Calon mahasiswa dapat mengikuti proses Penerimaan Mahasiswa Baru (PMB) STBA Prayoga secara daring. Perhatikan setiap tahap berikut dan siapkan dokumen yang diperlukan sebelum mengisi formulir.</p>
<p><img src="{{BASE_URL}}assets/uploads/alur-pmb-online-stba-prayoga.jpeg" alt="Infografis tahapan PMB Online STBA Prayoga"></p>

<h2>Tahapan PMB Online</h2>
<ol>
<li><strong>Membayar biaya pendaftaran.</strong> Lakukan pembayaran biaya pendaftaran sesuai informasi resmi yang tercantum pada infografis, kemudian unggah bukti pembayaran.</li>
<li><strong>Mengisi formulir PMB.</strong> Isi seluruh data yang diperlukan secara lengkap dan jujur.</li>
<li><strong>Mengunggah ijazah.</strong> Unggah hasil pindai ijazah SMA/SMK atau Surat Keterangan Lulus dari kepala sekolah.</li>
<li><strong>Mengerjakan USM.</strong> Setelah dokumen diverifikasi, panitia PMB akan mengirimkan soal Ujian Saringan Masuk untuk dikerjakan secara daring.</li>
<li><strong>Pengumuman USM dan pembayaran dana kuliah.</strong> Hasil USM disampaikan melalui WhatsApp. Peserta yang dinyatakan lulus mengikuti petunjuk pembayaran dari panitia.</li>
</ol>

<blockquote><p>Gunakan tombol Pendaftaran pada menu PMB untuk membuka formulir pendaftaran resmi di tab baru.</p></blockquote>
HTML;
$content = str_replace('{{BASE_URL}}', BASE_URL, $content);
$content = sanitize_rich_html($content);

$pdo->beginTransaction();
try {
    $page = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_slug=?");
    $page->execute(array(
        'Informasi dan Alur PMB', $content, 'Full Width Page Layout', 'Active',
        'Informasi dan Alur PMB STBA Prayoga',
        'PMB STBA Prayoga, pendaftaran mahasiswa baru, alur PMB online',
        'Informasi dan tahapan Penerimaan Mahasiswa Baru secara daring di STBA Prayoga Padang.',
        'pmb'
    ));
    if ($page->rowCount() < 1) throw new RuntimeException('Halaman Informasi PMB tidak ditemukan atau tidak berubah.');

    $infoMenu = $pdo->prepare("UPDATE tbl_menu SET menu_name='Informasi & Alur PMB' WHERE menu_parent=4 AND page_id=(SELECT page_id FROM tbl_page WHERE page_slug='pmb')");
    $infoMenu->execute();
    $registration = $pdo->prepare("UPDATE tbl_menu SET menu_type='Other',page_id='',menu_url=? WHERE menu_parent=4 AND menu_name='Pendaftaran'");
    $registration->execute(array('https://forms.gle/VTD9c75Y6NACK7V8A'));
    if ($registration->rowCount() < 1) throw new RuntimeException('Menu Pendaftaran tidak ditemukan atau tidak berubah.');

    $pdo->commit();
    echo "Halaman alur PMB dan tautan pendaftaran berhasil diperbarui.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
