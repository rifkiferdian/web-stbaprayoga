<?php
require_once __DIR__ . '/../panel-5tb4/config.php';
require_once __DIR__ . '/../panel-5tb4/functions.php';

function modern_e($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function modern_asset($filename, $fallback = 'no-photo1.jpg') {
    $safe = basename((string) $filename);
    if ($safe === '' || !is_file(__DIR__ . '/../assets/uploads/' . $safe)) $safe = $fallback;
    return BASE_URL . 'assets/uploads/' . rawurlencode($safe);
}
function modern_url($value, $fallback = '#') {
    $value = trim((string) $value);
    if ($value === '' || $value === '#') return $fallback;
    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) return $value;
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $value) || str_starts_with($value, '//')) return '#';
    return BASE_URL . ltrim($value, '/');
}
function modern_menu_name($menu) {
    return trim((string) $menu['menu_name']) !== '' ? $menu['menu_name'] : $menu['page_name'];
}
function modern_menu_link($menu) {
    $name = strtolower(trim((string) ($menu['menu_name'] ?? '')));
    if (in_array($name, array('home', 'beranda'), true)) return BASE_URL . 'modern/';
    if ($menu['menu_type'] === 'Page' && !empty($menu['page_slug'])) return BASE_URL . 'modern/page/' . rawurlencode($menu['page_slug']);
    return modern_url($menu['menu_url'], '#');
}
function modern_link_attrs($url) {
    $host = parse_url((string) $url, PHP_URL_HOST);
    $baseHost = parse_url(BASE_URL, PHP_URL_HOST);
    return $host && strcasecmp($host, (string) $baseHost) !== 0 ? ' target="_blank" rel="noopener noreferrer"' : '';
}
function modern_social_url($socials, $platform) {
    $aliases = array('youtube' => array('youtube'), 'instagram' => array('instagram'), 'x' => array('x', 'twitter'), 'whatsapp' => array('whatsapp', 'wa'));
    foreach ($socials as $social) {
        $name = strtolower(trim((string) ($social['social_name'] ?? '')));
        if (in_array($name, $aliases[$platform] ?? array(), true)) return modern_url($social['social_url'], '');
    }
    return '';
}
function modern_gallery_url($filename, $thumbnail = false) {
    $safe = basename((string) $filename);
    $directory = $thumbnail ? 'wordpress-media/thumbs/' : 'wordpress-media/';
    $path = __DIR__ . '/../assets/uploads/' . $directory . $safe;
    return $safe !== '' && is_file($path) ? BASE_URL . 'assets/uploads/' . $directory . rawurlencode($safe) : '';
}
$slug = trim((string) ($_GET['slug'] ?? ''));
$isNews = isset($_GET['type']) && $_GET['type'] === 'news';
$isNewsList = isset($_GET['type']) && $_GET['type'] === 'news-list';
$isSearch = isset($_GET['type']) && $_GET['type'] === 'search';
$isGallery = isset($_GET['type']) && $_GET['type'] === 'gallery';
$isHistory = !$isNews && $slug === 'sejarah-stba-prayoga';
$articleMeta = '';
if ($isGallery) {
    $page = array(
        'page_name' => 'Galeri Kampus',
        'page_content' => '',
        'banner' => '',
        'meta_description' => 'Dokumentasi kegiatan, fasilitas, prestasi, dan kehidupan kampus STBA Prayoga Padang.'
    );
} elseif ($isSearch) {
    $page = array(
        'page_name' => 'Pencarian',
        'page_content' => '',
        'banner' => '',
        'meta_description' => 'Cari halaman dan berita di website STBA Prayoga Padang.'
    );
} elseif ($isNewsList) {
    $page = array(
        'page_name' => 'Berita & Kegiatan',
        'page_content' => '',
        'banner' => '',
        'meta_description' => 'Berita, kegiatan akademik, dan informasi terbaru dari STBA Prayoga Padang.'
    );
} elseif ($isNews) {
    $pageStatement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_slug=? LIMIT 1");
    $pageStatement->execute(array($slug));
    $newsItem = $pageStatement->fetch(PDO::FETCH_ASSOC);
    $page = $newsItem ? array('page_name' => $newsItem['news_title'], 'page_content' => $newsItem['news_content'], 'banner' => $newsItem['photo'], 'meta_description' => $newsItem['meta_description']) : false;
    if ($newsItem) $articleMeta = trim($newsItem['news_date'] . ($newsItem['publisher'] !== '' ? ' · ' . $newsItem['publisher'] : ''));
} else {
    $pageStatement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? AND status='Active' LIMIT 1");
    $pageStatement->execute(array($slug));
    $page = $pageStatement->fetch(PDO::FETCH_ASSOC);
}
if (!$page) {
    http_response_code(404);
    $page = array('page_name' => 'Halaman tidak ditemukan', 'page_content' => '<p>Halaman yang Anda cari tidak tersedia.</p>', 'banner' => '');
}

