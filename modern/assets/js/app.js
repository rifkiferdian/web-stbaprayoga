(() => {
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
