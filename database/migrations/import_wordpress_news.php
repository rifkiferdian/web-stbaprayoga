<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__, 2);
$uploads = $root . '/assets/uploads';
$source = new PDO('mysql:host=127.0.0.1;port=3306;dbname=wordpress_stbaweb;charset=utf8mb4', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$target = new PDO('mysql:host=127.0.0.1;port=3306;dbname=web-stbaprayoga;charset=utf8mb4', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$posts = $source->query("SELECT p.ID, p.post_date, p.post_content, p.post_title, p.post_excerpt, p.post_name,
        COALESCE(NULLIF(u.display_name, ''), 'STBA Prayoga') AS publisher,
        attachment.guid AS featured_url
    FROM wp_posts p
    LEFT JOIN wp_users u ON u.ID = p.post_author
    LEFT JOIN wp_postmeta thumbnail ON thumbnail.post_id = p.ID AND thumbnail.meta_key = '_thumbnail_id'
    LEFT JOIN wp_posts attachment ON attachment.ID = CAST(thumbnail.meta_value AS UNSIGNED)
    WHERE p.post_type = 'post' AND p.post_status = 'publish' AND p.post_date >= '2020-01-01'
    ORDER BY p.post_date ASC, p.ID ASC")->fetchAll(PDO::FETCH_ASSOC);

$exists = $target->prepare('SELECT news_id, photo FROM tbl_news WHERE news_slug = ? LIMIT 1');
$updatePhoto = $target->prepare('UPDATE tbl_news SET photo = ? WHERE news_id = ?');
$insert = $target->prepare('INSERT INTO tbl_news
    (news_title, news_slug, news_content, news_content_short, news_date, photo, category_id, publisher, total_view, meta_title, meta_keyword, meta_description)
    VALUES (?, ?, ?, ?, ?, ?, 1, ?, 0, ?, ?, ?)');

function clean_wordpress_content($html)
{
    $html = preg_replace('/<!--\s*\/?wp:.*?-->/s', '', (string) $html);
    $html = preg_replace('/\[(?:caption|\/caption|gallery|embed|\/embed)[^\]]*\]/i', '', $html);
    return trim($html);
}

function make_summary($excerpt, $content)
{
    $text = trim(strip_tags(html_entity_decode($excerpt !== '' ? $excerpt : $content, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $text = preg_replace('/\s+/u', ' ', $text);
    return function_exists('mb_substr') ? mb_substr($text, 0, 260) : substr($text, 0, 260);
}

function download_featured_image($url, $postId, $uploads)
{
    if (!preg_match('#^https?://www\.stba-prayoga\.ac\.id/(?:portalbaru/)?wp-content/uploads/#i', (string) $url)) return 'no-photo1.jpg';
    $context = stream_context_create(array('http' => array('timeout' => 15, 'follow_location' => 1, 'user_agent' => 'STBA-Prayoga-Migration/1.0')));
    $data = @file_get_contents($url, false, $context);
    if ($data === false || strlen($data) === 0 || strlen($data) > 12 * 1024 * 1024) return 'no-photo1.jpg';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($data);
    $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp');
    if (!isset($extensions[$mime])) return 'no-photo1.jpg';
    $filename = 'wp-news-' . (int) $postId . '.' . $extensions[$mime];
    return file_put_contents($uploads . '/' . $filename, $data, LOCK_EX) !== false ? $filename : 'no-photo1.jpg';
}

$createdFiles = array();
$imported = 0;
$skipped = 0;
$fallbackImages = 0;
$repairedImages = 0;

try {
    $target->beginTransaction();
    foreach ($posts as $post) {
        $slug = trim($post['post_name']) !== '' ? trim($post['post_name']) : 'wordpress-post-' . $post['ID'];
        $exists->execute(array($slug));
        $existing = $exists->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['photo'] === 'no-photo1.jpg' && $post['featured_url'] !== null) {
                $photo = download_featured_image($post['featured_url'], $post['ID'], $uploads);
                if ($photo !== 'no-photo1.jpg') {
                    $updatePhoto->execute(array($photo, $existing['news_id']));
                    $createdFiles[] = $uploads . '/' . $photo;
                    $repairedImages++;
                }
            }
            $skipped++;
            continue;
        }
        $content = clean_wordpress_content($post['post_content']);
        $summary = make_summary($post['post_excerpt'], $content);
        $photo = download_featured_image($post['featured_url'], $post['ID'], $uploads);
        if ($photo === 'no-photo1.jpg') $fallbackImages++;
        else $createdFiles[] = $uploads . '/' . $photo;
        $insert->execute(array(
            $post['post_title'], $slug, $content, $summary,
            date('Y-m-d', strtotime($post['post_date'])), $photo, $post['publisher'],
            $post['post_title'], '', $summary
        ));
        $imported++;
    }
    $target->commit();
} catch (Throwable $error) {
    if ($target->inTransaction()) $target->rollBack();
    foreach ($createdFiles as $file) if (is_file($file)) unlink($file);
    fwrite(STDERR, "Migration failed: " . $error->getMessage() . PHP_EOL);
    exit(1);
}

echo "Imported: {$imported}" . PHP_EOL;
echo "Skipped existing: {$skipped}" . PHP_EOL;
echo "Fallback images: {$fallbackImages}" . PHP_EOL;
echo "Repaired existing images: {$repairedImages}" . PHP_EOL;

