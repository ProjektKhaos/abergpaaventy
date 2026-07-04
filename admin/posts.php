<?php
// admin/posts.php – lista och hantera alla inlägg Ⓐ Style

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Post.php';

$auth = new Auth($pdo);
$auth->require_login();

$post_model = new Post($pdo);

// Ta bort inlägg
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $post_model->delete((int)$_POST['delete_id']);
    header('Location: ' . url('admin/posts.php?deleted=1'));
    exit;
}

$posts      = $post_model->get_all();
$page_title = 'Inlägg';

require_once __DIR__ . '/includes/header.php';
?>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:var(--s3);">
    <h1 class="page-title" style="margin:0;">Alla inlägg</h1>
    <a href="<?= url('admin/post_edit.php') ?>" class="btn btn--primary">✏️ Nytt inlägg</a>
  </div>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);">Inlägget togs bort.</div>
  <?php endif; ?>
  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);">Inlägget sparades.</div>
  <?php endif; ?>

  <?php if (empty($posts)): ?>
    <div class="card" style="text-align:center;padding:var(--s4);">
      <p style="color:var(--muted);">Inga inlägg ännu.</p>
      <a href="<?= url('admin/post_edit.php') ?>" class="btn btn--primary">Skapa första inlägget</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Titel</th>
            <th>Status</th>
            <th>Datum</th>
            <th>Plats</th>
            <th style="min-width:130px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $p): ?>
          <tr>
            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">
              <?= e($p['title']) ?>
            </td>
            <td>
              <span class="badge badge--<?= $p['status'] ?>">
                <?= $p['status'] === 'published' ? 'Publicerat' : 'Utkast' ?>
              </span>
            </td>
            <td><?= $p['post_date'] ? e(format_date($p['post_date'])) : '—' ?></td>
            <td><?= $p['location'] ? e($p['location']) : '—' ?></td>
            <td>
              <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <a href="<?= url('admin/post_edit.php?id=' . $p['id']) ?>"
                   class="btn btn--ghost btn--sm">Redigera</a>
                <!-- Radera-knapp – kräver bekräftelse -->
                <button
                  class="btn btn--danger btn--sm"
                  onclick="confirmDelete(<?= $p['id'] ?>, '<?= e(addslashes($p['title'])) ?>')"
                >Ta bort</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- Dolt radera-formulär -->
  <div id="delete-form-wrap" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" id="del_csrf">
    <input type="hidden" name="delete_id" id="del_id">
  </div>

  <script>
    function confirmDelete(id, title) {
      if (!confirm('Är du säker på att du vill ta bort inlägget:\n"' + title + '"?')) return;

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '';

      const fields = {
        csrf_token: document.getElementById('del_csrf').value,
        delete_id: id
      };
      Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
