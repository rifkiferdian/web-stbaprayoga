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
    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) return $value;
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $value) || str_starts_with($value, '//')) return '#';
    return BASE_URL . ltrim($value, '/');
}

$settings = $pdo->query("SELECT * FROM tbl_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$sliders = $pdo->query("SELECT * FROM tbl_slider WHERE status='Active' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$newsLimit = max(3, min(6, (int) $settings['total_recent_news_home_page']));
$newsStatement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC LIMIT :limit");
$newsStatement->bindValue(':limit', $newsLimit, PDO::PARAM_INT);
$newsStatement->execute();
$news = $newsStatement->fetchAll(PDO::FETCH_ASSOC);
$partners = $pdo->query("SELECT * FROM tbl_partner ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$socials = $pdo->query("SELECT * FROM tbl_social WHERE social_url <> '' ORDER BY social_id ASC")->fetchAll(PDO::FETCH_ASSOC);
$allMenus = $pdo->query("SELECT m.*, p.page_name, p.page_slug FROM tbl_menu m LEFT JOIN tbl_page p ON m.page_id=p.page_id ORDER BY m.menu_parent ASC, m.menu_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$menus = array();
$menuChildren = array();
foreach ($allMenus as $menuItem) {
    if ((int) $menuItem['menu_parent'] === 0) $menus[] = $menuItem;
    else $menuChildren[(int) $menuItem['menu_parent']][] = $menuItem;
}
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
function modern_link_attrs($url) {
    $host = parse_url((string) $url, PHP_URL_HOST);
    $baseHost = parse_url(BASE_URL, PHP_URL_HOST);
    return $host && strcasecmp($host, (string) $baseHost) !== 0 ? ' target="_blank" rel="noopener noreferrer"' : '';
}
function modern_social_url($socials, $platform) {
    $aliases = array(
        'youtube' => array('youtube'),
        'instagram' => array('instagram'),
        'x' => array('x', 'twitter'),
        'whatsapp' => array('whatsapp', 'wa')
    );
    foreach ($socials as $social) {
        $name = strtolower(trim((string) ($social['social_name'] ?? '')));
        if (in_array($name, $aliases[$platform] ?? array(), true)) return modern_url($social['social_url'], '');
    }
    return '';
}

$heroFallbacks = array('slider-12.jpg', 'slider-11.jpg', 'slider-9.jpeg');
$counters = array(
    array($settings['counter_1_value'], $settings['counter_1_title']), array($settings['counter_2_value'], $settings['counter_2_title']),
    array($settings['counter_3_value'], $settings['counter_3_title']), array($settings['counter_4_value'], $settings['counter_4_title'])
);
$campusOffers = array(
    array('title' => 'Pembelajaran Bahasa Inggris', 'description' => 'Mengembangkan kemampuan bahasa Inggris akademik, profesional, dan komunikatif melalui proses belajar yang aktif dan terarah.', 'image' => 'campus-offer-english-learning.webp', 'label' => 'Komunikatif'),
    array('title' => 'Kajian Bahasa dan Sastra', 'description' => 'Mendalami linguistik, kesusastraan, penulisan, penerjemahan, serta analisis karya dalam bahasa Inggris.', 'image' => 'campus-offer-language-literature.webp', 'label' => 'Akademik'),
    array('title' => 'Dosen Berpengalaman', 'description' => 'Belajar bersama tenaga pengajar yang kompeten dan berdedikasi dalam bidang bahasa, sastra, dan penelitian.', 'image' => 'campus-offer-experienced-lecturer.webp', 'label' => 'Berkualitas'),
    array('title' => 'Lingkungan Belajar Humanis', 'description' => 'Suasana kampus yang mendukung perkembangan intelektual, karakter, kreativitas, dan kepedulian sosial.', 'image' => 'campus-offer-humanist-campus.webp', 'label' => 'Humanis'),
    array('title' => 'Pengembangan Kompetensi', 'description' => 'Kegiatan akademik dan kemahasiswaan membantu mahasiswa mengasah komunikasi, organisasi, serta kepemimpinan.', 'image' => 'campus-offer-competency.webp', 'label' => 'Bertumbuh'),
    array('title' => 'Persiapan Karier', 'description' => 'Mempersiapkan lulusan untuk berkarya di bidang pendidikan, penerjemahan, komunikasi, pariwisata, media, dan industri kreatif.', 'image' => 'campus-offer-career.webp', 'label' => 'Masa Depan')
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
  <link rel="stylesheet" href="assets/css/app.css?v=<?php echo filemtime(__DIR__ . '/assets/css/app.css'); ?>">
  <script>document.documentElement.classList.add('js');</script>
  <script src="assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/assets/js/app.js'); ?>" defer></script>
</head>
<body>
  <?php require __DIR__ . '/loader.php'; ?>
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
      <a href="<?php echo BASE_URL; ?>modern/" class="flex items-center gap-3" aria-label="Beranda">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-white shadow-lg"><img src="<?php echo modern_asset($settings['logo']); ?>" class="h-10 w-10 object-contain" alt="Logo"></span>
        <span><strong class="block text-sm tracking-[.12em]">STBA PRAYOGA</strong><span class="text-[11px] text-white/65">Padang · Sumatera Barat</span></span>
      </a>
      <nav class="hidden items-center gap-5 xl:flex xl:gap-7" aria-label="Navigasi utama">
        <?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; $children = $menuChildren[(int) $menu['menu_id']] ?? array(); ?>
          <div class="group relative">
            <a class="flex items-center gap-1.5 py-7 text-sm font-semibold text-white/80 transition hover:text-white" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?><?php if ($children): ?><svg class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg><?php endif; ?></a>
            <?php if ($children): ?><div class="invisible absolute left-0 top-full max-h-[calc(100vh-8rem)] w-72 translate-y-2 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 text-ink opacity-0 shadow-2xl shadow-brand-dark/15 transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100"><?php foreach ($children as $child): $childLink = modern_menu_link($child); ?><a class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-brand-soft hover:text-brand" href="<?php echo modern_e($childLink); ?>"<?php echo modern_link_attrs($childLink); ?>><?php echo modern_e(modern_menu_name($child)); ?></a><?php endforeach; ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </nav>
      <div class="flex items-center gap-3">
        <button type="button" data-search-open class="rounded-full border border-white/25 p-3 transition hover:bg-white/10" aria-label="Buka pencarian">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
        </button>
        <button data-menu-button class="rounded-full border border-white/25 p-3 xl:hidden" aria-expanded="false" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      </div>
    </div>
    <nav data-mobile-menu class="container-site hidden max-h-[calc(100vh-8rem)] overflow-y-auto border-t border-white/15 bg-brand-dark/95 py-5 xl:hidden" aria-label="Navigasi seluler">
      <?php foreach ($menus as $menu): $name = modern_menu_name($menu); if (!$name) continue; $children = $menuChildren[(int) $menu['menu_id']] ?? array(); ?><?php if ($children): ?><details class="border-b border-white/10"><summary class="flex cursor-pointer list-none items-center justify-between py-3 text-sm font-semibold"><?php echo modern_e($name); ?><span class="text-emerald-300">＋</span></summary><div class="mb-3 rounded-xl bg-white/5 px-3"><?php foreach ($children as $child): $childLink = modern_menu_link($child); ?><a class="block border-b border-white/10 py-3 text-sm text-white/75 last:border-0" href="<?php echo modern_e($childLink); ?>"<?php echo modern_link_attrs($childLink); ?>><?php echo modern_e(modern_menu_name($child)); ?></a><?php endforeach; ?></div></details><?php else: ?><a class="block border-b border-white/10 py-3 text-sm font-semibold" href="<?php echo modern_e(modern_menu_link($menu)); ?>"><?php echo modern_e($name); ?></a><?php endif; ?><?php endforeach; ?>
    </nav>
  </header>

  <div data-search-overlay class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-labelledby="search-title">
    <button type="button" data-search-close class="absolute inset-0 bg-brand-dark/80 backdrop-blur-md" aria-label="Tutup pencarian"></button>
    <div class="container-site relative flex min-h-full items-start justify-center px-4 pt-24 sm:pt-36"><div class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl sm:p-9"><div class="flex items-start justify-between gap-5"><div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-brand">Pencarian situs</p><h2 id="search-title" class="mt-2 font-display text-3xl text-brand-dark">Apa yang ingin Anda cari?</h2></div><button type="button" data-search-close class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-soft hover:text-brand" aria-label="Tutup pencarian"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div><form action="<?php echo BASE_URL; ?>modern/search" method="get" class="mt-7 flex gap-3"><label class="sr-only" for="site-search-home">Kata pencarian</label><div class="relative min-w-0 flex-1"><svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input data-search-input id="site-search-home" name="q" type="search" minlength="2" maxlength="100" required placeholder="Cari informasi di STBA Prayoga…" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm text-ink outline-none transition focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/10"></div><button class="rounded-2xl bg-brand px-6 py-4 text-sm font-extrabold text-white transition hover:bg-brand-dark">Cari</button></form><p class="mt-4 text-xs leading-6 text-slate-500">Cari profil kampus, program studi, PMB, akreditasi, dosen, serta berita dan kegiatan terbaru.</p></div></div>
  </div>

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
              <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-[.12em] text-emerald-50"><span class="rounded-full border border-emerald-200/30 bg-emerald-100/10 px-4 py-2">S1 Sastra Inggris</span></div>
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
    <section id="program-studi" class="bg-sand py-24 sm:py-32">
      <div class="container-site">
        <div class="reveal grid gap-7 lg:grid-cols-[.75fr_1.25fr] lg:items-end">
          <div><p class="section-kicker">Yang kami hadirkan</p><h2 class="section-title">Pengalaman belajar untuk masa depan</h2></div>
          <p class="section-copy lg:ml-auto">STBA Prayoga menghadirkan pendidikan bahasa dan sastra Inggris dalam lingkungan belajar yang humanis. Mahasiswa dibekali pengetahuan, keterampilan, pengalaman, dan nilai-nilai yang diperlukan untuk berkembang di dunia kerja maupun masyarakat.</p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <?php foreach ($campusOffers as $i => $offer): ?>
          <article class="reveal card-lift group overflow-hidden rounded-[1.75rem] border border-brand/10 bg-white shadow-sm" style="transition-delay:<?php echo $i * 70; ?>ms">
            <div class="relative h-56 overflow-hidden bg-brand-soft">
              <img src="<?php echo modern_asset($offer['image']); ?>" alt="Ilustrasi <?php echo modern_e($offer['title']); ?> di STBA Prayoga" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
              <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/70 via-transparent to-transparent"></div>
              <span class="absolute left-5 top-5 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[.14em] text-brand"><?php echo modern_e($offer['label']); ?></span>
              <span class="absolute bottom-5 right-5 font-display text-4xl text-white/70">0<?php echo $i + 1; ?></span>
            </div>
            <div class="p-7"><h3 class="font-display text-2xl text-brand-dark"><?php echo modern_e($offer['title']); ?></h3><p class="mt-3 text-sm leading-7 text-slate-600"><?php echo modern_e($offer['description']); ?></p></div>
          </article>
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
        <div class="reveal flex flex-col justify-between gap-6 sm:flex-row sm:items-end"><div><p class="section-kicker">Cerita terbaru</p><h2 class="section-title"><?php echo modern_e($settings['home_title_news']); ?></h2><p class="section-copy"><?php echo modern_e($settings['home_subtitle_news']); ?></p></div><a href="<?php echo BASE_URL; ?>modern/berita" class="w-fit rounded-full border border-slate-300 px-6 py-3 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft">Lihat semua berita</a></div>
        <div class="mt-12 grid gap-7 lg:grid-cols-3">
          <?php foreach ($news as $i => $article): ?>
          <article class="reveal group <?php echo $i === 0 ? 'lg:col-span-2 lg:row-span-2' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>modern/news/<?php echo rawurlencode($article['news_slug']); ?>" class="block overflow-hidden rounded-3xl bg-slate-100"><img src="<?php echo modern_asset($article['photo']); ?>" alt="" class="w-full object-cover transition duration-500 group-hover:scale-[1.03] <?php echo $i === 0 ? 'h-80 lg:h-[430px]' : 'h-52'; ?>"></a>
            <div class="pt-5"><time class="text-xs font-extrabold uppercase tracking-[.16em] text-brand"><?php echo modern_e($article['news_date']); ?></time><h3 class="mt-2 font-display leading-snug text-brand-dark <?php echo $i === 0 ? 'text-3xl sm:text-4xl' : 'text-2xl'; ?>"><a class="hover:text-brand" href="<?php echo BASE_URL; ?>modern/news/<?php echo rawurlencode($article['news_slug']); ?>"><?php echo modern_e($article['news_title']); ?></a></h3><?php if ($i === 0): ?><p class="mt-3 max-w-2xl line-clamp-2 text-sm leading-7 text-slate-600"><?php echo modern_e(strip_tags($article['news_content_short'])); ?></p><?php endif; ?></div>
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

  <footer id="contact" class="bg-[#062d20] py-14 text-white/70">
    <div class="container-site grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.2fr_.8fr_.8fr]">
      <div><img src="<?php echo modern_asset($settings['logo']); ?>" alt="Logo" class="h-14 w-14 rounded-xl bg-white object-contain p-1"><p class="mt-5 max-w-md text-sm leading-7"><?php echo modern_e(strip_tags($settings['footer_about'])); ?></p></div>
      <div><h3 class="text-sm font-extrabold text-white">Kontak</h3><p class="mt-4 text-sm leading-7"><?php echo modern_e(strip_tags($settings['contact_address'])); ?><br><?php echo modern_e($settings['contact_phone']); ?><br><?php echo modern_e($settings['contact_email']); ?></p></div>
      <div><h3 class="text-sm font-extrabold text-white">Ikuti kami</h3><div class="mt-4 flex flex-wrap gap-3"><?php foreach ($socials as $social): ?><a href="<?php echo modern_e(modern_url($social['social_url'])); ?>" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-xs font-bold hover:bg-white/10"><?php echo modern_e($social['social_name']); ?></a><?php endforeach; ?></div></div>
    </div>
    <div class="container-site flex flex-col gap-3 pt-7 text-xs sm:flex-row sm:justify-between"><span><?php echo modern_e(strip_tags($settings['footer_copyright'])); ?></span><span>Modern homepage preview</span></div>
  </footer>
</body>
</html>