$settings = $pdo->query("SELECT * FROM tbl_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$searchQuery = function_exists('mb_substr') ? mb_substr($searchQuery, 0, 100) : substr($searchQuery, 0, 100);
$searchResults = array();
if ($isSearch && (function_exists('mb_strlen') ? mb_strlen($searchQuery) : strlen($searchQuery)) >= 2) {
    $searchTerm = '%' . $searchQuery . '%';
    $pageSearch = $pdo->prepare("SELECT page_name AS title, page_slug AS slug, page_content AS content, 'Halaman' AS result_type FROM tbl_page WHERE status='Active' AND (page_name LIKE :page_title OR page_content LIKE :page_content) ORDER BY page_name ASC LIMIT 30");
    $pageSearch->execute(array(':page_title' => $searchTerm, ':page_content' => $searchTerm));
    $newsSearch = $pdo->prepare("SELECT news_title AS title, news_slug AS slug, COALESCE(NULLIF(news_content_short, ''), news_content) AS content, 'Berita' AS result_type FROM tbl_news WHERE news_title LIKE :news_title OR news_content_short LIKE :news_short OR news_content LIKE :news_content ORDER BY news_id DESC LIMIT 30");
    $newsSearch->execute(array(':news_title' => $searchTerm, ':news_short' => $searchTerm, ':news_content' => $searchTerm));
    $searchResults = array_merge($pageSearch->fetchAll(PDO::FETCH_ASSOC), $newsSearch->fetchAll(PDO::FETCH_ASSOC));
}
$newsItems = array();
$galleryItems = array();
if ($isGallery) {
    $manifestPath = __DIR__ . '/../assets/uploads/wordpress-media/manifest.json';
    $manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), true) : array();
    foreach (($manifest['media'] ?? array()) as $media) {
        if (!in_array($media['status'] ?? '', array('downloaded', 'existing'), true)) continue;
        $filename = basename((string) ($media['local_file'] ?? ''));
        $fullUrl = modern_gallery_url($filename);
        $thumbnailName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $thumbnailUrl = modern_gallery_url($thumbnailName, true);
        if ($fullUrl === '' || $thumbnailUrl === '') continue;
        $galleryItems[] = array(
            'id' => (int) ($media['id'] ?? 0),
            'full_url' => $fullUrl,
            'thumbnail_url' => $thumbnailUrl,
        );
    }
    usort($galleryItems, fn($left, $right) => $right['id'] <=> $left['id']);
}
$newsPage = 1;
$newsTotalPages = 1;
if ($isNewsList) {
    $newsPerPage = 9;
    $newsTotal = (int) $pdo->query("SELECT COUNT(*) FROM tbl_news")->fetchColumn();
    $newsTotalPages = max(1, (int) ceil($newsTotal / $newsPerPage));
    $newsPage = max(1, min($newsTotalPages, (int) ($_GET['page'] ?? 1)));
    $newsOffset = ($newsPage - 1) * $newsPerPage;
    $newsStatement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC LIMIT :limit OFFSET :offset");
    $newsStatement->bindValue(':limit', $newsPerPage, PDO::PARAM_INT);
    $newsStatement->bindValue(':offset', $newsOffset, PDO::PARAM_INT);
    $newsStatement->execute();
    $newsItems = $newsStatement->fetchAll(PDO::FETCH_ASSOC);
}
$allMenus = $pdo->query("SELECT m.*, p.page_name, p.page_slug FROM tbl_menu m LEFT JOIN tbl_page p ON m.page_id=p.page_id ORDER BY m.menu_parent ASC, m.menu_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$menus = array();
$menuChildren = array();
foreach ($allMenus as $menuItem) {
    if ((int) $menuItem['menu_parent'] === 0) $menus[] = $menuItem;
    else $menuChildren[(int) $menuItem['menu_parent']][] = $menuItem;
}
$socials = $pdo->query("SELECT * FROM tbl_social WHERE social_url <> '' ORDER BY social_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo modern_e($page['page_name']); ?> · STBA Prayoga</title>
  <meta name="description" content="<?php echo modern_e($page['meta_description'] ?? strip_tags($page['page_content'])); ?>">
  <link rel="icon" href="<?php echo modern_asset($settings['favicon']); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>modern/assets/css/app.css?v=<?php echo filemtime(__DIR__ . '/assets/css/app.css'); ?>">
  <script>document.documentElement.classList.add('js');</script>
  <script src="<?php echo BASE_URL; ?>modern/assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/assets/js/app.js'); ?>" defer></script>
</head>
<body>
  <?php require __DIR__ . '/loader.php'; ?>
  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-3">Lewati ke konten</a>
  <div class="fixed inset-x-0 top-0 z-[60] h-9 bg-brand-dark text-white/75">
    <div class="container-site flex h-full items-center justify-between text-[11px] font-semibold tracking-wide">
      <div class="flex min-w-0 items-center gap-4 sm:gap-6">
        <a href="mailto:<?php echo modern_e($settings['contact_email']); ?>" class="flex min-w-0 items-center gap-2 transition hover:text-white"><svg class="h-3.5 w-3.5 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6.5 12 13l9-6.5"/><rect x="3" y="5" width="18" height="14" rx="2"/></svg><span class="truncate"><?php echo modern_e($settings['contact_email']); ?></span></a>
        <a href="tel:<?php echo modern_e(preg_replace('/[^0-9+]/', '', $settings['contact_phone'])); ?>" class="hidden items-center gap-2 transition hover:text-white sm:flex"><svg class="h-3.5 w-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-4-2-2 2c-3.6-1.5-6.5-4.4-8-8l2-2-2-4Z"/></svg><?php echo modern_e($settings['contact_phone']); ?></a>
      </div>
      <div class="flex shrink-0 items-center gap-1" aria-label="Media sosial">
        <?php $socialUrl = modern_social_url($socials, 'youtube'); ?><a href="<?php echo modern_e($socialUrl ?: '#'); ?>"<?php if ($socialUrl): ?> target="_blank" rel="noopener noreferrer"<?php else: ?> aria-disabled="true"<?php endif; ?> class="grid h-7 w-7 place-items-center rounded-full text-white transition hover:bg-white/10 hover:text-red-300<?php echo $socialUrl ? '' : ' pointer-events-none opacity-40'; ?>" aria-label="YouTube" title="YouTube"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z"/></svg></a>
        <?php $socialUrl = modern_social_url($socials, 'instagram'); ?><a href="<?php echo modern_e($socialUrl ?: '#'); ?>"<?php if ($socialUrl): ?> target="_blank" rel="noopener noreferrer"<?php else: ?> aria-disabled="true"<?php endif; ?> class="grid h-7 w-7 place-items-center rounded-full text-white transition hover:bg-white/10 hover:text-pink-300<?php echo $socialUrl ? '' : ' pointer-events-none opacity-40'; ?>" aria-label="Instagram<?php echo $socialUrl ? '' : ' — tautan belum tersedia'; ?>" title="Instagram<?php echo $socialUrl ? '' : ' — tautan belum tersedia'; ?>"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
        <?php $socialUrl = modern_social_url($socials, 'x'); ?><a href="<?php echo modern_e($socialUrl ?: '#'); ?>"<?php if ($socialUrl): ?> target="_blank" rel="noopener noreferrer"<?php else: ?> aria-disabled="true"<?php endif; ?> class="grid h-7 w-7 place-items-center rounded-full text-white transition hover:bg-white/10 hover:text-emerald-200<?php echo $socialUrl ? '' : ' pointer-events-none opacity-40'; ?>" aria-label="X<?php echo $socialUrl ? '' : ' — tautan belum tersedia'; ?>" title="X<?php echo $socialUrl ? '' : ' — tautan belum tersedia'; ?>"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23.2 22H17l-4.9-6.4L6.5 22H3.3l7.3-8.4L2.8 2h6.4l4.4 5.8L18.9 2Zm-1.1 17.9h1.7L8.3 4H6.5l11.3 15.9Z"/></svg></a>
        <?php $socialUrl = modern_social_url($socials, 'whatsapp'); ?><a href="<?php echo modern_e($socialUrl ?: '#'); ?>"<?php if ($socialUrl): ?> target="_blank" rel="noopener noreferrer"<?php else: ?> aria-disabled="true"<?php endif; ?> class="grid h-7 w-7 place-items-center rounded-full text-white transition hover:bg-white/10 hover:text-emerald-300<?php echo $socialUrl ? '' : ' pointer-events-none opacity-40'; ?>" aria-label="WhatsApp" title="WhatsApp"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0 11.9 11.9 0 0 0 1.8 17.8L.1 24l6.4-1.7A11.9 11.9 0 0 0 24 11.9c0-3.2-1.2-6.2-3.5-8.4ZM12.1 20a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.8 1 1-3.7-.2-.4a9.8 9.8 0 1 1 8.4 4.7Zm5.4-7.3c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2l-.9 1.1c-.2.2-.3.2-.6.1a8 8 0 0 1-2.4-1.5A9 9 0 0 1 9.1 9.5c-.2-.3 0-.5.1-.6l.5-.6.3-.5c.1-.2.1-.4 0-.6l-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.9 5.2.8.3 1.5.5 2 .6.8.3 1.6.2 2.2.1.7-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.2-.3-.3-.6-.4l-1.2-.6Z"/></svg></a>
      </div>
    </div>
  </div>
  <header data-site-header data-header-solid class="is-scrolled fixed inset-x-0 top-9 z-50 border-b border-white/15 text-white">
    <div class="container-site flex h-20 items-center justify-between">
      <a href="<?php echo BASE_URL; ?>modern/" class="flex items-center gap-3" aria-label="Beranda"><span class="grid h-12 w-12 place-items-center rounded-xl bg-white shadow-lg"><img src="<?php echo modern_asset($settings['logo']); ?>" class="h-10 w-10 object-contain" alt="Logo"></span><span><strong class="block text-sm tracking-[.12em]">STBA PRAYOGA</strong><span class="text-[11px] text-white/65">Padang · Sumatera Barat</span></span></a>
      <nav class="hidden items-center gap-5 xl:flex xl:gap-7" aria-label="Navigasi utama"><?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; $children = $menuChildren[(int) $menu['menu_id']] ?? array(); ?><div class="group relative"><a class="flex items-center gap-1.5 py-7 text-sm font-semibold text-white/80 transition hover:text-white" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?><?php if ($children): ?><svg class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg><?php endif; ?></a><?php if ($children): ?><div class="invisible absolute left-0 top-full max-h-[calc(100vh-8rem)] w-72 translate-y-2 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 text-ink opacity-0 shadow-2xl shadow-brand-dark/15 transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100"><?php foreach ($children as $child): $childLink = modern_menu_link($child); ?><a class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-brand-soft hover:text-brand" href="<?php echo modern_e($childLink); ?>"<?php echo modern_link_attrs($childLink); ?>><?php echo modern_e(modern_menu_name($child)); ?></a><?php endforeach; ?></div><?php endif; ?></div><?php endforeach; ?></nav>
      <div class="flex items-center gap-3"><button type="button" data-search-open class="rounded-full border border-white/25 p-3 transition hover:bg-white/10" aria-label="Buka pencarian"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></button><button data-menu-button class="rounded-full border border-white/25 p-3 xl:hidden" aria-expanded="false" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button></div>
    </div>
    <nav data-mobile-menu class="container-site hidden max-h-[calc(100vh-8rem)] overflow-y-auto border-t border-white/15 bg-brand-dark/95 py-5 xl:hidden" aria-label="Navigasi seluler"><?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; $children = $menuChildren[(int) $menu['menu_id']] ?? array(); ?><?php if ($children): ?><details class="border-b border-white/10"><summary class="flex cursor-pointer list-none items-center justify-between py-3 text-sm font-semibold"><?php echo modern_e($name); ?><span class="text-emerald-300">＋</span></summary><div class="mb-3 rounded-xl bg-white/5 px-3"><?php foreach ($children as $child): $childLink = modern_menu_link($child); ?><a class="block border-b border-white/10 py-3 text-sm text-white/75 last:border-0" href="<?php echo modern_e($childLink); ?>"<?php echo modern_link_attrs($childLink); ?>><?php echo modern_e(modern_menu_name($child)); ?></a><?php endforeach; ?></div></details><?php else: ?><a class="block border-b border-white/10 py-3 text-sm font-semibold" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endif; ?><?php endforeach; ?></nav>
  </header>

  <div data-search-overlay class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-labelledby="search-title">
    <button type="button" data-search-close class="absolute inset-0 bg-brand-dark/80 backdrop-blur-md" aria-label="Tutup pencarian"></button>
    <div class="container-site relative flex min-h-full items-start justify-center px-4 pt-24 sm:pt-36">
      <div class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl sm:p-9">
        <div class="flex items-start justify-between gap-5"><div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-brand">Pencarian situs</p><h2 id="search-title" class="mt-2 font-display text-3xl text-brand-dark">Apa yang ingin Anda cari?</h2></div><button type="button" data-search-close class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-soft hover:text-brand" aria-label="Tutup pencarian"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
        <form action="<?php echo BASE_URL; ?>modern/search" method="get" class="mt-7 flex gap-3"><label class="sr-only" for="site-search-page">Kata pencarian</label><div class="relative min-w-0 flex-1"><svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input data-search-input id="site-search-page" name="q" type="search" minlength="2" maxlength="100" required placeholder="Cari informasi di STBA Prayoga…" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm text-ink outline-none transition focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/10"></div><button class="rounded-2xl bg-brand px-6 py-4 text-sm font-extrabold text-white transition hover:bg-brand-dark">Cari</button></form>
        <p class="mt-4 text-xs leading-6 text-slate-500">Cari profil kampus, program studi, PMB, akreditasi, dosen, serta berita dan kegiatan terbaru.</p>
      </div>
    </div>
  </div>

  <main id="main" class="min-h-screen bg-sand pt-29">
    <section class="relative overflow-hidden bg-brand-dark py-20 text-white sm:py-24">
      <?php if (!empty($page['banner'])): ?><img src="<?php echo modern_asset($page['banner']); ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25"><?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/90 to-brand/50"></div>
      <div class="container-site relative"><nav class="mb-6 flex items-center gap-2 text-xs font-bold uppercase tracking-[.14em] text-emerald-100/70" aria-label="Breadcrumb"><a href="<?php echo BASE_URL; ?>modern/" class="transition hover:text-white">Beranda</a><span aria-hidden="true">/</span><span class="text-emerald-200"><?php echo ($isNews || $isNewsList) ? 'Berita' : modern_e($page['page_name']); ?></span></nav><h1 class="max-w-5xl font-display text-5xl leading-tight sm:text-6xl"><?php echo modern_e($page['page_name']); ?></h1><?php if ($isGallery): ?><p class="mt-5 max-w-2xl text-base leading-7 text-emerald-50/75">Merekam kegiatan, fasilitas, prestasi, dan kebersamaan sivitas akademika STBA Prayoga.</p><?php elseif ($isSearch): ?><p class="mt-5 max-w-2xl text-base leading-7 text-emerald-50/75">Temukan halaman, informasi akademik, dan kabar terbaru STBA Prayoga.</p><?php elseif ($isNewsList): ?><p class="mt-5 max-w-2xl text-base leading-7 text-emerald-50/75">Ikuti berita, kegiatan akademik, dan cerita terbaru dari lingkungan STBA Prayoga Padang.</p><?php elseif ($articleMeta !== ''): ?><p class="mt-5 text-sm font-bold text-emerald-100/75"><?php echo modern_e($articleMeta); ?></p><?php endif; ?><div class="mt-7 h-1 w-20 rounded-full bg-accent"></div></div>
    </section>
    <?php if ($isGallery): ?>
    <section class="relative overflow-hidden py-16 sm:py-24">
      <div class="pointer-events-none absolute -right-28 top-10 h-72 w-72 rounded-full bg-brand-soft blur-3xl"></div>
      <div class="container-site relative">
        <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="section-kicker">Dokumentasi kampus</p><h2 class="section-title">Momen dari STBA Prayoga</h2><p class="section-copy">Jelajahi dokumentasi kegiatan mahasiswa, fasilitas pembelajaran, pengabdian, serta perjalanan kampus.</p></div><span class="w-fit rounded-full bg-brand-soft px-4 py-2 text-xs font-extrabold text-brand"><?php echo count($galleryItems); ?> foto</span></div>
        <?php if ($galleryItems): ?><div class="grid items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <?php foreach ($galleryItems as $index => $photo): $photoLabel = 'Foto galeri ' . ($index + 1); ?>
          <button type="button" class="reveal group relative aspect-[4/3] w-full overflow-hidden rounded-[1.4rem] bg-brand-dark text-left shadow-md shadow-brand-dark/10 ring-1 ring-brand-dark/5 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/20 focus:outline-none focus:ring-4 focus:ring-brand/30" data-gallery-item data-gallery-src="<?php echo modern_e($photo['full_url']); ?>" aria-label="Buka <?php echo modern_e($photoLabel); ?>" style="transition-delay:<?php echo ($index % 4) * 45; ?>ms">
            <img src="<?php echo modern_e($photo['thumbnail_url']); ?>" alt="<?php echo modern_e($photoLabel); ?>" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
            <span class="absolute inset-0 bg-gradient-to-t from-brand-dark/40 via-transparent to-transparent opacity-70 transition duration-300 group-hover:opacity-100"></span>
            <span class="absolute bottom-4 right-4 grid h-10 w-10 place-items-center rounded-full border border-white/25 bg-brand-dark/45 text-base text-white opacity-0 backdrop-blur transition group-hover:bg-accent group-hover:text-brand-dark group-hover:opacity-100 group-focus:opacity-100" aria-hidden="true">↗</span>
          </button>
          <?php endforeach; ?>
        </div><?php else: ?><div class="rounded-3xl border border-emerald-900/5 bg-white p-12 text-center shadow-sm"><h2 class="font-display text-3xl text-brand-dark">Galeri belum tersedia</h2><p class="mt-3 text-slate-600">Dokumentasi kampus akan ditampilkan di halaman ini.</p></div><?php endif; ?>
      </div>
    </section>
    <div data-gallery-dialog class="fixed inset-0 z-[120] hidden items-center justify-center bg-brand-dark/95 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-label="Tampilan foto galeri">
      <button type="button" data-gallery-close class="absolute inset-0" aria-label="Tutup foto"></button>
      <figure class="relative z-10 max-h-[92vh] max-w-6xl"><img data-gallery-image src="" alt="Foto galeri" class="max-h-[86vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl"><button type="button" data-gallery-close class="absolute -right-2 -top-12 grid h-10 w-10 place-items-center rounded-full bg-white text-xl text-brand-dark shadow-lg" aria-label="Tutup foto">×</button></figure>
    </div>
    <?php elseif ($isSearch): ?>
    <section class="relative overflow-hidden py-16 sm:py-24"><div class="pointer-events-none absolute -right-28 top-10 h-72 w-72 rounded-full bg-brand-soft blur-3xl"></div><div class="container-site relative max-w-5xl">
      <form action="<?php echo BASE_URL; ?>modern/search" method="get" class="flex flex-col gap-3 rounded-3xl border border-emerald-900/5 bg-white p-5 shadow-xl shadow-brand-dark/5 sm:flex-row sm:p-7"><label class="sr-only" for="search-query">Kata pencarian</label><div class="relative min-w-0 flex-1"><svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input id="search-query" name="q" type="search" minlength="2" maxlength="100" required value="<?php echo modern_e($searchQuery); ?>" placeholder="Cari informasi di STBA Prayoga…" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm text-ink outline-none transition focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/10"></div><button class="rounded-2xl bg-brand px-8 py-4 text-sm font-extrabold text-white transition hover:bg-brand-dark">Cari</button></form>
      <?php if ($searchQuery === ''): ?><div class="mt-8 rounded-3xl border border-emerald-900/5 bg-white p-9 text-center"><h2 class="font-display text-3xl text-brand-dark">Mulai pencarian</h2><p class="mt-3 text-slate-600">Masukkan sedikitnya dua karakter untuk mencari seluruh konten website.</p></div><?php elseif ((function_exists('mb_strlen') ? mb_strlen($searchQuery) : strlen($searchQuery)) < 2): ?><div class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-7 text-amber-900">Kata pencarian harus terdiri dari sedikitnya dua karakter.</div><?php elseif (!$searchResults): ?><div class="mt-8 rounded-3xl border border-emerald-900/5 bg-white p-9 text-center"><h2 class="font-display text-3xl text-brand-dark">Tidak ada hasil ditemukan</h2><p class="mt-3 text-slate-600">Coba gunakan kata yang lebih umum atau periksa kembali ejaannya.</p></div><?php else: ?><div class="mt-10 flex items-end justify-between gap-5"><div><p class="section-kicker">Hasil pencarian</p><h2 class="font-display text-3xl text-brand-dark">Ditemukan <?php echo count($searchResults); ?> hasil</h2></div><p class="hidden text-sm text-slate-500 sm:block">untuk “<?php echo modern_e($searchQuery); ?>”</p></div><div class="mt-6 space-y-4"><?php foreach ($searchResults as $result): $resultUrl = $result['result_type'] === 'Berita' ? BASE_URL . 'modern/news/' . rawurlencode($result['slug']) : BASE_URL . 'modern/page/' . rawurlencode($result['slug']); ?><article class="group rounded-3xl border border-emerald-900/5 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-7"><div class="flex items-start justify-between gap-5"><div><span class="inline-flex rounded-full bg-brand-soft px-3 py-1 text-[10px] font-extrabold uppercase tracking-[.14em] text-brand"><?php echo modern_e($result['result_type']); ?></span><h3 class="mt-3 font-display text-2xl text-brand-dark"><a href="<?php echo modern_e($resultUrl); ?>" class="transition hover:text-brand"><?php echo modern_e($result['title']); ?></a></h3><p class="mt-3 line-clamp-2 text-sm leading-7 text-slate-600"><?php echo modern_e(strip_tags($result['content'])); ?></p></div><a href="<?php echo modern_e($resultUrl); ?>" class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-soft font-bold text-brand transition group-hover:bg-brand group-hover:text-white" aria-label="Buka <?php echo modern_e($result['title']); ?>">→</a></div></article><?php endforeach; ?></div><?php endif; ?>
    </div></section>
    <?php elseif ($isNewsList): ?>
    <section class="relative overflow-hidden py-16 sm:py-24">
      <div class="pointer-events-none absolute -right-28 top-10 h-72 w-72 rounded-full bg-brand-soft blur-3xl"></div>
      <div class="container-site relative">
        <?php if ($newsItems): ?><div class="grid gap-7 md:grid-cols-2 xl:grid-cols-3">
          <?php foreach ($newsItems as $index => $article): ?>
          <article class="reveal card-lift group overflow-hidden rounded-[1.75rem] border border-emerald-900/5 bg-white shadow-sm" style="transition-delay:<?php echo ($index % 3) * 70; ?>ms">
            <a href="<?php echo BASE_URL; ?>modern/news/<?php echo rawurlencode($article['news_slug']); ?>" class="block overflow-hidden bg-brand-soft"><img src="<?php echo modern_asset($article['photo']); ?>" alt="<?php echo modern_e($article['news_title']); ?>" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy"></a>
            <div class="p-7"><time class="text-[11px] font-extrabold uppercase tracking-[.14em] text-brand"><?php echo modern_e($article['news_date']); ?></time><h2 class="mt-3 font-display text-2xl leading-snug text-brand-dark"><a href="<?php echo BASE_URL; ?>modern/news/<?php echo rawurlencode($article['news_slug']); ?>" class="transition hover:text-brand"><?php echo modern_e($article['news_title']); ?></a></h2><p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-600"><?php echo modern_e(strip_tags($article['news_content_short'] ?: $article['news_content'])); ?></p><a href="<?php echo BASE_URL; ?>modern/news/<?php echo rawurlencode($article['news_slug']); ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-extrabold text-brand">Baca selengkapnya <span class="transition group-hover:translate-x-1">→</span></a></div>
          </article>
          <?php endforeach; ?>
        </div><?php else: ?><div class="rounded-3xl border border-emerald-900/5 bg-white p-12 text-center shadow-sm"><h2 class="font-display text-3xl text-brand-dark">Belum ada berita</h2><p class="mt-3 text-slate-600">Berita terbaru akan ditampilkan di halaman ini.</p></div><?php endif; ?>
        <?php if ($newsTotalPages > 1): ?><nav class="mt-14 flex flex-wrap items-center justify-center gap-2" aria-label="Navigasi halaman berita"><?php if ($newsPage > 1): ?><a href="<?php echo BASE_URL; ?>modern/berita?page=<?php echo $newsPage - 1; ?>" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft">← Sebelumnya</a><?php endif; ?><?php for ($number = 1; $number <= $newsTotalPages; $number++): ?><a href="<?php echo BASE_URL; ?>modern/berita?page=<?php echo $number; ?>" class="grid h-10 w-10 place-items-center rounded-full text-sm font-extrabold transition <?php echo $number === $newsPage ? 'bg-brand text-white' : 'border border-slate-300 text-brand hover:border-brand hover:bg-brand-soft'; ?>"<?php echo $number === $newsPage ? ' aria-current="page"' : ''; ?>><?php echo $number; ?></a><?php endfor; ?><?php if ($newsPage < $newsTotalPages): ?><a href="<?php echo BASE_URL; ?>modern/berita?page=<?php echo $newsPage + 1; ?>" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft">Berikutnya →</a><?php endif; ?></nav><?php endif; ?>
      </div>
    </section>
    <?php else: ?>
    <section class="relative overflow-hidden py-12 sm:py-20"><div class="pointer-events-none absolute -right-28 top-10 h-72 w-72 rounded-full bg-brand-soft blur-3xl"></div><div class="container-site relative"><article class="modern-content mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-emerald-900/5 bg-white p-7 shadow-xl shadow-brand-dark/5 sm:p-10 lg:p-14<?php echo $isHistory ? ' history-content' : ''; ?>"><?php if ($isNews && !empty($page['banner'])): ?><figure class="mb-10 overflow-hidden rounded-3xl bg-brand-soft"><img src="<?php echo modern_asset($page['banner']); ?>" alt="<?php echo modern_e($page['page_name']); ?>" class="h-auto max-h-[620px] w-full object-cover"></figure><?php endif; ?><?php if ($isHistory): ?><div class="history-intro"><span>Jejak perjalanan</span><h2>Dari ABA Prayoga menuju STBA Prayoga</h2><p>Menelusuri perjalanan pendidikan bahasa di Padang sejak tahun 1971.</p></div><?php endif; ?><?php echo sanitize_rich_html($page['page_content']); ?></article></div></section>
    <?php endif; ?>
  </main>

  <footer id="contact" class="bg-[#062d20] py-14 text-white/70">
    <div class="container-site grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.2fr_.8fr_.8fr]"><div><img src="<?php echo modern_asset($settings['logo']); ?>" alt="Logo" class="h-14 w-14 rounded-xl bg-white object-contain p-1"><p class="mt-5 max-w-md text-sm leading-7"><?php echo modern_e(strip_tags($settings['footer_about'])); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Kontak</h2><p class="mt-4 text-sm leading-7"><?php echo modern_e(strip_tags($settings['contact_address'])); ?><br><?php echo modern_e($settings['contact_phone']); ?><br><?php echo modern_e($settings['contact_email']); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Ikuti kami</h2><div class="mt-4 flex flex-wrap gap-3"><?php foreach ($socials as $social): ?><a href="<?php echo modern_e(modern_url($social['social_url'])); ?>" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-xs font-bold hover:bg-white/10"><?php echo modern_e($social['social_name']); ?></a><?php endforeach; ?></div></div></div>
    <div class="container-site pt-7 text-xs"><?php echo modern_e(strip_tags($settings['footer_copyright'])); ?></div>
  </footer>
</body>
</html>
