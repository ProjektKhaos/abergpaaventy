<?php
// Tag.php – tagghantering för Thailand-bloggen Ⓐ Style

class Tag
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Hämtar taggar kopplade till ett inlägg.
     */
    public function get_for_post(int $post_id): array
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

    public function get_all(): array
    {
        return $this->pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $stmt = $this->pdo->prepare("UPDATE tags SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
    }

    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count_posts(int $id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM post_tags WHERE tag_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Tolkar en taggsträng, t.ex. '#resa #thailand', till unika taggnamn.
     */
    public function parse_input(string $input): array
    {
        $tokens = preg_split('/[\s,;]+/u', $input, -1, PREG_SPLIT_NO_EMPTY);
        $tags = [];

        foreach ($tokens as $token) {
            $name = trim($token);
            $name = preg_replace('/^#+/u', '', $name);
            $name = trim($name, " \t\n\r\0\x0B#.,;");

            if ($name === '') {
                continue;
            }

            $slug = slugify($name);
            if ($slug === '') {
                continue;
            }

            $tags[$slug] = $name;
        }

        return array_values($tags);
    }

    /**
     * Skapar en tagg om den saknas och returnerar taggens ID.
     */
    public function find_or_create(string $name): int
    {
        $slug = slugify($name);

        $stmt = $this->pdo->prepare("SELECT id FROM tags WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            return (int)$existing;
        }

        try {
            $insert = $this->pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $insert->execute([$name, $slug]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            $stmt->execute([$slug]);
            return (int)$stmt->fetchColumn();
        }
    }

    /**
     * Synkroniserar taggar för ett inlägg från ett fritextfält.
     */
    public function sync_post_tags(int $post_id, string $input): void
    {
        $delete = $this->pdo->prepare("DELETE FROM post_tags WHERE post_id = ?");
        $delete->execute([$post_id]);

        $tag_names = $this->parse_input($input);
        if (!$tag_names) {
            return;
        }

        $insert = $this->pdo->prepare(
            "INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)"
        );

        foreach ($tag_names as $name) {
            $insert->execute([$post_id, $this->find_or_create($name)]);
        }
    }
}
