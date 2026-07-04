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
        return $this->pdo->query("
            SELECT c.id, c.name, c.slug,
                   COUNT(pc.post_id) AS post_count
            FROM categories c
            LEFT JOIN post_categories pc ON pc.category_id = c.id
            LEFT JOIN posts p ON p.id = pc.post_id AND p.status = 'published'
            GROUP BY c.id
            ORDER BY c.name ASC
        ")->fetchAll();
    }

    /**
     * Hämtar alla kategorier (enkel lista, för formulär i admin).
     */
    public function get_all(): array
    {
        return $this->pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
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
              AND p.status = 'published'
            ORDER BY p.post_date DESC
        ");
        $stmt->execute([$category_id]);
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
