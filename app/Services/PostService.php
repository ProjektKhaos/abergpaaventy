<?php
// PostService.php – affärslogik för artiklar i adminpanelen Ⓐ Style

class PostService
{
    private PDO $pdo;
    private Post $posts;
    private Media $media;
    private Category $categories;
    private Tag $tags;
    private MediaService $media_service;

    public function __construct(
        PDO $pdo,
        Post $posts,
        Media $media,
        Category $categories,
        Tag $tags,
        MediaService $media_service
    ) {
        $this->pdo = $pdo;
        $this->posts = $posts;
        $this->media = $media;
        $this->categories = $categories;
        $this->tags = $tags;
        $this->media_service = $media_service;
    }

    public function save(array $input, array $files, ?array $post = null, array $post_gallery = [], string $content_type = 'article'): ServiceResult
    {
        $content_type = in_array($content_type, ['article', 'news', 'cmt'], true) ? $content_type : 'article';
        $content_label = match ($content_type) {
            'news' => 'nyhet',
            'cmt'  => 'Chiang Mai-tips',
            default => 'artikel',
        };
        $content_definite = match ($content_type) {
            'news' => 'Nyheten',
            'cmt'  => 'Tipset',
            default => 'Artikeln',
        };
        $id = $post ? (int)$post['id'] : 0;
        $title = trim($input['title'] ?? '');
        $slug_raw = trim($input['slug'] ?? '');
        $slug = $slug_raw ?: slugify($title);
        $post_date = trim($input['post_date'] ?? '');
        $publish_date = trim($input['publish_date'] ?? '');
        $status = in_array($input['status'] ?? '', ['draft', 'published'], true) ? $input['status'] : 'draft';
        $cover_id = !empty($input['cover_image_id']) ? (int)$input['cover_image_id'] : null;
        $category_ids = array_map('intval', $input['categories'] ?? []);
        $tag_input = trim($input['tags'] ?? '');
        $latitude = trim($input['latitude'] ?? '');
        $longitude = trim($input['longitude'] ?? '');
        $link_labels = is_array($input['link_label'] ?? null) ? $input['link_label'] : [];
        $link_urls = is_array($input['link_url'] ?? null) ? $input['link_url'] : [];
        $links = [];

        $errors = [];
        if ($title === '') {
            $errors[] = 'Titel är obligatorisk.';
        }
        if ($slug === '') {
            $errors[] = 'Slug kunde inte skapas.';
        }

        if ($content_type === 'cmt') {
            if (($latitude === '') !== ($longitude === '')) {
                $errors[] = 'Fyll i både latitud och longitud, eller lämna båda tomma.';
            } elseif ($latitude !== '' && $longitude !== '') {
                if (!is_numeric($latitude) || (float)$latitude < -90 || (float)$latitude > 90) {
                    $errors[] = 'Latitud måste vara ett tal mellan -90 och 90.';
                }
                if (!is_numeric($longitude) || (float)$longitude < -180 || (float)$longitude > 180) {
                    $errors[] = 'Longitud måste vara ett tal mellan -180 och 180.';
                }
            }

            $link_count = max(count($link_labels), count($link_urls));
            for ($i = 0; $i < $link_count; $i++) {
                $label = trim((string)($link_labels[$i] ?? ''));
                $link_url = trim((string)($link_urls[$i] ?? ''));

                if ($label === '' && $link_url === '') {
                    continue;
                }
                if ($label === '' || $link_url === '') {
                    $errors[] = 'Varje tipslänk måste ha både länktext och URL.';
                    continue;
                }

                $scheme = strtolower((string)parse_url($link_url, PHP_URL_SCHEME));
                if (!filter_var($link_url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                    $errors[] = 'Tipslänken "' . $label . '" måste vara en giltig HTTP- eller HTTPS-adress.';
                    continue;
                }
                if (mb_strlen($label) > 255 || strlen($link_url) > 2048) {
                    $errors[] = 'Tipslänken "' . $label . '" är för lång.';
                    continue;
                }

                $links[] = ['label' => $label, 'url' => $link_url];
            }
        } else {
            $latitude = '';
            $longitude = '';
        }

        if ($post_date === '') {
            $post_date = date('Y-m-d');
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_date)) {
            $errors[] = 'Datum har fel format.';
        }

        if ($publish_date === '') {
            $publish_date = null;
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publish_date)) {
            $errors[] = 'Publiceringsdatum har fel format.';
        }

