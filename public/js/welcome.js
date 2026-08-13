(() => {
    const header = document.querySelector('.ff-header');
    const toggle = document.querySelector('.ff-menu-toggle');
    const sections = [...document.querySelectorAll('[data-section]')];
    const navLinks = [...document.querySelectorAll('[data-nav]')];

    toggle?.addEventListener('click', () => {
        header.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', header.classList.contains('is-open'));
    });

    navLinks.forEach((link) => {
        link.addEventListener('click', () => header.classList.remove('is-open'));
    });

    const setActive = (id) => {
        navLinks.forEach((link) => {
            const active = link.getAttribute('href') === `#${id}`;
            link.classList.toggle('is-active', active);
            if (link.closest('.ff-dock')) {
                link.setAttribute('aria-current', active ? 'true' : 'false');
            }
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                setActive(entry.target.id);
            }
        });
    }, { threshold: 0.45 });

    sections.forEach((section) => observer.observe(section));
    setActive(sections[0]?.id || 'inicio');

    const slides = [...document.querySelectorAll('.ff-slide')];
    if (slides.length > 1) {
        let current = 0;
        setInterval(() => {
            slides[current].classList.remove('is-active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('is-active');
        }, 5000);
    }
})();
