<?php
// admin/post_edit.php – skapa och redigera inlägg Ⓐ Style

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Post.php';
require_once __DIR__ . '/../app/Media.php';
require_once __DIR__ . '/../app/Category.php';

$auth = new Auth($pdo);
$auth->require_login();

$post_model     = new Post($pdo);
$media_model    = new Media($pdo);
$category_model = new Category($pdo);

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $id ? $post_model->get_by_id($id) : null;
$all_categories = $category_model->get_all();
$post_cat_ids   = [];

if ($post) {
    $stmt = $pdo->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
    $stmt->execute([$post['id']]);
    $post_cat_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    csrf_verify();

    $title     = trim($_POST['title']    ?? '');
    $slug_raw  = trim($_POST['slug']     ?? '');
    $intro     = trim($_POST['intro']    ?? '');
    $body      = trim($_POST['body']     ?? '');
    $location  = trim($_POST['location'] ?? '');
    $post_date = trim($_POST['post_date']?? date('Y-m-d'));
    $status    = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $cover_id  = !empty($_POST['cover_image_id']) ? (int)$_POST['cover_image_id'] : null;
    $cat_ids   = array_map('intval', $_POST['categories'] ?? []);
    $slug      = $slug_raw ?: slugify($title);

    if (!$title) $errors[] = 'Titel är obligatorisk.';
    if (!$slug)  $errors[] = 'Slug kunde inte skapas.';

    if (!empty($_FILES['cover_upload']['name'])) {
        $new_media_id = $media_model->upload($_FILES['cover_upload']);
        if ($new_media_id) {
            $cover_id = $new_media_id;
        } else {
            $errors[] = 'Bilduppladdning misslyckades. Kontrollera filtyp (jpg, png, webp).';
        }
    }

    if (empty($errors)) {
        try {
            $data = [
                'title'          => $title,
                'slug'           => $slug,
                'intro'          => $intro,
                'body'           => $body,
                'location'       => $location,
                'post_date'      => $post_date,
                'status'         => $status,
                'cover_image_id' => $cover_id,
            ];

            if ($id && $post) {
                $post_model->update($id, $data);
                $category_model->sync_post_categories($id, $cat_ids);
                $success      = 'Inlägget uppdaterades.';
                $post         = $post_model->get_by_id($id);
                $post_cat_ids = $cat_ids;
            } else {
                $new_id = $post_model->create($data);
                $category_model->sync_post_categories($new_id, $cat_ids);
                header('Location: ' . url('admin/post_edit.php?id=' . $new_id . '&saved=1'));
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Slug används redan. Välj en annan slug.';
            } else {
                throw $e;
            }
        }
    }
}

if (isset($_GET['saved'])) $success = 'Inlägget sparades.';

$page_title  = $post ? 'Redigera: ' . $post['title'] : 'Nytt inlägg';
$f_title     = $post['title']          ?? '';
$f_slug      = $post['slug']           ?? '';
$f_intro     = $post['intro']          ?? '';
$f_body      = $post['body']           ?? '';
$f_location  = $post['location']       ?? '';
$f_post_date = $post['post_date']      ?? date('Y-m-d');
$f_status    = $post['status']         ?? 'draft';
$f_cover_id  = $post['cover_image_id'] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:var(--s3);">
    <h1 class="page-title" style="margin:0;"><?= $post ? 'Redigera inlägg' : 'Nytt inlägg' ?></h1>
    <a href="<?= url('admin/posts.php') ?>" class="btn btn--ghost btn--sm">← Alla inlägg</a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error" style="margin-bottom:var(--s2);"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);"><?= e($success) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="save_post" value="1">

    <div class="card">
      <p class="card__title">📝 Innehåll</p>
      <div class="form-group">
        <label for="title">Titel *</label>
        <input type="text" id="title" name="title" class="form-control"
               value="<?= e($f_title) ?>" required oninput="autoSlug(this.value)">
      </div>
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" class="form-control" value="<?= e($f_slug) ?>">
        <p class="form-hint">Lämna tomt för automatisk generering från titeln.</p>
      </div>
      <div class="form-group">
        <label for="intro">Ingress</label>
        <textarea id="intro" name="intro" class="form-control" rows="3"><?= e($f_intro) ?></textarea>
      </div>
      <div class="form-group">
        <label for="body">Brödtext</label>
        <textarea id="body" name="body" class="form-control" style="min-height:260px;"><?= e($f_body) ?></textarea>
      </div>
    </div>

    <div class="card">
      <p class="card__title">📍 Metadata</p>
      <div class="form-group">
        <label for="location">Plats</label>
        <input type="text" id="location" name="location" class="form-control"
               value="<?= e($f_location) ?>" placeholder="t.ex. Chiang Mai">
      </div>
      <div class="form-group">
        <label for="post_date">Datum</label>
        <input type="date" id="post_date" name="post_date" class="form-control"
               value="<?= e($f_post_date) ?>">
      </div>
      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control">
          <option value="draft"     <?= $f_status === 'draft'     ? 'selected' : '' ?>>Utkast</option>
          <option value="published" <?= $f_status === 'published' ? 'selected' : '' ?>>Publicerat</option>
        </select>
      </div>
    </div>

    <?php if (!empty($all_categories)): ?>
    <div class="card">
      <p class="card__title">📂 Kategorier</p>
      <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
        <?php foreach ($all_categories as $cat): ?>
        <label style="display:flex;align-items:center;gap:0.4rem;min-height:44px;cursor:pointer;">
          <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                 <?= in_array((int)$cat['id'], array_map('intval', $post_cat_ids)) ? 'checked' : '' ?>>
          <?= e($cat['name']) ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <p class="card__title">🖼️ Omslagsbild</p>
      <?php if ($f_cover_id && ($cover = $media_model->get_by_id($f_cover_id))): ?>
        <img src="<?= UPLOAD_URL . e($cover['file_name']) ?>"
             alt="Omslagsbild"
             style="max-height:180px;border-radius:8px;margin-bottom:var(--s2);">
        <input type="hidden" name="cover_image_id" value="<?= $f_cover_id ?>">
      <?php endif; ?>
      <div class="form-group">
        <label for="cover_upload">Ladda upp ny omslagsbild</label>
        <input type="file" id="cover_upload" name="cover_upload"
               class="form-control" accept="image/jpeg,image/png,image/webp">
        <p class="form-hint">JPG, PNG eller WebP. Max 5 MB rekommenderas.</p>
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn--primary">💾 Spara inlägg</button>
      <?php if ($post): ?>
        <a href="<?= url('post.php?slug=' . e($post['slug'])) ?>" target="_blank"
           class="btn btn--ghost">↗ Förhandsgranska</a>
      <?php endif; ?>
      <a href="<?= url('admin/posts.php') ?>" class="btn btn--ghost">Avbryt</a>
    </div>
  </form>

  <script>
    function autoSlug(title) {
      const slugField = document.getElementById('slug');
      if (slugField.dataset.manual) return;
      const slug = title.toLowerCase()
        .replace(/[åä]/g,'a').replace(/ö/g,'o')
        .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
      slugField.value = slug;
    }
    document.getElementById('slug').addEventListener('input', function() {
      this.dataset.manual = '1';
    });
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
