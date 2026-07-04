// script.js – frontend JavaScript för Thailand-bloggen Ⓐ Style
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
'use strict';

// Enkel och snäll lightbox: används bara på länkar markerade med data-lightbox.
document.querySelectorAll('[data-lightbox]').forEach(item => {
  item.addEventListener('click', event => {
    event.preventDefault();

    const img = item.querySelector('img');
    const src = item.getAttribute('href') || (img ? img.src : '');
    if (!src) return;

    const overlay = document.createElement('div');
    overlay.className = 'lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-label', 'Förstorad bild');

    const fullImg = document.createElement('img');
    fullImg.src = src;
    fullImg.alt = img ? img.alt : 'Förstorad bild';

    overlay.appendChild(fullImg);
    overlay.addEventListener('click', () => overlay.remove());
    document.addEventListener('keydown', function closeOnEscape(e) {
      if (e.key === 'Escape') {
        overlay.remove();
        document.removeEventListener('keydown', closeOnEscape);
      }
    });

    document.body.appendChild(overlay);
  });
});
