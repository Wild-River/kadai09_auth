(() => {
    const gallery = document.querySelector('.article-gallery');
    const lightbox = document.getElementById('lightbox');
    if (!gallery || !lightbox) return;

    const lightboxImg = lightbox.querySelector('.lightbox__img');
    const closeBtn = lightbox.querySelector('.lightbox__close');

    const open = (src, alt) => {
        lightboxImg.src = src;
        lightboxImg.alt = alt;
        lightbox.hidden = false;
    };

    const close = () => {
        lightbox.hidden = true;
        lightboxImg.src = '';
    };

    gallery.addEventListener('click', (e) => {
        const img = e.target.closest('img');
        if (!img) return;
        open(img.src, img.alt);
    });

    closeBtn.addEventListener('click', close);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !lightbox.hidden) close();
    });
})();
