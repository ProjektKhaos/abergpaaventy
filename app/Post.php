<?php
// Post.php – modell för inlägg i Thailand-bloggen Ⓐ Style

class Post
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Hämtar alla publicerade inlägg, nyast först.
     * Inkluderar omslagsbild om den finns.
     */
    public function get_published(int $limit = 20, int $offset = 0, string $content_type = 'article'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.file_name AS cover_file
            FROM posts p
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE p.status = 'published'
              AND p.content_type = :content_type
              AND (p.publish_date IS NULL OR p.publish_date <= :today)
            ORDER BY p.post_date DESC, p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':today',  date('Y-m-d'));
        $stmt->bindValue(':content_type', $content_type);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hämtar ett enskilt inlägg via slug.
     * Returnerar false om inlägget inte hittas eller inte är publicerat.
     */
    public function get_by_slug(string $slug, bool $public_only = true): array|false
    {
        $sql = "
            SELECT p.*, m.file_name AS cover_file, m.alt_text AS cover_alt
            FROM posts p
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE p.slug = ?
        ";
        if ($public_only) {
            $sql .= " AND p.status = 'published'
                      AND (p.publish_date IS NULL OR p.publish_date <= ?)";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $params = [$slug];
        if ($public_only) {
            $params[] = date('Y-m-d');
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Hämtar ett inlägg via ID (för admin).
     */
    public function get_by_id(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.file_name AS cover_file
            FROM posts p
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE p.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Hämtar alla inlägg för admin-listan (oavsett status).
     */
    public function get_all(string $content_type = 'article'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.title, p.slug, p.content_type, p.status, p.post_date, p.publish_date, p.location,
                   p.created_at, p.updated_at
            FROM posts p
            WHERE p.content_type = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$content_type]);
        return $stmt->fetchAll();
    }

    /**
     * Hämtar galleribilder kopplade till ett inlägg.
     */
    public function get_gallery(int $post_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.*, pm.sort_order
            FROM post_media pm
            JOIN media m ON m.id = pm.media_id
            WHERE pm.post_id = ?
            ORDER BY pm.sort_order ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    /**
     * Hämtar kategorier kopplade till ett inlägg.
     */
    public function get_categories(int $post_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.slug
            FROM post_categories pc
            JOIN categories c ON c.id = pc.category_id
            WHERE pc.post_id = ?
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    /**
     * Hämtar taggar kopplade till ett inlägg.
     */
    public function get_tags(int $post_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id, t.name, t.slug
            FROM post_tags pt
            JOIN tags t ON t.id = pt.tag_id
            WHERE pt.post_id = ?
            ORDER BY t.name ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    /**
     * Hämtar externa länkar kopplade till ett Chiang Mai-tips.
     */
    public function get_links(int $post_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, label, url, sort_order
            FROM post_links
            WHERE post_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    /**
     * Ersätter alla externa länkar för ett tips.
     */
    public function sync_links(int $post_id, array $links): void
    {
        $delete = $this->pdo->prepare("DELETE FROM post_links WHERE post_id = ?");
        $delete->execute([$post_id]);

        if (!$links) {
            return;
        }

        $insert = $this->pdo->prepare("
            INSERT INTO post_links (post_id, label, url, sort_order)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($links as $sort => $link) {
            $insert->execute([$post_id, $link['label'], $link['url'], $sort]);
        }
    }

    /**
     * Skapar ett nytt inlägg.
     * Returnerar det nya inläggets ID.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (title, slug, content_type, intro, body, location, latitude, longitude, post_date, publish_date, status, cover_image_id)
            VALUES (:title, :slug, :content_type, :intro, :body, :location, :latitude, :longitude, :post_date, :publish_date, :status, :cover_image_id)
        ");
        $stmt->execute([
            ':title'          => $data['title'],
            ':slug'           => $data['slug'],
            ':content_type'   => $data['content_type']   ?? 'article',
            ':intro'          => $data['intro']          ?? null,
            ':body'           => $data['body']           ?? null,
            ':location'       => $data['location']       ?? null,
            ':latitude'       => $data['latitude']       ?? null,
            ':longitude'      => $data['longitude']      ?? null,
            ':post_date'      => $data['post_date']      ?? date('Y-m-d'),
            ':publish_date'   => $data['publish_date']   ?? null,
            ':status'         => $data['status']         ?? 'draft',
            ':cover_image_id' => $data['cover_image_id'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Uppdaterar ett befintligt inlägg.
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE posts
            SET title = :title, slug = :slug, content_type = :content_type, intro = :intro, body = :body,
                location = :location, latitude = :latitude, longitude = :longitude,
                post_date = :post_date, publish_date = :publish_date,
                status = :status, cover_image_id = :cover_image_id
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'             => $id,
            ':title'          => $data['title'],
            ':slug'           => $data['slug'],
            ':content_type'   => $data['content_type']   ?? 'article',
            ':intro'          => $data['intro']          ?? null,
            ':body'           => $data['body']           ?? null,
            ':location'       => $data['location']       ?? null,
            ':latitude'       => $data['latitude']       ?? null,
            ':longitude'      => $data['longitude']      ?? null,
            ':post_date'      => $data['post_date']      ?? date('Y-m-d'),
            ':publish_date'   => $data['publish_date']   ?? null,
            ':status'         => $data['status']         ?? 'draft',
            ':cover_image_id' => $data['cover_image_id'] ?? null,
        ]);
    }

    /**
     * Tar bort ett inlägg (och kopplade relationer via CASCADE).
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Hämtar publicerade inlägg för en specifik plats.
     */
    public function get_by_location(string $location): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.file_name AS cover_file
            FROM posts p
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE p.location = ?
              AND p.content_type = 'article'
              AND p.status = 'published'
              AND (p.publish_date IS NULL OR p.publish_date <= ?)
            ORDER BY p.post_date DESC, p.created_at DESC
        ");
        $stmt->execute([$location, date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    /**
     * Räknar publicerade inlägg (för dashboard).
     */
    public function count_published(string $content_type = 'article'): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM posts WHERE status = 'published' AND content_type = ?"
        );
        $stmt->execute([$content_type]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Räknar utkast (för dashboard).
     */
    public function count_drafts(string $content_type = 'article'): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM posts WHERE status = 'draft' AND content_type = ?"
        );
        $stmt->execute([$content_type]);
        return (int)$stmt->fetchColumn();
    }
}
