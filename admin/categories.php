<?php
// admin/categories.php – hantera kategorier Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$category_model = new Category($pdo);
$errors = [];
$success = '';
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $edit_id ? $category_model->get_by_id($edit_id) : false;

if ($request_method === 'POST') {
    csrf_verify();

    if (isset($_POST['delete_id'])) {
        $category_model->delete((int)$_POST['delete_id']);
        header('Location: ' . url('admin/categories.php?deleted=1'));
        exit;
    }

    if (isset($_POST['save_category'])) {
        $id = (int)($_POST['category_id'] ?? 0);
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
                    $category_model->update($id, $name, $slug);
                    header('Location: ' . url('admin/categories.php?saved=1'));
                    exit;
                }

                $category_model->create($name, $slug);
                header('Location: ' . url('admin/categories.php?saved=1'));
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

if (isset($_GET['saved'])) $success = 'Kategorin sparades.';
if (isset($_GET['deleted'])) $success = 'Kategorin togs bort.';

$categories = $category_model->get_all();
$page_title = 'Kategorier';

require_once __DIR__ . '/includes/header.php';
?>

  <h1 class="page-title">Kategorier</h1>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <p class="card__title"><?= $editing ? 'Redigera kategori' : 'Ny kategori' ?></p>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="save_category" value="1">
      <input type="hidden" name="category_id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

      <div class="form-group">
        <label for="name">Namn</label>
        <input type="text" id="name" name="name" class="form-control"
               value="<?= e($_POST['name'] ?? ($editing['name'] ?? '')) ?>" required>
      </div>
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" class="form-control"
               value="<?= e($_POST['slug'] ?? ($editing['slug'] ?? '')) ?>">
        <p class="form-hint">Lämna tomt för automatisk slug från namnet.</p>
      </div>
      <div class="btn-row">
        <button type="submit" class="btn btn--primary">Spara kategori</button>
        <?php if ($editing): ?>
          <a href="<?= url('admin/categories.php') ?>" class="btn btn--ghost">Avbryt</a>
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
        <?php foreach ($categories as $category): ?>
        <tr>
          <td><?= e($category['name']) ?></td>
          <td><?= e($category['slug']) ?></td>
          <td><?= $category_model->count_posts((int)$category['id']) ?></td>
          <td>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
              <a href="<?= url('admin/categories.php?id=' . (int)$category['id']) ?>" class="btn btn--ghost btn--sm">Redigera</a>
              <form method="POST" onsubmit="return confirm('Ta bort kategorin?');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="delete_id" value="<?= (int)$category['id'] ?>">
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
