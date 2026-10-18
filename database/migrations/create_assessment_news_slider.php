<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$slug = 'pelaksanaan-asesmen-lapangan-akreditasi-s1-sastra-inggris';
$title = 'Pelaksanaan Asesmen Lapangan Akreditasi Prodi S1 Sastra Inggris';
$content = sanitize_rich_html(<<<'HTML'
<p>Pada tanggal 7–9 Juni 2024, tim asesor Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) melaksanakan kunjungan ke kampus STBA Prayoga dalam rangka asesmen lapangan akreditasi Program Studi S1 Sastra Inggris.</p>
<h2>Tim Asesor</h2>
<ol>
<li>Prof. Dr. Yudi Juniardi, M.Pd.</li>
<li>Prof. Dr. M. Farkhan, M.Pd.</li>
<li>Tri Sumarni</li>
</ol>
<h2>Rangkaian Asesmen Lapangan</h2>
<p>Selain melakukan penilaian terhadap dokumen dan data, tim asesor juga melakukan wawancara dengan alumni, pengguna lulusan, dan pemangku kepentingan untuk memperoleh gambaran mengenai kinerja program studi dalam beberapa tahun terakhir.</p>
<p>Seluruh rangkaian proses asesmen lapangan ditutup dengan penandatanganan berita acara asesmen lapangan.</p>
HTML);
$short = 'Tim asesor BAN-PT melakukan asesmen lapangan Program Studi S1 Sastra Inggris di kampus STBA Prayoga pada 7–9 Juni 2024.';
$photo = 'stba-history-yard.jpg';

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_slug=? LIMIT 1");
    $find->execute(array($slug));
    $newsId = $find->fetchColumn();
    if ($newsId) {
        $update = $pdo->prepare("UPDATE tbl_news SET news_title=?,news_content=?,news_content_short=?,news_date=?,photo=?,category_id=1,publisher='STBA Prayoga',meta_title=?,meta_keyword=?,meta_description=? WHERE news_id=?");
        $update->execute(array($title, $content, $short, '7–9 Juni 2024', $photo, 'Asesmen Lapangan Akreditasi S1 Sastra Inggris', 'asesmen lapangan, akreditasi, S1 Sastra Inggris, BAN-PT', $short, $newsId));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_news (news_title,news_slug,news_content,news_content_short,news_date,photo,category_id,publisher,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,1,'STBA Prayoga',0,?,?,?)");
        $insert->execute(array($title, $slug, $content, $short, '7–9 Juni 2024', $photo, 'Asesmen Lapangan Akreditasi S1 Sastra Inggris', 'asesmen lapangan, akreditasi, S1 Sastra Inggris, BAN-PT', $short));
        $newsId = (int) $pdo->lastInsertId();
    }

    $slideFind = $pdo->prepare("SELECT id FROM tbl_slider WHERE heading=? LIMIT 1");
    $slideFind->execute(array($title));
    $slideId = $slideFind->fetchColumn();
    $slideValues = array($photo, $title, $short, 'Selengkapnya', 'modern/news/' . $slug, 'Left', 'Active');
    if ($slideId) {
        $slideUpdate = $pdo->prepare("UPDATE tbl_slider SET photo=?,heading=?,content=?,button_text=?,button_url=?,position=?,status=? WHERE id=?");
        $slideUpdate->execute(array_merge($slideValues, array($slideId)));
    } else {
        $slideInsert = $pdo->prepare("INSERT INTO tbl_slider (photo,heading,content,button_text,button_url,position,status) VALUES (?,?,?,?,?,?,?)");
        $slideInsert->execute($slideValues);
        $slideId = (int) $pdo->lastInsertId();
    }
    $pdo->commit();
    echo "Created news_id={$newsId}, slider_id={$slideId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
