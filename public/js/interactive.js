/* ================================================================
   INTERAKSI HALAMAN — animasi gulir, parallax, sorotan kursor
   ================================================================ */
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const root = document.documentElement;

    /* ---------- 1. Bilah kemajuan gulir ---------- */
    const bar = document.createElement('div');
    bar.className = 'scroll-progress';
    document.body.prepend(bar);

    /* ---------- 2. Header mengecil saat digulir ---------- */
    const header = document.querySelector('.header');

    let raf = null;
    const onScroll = () => {
        if (raf) return;
        raf = requestAnimationFrame(() => {
            const y = window.scrollY;
            const max = Math.max(document.body.scrollHeight - window.innerHeight, 1);
            bar.style.transform = `scaleX(${Math.min(y / max, 1)})`;
            header?.classList.toggle('is-scrolled', y > 12);
            raf = null;
        });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- 3. Muncul saat di-scroll ---------- */
    // Tandai elemen umum secara otomatis, lalu hormati .reveal manual
    const autoTargets = [
        '.tp-section-head', '.section-head', '.agenda-info-grid > *', '.stats-grid > *',
        '.stat-grid > *', '.app-card', '.panel', '.summary-box', '.page-body > *',
        '.page-head .container > *', '.cta-inner > *', '.filters', '.cart-summary',
        '.checkout-form', '.auth-card', '.course-detail > *', '.info-grid > *',
        '.service-tile',
    ];
    autoTargets.forEach((sel) => {
        document.querySelectorAll(sel).forEach((el, i) => {
            if (!el.classList.contains('reveal')) {
                el.classList.add('reveal');
                if (i < 6) el.classList.add('reveal-delay-' + Math.min(i, 5));
            }
        });
    });

    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && !reduceMotion) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

        reveals.forEach((el) => io.observe(el));

        // Elemen yang sudah terlihat saat halaman dibuka
        window.addEventListener('load', () => {
            reveals.forEach((el) => {
                if (el.getBoundingClientRect().top < window.innerHeight * 0.92) {
                    el.classList.add('is-visible');
                }
            });
        });
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
    }

    if (reduceMotion) return;

    /* ---------- 4. Sorotan mengikuti kursor pada kartu ---------- */
    document.querySelectorAll('.course-card, .tp-card, .service-tile').forEach((card) => {
        card.addEventListener('pointermove', (e) => {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
            card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
        });
    });

    /* ---------- 5. Parallax ringan pada hero ---------- */
    const layers = [
        { el: document.querySelector('.hero-visual'), speed: 0.05 },
        { el: document.querySelector('.hero-tech-circle'), speed: -0.03 },
    ].filter((l) => l.el);

    const floaters = document.querySelectorAll('.hero-float-card');
    const particles = document.querySelectorAll('.hero-particle');

    if (layers.length || floaters.length) {
        let pRaf = null;
        const parallax = () => {
            const y = window.scrollY;
            if (y > window.innerHeight * 1.3) { pRaf = null; return; }
            layers.forEach(({ el, speed }) => { el.style.transform = `translate3d(0, ${(y * speed).toFixed(1)}px, 0)`; });

            const t = performance.now() / 1000;
            floaters.forEach((f, i) => {
                const depth = (i + 1) * 0.4;
                f.style.setProperty('--py', (Math.sin(t * 0.9 + i) * 6 * depth).toFixed(2) + 'px');
                f.style.marginTop = (y * 0.02 * depth).toFixed(1) + 'px';
            });
            particles.forEach((p, i) => {
                p.style.transform = `translate3d(${(Math.sin(t * 0.6 + i) * 14).toFixed(1)}px, ${(y * -0.02).toFixed(1)}px, 0)`;
            });
            pRaf = null;
        };
        window.addEventListener('scroll', () => { if (!pRaf) pRaf = requestAnimationFrame(parallax); }, { passive: true });
    }

    /* ---------- 6. Judul hero mengetik lembut ---------- */
    const heroTitle = document.querySelector('.hero-text h1');
    if (heroTitle) {
        heroTitle.style.opacity = '0';
        heroTitle.style.transform = 'translateY(16px)';
        heroTitle.style.transition = 'opacity .8s ease .1s, transform .8s cubic-bezier(.16,1,.3,1) .1s';
        requestAnimationFrame(() => {
            heroTitle.style.opacity = '1';
            heroTitle.style.transform = 'translateY(0)';
        });
    }

    /* ---------- 7. Angka statistik hero naik halus ---------- */
    document.querySelectorAll('.hero-stat-item h3').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(12px)';
        el.style.transition = `opacity .6s ease ${0.5 + i * 0.14}s, transform .6s cubic-bezier(.16,1,.3,1) ${0.5 + i * 0.14}s`;
        requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
    });

    /* ---------- 8. Perbesar gambar panduan saat hover ---------- */
    document.querySelectorAll('.course-thumb, .tp-card-media').forEach((media) => {
        media.addEventListener('pointermove', (e) => {
            const r = media.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            const inner = media.querySelector('.course-thumb-icon, .tp-card-emoji');
            if (inner) inner.style.transform = `translate(${px * 12}px, ${py * 10}px) scale(1.12)`;
        });
        media.addEventListener('pointerleave', () => {
            const inner = media.querySelector('.course-thumb-icon, .tp-card-emoji');
            if (inner) inner.style.transform = '';
        });
    });

    /* ---------- 9. Tombol tambah ke keranjang: pantulan ikon keranjang ---------- */
    document.querySelectorAll('form[action*="keranjang"]').forEach((form) => {
        form.addEventListener('submit', () => {
            const cart = document.querySelector('.cart-btn');
            cart?.classList.add('bump');
            setTimeout(() => cart?.classList.remove('bump'), 700);
        });
    });

    /* ---------- 10. Notifikasi melayang menutup sendiri ---------- */
    document.querySelectorAll('.alert').forEach((alert, i) => {
        if (alert.closest('.floating-alert')) return;
        const box = alert.cloneNode(true);
        box.classList.add('floating-alert');
        box.style.top = `${1.2 + i * 4.4}rem`;
        alert.style.display = 'none';
        document.body.appendChild(box);

        const close = () => {
            box.classList.add('is-hiding');
            setTimeout(() => box.remove(), 350);
        };
        box.addEventListener('click', close);
        setTimeout(close, 5200);
    });
})();
