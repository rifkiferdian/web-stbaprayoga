<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$pdo->beginTransaction();
try {
    $contact = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_name='Kontak' LIMIT 1");
    $contact->execute();
    $contactId = $contact->fetchColumn();
    if (!$contactId) throw new RuntimeException('Menu Kontak tidak ditemukan.');

    $nextOrder = (int) $pdo->query("SELECT COALESCE(MAX(menu_order),0)+1 FROM tbl_menu WHERE menu_parent=2")->fetchColumn();
    $update = $pdo->prepare("UPDATE tbl_menu SET menu_parent=2,menu_order=? WHERE menu_id=?");
    $update->execute(array($nextOrder, $contactId));

    $roots = $pdo->query("SELECT menu_id FROM tbl_menu WHERE menu_parent=0 ORDER BY menu_order,menu_id")->fetchAll(PDO::FETCH_COLUMN);
    $reorder = $pdo->prepare("UPDATE tbl_menu SET menu_order=? WHERE menu_id=?");
    foreach ($roots as $index => $menuId) $reorder->execute(array($index + 1, $menuId));

    $pdo->commit();
    echo "Menu Kontak dipindahkan ke Profil.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
