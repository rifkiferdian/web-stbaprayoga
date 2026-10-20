<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$xmlPath = $argv[1] ?? '';
if ($xmlPath === '' || !is_file($xmlPath)) {
    fwrite(STDERR, "Usage: php download_wordpress_gallery_media.php <wordpress-export.xml>\n");
    exit(1);
}

$xml = file_get_contents($xmlPath);
if ($xml === false) {
    fwrite(STDERR, "Unable to read XML export.\n");
    exit(1);
}

preg_match_all('/<image>(\d+)<\/image>/', $xml, $matches);
$mediaIds = array_values(array_unique(array_map('intval', $matches[1] ?? [])));
sort($mediaIds, SORT_NUMERIC);

$outputDirectory = dirname(__DIR__, 2) . '/assets/uploads/wordpress-media';
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0755, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create output directory.\n");
    exit(1);
}

function fetchUrl(string $url): array
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_USERAGENT => 'STBA-Prayoga-Media-Migration/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json,image/*;q=0.9,*/*;q=0.1'],
    ]);
    $body = curl_exec($handle);
    $error = curl_error($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $contentType = (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
    curl_close($handle);

    return [$status, $contentType, $body, $error];
}

$manifest = [];
$success = 0;
$skipped = 0;
$failed = 0;
$total = count($mediaIds);

foreach ($mediaIds as $position => $mediaId) {
    $apiUrl = 'https://www.stba-prayoga.ac.id/?rest_route=/wp/v2/media/' . $mediaId;
    [$apiStatus, , $apiBody, $apiError] = fetchUrl($apiUrl);

    $entry = ['id' => $mediaId, 'api_url' => $apiUrl];
    if ($apiStatus !== 200 || !is_string($apiBody)) {
        $entry += ['status' => 'failed', 'error' => $apiError ?: 'API HTTP ' . $apiStatus];
        $manifest[] = $entry;
        $failed++;
        echo '[' . ($position + 1) . "/{$total}] {$mediaId}: API failed\n";
        continue;
    }

    $media = json_decode($apiBody, true);
    $sourceUrl = is_array($media) ? (string) ($media['source_url'] ?? '') : '';
    $mimeType = is_array($media) ? (string) ($media['mime_type'] ?? '') : '';
    if ($sourceUrl === '' || !str_starts_with($mimeType, 'image/')) {
        $entry += ['status' => 'failed', 'error' => 'No downloadable image URL', 'mime_type' => $mimeType];
        $manifest[] = $entry;
        $failed++;
        echo '[' . ($position + 1) . "/{$total}] {$mediaId}: not an image\n";
        continue;
    }

    $urlPath = (string) parse_url($sourceUrl, PHP_URL_PATH);
    $basename = basename(rawurldecode($urlPath));
    $basename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $basename) ?: 'image-' . $mediaId;
    $localName = 'wp-media-' . $mediaId . '-' . $basename;
    $localPath = $outputDirectory . '/' . $localName;
    $entry += ['source_url' => $sourceUrl, 'mime_type' => $mimeType, 'local_file' => $localName];

    if (is_file($localPath) && filesize($localPath) > 0) {
        $entry['status'] = 'existing';
        $entry['bytes'] = filesize($localPath);
        $manifest[] = $entry;
        $skipped++;
        echo '[' . ($position + 1) . "/{$total}] {$mediaId}: already exists\n";
        continue;
    }

    [$imageStatus, $downloadType, $imageBody, $downloadError] = fetchUrl($sourceUrl);
    $bytes = is_string($imageBody) ? strlen($imageBody) : 0;
    if ($imageStatus !== 200 || $bytes === 0 || $bytes > 25 * 1024 * 1024 || !str_starts_with(strtolower($downloadType), 'image/')) {
        $entry += ['status' => 'failed', 'error' => $downloadError ?: 'Image HTTP ' . $imageStatus, 'bytes' => $bytes];
        $manifest[] = $entry;
        $failed++;
        echo '[' . ($position + 1) . "/{$total}] {$mediaId}: download failed\n";
        continue;
    }

    $temporaryPath = $localPath . '.part';
    if (file_put_contents($temporaryPath, $imageBody, LOCK_EX) !== $bytes || !rename($temporaryPath, $localPath)) {
        @unlink($temporaryPath);
        $entry += ['status' => 'failed', 'error' => 'Unable to save local file'];
        $manifest[] = $entry;
        $failed++;
        echo '[' . ($position + 1) . "/{$total}] {$mediaId}: save failed\n";
        continue;
    }

    $entry += ['status' => 'downloaded', 'bytes' => $bytes];
    $manifest[] = $entry;
    $success++;
    echo '[' . ($position + 1) . "/{$total}] {$mediaId}: downloaded {$localName}\n";
}

$manifestPath = $outputDirectory . '/manifest.json';
file_put_contents($manifestPath, json_encode([
    'source_export' => basename($xmlPath),
    'generated_at' => date(DATE_ATOM),
    'total_ids' => $total,
    'downloaded' => $success,
    'existing' => $skipped,
    'failed' => $failed,
    'media' => $manifest,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Complete: downloaded={$success}, existing={$skipped}, failed={$failed}, total={$total}\n";
exit($failed > 0 ? 2 : 0);
