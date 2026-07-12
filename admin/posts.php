<?php
// admin/posts.php – lista och hantera alla inlägg Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$content_type = defined('ADMIN_CONTENT_TYPE') ? ADMIN_CONTENT_TYPE : 'article';
$content_config = admin_content_config($content_type);
$content_type = $content_config['type'];
$list_path = $content_config['list_path'];
$edit_path = $content_config['edit_path'];
$plural_label = $content_config['plural'];
$singular_label = $content_config['singular_definite'];

$post_model = new Post($pdo);

// Ta bort inlägg
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $post_model->delete((int)$_POST['delete_id']);
    header('Location: ' . url($list_path . '?deleted=1'));
    exit;
}

$posts      = $post_model->get_all($content_type);
$today      = date('Y-m-d');
$page_title = $content_config['page_title'];

require_once __DIR__ . '/includes/header.php';
?>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:var(--s3);">
    <h1 class="page-title" style="margin:0;">Alla <?= e($plural_label) ?></h1>
    <a href="<?= e(url($edit_path)) ?>" class="btn btn--primary">✏️ <?= e($content_config['new_label']) ?></a>
  </div>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);"><?= ucfirst(e($singular_label)) ?> togs bort.</div>
  <?php endif; ?>
  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);"><?= ucfirst(e($singular_label)) ?> sparades.</div>
  <?php endif; ?>

  <?php if (empty($posts)): ?>
    <div class="card" style="text-align:center;padding:var(--s4);">
      <p style="color:var(--muted);">Inga <?= e($plural_label) ?> ännu.</p>
      <a href="<?= e(url($edit_path)) ?>" class="btn btn--primary">Skapa första <?= e($content_config['singular']) ?></a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Titel</th>
            <th>Status</th>
            <th>Artikelns datum</th>
            <th>Publicering</th>
            <th>Plats</th>
            <th style="min-width:130px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $p): ?>
          <?php
            $status = admin_status_label($p, $today);
          ?>
          <tr>
            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">
              <?= e($p['title']) ?>
            </td>
            <td>
              <span class="badge badge--<?= e($status['class']) ?>">
                <?= e($status['label']) ?>
              </span>
            </td>
            <td><?= $p['post_date'] ? e(format_date($p['post_date'])) : '—' ?></td>
            <td><?= $p['publish_date'] ? e(format_date($p['publish_date'])) : 'Direkt' ?></td>
            <td><?= $p['location'] ? e($p['location']) : '—' ?></td>
            <td>
              <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <a href="<?= e(query_url($edit_path, ['id' => $p['id']])) ?>"
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
      if (!confirm('Är du säker på att du vill ta bort <?= e($singular_label) ?>:\n"' + title + '"?')) return;

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
