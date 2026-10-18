<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';
require_once __DIR__ . '/../../panel-5tb4/functions.php';

$content = <<<'HTML'
<p>Civitas academica STBA Prayoga Padang percaya bahwa pendidikan adalah kunci kemajuan. Sebagai kunci kemajuan, pendidikan, baik formal maupun informal, diyakini merupakan kebutuhan penting bagi manusia. Asas, dasar, dan tujuan pendidikan yang sangat penting bagi manusia tersebut adalah sebagai berikut.</p>

<h2>Asas dan Dasar Pendidikan</h2>
<blockquote><p>STBA Prayoga Padang adalah perguruan tinggi berciri khas Katolik yang berasaskan dan berdasarkan Pancasila dan Undang-Undang Dasar 1945.</p></blockquote>

<h2>Tujuan Pendidikan STBA Prayoga Padang</h2>
<h3>Tujuan Umum</h3>
<blockquote><p>Tujuan pendidikan STBA Prayoga Padang sejalan dengan tujuan Pendidikan Nasional Indonesia sebagaimana dirumuskan dalam alinea keempat Pembukaan UUD 1945, yaitu mencerdaskan kehidupan bangsa. Kecerdasan yang dimaksud bersifat holistik, meliputi kecerdasan intelektual, emosional, dan spiritual.</p></blockquote>

<h3>Tujuan Khusus</h3>
<ol>
<li>Menghasilkan lulusan yang mampu menguasai keterampilan bahasa Inggris dengan baik.</li>
<li>Menghasilkan lulusan yang mampu menerjemahkan teks secara tertulis maupun lisan dengan baik.</li>
<li>Menghasilkan lulusan yang mampu menciptakan dan mengkritik karya sastra dalam bahasa Inggris.</li>
<li>Menghasilkan lulusan yang mampu melaksanakan penelitian dalam bidang kebahasaan dan kesusastraan.</li>
<li>Menghasilkan lulusan yang mempunyai kompetensi untuk mengembangkan ilmu bahasa dan sastra Inggris secara mandiri di lingkungan kerja dan masyarakat.</li>
</ol>
HTML;

$content = sanitize_rich_html($content);
$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug=? LIMIT 1");
    $find->execute(array('asas-dasar-dan-tujuan'));
    $pageId = $find->fetchColumn();
    $values = array(
        'Asas, Dasar, dan Tujuan', $content, 'Full Width Page Layout', '', 'Active',
        'Asas, Dasar, dan Tujuan STBA Prayoga Padang',
        'asas STBA Prayoga, dasar pendidikan, tujuan pendidikan, tujuan lulusan',
        'Asas, dasar, tujuan umum, dan tujuan khusus pendidikan STBA Prayoga Padang.'
    );
    if ($pageId) {
        $update = $pdo->prepare("UPDATE tbl_page SET page_name=?,page_content=?,page_layout=?,banner=?,status=?,meta_title=?,meta_keyword=?,meta_description=? WHERE page_id=?");
        $update->execute(array_merge($values, array($pageId)));
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?, 'asas-dasar-dan-tujuan', ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute($values);
        $pageId = (int) $pdo->lastInsertId();
    }

    $menuFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=2 AND menu_name=? LIMIT 1");
    $menuFind->execute(array('Asas, Dasar & Tujuan'));
    $menuId = $menuFind->fetchColumn();
    if ($menuId) {
        $updateMenu = $pdo->prepare("UPDATE tbl_menu SET menu_type='Page',page_id=?,menu_url='',menu_order=5 WHERE menu_id=?");
        $updateMenu->execute(array($pageId, $menuId));
    } else {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=2 AND menu_order>=5");
        $insertMenu = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Page',?,'Asas, Dasar & Tujuan','',5,2)");
        $insertMenu->execute(array($pageId));
    }
    $pdo->commit();
    echo "Imported page_id={$pageId}\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
