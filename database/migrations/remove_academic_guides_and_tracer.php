<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$pageNames = array('Panduan Akademik', 'Panduan Tugas Akhir', 'Tracer Study', 'Pelacakan Alumni/Tracer Study');
$knownPageIds = array('21', '24', '25');
$placeholders = implode(',', array_fill(0, count($pageNames), '?'));

$idPlaceholders = implode(',', array_fill(0, count($knownPageIds), '?'));
$pageQuery = $pdo->prepare("SELECT * FROM tbl_page WHERE page_name IN ({$placeholders}) OR page_id IN ({$idPlaceholders}) ORDER BY page_id");
$pageQuery->execute(array_merge($pageNames, $knownPageIds));
$pages = $pageQuery->fetchAll(PDO::FETCH_ASSOC);
$pageIds = array_map(static fn($page) => (string) $page['page_id'], $pages);

$menuConditions = "menu_name IN ({$placeholders})";
$menuParameters = $pageNames;
if ($pageIds) {
    $menuConditions .= ' OR page_id IN (' . implode(',', array_fill(0, count($pageIds), '?')) . ')';
    $menuParameters = array_merge($menuParameters, $pageIds);
}
$menuQuery = $pdo->prepare("SELECT * FROM tbl_menu WHERE {$menuConditions} ORDER BY menu_id");
$menuQuery->execute($menuParameters);
$menus = $menuQuery->fetchAll(PDO::FETCH_ASSOC);

$backupDirectory = dirname(__DIR__, 2) . '/backups';
if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0755, true) && !is_dir($backupDirectory)) {
    throw new RuntimeException('Unable to create backup directory.');
}
$backupPath = $backupDirectory . '/removed-academic-guides-and-tracer-' . date('Ymd-His') . '.backup';
$backup = json_encode(array(
    'created_at' => date(DATE_ATOM),
    'pages' => $pages,
    'menus' => $menus,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (file_put_contents($backupPath, $backup, LOCK_EX) === false) {
    throw new RuntimeException('Unable to write backup file.');
}

$pdo->beginTransaction();
try {
    if ($menus) {
        $menuIds = array_column($menus, 'menu_id');
        $deleteChildren = $pdo->prepare('DELETE FROM tbl_menu WHERE menu_parent IN (' . implode(',', array_fill(0, count($menuIds), '?')) . ')');
        $deleteChildren->execute($menuIds);
        $deleteMenus = $pdo->prepare('DELETE FROM tbl_menu WHERE menu_id IN (' . implode(',', array_fill(0, count($menuIds), '?')) . ')');
        $deleteMenus->execute($menuIds);
    }
    if ($pageIds) {
        $deletePages = $pdo->prepare('DELETE FROM tbl_page WHERE page_id IN (' . implode(',', array_fill(0, count($pageIds), '?')) . ')');
        $deletePages->execute($pageIds);
    }

    $academicMenu = $pdo->query("SELECT menu_id FROM tbl_menu WHERE menu_parent=0 AND menu_name='Akademik' LIMIT 1")->fetchColumn();
    if ($academicMenu) {
        $children = $pdo->prepare('SELECT menu_id FROM tbl_menu WHERE menu_parent=? ORDER BY menu_order,menu_id');
        $children->execute(array($academicMenu));
        $updateOrder = $pdo->prepare('UPDATE tbl_menu SET menu_order=? WHERE menu_id=?');
        foreach ($children->fetchAll(PDO::FETCH_COLUMN) as $index => $menuId) {
            $updateOrder->execute(array($index + 1, $menuId));
        }
    }
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

echo 'Removed pages=' . count($pages) . ', menus=' . count($menus) . PHP_EOL;
echo 'Backup=' . $backupPath . PHP_EOL;
