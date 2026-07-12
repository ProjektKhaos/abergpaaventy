<?php
// admin/settings.php – grundinställningar för webbplatsen Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$settings_model = new Settings($pdo);
$success = '';
$errors = [];
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$allowed_keys = [
    'site_title'       => 'Webbplatsnamn',
    'site_description' => 'Standardbeskrivning',
    'home_intro'       => 'Startsidans intro',
];

if ($request_method === 'POST') {
    csrf_verify();

    $values = [];
    foreach ($allowed_keys as $key => $label) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    $settings_model->set_many($values);
    $success = 'Inställningarna sparades.';
}

$settings = array_merge([
    'site_title'       => 'Åberg På Äventyr',
    'site_description' => 'Studier, vardag, bilder och små äventyr från Chiang Mai.',
    'home_intro'       => '',
], $settings_model->all());

$page_title = 'Inställningar';

require_once __DIR__ . '/includes/header.php';
?>

  <h1 class="page-title">Inställningar</h1>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
  <?php endif; ?>

  <div class="card">
    <p class="card__title">Webbplats</p>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label for="site_title">Webbplatsnamn</label>
        <input type="text" id="site_title" name="site_title" class="form-control"
               value="<?= e($settings['site_title']) ?>">
      </div>

      <div class="form-group">
        <label for="site_description">Standardbeskrivning</label>
        <textarea id="site_description" name="site_description" class="form-control" rows="3"><?= e($settings['site_description']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="home_intro">Startsidans intro</label>
        <textarea id="home_intro" name="home_intro" class="form-control" rows="4"><?= e($settings['home_intro']) ?></textarea>
        <p class="form-hint">Förberett för startsidan. Nuvarande publika texter ligger fortfarande i PHP-vyerna.</p>
      </div>

      <button type="submit" class="btn btn--primary">Spara inställningar</button>
    </form>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
