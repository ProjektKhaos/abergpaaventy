<?php
// Admin.php – gemensamma hjälpfunktioner för adminpanelen Ⓐ Style

function admin_modules(): array
{
    return [
        'dashboard' => [
            'label' => 'Dashboard',
            'url'   => url('admin/dashboard.php'),
            'pages' => ['dashboard.php'],
        ],
        'posts' => [
            'label' => 'Artiklar',
            'url'   => url('admin/posts.php'),
            'pages' => ['posts.php', 'post_edit.php'],
        ],
        'news' => [
            'label' => 'Nyheter',
            'url'   => url('admin/news.php'),
            'pages' => ['news.php', 'news_edit.php'],
        ],
        'cmt' => [
            'label' => 'Chiang Mai Tips',
            'url'   => url('admin/cmt.php'),
            'pages' => ['cmt.php', 'cmt_edit.php'],
        ],
        'media' => [
            'label' => 'Media',
            'url'   => url('admin/media.php'),
            'pages' => ['media.php'],
        ],
        'categories' => [
            'label' => 'Kategorier',
            'url'   => url('admin/categories.php'),
            'pages' => ['categories.php'],
        ],
        'tags' => [
            'label' => 'Taggar',
            'url'   => url('admin/tags.php'),
            'pages' => ['tags.php'],
        ],
        'settings' => [
            'label' => 'Inställningar',
            'url'   => url('admin/settings.php'),
            'pages' => ['settings.php'],
        ],
    ];
}

function admin_content_config(string $content_type): array
{
    return match ($content_type) {
        'news' => [
            'type' => 'news',
            'list_path' => 'admin/news.php',
            'edit_path' => 'admin/news_edit.php',
            'page_title' => 'Nyheter',
            'singular' => 'nyhet',
            'new_label' => 'Ny nyhet',
            'singular_definite' => 'nyheten',
            'plural' => 'nyheter',
            'image_label' => 'nyhetsbilder',
        ],
        'cmt' => [
            'type' => 'cmt',
            'list_path' => 'admin/cmt.php',
            'edit_path' => 'admin/cmt_edit.php',
            'page_title' => 'Chiang Mai Tips',
            'singular' => 'Chiang Mai-tips',
            'new_label' => 'Nytt Chiang Mai-tips',
            'singular_definite' => 'tipset',
            'plural' => 'Chiang Mai-tips',
            'image_label' => 'tipsbilder',
        ],
        default => [
            'type' => 'article',
            'list_path' => 'admin/posts.php',
            'edit_path' => 'admin/post_edit.php',
            'page_title' => 'Artiklar',
            'singular' => 'artikel',
            'new_label' => 'Ny artikel',
            'singular_definite' => 'artikeln',
            'plural' => 'artiklar',
            'image_label' => 'artikelbilder',
        ],
    };
}

function admin_active_module(string $current_page, array $modules): string
{
    foreach ($modules as $key => $module) {
        if (in_array($current_page, $module['pages'], true)) {
            return $key;
        }
    }

    return '';
}

function admin_status_label(array $post, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $is_scheduled = ($post['status'] ?? '') === 'published'
        && !empty($post['publish_date'])
        && $post['publish_date'] > $today;

    if ($is_scheduled) {
        return ['class' => 'scheduled', 'label' => 'Schemalagd'];
    }

    if (($post['status'] ?? '') === 'published') {
        return ['class' => 'published', 'label' => 'Publicerat'];
    }

    return ['class' => 'draft', 'label' => 'Utkast'];
}
