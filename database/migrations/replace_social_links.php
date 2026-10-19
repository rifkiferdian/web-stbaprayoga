<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../panel-5tb4/config.php';

$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM tbl_social");
    $insert = $pdo->prepare("INSERT INTO tbl_social (social_name,social_url,social_icon) VALUES (?,?,?)");
    $insert->execute(array('YouTube', 'https://www.youtube.com/@stbaprayoga_official', 'fa fa-youtube-play'));
    $insert->execute(array('WhatsApp', 'https://wa.me/628122521188', 'fa fa-whatsapp'));
    $pdo->commit();
    echo "Tautan media sosial STBA Prayoga berhasil diperbarui.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