        $cover_upload = null;
        if (!empty($files['cover_upload']['name'])) {
            $cover_upload = $files['cover_upload'];
            $error = $this->media_service->validate_image($cover_upload, 'Omslagsbilden');
            if ($error) {
                $errors[] = $error;
            }
        }

        $article_uploads = [];
        if (isset($files['article_images']) && is_array($files['article_images']['name'])) {
            $article_uploads = $this->media_service->selected_uploads($files['article_images']);
            foreach ($article_uploads as $upload) {
                $error = $this->media_service->validate_image($upload, 'En artikelbild');
                if ($error) {
                    $errors[] = $error;
                }
            }
        }

        $existing_gallery_ids = array_map(static fn (array $img): int => (int)$img['id'], $post_gallery);
        $remove_media_ids = array_values(array_intersect(
            $existing_gallery_ids,
            array_map('intval', $input['remove_media'] ?? [])
        ));
        $remaining_gallery_count = count($post_gallery) - count($remove_media_ids);
        if ($remaining_gallery_count + count($article_uploads) > 5) {
            $errors[] = 'En ' . $content_label . ' kan ha max 5 bilder. Ta bort en befintlig bild eller välj färre nya bilder.';
        }

        if ($errors) {
            return ServiceResult::failure($errors, [
                'category_ids' => $category_ids,
                'links' => $links,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        }

        $data = [
            'title'          => $title,
            'slug'           => $slug,
            'content_type'   => $content_type,
            'intro'          => trim($input['intro'] ?? ''),
            'body'           => sanitize_html(trim($input['body'] ?? '')),
            'location'       => trim($input['location'] ?? ''),
            'latitude'       => $latitude === '' ? null : $latitude,
            'longitude'      => $longitude === '' ? null : $longitude,
            'post_date'      => $post_date,
            'publish_date'   => $publish_date,
            'status'         => $status,
            'cover_image_id' => $cover_id,
        ];

        try {
            $this->pdo->beginTransaction();

            if ($cover_upload) {
                $new_cover_id = $this->media->upload($cover_upload);
                if (!$new_cover_id) {
                    $this->pdo->rollBack();
                    return ServiceResult::failure(['Omslagsbilden kunde inte laddas upp. Kontrollera filtyp (jpg, png, webp).']);
                }
                $data['cover_image_id'] = $new_cover_id;
            }

            if ($id > 0) {
                $this->posts->update($id, $data);
                $message = $content_definite . ' uppdaterades.';
            } else {
                $id = $this->posts->create($data);
                $message = $content_definite . ' sparades.';
            }

            $this->categories->sync_post_categories($id, $category_ids);
            $this->tags->sync_post_tags($id, $tag_input);
            $this->posts->sync_links($id, $content_type === 'cmt' ? $links : []);
            $this->media->detach_from_post($id, $remove_media_ids);

            $sort = $this->media->max_sort_for_post($id) + 1;
            foreach ($article_uploads as $upload) {
                $media_id = $this->media->upload($upload);
                if (!$media_id) {
                    $this->pdo->rollBack();
                    return ServiceResult::failure(['En artikelbild kunde inte laddas upp.'], [], $id);
                }
                $this->media->attach_to_post($media_id, $id, $sort++);
            }

            $this->pdo->commit();
            return ServiceResult::success($message, [
                'category_ids' => $category_ids,
                'links' => $links,
            ], $id);
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                return ServiceResult::failure(['Slug används redan. Välj en annan slug.'], [], $id ?: null);
            }

            throw $e;
        }
    }
}
