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
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Förstorad bild');
    overlay.tabIndex = -1;

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'lightbox__close';
    closeButton.textContent = 'Stäng';
    closeButton.setAttribute('aria-label', 'Stäng förstorad bild');

    const fullImg = document.createElement('img');
    fullImg.src = src;
    fullImg.alt = img ? img.alt : 'Förstorad bild';

    function closeLightbox() {
      overlay.remove();
      document.body.classList.remove('lightbox-open');
      document.removeEventListener('keydown', closeOnEscape);
      item.focus({ preventScroll: true });
    }

    function closeOnEscape(e) {
      if (e.key === 'Escape') {
        closeLightbox();
      }
    }

    closeButton.addEventListener('click', closeLightbox);
    overlay.addEventListener('click', event => {
      if (event.target === overlay) {
        closeLightbox();
      }
    });
    document.addEventListener('keydown', closeOnEscape);

    overlay.appendChild(closeButton);
    overlay.appendChild(fullImg);
    document.body.classList.add('lightbox-open');
    document.body.appendChild(overlay);
    closeButton.focus({ preventScroll: true });
  });
});
