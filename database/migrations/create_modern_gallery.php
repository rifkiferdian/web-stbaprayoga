<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$mediaDirectory = dirname(__DIR__, 2) . '/assets/uploads/wordpress-media';
$thumbnailDirectory = $mediaDirectory . '/thumbs';
$manifestPath = $mediaDirectory . '/manifest.json';

if (!is_file($manifestPath)) {
    throw new RuntimeException('WordPress media manifest was not found.');
}
if (!is_dir($thumbnailDirectory) && !mkdir($thumbnailDirectory, 0755, true) && !is_dir($thumbnailDirectory)) {
    throw new RuntimeException('Unable to create gallery thumbnail directory.');
}

$manifest = json_decode((string) file_get_contents($manifestPath), true);
$created = 0;
foreach (($manifest['media'] ?? []) as $media) {
    if (!in_array($media['status'] ?? '', ['downloaded', 'existing'], true)) continue;
    $filename = basename((string) ($media['local_file'] ?? ''));
    $sourcePath = $mediaDirectory . '/' . $filename;
    if ($filename === '' || !is_file($sourcePath)) continue;

    $thumbnailName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
    $thumbnailPath = $thumbnailDirectory . '/' . $thumbnailName;
    if (is_file($thumbnailPath) && filesize($thumbnailPath) > 0) continue;

    $imageData = @file_get_contents($sourcePath);
    $source = is_string($imageData) ? @imagecreatefromstring($imageData) : false;
    if (!$source) continue;

    $width = imagesx($source);
    $height = imagesy($source);
    $targetWidth = min(720, $width);
    $targetHeight = max(1, (int) round($height * $targetWidth / $width));
    $target = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    imagewebp($target, $thumbnailPath, 78);
    imagedestroy($target);
    imagedestroy($source);
    $created++;
}

$pdo->beginTransaction();
try {
    $find = $pdo->prepare("SELECT menu_id FROM tbl_menu WHERE menu_parent=0 AND menu_name='Galeri' LIMIT 1");
    $find->execute();
    $menuId = $find->fetchColumn();
    if ($menuId) {
        $update = $pdo->prepare("UPDATE tbl_menu SET menu_type='Other',page_id='',menu_url='modern/galeri',menu_order=8 WHERE menu_id=?");
        $update->execute([$menuId]);
    } else {
        $pdo->exec('UPDATE tbl_menu SET menu_order=menu_order+1 WHERE menu_parent=0 AND menu_order>=8');
        $insert = $pdo->prepare("INSERT INTO tbl_menu (menu_type,page_id,menu_name,menu_url,menu_order,menu_parent) VALUES ('Other','','Galeri','modern/galeri',8,0)");
        $insert->execute();
        $menuId = (int) $pdo->lastInsertId();
    }
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

echo "Gallery ready: menu_id={$menuId}, thumbnails_created={$created}\n";
