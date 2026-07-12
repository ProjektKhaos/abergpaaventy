<?php
// admin/media.php – mediabibliotek och bilduppladdning Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$media_model = new Media($pdo);
$media_service = new MediaService($media_model);
$success     = '';
$error       = '';

// Hantera uppladdning
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images'])) {
    csrf_verify();

    $alt     = trim($_POST['alt']     ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $result  = $media_service->upload_many($_FILES['images'], $alt, $caption);

    if ($result->ok) {
        $success = $result->message;
    } else {
        $error = implode(' ', $result->errors);
    }
}

$images     = $media_model->get_all();
$page_title = 'Media';

require_once __DIR__ . '/includes/header.php';
?>

  <h1 class="page-title">📷 Mediabibliotek</h1>

  <?php if ($success): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert--error" style="margin-bottom:var(--s2);"><?= e($error) ?></div>
  <?php endif; ?>

  <!-- Uppladdningsformulär -->
  <div class="card" style="margin-bottom:var(--s3);">
    <p class="card__title">⬆️ Ladda upp bilder</p>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label for="images">Välj bild(er)</label>
        <input type="file" id="images" name="images[]"
               class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
        <p class="form-hint">JPG, PNG eller WebP. Du kan välja flera bilder samtidigt.</p>
      </div>

      <div class="form-group">
        <label for="alt">Alt-text</label>
        <input type="text" id="alt" name="alt" class="form-control"
               placeholder="Beskriv bilden kort">
        <p class="form-hint">Viktig för tillgänglighet och SEO.</p>
      </div>

      <div class="form-group">
        <label for="caption">Bildtext</label>
        <input type="text" id="caption" name="caption" class="form-control"
               placeholder="Valfri bildtext">
      </div>

      <button type="submit" class="btn btn--primary">⬆️ Ladda upp</button>
    </form>
  </div>

  <!-- Bildgalleri -->
  <?php if (empty($images)): ?>
    <div class="card" style="text-align:center;padding:var(--s4);">
      <p style="color:var(--muted);">Inga bilder uppladdade ännu.</p>
    </div>
  <?php else: ?>
    <p class="card__title"><?= count($images) ?> uppladdade bilder</p>
    <div class="media-grid">
      <?php foreach ($images as $img): ?>
      <div style="position:relative;">
        <img
          class="media-thumb"
          src="<?= UPLOAD_URL . e($img['file_name']) ?>"
          alt="<?= e($img['alt_text'] ?: $img['original_name']) ?>"
          loading="lazy"
          title="ID: <?= $img['id'] ?> | <?= e($img['original_name']) ?>"
        >
        <div style="font-size:0.68rem;color:var(--muted);margin-top:0.2rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
          ID <?= $img['id'] ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
