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

$slug = trim((string) ($_GET['slug'] ?? ''));
$isNews = isset($_GET['type']) && $_GET['type'] === 'news';
$isHistory = !$isNews && $slug === 'sejarah-stba-prayoga';
$articleMeta = '';
if ($isNews) {
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
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>modern/assets/css/app.css">
  <script src="<?php echo BASE_URL; ?>modern/assets/js/app.js" defer></script>
</head>
<body>
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
      <div class="flex items-center gap-3"><a href="<?php echo BASE_URL; ?>search" class="hidden rounded-full border border-white/25 p-3 transition hover:bg-white/10 sm:block" aria-label="Cari"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></a><button data-menu-button class="rounded-full border border-white/25 p-3 xl:hidden" aria-expanded="false" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button></div>
    </div>
    <nav data-mobile-menu class="container-site hidden max-h-[calc(100vh-8rem)] overflow-y-auto border-t border-white/15 bg-brand-dark/95 py-5 xl:hidden" aria-label="Navigasi seluler"><?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; $children = $menuChildren[(int) $menu['menu_id']] ?? array(); ?><?php if ($children): ?><details class="border-b border-white/10"><summary class="flex cursor-pointer list-none items-center justify-between py-3 text-sm font-semibold"><?php echo modern_e($name); ?><span class="text-emerald-300">＋</span></summary><div class="mb-3 rounded-xl bg-white/5 px-3"><?php foreach ($children as $child): $childLink = modern_menu_link($child); ?><a class="block border-b border-white/10 py-3 text-sm text-white/75 last:border-0" href="<?php echo modern_e($childLink); ?>"<?php echo modern_link_attrs($childLink); ?>><?php echo modern_e(modern_menu_name($child)); ?></a><?php endforeach; ?></div></details><?php else: ?><a class="block border-b border-white/10 py-3 text-sm font-semibold" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endif; ?><?php endforeach; ?></nav>
  </header>

  <main id="main" class="min-h-screen bg-sand pt-29">
    <section class="relative overflow-hidden bg-brand-dark py-20 text-white sm:py-24">
      <?php if (!empty($page['banner'])): ?><img src="<?php echo modern_asset($page['banner']); ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25"><?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/90 to-brand/50"></div>
      <div class="container-site relative"><nav class="mb-6 flex items-center gap-2 text-xs font-bold uppercase tracking-[.14em] text-emerald-100/70" aria-label="Breadcrumb"><a href="<?php echo BASE_URL; ?>modern/" class="transition hover:text-white">Beranda</a><span aria-hidden="true">/</span><span class="text-emerald-200"><?php echo $isNews ? 'Berita' : modern_e($page['page_name']); ?></span></nav><h1 class="max-w-5xl font-display text-5xl leading-tight sm:text-6xl"><?php echo modern_e($page['page_name']); ?></h1><?php if ($articleMeta !== ''): ?><p class="mt-5 text-sm font-bold text-emerald-100/75"><?php echo modern_e($articleMeta); ?></p><?php endif; ?><div class="mt-7 h-1 w-20 rounded-full bg-accent"></div></div>
    </section>
    <section class="relative overflow-hidden py-12 sm:py-20"><div class="pointer-events-none absolute -right-28 top-10 h-72 w-72 rounded-full bg-brand-soft blur-3xl"></div><div class="container-site relative"><article class="modern-content mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-emerald-900/5 bg-white p-7 shadow-xl shadow-brand-dark/5 sm:p-10 lg:p-14<?php echo $isHistory ? ' history-content' : ''; ?>"><?php if ($isNews && !empty($page['banner'])): ?><figure class="mb-10 overflow-hidden rounded-3xl bg-brand-soft"><img src="<?php echo modern_asset($page['banner']); ?>" alt="<?php echo modern_e($page['page_name']); ?>" class="h-auto max-h-[620px] w-full object-cover"></figure><?php endif; ?><?php if ($isHistory): ?><div class="history-intro"><span>Jejak perjalanan</span><h2>Dari ABA Prayoga menuju STBA Prayoga</h2><p>Menelusuri perjalanan pendidikan bahasa di Padang sejak tahun 1971.</p></div><?php endif; ?><?php echo sanitize_rich_html($page['page_content']); ?></article></div></section>
  </main>

  <footer id="contact" class="bg-[#062d20] py-14 text-white/70">
    <div class="container-site grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.2fr_.8fr_.8fr]"><div><img src="<?php echo modern_asset($settings['logo']); ?>" alt="Logo" class="h-14 w-14 rounded-xl bg-white object-contain p-1"><p class="mt-5 max-w-md text-sm leading-7"><?php echo modern_e(strip_tags($settings['footer_about'])); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Kontak</h2><p class="mt-4 text-sm leading-7"><?php echo modern_e(strip_tags($settings['contact_address'])); ?><br><?php echo modern_e($settings['contact_phone']); ?><br><?php echo modern_e($settings['contact_email']); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Ikuti kami</h2><div class="mt-4 flex flex-wrap gap-3"><?php foreach ($socials as $social): ?><a href="<?php echo modern_e(modern_url($social['social_url'])); ?>" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-xs font-bold hover:bg-white/10"><?php echo modern_e($social['social_name']); ?></a><?php endforeach; ?></div></div></div>
    <div class="container-site pt-7 text-xs"><?php echo modern_e(strip_tags($settings['footer_copyright'])); ?></div>
  </footer>
</body>
</html>
