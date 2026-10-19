<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$items = array(
    array('Sistem Informasi Akademik Terpadu', 'http://simantap.stba-prayoga.ac.id'),
    array('Sistem Penerimaan Mahasiswa Baru Online', 'https://pmb.stba-prayoga.ac.id/'),
    array('Jurnal Penelitian', 'http://journal.stba-prayoga.ac.id'),
    array('eLibrary', 'https://library.stba-prayoga.ac.id/'),
    array('Alumni and Career Center', 'http://alumni.stba-prayoga.ac.id'),
    array('eLearning', 'http://elearning.stba-prayoga.ac.id/')
);

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=0 AND menu_name='Sistem Informasi' LIMIT 1");
    $find->execute();
    $parentId = $find->fetchColumn();
    if (!$parentId) {
        $pdo->exec("UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=0 AND menu_order>=7");
        $insert = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Other','','Sistem Informasi','#',7,0)");
        $insert->execute();
        $parentId = (int) $pdo->lastInsertId();
    }

    $childFind = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=? AND menu_name=? LIMIT 1");
    $childInsert = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Other','',?,?,?,?)");
    $childUpdate = $pdo->prepare("UPDATE tbl_menu SET menu_type='Other',page_id='',menu_url=?,menu_order=? WHERE menu_id=?");
    foreach ($items as $index => $item) {
        $childFind->execute(array($parentId, $item[0]));
        $menuId = $childFind->fetchColumn();
        if ($menuId) $childUpdate->execute(array($item[1], $index + 1, $menuId));
        else $childInsert->execute(array($item[0], $item[1], $index + 1, $parentId));
    }

    $pdo->commit();
    echo "Created information systems menu_id={$parentId}, items=" . count($items) . "\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
