<?php
// admin/dashboard.php – översikt för adminpanelen Ⓐ Style

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Post.php';

$auth = new Auth($pdo);
$auth->require_login();

$post_model = new Post($pdo);
$published  = $post_model->count_published();
$drafts     = $post_model->count_drafts();
$latest     = $post_model->get_all();
$latest     = array_slice($latest, 0, 5); // Visa max 5 senaste

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

  <h1 class="page-title">👋 Hej, <?= e($auth->name()) ?>!</h1>

  <!-- Statistik-kort -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-card__number"><?= $published ?></div>
      <div class="stat-card__label">Publicerade inlägg</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__number"><?= $drafts ?></div>
      <div class="stat-card__label">Utkast</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__number"><?= $published + $drafts ?></div>
      <div class="stat-card__label">Totalt</div>
    </div>
  </div>

  <!-- Snabbknappar -->
  <div class="btn-row" style="margin-bottom:var(--s3);">
    <a href="<?= url('admin/post_edit.php') ?>" class="btn btn--primary">
      ✏️ Nytt inlägg
    </a>
    <a href="<?= url('admin/media.php') ?>" class="btn btn--ghost">
      📷 Ladda upp bild
    </a>
    <a href="<?= url() ?>" target="_blank" class="btn btn--ghost">
      ↗ Se sidan
    </a>
  </div>

  <!-- Senaste inlägg -->
  <?php if (!empty($latest)): ?>
  <p class="card__title">Senaste inlägg</p>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Titel</th>
          <th>Status</th>
          <th>Datum</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latest as $p): ?>
        <tr>
          <td><?= e($p['title']) ?></td>
          <td>
            <span class="badge badge--<?= $p['status'] ?>">
              <?= $p['status'] === 'published' ? 'Publicerat' : 'Utkast' ?>
            </span>
          </td>
          <td><?= $p['post_date'] ? e(format_date($p['post_date'])) : '—' ?></td>
          <td>
            <a href="<?= url('admin/post_edit.php?id=' . $p['id']) ?>"
               class="btn btn--ghost btn--sm">Redigera</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="margin-top:var(--s2);">
    <a href="<?= url('admin/posts.php') ?>" class="btn btn--ghost btn--sm">Visa alla inlägg →</a>
  </div>
  <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
