<?php
// Media.php – bilduppladdning och mediahantering Ⓐ Style

class Media
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Hanterar uppladdning av en bild.
     * Kontrollerar MIME-typ, skapar säkert filnamn och sparar filen.
     * Returnerar det nya media-postens ID, eller false vid fel.
     */
    public function upload(array $file, string $alt = '', string $caption = ''): int|false
    {
        // Kontrollera att filen laddades upp utan fel
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Kontrollera MIME-typ mot vitlistan (inte bara filtillägg)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mime     = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME, true)) {
            return false; // Ej tillåten filtyp
        }

        // Mappa MIME → filextension
        $ext_map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $ext = $ext_map[$mime] ?? 'jpg';

        // Skapa ett unikt, säkert filnamn – aldrig användarens eget namn
        $safe_name = uniqid('img_', true) . '.' . $ext;
        $dest      = UPLOAD_DIR . $safe_name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false; // Kunde inte flytta filen
        }

        // Spara i databasen
        $stmt = $this->pdo->prepare("
            INSERT INTO media (original_name, file_name, file_path, mime_type, file_size, alt_text, caption)
            VALUES (:original_name, :file_name, :file_path, :mime_type, :file_size, :alt_text, :caption)
        ");
        $stmt->execute([
            ':original_name' => $file['name'],
            ':file_name'     => $safe_name,
            ':file_path'     => 'uploads/' . $safe_name,
            ':mime_type'     => $mime,
            ':file_size'     => $file['size'],
            ':alt_text'      => $alt,
            ':caption'       => $caption,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Hämtar alla media-poster, nyast först.
     */
    public function get_all(): array
    {
        return $this->pdo->query("
            SELECT * FROM media ORDER BY created_at DESC
        ")->fetchAll();
    }

    /**
     * Hämtar ett media-objekt via ID.
     */
    public function get_by_id(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM media WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Kopplar ett media-objekt till ett inlägg (galleri).
     */
    public function attach_to_post(int $media_id, int $post_id, int $sort = 0): void
    {
        $check = $this->pdo->prepare("
            SELECT id FROM post_media
            WHERE post_id = ? AND media_id = ?
            LIMIT 1
        ");
        $check->execute([$post_id, $media_id]);
        $existing_id = $check->fetchColumn();

        if ($existing_id) {
            $update = $this->pdo->prepare("UPDATE post_media SET sort_order = ? WHERE id = ?");
            $update->execute([$sort, $existing_id]);
            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO post_media (post_id, media_id, sort_order)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$post_id, $media_id, $sort]);
    }

    /**
     * Tar bort kopplingar mellan ett inlägg och valda mediaposter.
     */
    public function detach_from_post(int $post_id, array $media_ids): void
    {
        $media_ids = array_values(array_unique(array_filter(array_map('intval', $media_ids))));
        if (!$media_ids) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($media_ids), '?'));
        $stmt = $this->pdo->prepare("
            DELETE FROM post_media
            WHERE post_id = ? AND media_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$post_id], $media_ids));
    }

    /**
     * Räknar galleribilder för ett inlägg.
     */
    public function count_for_post(int $post_id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM post_media WHERE post_id = ?");
        $stmt->execute([$post_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Hämtar högsta sorteringsordningen för ett inläggs galleri.
     */
    public function max_sort_for_post(int $post_id): int
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM post_media WHERE post_id = ?");
        $stmt->execute([$post_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Hämtar alla bilder för gallerisidan (alla publicerade inlägg).
     */
    public function get_gallery_images(int $limit = 60): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.*, p.title AS post_title, p.slug AS post_slug,
                   p.post_date, p.location
            FROM media m
            JOIN post_media pm ON pm.media_id = m.id
            JOIN posts p ON p.id = pm.post_id
            WHERE p.status = 'published'
              AND p.content_type = 'article'
              AND (p.publish_date IS NULL OR p.publish_date <= :today)
            ORDER BY pm.post_id DESC, pm.sort_order ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':today', date('Y-m-d'));
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
