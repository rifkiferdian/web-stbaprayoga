<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$items = array(
    array('Kepuasan Pengguna Lulusan', 'https://goo.gl/forms/fxUlRvWYyB123N663'),
    array('Evaluasi Kinerja Dosen', 'https://goo.gl/forms/7gVxAK8ZQcKRQHal1'),
    array('Pelacakan Alumni/Tracer Study', 'https://goo.gl/forms/h06V67Eim1GDCSRp2'),
    array('Penelusuran Minat Program Studi', 'https://goo.gl/forms/FcU6WTZbIvW1QFv02'),
    array('Angket Tata Pamong', 'https://forms.gle/mNYsjFqFMwGyohgB9'),
    array('Kemahasiswaan', 'https://forms.gle/rKNxkjgK7kp6qpBb7'),
    array('Keuangan dan Sarpras', 'https://forms.gle/NKKLdNsm9t1mrG3B6'),
    array('Layanan Pendidikan', 'https://forms.gle/52cSvZZFVMkAqNiT8'),
    array('Pelayanan Penelitian UPPM', 'https://forms.gle/7eqjoJSuXXxbXTyN6'),
    array('Pelayanan PkM', 'https://forms.gle/bUYey25am2sifbHT9'),
    array('Kepuasan Pengabdi PkM', 'https://forms.gle/kAZDUpqyJd5cWvFS7')
);

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=0 AND menu_name='Angket' LIMIT 1");
    $find->execute();
    $parentId = $find->fetchColumn();
    if (!$parentId) {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=0 AND menu_order>=6");
        $insert = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Other','','Angket','#',6,0)");
        $insert->execute();
        $parentId = (int) $pdo->lastInsertId();
    }

    $upsertFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=? AND menu_name=? LIMIT 1");
    $insertChild = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Other','',?,?,?,?)");
    $updateChild = $pdo->prepare("UPDATE tbl_menu SET menu_type='Other',page_id='',menu_url=?,menu_order=? WHERE menu_id=?");
    foreach ($items as $index => $item) {
        $upsertFind->execute(array($parentId, $item[0]));
        $menuId = $upsertFind->fetchColumn();
        if ($menuId) $updateChild->execute(array($item[1], $index + 1, $menuId));
        else $insertChild->execute(array($item[0], $item[1], $index + 1, $parentId));
    }
    $pdo->commit();
    echo "Created questionnaire menu_id={$parentId}, items=" . count($items) . "\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
