## 2026-05-27
- Skapade backup: `/var/www/thailand_backup_20260527_161212`.
- Integrerade Chillhasse & Apan-designen i den publika siten.
- Kopierade `hero-reference.png` till `assets/img/hero-reference.png` och skapade lättare `hero-reference.webp`/`.jpg`.
- Ersatte publik CSS med comic/resejournal-känsla: mörk bakgrund, gula/pink/gröna paneler, kraftiga ramar och mobil-first grids.
- Uppdaterade `index.php` med hero, seriepaneler, senaste inlägg, senaste bilder, studielogg och adminlänk.
- Uppdaterade brand/nav/footer i `post.php`, `category.php`, `gallery.php` och `about.php`.
- Behöll befintlig PHP-logik, databas, admin och uploads.

## 2026-05-27
- Skapade backup före redesign: `/var/www/thailand_backup_before_cutter_style_20260527_163345`.
- Bytte bort comic-designen till Cutter-inspirerad mörk design med orange accent, paneler och monospace.
- Lade till `app/public_layout.php` som gemensam publik layout för header/footer, motsvarande base-tänket från Cutter.
- Kopplade `index.php`, `post.php`, `category.php`, `gallery.php` och `about.php` till den gemensamma layouten.
- Verifierade publik sida, galleri, post, om-sida och admin-login i mobil och desktop utan overflow eller trasiga bilder.
