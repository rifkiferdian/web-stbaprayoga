(() => {
  const siteLoader = document.querySelector('[data-site-loader]');
  if (siteLoader) {
    let loaderDismissed = false;
    const dismissLoader = () => {
      if (loaderDismissed) return;
      loaderDismissed = true;
      const minimumDelay = Math.max(0, 700 - performance.now());
      window.setTimeout(() => {
        siteLoader.classList.add('is-loaded');
        window.setTimeout(() => siteLoader.remove(), 650);
      }, minimumDelay);
    };
    if (document.readyState === 'complete') dismissLoader();
    else window.addEventListener('load', dismissLoader, { once: true });
    window.setTimeout(dismissLoader, 4500);
  }

  const siteHeader = document.querySelector('[data-site-header]');
  const updateHeader = () => siteHeader?.classList.toggle(
    'is-scrolled',
    siteHeader.hasAttribute('data-header-solid') || window.scrollY > 24
  );
  updateHeader();
  window.addEventListener('scroll', updateHeader, { passive: true });

  const menuButton = document.querySelector('[data-menu-button]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  if (menuButton && mobileMenu) {
    menuButton.addEventListener('click', () => {
      const open = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!open));
      mobileMenu.classList.toggle('hidden', open);
    });
  }

  const searchOverlay = document.querySelector('[data-search-overlay]');
  const searchOpeners = document.querySelectorAll('[data-search-open]');
  const searchClosers = document.querySelectorAll('[data-search-close]');
  const searchInput = searchOverlay?.querySelector('[data-search-input]');
  let searchReturnFocus = null;
  const closeSearch = () => {
    if (!searchOverlay || searchOverlay.classList.contains('hidden')) return;
    searchOverlay.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    searchReturnFocus?.focus();
  };
  searchOpeners.forEach((button) => button.addEventListener('click', () => {
    if (!searchOverlay) return;
    searchReturnFocus = button;
    searchOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    window.setTimeout(() => searchInput?.focus(), 50);
  }));
  searchClosers.forEach((button) => button.addEventListener('click', closeSearch));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSearch();
  });

  const galleryDialog = document.querySelector('[data-gallery-dialog]');
  const galleryImage = galleryDialog?.querySelector('[data-gallery-image]');
  let galleryReturnFocus = null;
  const closeGallery = () => {
    if (!galleryDialog || galleryDialog.classList.contains('hidden')) return;
    galleryDialog.classList.add('hidden');
    galleryDialog.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    if (galleryImage) galleryImage.src = '';
    galleryReturnFocus?.focus();
  };
  document.querySelectorAll('[data-gallery-item]').forEach((item) => item.addEventListener('click', () => {
    if (!galleryDialog || !galleryImage) return;
    galleryReturnFocus = item;
    galleryImage.src = item.dataset.gallerySrc || '';
    galleryImage.alt = item.getAttribute('aria-label') || 'Foto galeri';
    galleryDialog.classList.remove('hidden');
    galleryDialog.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    galleryDialog.querySelector('[data-gallery-close]')?.focus();
  }));
  galleryDialog?.querySelectorAll('[data-gallery-close]').forEach((button) => button.addEventListener('click', closeGallery));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeGallery();
  });

  const slides = [...document.querySelectorAll('[data-hero-slide]')];
  const dots = [...document.querySelectorAll('[data-hero-dot]')];
  let current = 0;
  let timer;
  const showSlide = (index) => {
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
    dots.forEach((dot, i) => {
      dot.classList.toggle('bg-white', i === current);
      dot.classList.toggle('bg-white/35', i !== current);
      dot.setAttribute('aria-current', i === current ? 'true' : 'false');
    });
  };
  const autoplay = () => {
    clearInterval(timer);
    if (slides.length > 1) timer = setInterval(() => showSlide(current + 1), 6500);
  };
  dots.forEach((dot, i) => dot.addEventListener('click', () => { showSlide(i); autoplay(); }));
  showSlide(0);
  autoplay();

  const observer = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
      }), { threshold: 0.14 })
    : null;
  document.querySelectorAll('.reveal').forEach((el) => observer ? observer.observe(el) : el.classList.add('is-visible'));
})();
