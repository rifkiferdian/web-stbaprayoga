<?php
require_once __DIR__ . '/../panel-5tb4/config.php';

function modern_e($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function modern_asset($filename, $fallback = 'no-photo1.jpg') {
    $safe = basename((string) $filename);
    if ($safe === '' || !is_file(__DIR__ . '/../assets/uploads/' . $safe)) { $safe = $fallback; }
    return BASE_URL . 'assets/uploads/' . rawurlencode($safe);
}
function modern_url($value, $fallback = '#') {
    $value = trim((string) $value);
    if ($value === '' || $value === '#') return $fallback;
    if (preg_match('#^(https?://|/)#i', $value)) return $value;
    return '#';
}

$settings = $pdo->query("SELECT * FROM tbl_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$sliders = $pdo->query("SELECT * FROM tbl_slider WHERE status='Active' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT * FROM tbl_service ORDER BY id ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
$newsLimit = max(3, min(6, (int) $settings['total_recent_news_home_page']));
$newsStatement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC LIMIT :limit");
$newsStatement->bindValue(':limit', $newsLimit, PDO::PARAM_INT);
$newsStatement->execute();
$news = $newsStatement->fetchAll(PDO::FETCH_ASSOC);
$partners = $pdo->query("SELECT * FROM tbl_partner ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$socials = $pdo->query("SELECT * FROM tbl_social WHERE social_url <> '' ORDER BY social_id ASC")->fetchAll(PDO::FETCH_ASSOC);
$menus = $pdo->query("SELECT m.*, p.page_name, p.page_slug FROM tbl_menu m LEFT JOIN tbl_page p ON m.page_id=p.page_id WHERE m.menu_parent=0 ORDER BY m.menu_order ASC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
if (!$sliders) $sliders = array(array('heading' => $settings['meta_title_home'], 'content' => $settings['meta_description_home'], 'button_text' => '', 'button_url' => '#', 'photo' => 'slider-12.jpg'));

function modern_menu_link($menu) {
    $name = strtolower(trim((string) ($menu['menu_name'] ?? '')));
    if (in_array($name, array('home', 'beranda'), true)) return BASE_URL . 'modern/';
    if ($menu['menu_type'] === 'Page' && !empty($menu['page_slug'])) return BASE_URL . 'modern/page/' . rawurlencode($menu['page_slug']);
    return modern_url($menu['menu_url'], '#');
}
function modern_menu_name($menu) {
    return trim($menu['menu_name']) !== '' ? $menu['menu_name'] : $menu['page_name'];
}

$heroFallbacks = array('slider-12.jpg', 'slider-11.jpg', 'slider-9.jpeg');
$counters = array(
    array($settings['counter_1_value'], $settings['counter_1_title']), array($settings['counter_2_value'], $settings['counter_2_title']),
    array($settings['counter_3_value'], $settings['counter_3_title']), array($settings['counter_4_value'], $settings['counter_4_title'])
);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo modern_e($settings['meta_title_home']); ?></title>
  <meta name="description" content="<?php echo modern_e(strip_tags($settings['meta_description_home'])); ?>">
  <link rel="icon" href="<?php echo modern_asset($settings['favicon']); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body>
  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-3">Lewati ke konten</a>
  <div class="fixed inset-x-0 top-0 z-[60] h-9 bg-brand-dark text-white/75">
    <div class="container-site flex h-full items-center justify-between text-[11px] font-semibold tracking-wide">
      <div class="flex min-w-0 items-center gap-4 sm:gap-6">
        <a href="mailto:<?php echo modern_e($settings['contact_email']); ?>" class="flex min-w-0 items-center gap-2 transition hover:text-white">
          <svg class="h-3.5 w-3.5 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6.5 12 13l9-6.5"/><rect x="3" y="5" width="18" height="14" rx="2"/></svg>
          <span class="truncate"><?php echo modern_e($settings['contact_email']); ?></span>
        </a>
        <a href="tel:<?php echo modern_e(preg_replace('/[^0-9+]/', '', $settings['contact_phone'])); ?>" class="hidden items-center gap-2 transition hover:text-white sm:flex">
          <svg class="h-3.5 w-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-4-2-2 2c-3.6-1.5-6.5-4.4-8-8l2-2-2-4Z"/></svg>
          <?php echo modern_e($settings['contact_phone']); ?>
        </a>
      </div>
      <div class="flex shrink-0 items-center gap-4">
        <span class="hidden uppercase tracking-[.14em] md:inline">Kampus Bahasa · Padang</span>
        <a href="<?php echo BASE_URL; ?>panel-5tb4/login.php" class="border-l border-white/20 pl-4 text-white transition hover:text-emerald-200">Portal Admin</a>
      </div>
    </div>
  </div>
  <header data-site-header class="fixed inset-x-0 top-9 z-50 border-b border-white/15 text-white">
    <div class="container-site flex h-20 items-center justify-between">
      <a href="<?php echo BASE_URL; ?>modern/" class="flex items-center gap-3" aria-label="Beranda">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-white shadow-lg"><img src="<?php echo modern_asset($settings['logo']); ?>" class="h-10 w-10 object-contain" alt="Logo"></span>
        <span><strong class="block text-sm tracking-[.12em]">STBA PRAYOGA</strong><span class="text-[11px] text-white/65">Padang · Sumatera Barat</span></span>
      </a>
      <nav class="hidden items-center gap-7 lg:flex" aria-label="Navigasi utama">
        <?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; ?>
          <a class="text-sm font-semibold text-white/80 transition hover:text-white" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="flex items-center gap-3">
        <a href="<?php echo BASE_URL; ?>search" class="hidden rounded-full border border-white/25 p-3 transition hover:bg-white/10 sm:block" aria-label="Cari">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
        </a>
        <button data-menu-button class="rounded-full border border-white/25 p-3 lg:hidden" aria-expanded="false" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      </div>
    </div>
    <nav data-mobile-menu class="container-site hidden border-t border-white/15 bg-brand-dark/95 py-5 lg:hidden" aria-label="Navigasi seluler">
      <?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; ?><a class="block border-b border-white/10 py-3 text-sm font-semibold" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endforeach; ?>
    </nav>
  </header>

  <main id="main">
    <section class="relative min-h-[760px] overflow-hidden bg-brand-dark text-white">
      <?php foreach ($sliders as $index => $slide): ?>
        <article data-hero-slide class="hero-slide absolute inset-0 <?php echo $index === 0 ? 'is-active' : ''; ?>">
          <img src="<?php echo modern_asset($slide['photo'], $heroFallbacks[$index % count($heroFallbacks)]); ?>" alt="" class="absolute inset-0 h-full w-full object-cover">
          <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/85 to-brand-dark/20"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/85 via-transparent to-transparent"></div>
          <div class="container-site relative flex min-h-[760px] items-center pt-32">
            <div class="hero-copy max-w-3xl py-20">
              <p class="mb-5 flex items-center gap-3 text-xs font-extrabold uppercase tracking-[.24em] text-emerald-100"><span class="h-px w-10 bg-accent"></span>Kampus bahasa untuk masa depan</p>
              <h1 class="font-display text-5xl leading-[1.06] tracking-tight sm:text-6xl lg:text-7xl"><?php echo modern_e($slide['heading']); ?></h1>
              <p class="mt-6 max-w-2xl text-base leading-8 text-white/75 sm:text-lg"><?php echo modern_e(strip_tags($slide['content'])); ?></p>
              <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-[.12em] text-emerald-50"><span class="rounded-full border border-emerald-200/30 bg-emerald-100/10 px-4 py-2">S1 Sastra Inggris</span><span class="rounded-full border border-emerald-200/30 bg-emerald-100/10 px-4 py-2">D3 Bahasa Inggris</span></div>
              <div class="mt-9 flex flex-wrap gap-4">
                <?php if ($slide['button_text'] !== ''): ?><a href="<?php echo modern_e(modern_url($slide['button_url'], '#services')); ?>" class="rounded-full bg-accent px-7 py-3.5 text-sm font-extrabold text-brand-dark transition hover:bg-amber-300"><?php echo modern_e($slide['button_text']); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
                <a href="#news" class="rounded-full border border-white/30 px-7 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Lihat kabar terbaru</a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
      <div class="container-site absolute inset-x-0 bottom-9 z-10 flex items-center justify-between">
        <div class="flex gap-2"><?php foreach ($sliders as $index => $_): ?><button data-hero-dot class="h-2.5 w-8 rounded-full <?php echo $index === 0 ? 'bg-white' : 'bg-white/35'; ?>" aria-label="Tampilkan slide <?php echo $index + 1; ?>"></button><?php endforeach; ?></div>
        <span class="hidden text-xs font-bold uppercase tracking-[.18em] text-white/50 sm:block">Scroll untuk menjelajah</span>
      </div>
    </section>

    <?php if ($settings['home_status_service'] === 'Show'): ?>
    <section id="services" class="bg-sand py-24 sm:py-32">
      <div class="container-site">
        <div class="reveal grid gap-7 lg:grid-cols-[.75fr_1.25fr] lg:items-end">
          <div><p class="section-kicker">Yang kami hadirkan</p><h2 class="section-title"><?php echo modern_e($settings['home_title_service']); ?></h2></div>
          <p class="section-copy lg:ml-auto"><?php echo modern_e($settings['home_subtitle_service'] ?: 'Komitmen untuk menghadirkan pengalaman dan layanan terbaik bagi seluruh komunitas.'); ?></p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
          <?php foreach ($services as $i => $service): ?>
          <a href="<?php echo BASE_URL; ?>service/<?php echo rawurlencode($service['slug']); ?>" class="reveal card-lift group overflow-hidden rounded-[1.75rem] bg-white shadow-sm" style="transition-delay:<?php echo $i * 70; ?>ms">
            <div class="relative h-56 overflow-hidden bg-brand-soft"><img src="<?php echo modern_asset($service['photo']); ?>" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105"><span class="absolute left-5 top-5 grid h-10 w-10 place-items-center rounded-full bg-white/90 text-xs font-extrabold text-brand">0<?php echo $i + 1; ?></span></div>
            <div class="p-7"><h3 class="font-display text-2xl text-brand-dark"><?php echo modern_e($service['name']); ?></h3><p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-600"><?php echo modern_e(strip_tags($service['short_description'])); ?></p><span class="mt-6 inline-flex items-center gap-2 text-sm font-extrabold text-brand">Selengkapnya <span class="transition group-hover:translate-x-1">→</span></span></div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($settings['counter_status'] === 'Show'): ?>
    <section class="relative overflow-hidden bg-brand-dark py-20 text-white">
      <div class="absolute inset-0 opacity-15" style="background-image:url('<?php echo modern_asset($settings['counter_photo']); ?>');background-size:cover;background-position:center"></div>
      <div class="container-site relative grid grid-cols-2 gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 lg:grid-cols-4">
        <?php foreach ($counters as $counter): ?><div class="reveal bg-brand-dark/75 p-8 text-center backdrop-blur"><strong class="font-display text-4xl text-white sm:text-5xl"><?php echo modern_e($counter[0]); ?></strong><span class="mt-2 block text-xs font-bold uppercase tracking-[.16em] text-emerald-100/70"><?php echo modern_e($counter[1]); ?></span></div><?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($settings['home_status_news'] === 'Show'): ?>
    <section id="news" class="py-24 sm:py-32">
      <div class="container-site">
        <div class="reveal flex flex-col justify-between gap-6 sm:flex-row sm:items-end"><div><p class="section-kicker">Cerita terbaru</p><h2 class="section-title"><?php echo modern_e($settings['home_title_news']); ?></h2><p class="section-copy"><?php echo modern_e($settings['home_subtitle_news']); ?></p></div><a href="<?php echo BASE_URL; ?>category/news" class="w-fit rounded-full border border-slate-300 px-6 py-3 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft">Lihat semua berita</a></div>
        <div class="mt-12 grid gap-7 lg:grid-cols-3">
          <?php foreach ($news as $i => $article): ?>
          <article class="reveal group <?php echo $i === 0 ? 'lg:col-span-2 lg:row-span-2' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>news/<?php echo rawurlencode($article['news_slug']); ?>" class="block overflow-hidden rounded-3xl bg-slate-100"><img src="<?php echo modern_asset($article['photo']); ?>" alt="" class="w-full object-cover transition duration-500 group-hover:scale-[1.03] <?php echo $i === 0 ? 'h-80 lg:h-[430px]' : 'h-52'; ?>"></a>
            <div class="pt-5"><time class="text-xs font-extrabold uppercase tracking-[.16em] text-brand"><?php echo modern_e($article['news_date']); ?></time><h3 class="mt-2 font-display leading-snug text-brand-dark <?php echo $i === 0 ? 'text-3xl sm:text-4xl' : 'text-2xl'; ?>"><a class="hover:text-brand" href="<?php echo BASE_URL; ?>news/<?php echo rawurlencode($article['news_slug']); ?>"><?php echo modern_e($article['news_title']); ?></a></h3><?php if ($i === 0): ?><p class="mt-3 max-w-2xl line-clamp-2 text-sm leading-7 text-slate-600"><?php echo modern_e(strip_tags($article['news_content_short'])); ?></p><?php endif; ?></div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($settings['home_status_partner'] === 'Show' && $partners): ?>
    <section class="border-y border-slate-200 bg-slate-50 py-16">
      <div class="container-site reveal"><p class="text-center text-xs font-extrabold uppercase tracking-[.2em] text-slate-500"><?php echo modern_e($settings['home_title_partner']); ?></p><div class="mt-9 flex flex-wrap items-center justify-center gap-x-12 gap-y-8"><?php foreach ($partners as $partner): ?><a href="<?php echo modern_e(modern_url($partner['url'])); ?>" class="grayscale transition hover:grayscale-0" <?php echo $partner['url'] ? 'target="_blank" rel="noopener"' : ''; ?>><img src="<?php echo modern_asset($partner['photo']); ?>" alt="<?php echo modern_e($partner['name']); ?>" class="max-h-12 max-w-36 object-contain"></a><?php endforeach; ?></div></div>
    </section>
    <?php endif; ?>

    <section class="bg-brand py-20 text-white">
      <div class="container-site reveal flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center"><div><p class="text-xs font-extrabold uppercase tracking-[.22em] text-emerald-100/70">Mulai perjalanan akademik</p><h2 class="mt-3 max-w-2xl font-display text-4xl leading-tight">Kembangkan kemampuan bahasa dan wawasan lintas budaya bersama STBA Prayoga.</h2></div><a href="mailto:<?php echo modern_e($settings['contact_email']); ?>" class="rounded-full bg-white px-7 py-4 text-sm font-extrabold text-brand-dark transition hover:bg-amber-100">Hubungi kampus <span aria-hidden="true">→</span></a></div>
    </section>
  </main>

  <footer class="bg-[#062d20] py-14 text-white/70">
    <div class="container-site grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.2fr_.8fr_.8fr]">
      <div><img src="<?php echo modern_asset($settings['logo']); ?>" alt="Logo" class="h-14 w-14 rounded-xl bg-white object-contain p-1"><p class="mt-5 max-w-md text-sm leading-7"><?php echo modern_e(strip_tags($settings['footer_about'])); ?></p></div>
      <div><h3 class="text-sm font-extrabold text-white">Kontak</h3><p class="mt-4 text-sm leading-7"><?php echo modern_e(strip_tags($settings['contact_address'])); ?><br><?php echo modern_e($settings['contact_phone']); ?><br><?php echo modern_e($settings['contact_email']); ?></p></div>
      <div><h3 class="text-sm font-extrabold text-white">Ikuti kami</h3><div class="mt-4 flex flex-wrap gap-3"><?php foreach ($socials as $social): ?><a href="<?php echo modern_e(modern_url($social['social_url'])); ?>" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-xs font-bold hover:bg-white/10"><?php echo modern_e($social['social_name']); ?></a><?php endforeach; ?></div></div>
    </div>
    <div class="container-site flex flex-col gap-3 pt-7 text-xs sm:flex-row sm:justify-between"><span><?php echo modern_e(strip_tags($settings['footer_copyright'])); ?></span><span>Modern homepage preview</span></div>
  </footer>
</body>
</html>
