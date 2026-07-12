<?php
// admin/tags.php – hantera taggar Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$tag_model = new Tag($pdo);
$errors = [];
$success = '';
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = false;
if ($edit_id) {
    foreach ($tag_model->get_all() as $tag) {
        if ((int)$tag['id'] === $edit_id) {
            $editing = $tag;
            break;
        }
    }
}

if ($request_method === 'POST') {
    csrf_verify();

    if (isset($_POST['delete_id'])) {
        $tag_model->delete((int)$_POST['delete_id']);
        header('Location: ' . url('admin/tags.php?deleted=1'));
        exit;
    }

    if (isset($_POST['save_tag'])) {
        $id = (int)($_POST['tag_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);

        if ($name === '') {
            $errors[] = 'Namn är obligatoriskt.';
        }
        if ($slug === '') {
            $errors[] = 'Slug kunde inte skapas.';
        }

        if (!$errors) {
            try {
                if ($id > 0) {
                    $tag_model->update($id, $name, $slug);
                } else {
                    $tag_model->create($name, $slug);
                }
                header('Location: ' . url('admin/tags.php?saved=1'));
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors[] = 'Slug används redan. Välj en annan slug.';
                } else {
                    throw $e;
                }
            }
        }
    }
}

if (isset($_GET['saved'])) $success = 'Taggen sparades.';
if (isset($_GET['deleted'])) $success = 'Taggen togs bort.';

$tags = $tag_model->get_all();
$page_title = 'Taggar';

require_once __DIR__ . '/includes/header.php';
?>

  <h1 class="page-title">Taggar</h1>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <p class="card__title"><?= $editing ? 'Redigera tagg' : 'Ny tagg' ?></p>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="save_tag" value="1">
      <input type="hidden" name="tag_id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

      <div class="form-group">
        <label for="name">Namn</label>
        <input type="text" id="name" name="name" class="form-control"
               value="<?= e($_POST['name'] ?? ($editing['name'] ?? '')) ?>" required>
      </div>
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" class="form-control"
               value="<?= e($_POST['slug'] ?? ($editing['slug'] ?? '')) ?>">
      </div>
      <div class="btn-row">
        <button type="submit" class="btn btn--primary">Spara tagg</button>
        <?php if ($editing): ?>
          <a href="<?= url('admin/tags.php') ?>" class="btn btn--ghost">Avbryt</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Namn</th>
          <th>Slug</th>
          <th>Inlägg</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tags as $tag): ?>
        <tr>
          <td>#<?= e($tag['name']) ?></td>
          <td><?= e($tag['slug']) ?></td>
          <td><?= $tag_model->count_posts((int)$tag['id']) ?></td>
          <td>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
              <a href="<?= url('admin/tags.php?id=' . (int)$tag['id']) ?>" class="btn btn--ghost btn--sm">Redigera</a>
              <form method="POST" onsubmit="return confirm('Ta bort taggen?');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="delete_id" value="<?= (int)$tag['id'] ?>">
                <button type="submit" class="btn btn--danger btn--sm">Ta bort</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
