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
    return preg_match('#^(https?://|/)#i', $value) ? $value : '#';
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

$slug = trim((string) ($_GET['slug'] ?? ''));
$pageStatement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? AND status='Active' LIMIT 1");
$pageStatement->execute(array($slug));
$page = $pageStatement->fetch(PDO::FETCH_ASSOC);
if (!$page) {
    http_response_code(404);
    $page = array('page_name' => 'Halaman tidak ditemukan', 'page_content' => '<p>Halaman yang Anda cari tidak tersedia.</p>', 'banner' => '');
}

$settings = $pdo->query("SELECT * FROM tbl_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$menus = $pdo->query("SELECT m.*, p.page_name, p.page_slug FROM tbl_menu m LEFT JOIN tbl_page p ON m.page_id=p.page_id WHERE m.menu_parent=0 ORDER BY m.menu_order ASC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
$socials = $pdo->query("SELECT * FROM tbl_social WHERE social_url <> '' ORDER BY social_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo modern_e($page['page_name']); ?> · STBA Prayoga</title>
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
      <a href="mailto:<?php echo modern_e($settings['contact_email']); ?>" class="truncate transition hover:text-white"><?php echo modern_e($settings['contact_email']); ?></a>
      <div class="flex items-center gap-4"><span class="hidden uppercase tracking-[.14em] md:inline">Kampus Bahasa · Padang</span><a href="<?php echo BASE_URL; ?>panel-5tb4/login.php" class="border-l border-white/20 pl-4 text-white">Portal Admin</a></div>
    </div>
  </div>
  <header data-site-header data-header-solid class="is-scrolled fixed inset-x-0 top-9 z-50 border-b border-white/15 text-white">
    <div class="container-site flex h-20 items-center justify-between">
      <a href="<?php echo BASE_URL; ?>modern/" class="flex items-center gap-3" aria-label="Beranda"><span class="grid h-12 w-12 place-items-center rounded-xl bg-white shadow-lg"><img src="<?php echo modern_asset($settings['logo']); ?>" class="h-10 w-10 object-contain" alt="Logo"></span><span><strong class="block text-sm tracking-[.12em]">STBA PRAYOGA</strong><span class="text-[11px] text-white/65">Padang · Sumatera Barat</span></span></a>
      <nav class="hidden items-center gap-7 lg:flex" aria-label="Navigasi utama"><?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; ?><a class="text-sm font-semibold text-white/80 transition hover:text-white" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endforeach; ?></nav>
      <button data-menu-button class="rounded-full border border-white/25 p-3 lg:hidden" aria-expanded="false" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    </div>
    <nav data-mobile-menu class="container-site hidden border-t border-white/15 bg-brand-dark/95 py-5 lg:hidden" aria-label="Navigasi seluler"><?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; ?><a class="block border-b border-white/10 py-3 text-sm font-semibold" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endforeach; ?></nav>
  </header>

  <main id="main" class="min-h-screen bg-sand pt-29">
    <section class="relative overflow-hidden bg-brand-dark py-24 text-white">
      <?php if (!empty($page['banner'])): ?><img src="<?php echo modern_asset($page['banner']); ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25"><?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/90 to-brand/50"></div>
      <div class="container-site relative"><p class="mb-4 text-xs font-extrabold uppercase tracking-[.22em] text-emerald-200">STBA Prayoga Padang</p><h1 class="max-w-4xl font-display text-5xl leading-tight sm:text-6xl"><?php echo modern_e($page['page_name']); ?></h1></div>
    </section>
    <section class="py-16 sm:py-24"><div class="container-site"><article class="modern-content mx-auto max-w-4xl rounded-3xl bg-white p-7 shadow-sm sm:p-12"><?php echo sanitize_rich_html($page['page_content']); ?></article></div></section>
  </main>

  <footer class="bg-[#062d20] py-14 text-white/70">
    <div class="container-site grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.2fr_.8fr_.8fr]"><div><img src="<?php echo modern_asset($settings['logo']); ?>" alt="Logo" class="h-14 w-14 rounded-xl bg-white object-contain p-1"><p class="mt-5 max-w-md text-sm leading-7"><?php echo modern_e(strip_tags($settings['footer_about'])); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Kontak</h2><p class="mt-4 text-sm leading-7"><?php echo modern_e(strip_tags($settings['contact_address'])); ?><br><?php echo modern_e($settings['contact_phone']); ?><br><?php echo modern_e($settings['contact_email']); ?></p></div><div><h2 class="text-sm font-extrabold text-white">Ikuti kami</h2><div class="mt-4 flex flex-wrap gap-3"><?php foreach ($socials as $social): ?><a href="<?php echo modern_e(modern_url($social['social_url'])); ?>" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-xs font-bold hover:bg-white/10"><?php echo modern_e($social['social_name']); ?></a><?php endforeach; ?></div></div></div>
    <div class="container-site pt-7 text-xs"><?php echo modern_e(strip_tags($settings['footer_copyright'])); ?></div>
  </footer>
</body>
</html>
