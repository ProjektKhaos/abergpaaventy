// admin/assets/admin.js – adminpanel JavaScript Ⓐ Style
'use strict';

// Markera aktiv nav-länk
document.querySelectorAll('.admin-nav a').forEach(a => {
  if (a.href.split('?')[0] === location.href.split('?')[0]) {
    a.classList.add('active');
  }
});
