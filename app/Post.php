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
    public function get_published(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.file_name AS cover_file
            FROM posts p
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE p.status = 'published'
            ORDER BY p.post_date DESC, p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
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
            $sql .= " AND p.status = 'published'";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
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
    public function get_all(): array
    {
        return $this->pdo->query("
            SELECT p.id, p.title, p.slug, p.status, p.post_date, p.location,
                   p.created_at, p.updated_at
            FROM posts p
            ORDER BY p.created_at DESC
        ")->fetchAll();
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
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    /**
     * Skapar ett nytt inlägg.
     * Returnerar det nya inläggets ID.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (title, slug, intro, body, location, post_date, status, cover_image_id)
            VALUES (:title, :slug, :intro, :body, :location, :post_date, :status, :cover_image_id)
        ");
        $stmt->execute([
            ':title'          => $data['title'],
            ':slug'           => $data['slug'],
            ':intro'          => $data['intro']          ?? null,
            ':body'           => $data['body']           ?? null,
            ':location'       => $data['location']       ?? null,
            ':post_date'      => $data['post_date']      ?? date('Y-m-d'),
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
            SET title = :title, slug = :slug, intro = :intro, body = :body,
                location = :location, post_date = :post_date,
                status = :status, cover_image_id = :cover_image_id
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'             => $id,
            ':title'          => $data['title'],
            ':slug'           => $data['slug'],
            ':intro'          => $data['intro']          ?? null,
            ':body'           => $data['body']           ?? null,
            ':location'       => $data['location']       ?? null,
            ':post_date'      => $data['post_date']      ?? date('Y-m-d'),
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
            WHERE p.location = ? AND p.status = 'published'
            ORDER BY p.post_date DESC, p.created_at DESC
        ");
        $stmt->execute([$location]);
        return $stmt->fetchAll();
    }

    /**
     * Räknar publicerade inlägg (för dashboard).
     */
    public function count_published(): int
    {
        return (int)$this->pdo->query(
            "SELECT COUNT(*) FROM posts WHERE status = 'published'"
        )->fetchColumn();
    }

    /**
     * Räknar utkast (för dashboard).
     */
    public function count_drafts(): int
    {
        return (int)$this->pdo->query(
            "SELECT COUNT(*) FROM posts WHERE status = 'draft'"
        )->fetchColumn();
    }
}
