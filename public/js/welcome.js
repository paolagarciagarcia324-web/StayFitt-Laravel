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

    // Modal novedades welcome
    const newsOpenBtn = document.querySelector('[data-news-open]');
    const newsModal = document.querySelector('[data-news-modal]');
    if (newsOpenBtn && newsModal) {
        const newsCloseBtns = newsModal.querySelectorAll('[data-news-close]');

        const openNews = () => {
            newsModal.classList.remove('is-hidden');
            newsModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ff-news-open');
            newsOpenBtn.setAttribute('aria-expanded', 'true');
        };

        const closeNews = () => {
            newsModal.classList.add('is-hidden');
            newsModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ff-news-open');
            newsOpenBtn.setAttribute('aria-expanded', 'false');
            newsOpenBtn.focus();
        };

        newsOpenBtn.addEventListener('click', openNews);
        newsCloseBtns.forEach((btn) => btn.addEventListener('click', closeNews));
        newsModal.addEventListener('click', (e) => {
            if (e.target === newsModal) closeNews();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !newsModal.classList.contains('is-hidden')) {
                closeNews();
            }
        });
    }
})();
