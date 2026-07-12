<?php
// Category.php – kategorihantering för Thailand-bloggen Ⓐ Style

class Category
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Hämtar alla kategorier med antal publicerade inlägg per kategori.
     */
    public function get_all_with_count(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.slug,
                   COUNT(p.id) AS post_count
            FROM categories c
            LEFT JOIN post_categories pc ON pc.category_id = c.id
            LEFT JOIN posts p ON p.id = pc.post_id AND p.status = 'published'
                AND p.content_type = 'article'
                AND (p.publish_date IS NULL OR p.publish_date <= ?)
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute([date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    /**
     * Hämtar alla kategorier (enkel lista, för formulär i admin).
     */
    public function get_all(): array
    {
        return $this->pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }

    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count_posts(int $id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM post_categories WHERE category_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Hämtar en kategori via slug.
     */
    public function get_by_slug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function get_by_id(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Hämtar publicerade inlägg inom en kategori.
     */
    public function get_posts(int $category_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.file_name AS cover_file
            FROM posts p
            JOIN post_categories pc ON pc.post_id = p.id
            LEFT JOIN media m ON m.id = p.cover_image_id
            WHERE pc.category_id = ?
              AND p.content_type = 'article'
              AND p.status = 'published'
              AND (p.publish_date IS NULL OR p.publish_date <= ?)
            ORDER BY p.post_date DESC
        ");
        $stmt->execute([$category_id, date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    /**
     * Synkroniserar kategorier för ett inlägg.
     * Tar bort gamla kopplingar och lägger till de nya.
     */
    public function sync_post_categories(int $post_id, array $category_ids): void
    {
        // Ta bort befintliga kopplingar
        $del = $this->pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
        $del->execute([$post_id]);

        // Lägg till de nya
        $ins = $this->pdo->prepare(
            "INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)"
        );
        foreach ($category_ids as $cid) {
            $ins->execute([$post_id, (int)$cid]);
        }
    }
}
