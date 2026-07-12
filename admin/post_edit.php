<?php
// admin/post_edit.php – skapa och redigera inlägg Ⓐ Style

require_once __DIR__ . '/bootstrap.php';

$content_type = defined('ADMIN_CONTENT_TYPE') ? ADMIN_CONTENT_TYPE : 'article';
$content_config = admin_content_config($content_type);
$content_type = $content_config['type'];
$is_cmt = $content_type === 'cmt';
$list_path = $content_config['list_path'];
$edit_path = $content_config['edit_path'];
$content_label = $content_config['singular'];
$image_label = $content_config['image_label'];

$post_model     = new Post($pdo);
$media_model    = new Media($pdo);
$category_model = new Category($pdo);
$tag_model      = new Tag($pdo);
$media_service  = new MediaService($media_model);
$post_service   = new PostService($pdo, $post_model, $media_model, $category_model, $tag_model, $media_service);

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $id ? $post_model->get_by_id($id) : null;
if ($post && ($post['content_type'] ?? 'article') !== $content_type) {
    http_response_code(404);
    die('Innehållet hittades inte.');
}
$all_categories = $category_model->get_all();
$post_cat_ids   = [];
$post_gallery   = [];
$post_tags      = [];
$post_links     = [];

if ($post) {
    $stmt = $pdo->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
    $stmt->execute([$post['id']]);
    $post_cat_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $post_gallery = $post_model->get_gallery($post['id']);
    $post_tags    = $tag_model->get_for_post($post['id']);
    $post_links   = $is_cmt ? $post_model->get_links($post['id']) : [];
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    csrf_verify();

    $result = $post_service->save($_POST, $_FILES, $post ?: null, $post_gallery, $content_type);

    if ($result->ok) {
        $id = $result->id ?? $id;

        if (!$post) {
            header('Location: ' . query_url($edit_path, ['id' => $id, 'saved' => 1]));
            exit;
        }

        $success = $result->message;
        $post = $post_model->get_by_id($id);
        $post_cat_ids = $result->data['category_ids'] ?? [];
        $post_gallery = $post_model->get_gallery($id);
        $post_tags = $tag_model->get_for_post($id);
        $post_links = $is_cmt ? $post_model->get_links($id) : [];
    } else {
        $errors = $result->errors;
        $post_cat_ids = array_map('intval', $_POST['categories'] ?? []);
        if ($is_cmt) {
            $labels = is_array($_POST['link_label'] ?? null) ? $_POST['link_label'] : [];
            $urls = is_array($_POST['link_url'] ?? null) ? $_POST['link_url'] : [];
            $post_links = [];
            for ($i = 0, $count = max(count($labels), count($urls)); $i < $count; $i++) {
                $post_links[] = [
                    'label' => (string)($labels[$i] ?? ''),
                    'url' => (string)($urls[$i] ?? ''),
                ];
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $success = match ($content_type) {
        'news' => 'Nyheten sparades.',
        'cmt' => 'Tipset sparades.',
        default => 'Artikeln sparades.',
    };
}

$page_title  = $post ? 'Redigera: ' . $post['title'] : $content_config['new_label'];
$f_title        = $_POST['title']        ?? ($post['title']          ?? '');
$f_slug         = $_POST['slug']         ?? ($post['slug']           ?? '');
$f_intro        = $_POST['intro']        ?? ($post['intro']          ?? '');
$f_body         = $_POST['body']         ?? ($post['body']           ?? '');
$f_location     = $_POST['location']     ?? ($post['location']       ?? '');
$f_latitude     = $_POST['latitude']     ?? ($post['latitude']       ?? '');
$f_longitude    = $_POST['longitude']    ?? ($post['longitude']      ?? '');
$f_post_date    = $_POST['post_date']    ?? ($post['post_date']      ?? date('Y-m-d'));
$f_publish_date = $_POST['publish_date'] ?? ($post['publish_date']   ?? '');
$f_status       = $_POST['status']       ?? ($post['status']         ?? 'draft');
$f_cover_id     = $post['cover_image_id'] ?? null;
$f_tag_input    = $_POST['tags'] ?? implode(' ', array_map(
    static fn ($tag): string => '#' . $tag['name'],
    $post_tags
));
$f_body_editor = editor_body_html((string)$f_body);

require_once __DIR__ . '/includes/header.php';
?>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:var(--s3);">
    <h1 class="page-title" style="margin:0;"><?= $post ? 'Redigera ' . e($content_label) : e($content_config['new_label']) ?></h1>
    <a href="<?= e(url($list_path)) ?>" class="btn btn--ghost btn--sm">← Alla <?= e($content_config['plural']) ?></a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error" style="margin-bottom:var(--s2);"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert--success" style="margin-bottom:var(--s2);"><?= e($success) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" action="<?= $id ? e(query_url($edit_path, ['id' => $id])) : '' ?>">
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
        <input type="hidden" id="body" name="body" value="<?= e($f_body) ?>">
        <div class="wysiwyg-toolbar" aria-label="Textverktyg">
          <button type="button" class="btn btn--ghost btn--sm" data-command="formatBlock" data-value="h2">H2</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="formatBlock" data-value="h3">H3</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="bold"><strong>B</strong></button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="italic"><em>I</em></button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="underline"><u>U</u></button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="insertUnorderedList">Lista</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="insertOrderedList">1. Lista</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="formatBlock" data-value="blockquote">Citat</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="createLink">Länk</button>
          <button type="button" class="btn btn--ghost btn--sm" data-command="formatBlock" data-value="p">Text</button>
        </div>
        <div id="body_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-multiline="true"><?= $f_body_editor ?></div>
        <p class="form-hint">HTML sparas med tillåtna format: rubriker, fet/kursiv/understruken text, listor, citat och länkar.</p>
      </div>
    </div>

    <div class="card">
      <p class="card__title">📍 Metadata</p>
      <div class="form-group">
        <label for="location">Plats</label>
        <input type="text" id="location" name="location" class="form-control"
               value="<?= e($f_location) ?>" placeholder="t.ex. Chiang Mai">
      </div>
      <?php if ($is_cmt): ?>
      <div class="coordinate-grid">
        <div class="form-group">
          <label for="latitude">Latitud</label>
          <input type="number" id="latitude" name="latitude" class="form-control"
                 value="<?= e($f_latitude) ?>" min="-90" max="90" step="0.0000001"
                 placeholder="t.ex. 18.7883">
        </div>
        <div class="form-group">
          <label for="longitude">Longitud</label>
          <input type="number" id="longitude" name="longitude" class="form-control"
                 value="<?= e($f_longitude) ?>" min="-180" max="180" step="0.0000001"
                 placeholder="t.ex. 98.9853">
        </div>
      </div>
      <p class="form-hint">Valfritt. Fyll i både latitud och longitud för att visa en Google Maps-länk.</p>
      <?php endif; ?>
      <div class="form-group">
        <label for="post_date">Datum</label>
        <input type="date" id="post_date" name="post_date" class="form-control"
               value="<?= e($f_post_date) ?>">
      </div>
      <div class="form-group">
        <label for="publish_date">Publiceringsdatum</label>
        <input type="date" id="publish_date" name="publish_date" class="form-control"
               value="<?= e($f_publish_date) ?>">
        <p class="form-hint">Valfritt. Lämna tomt för att publicera direkt när status är Publicerat.</p>
      </div>
      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control">
          <option value="draft"     <?= $f_status === 'draft'     ? 'selected' : '' ?>>Utkast</option>
          <option value="published" <?= $f_status === 'published' ? 'selected' : '' ?>>Publicerat</option>
        </select>
      </div>
    </div>

    <?php if ($is_cmt): ?>
    <div class="card">
      <p class="card__title">🔗 Externa länkar</p>
      <p class="form-hint">Valfritt. Lägg till länkar till exempelvis en restaurangs webbplats eller en informationssida.</p>
      <div id="tip-links" class="tip-links">
        <?php foreach ($post_links as $link): ?>
        <div class="tip-link-row">
          <div class="form-group">
            <label>Länktext</label>
            <input type="text" name="link_label[]" class="form-control"
                   value="<?= e($link['label'] ?? '') ?>" placeholder="t.ex. Besök webbplatsen">
          </div>
          <div class="form-group">
            <label>URL</label>
            <input type="url" name="link_url[]" class="form-control"
                   value="<?= e($link['url'] ?? '') ?>" placeholder="https://example.com">
          </div>
          <button type="button" class="btn btn--danger btn--sm tip-link-remove">Ta bort</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" id="add-tip-link" class="btn btn--ghost btn--sm">+ Lägg till länk</button>
    </div>
    <?php endif; ?>

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
      <p class="card__title">🏷️ Taggar</p>
      <div class="form-group">
        <label for="tags">#taggar</label>
        <input type="text" id="tags" name="tags" class="form-control"
               value="<?= e($f_tag_input) ?>" placeholder="#thailand #resa #vardag">
        <p class="form-hint">Skriv taggar med # eller mellanslag. Nya taggar skapas automatiskt.</p>
      </div>
    </div>

    <div class="card">
      <p class="card__title">🖼️ Omslagsbild</p>
      <?php if ($f_cover_id && ($cover = $media_model->get_by_id($f_cover_id))): ?>
        <img src="<?= e(upload_url($cover['file_name'])) ?>"
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

    <div class="card">
      <p class="card__title">📷 <?= ucfirst(e($image_label)) ?></p>

      <?php if (!empty($post_gallery)): ?>
        <div class="article-image-grid">
          <?php foreach ($post_gallery as $img): ?>
          <div class="article-image-item">
            <img
              src="<?= e(upload_url($img['file_name'])) ?>"
              alt="<?= e($img['alt_text'] ?: $img['original_name']) ?>"
              loading="lazy"
            >
            <label class="article-image-remove">
              <input type="checkbox" name="remove_media[]" value="<?= (int)$img['id'] ?>">
              Ta bort
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="form-group">
        <label for="article_images">Ladda upp <?= e($image_label) ?></label>
        <input type="file" id="article_images" name="article_images[]"
               class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
        <p class="form-hint">JPG, PNG eller WebP. Max 5 bilder totalt per innehållspost.</p>
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn--primary">💾 Spara <?= e($content_label) ?></button>
      <?php if ($post): ?>
        <a href="<?= url('post.php?slug=' . e($post['slug'])) ?>" target="_blank"
           class="btn btn--ghost">↗ Förhandsgranska</a>
      <?php endif; ?>
      <a href="<?= e(url($list_path)) ?>" class="btn btn--ghost">Avbryt</a>
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

    const editor = document.getElementById('body_editor');
    const bodyField = document.getElementById('body');

    function syncEditor() {
      bodyField.value = editor.innerHTML.trim();
    }

    document.querySelectorAll('.wysiwyg-toolbar [data-command]').forEach((button) => {
      button.addEventListener('click', () => {
        editor.focus();
        const command = button.dataset.command;
        let value = button.dataset.value || null;

        if (command === 'createLink') {
          value = prompt('Länkens URL');
          if (!value) return;
        }

        document.execCommand(command, false, value);
        syncEditor();
      });
    });

    editor.addEventListener('input', syncEditor);
    editor.closest('form').addEventListener('submit', syncEditor);

    <?php if ($is_cmt): ?>
    const linksContainer = document.getElementById('tip-links');
    const addLinkButton = document.getElementById('add-tip-link');

    function addLinkRow(label = '', url = '') {
      const row = document.createElement('div');
      row.className = 'tip-link-row';
      row.innerHTML = `
        <div class="form-group">
          <label>Länktext</label>
          <input type="text" name="link_label[]" class="form-control" placeholder="t.ex. Besök webbplatsen">
        </div>
        <div class="form-group">
          <label>URL</label>
          <input type="url" name="link_url[]" class="form-control" placeholder="https://example.com">
        </div>
        <button type="button" class="btn btn--danger btn--sm tip-link-remove">Ta bort</button>`;
      row.querySelector('[name="link_label[]"]').value = label;
      row.querySelector('[name="link_url[]"]').value = url;
      linksContainer.appendChild(row);
    }

    addLinkButton.addEventListener('click', () => addLinkRow());
    linksContainer.addEventListener('click', (event) => {
      if (event.target.classList.contains('tip-link-remove')) {
        event.target.closest('.tip-link-row').remove();
      }
    });

    if (!linksContainer.children.length) addLinkRow();
    <?php endif; ?>
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
