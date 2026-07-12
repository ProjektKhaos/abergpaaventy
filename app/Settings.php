<?php
// Settings.php – enkel webbplatsinställningar för adminpanelen Ⓐ Style

class Settings
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $rows = $this->pdo->query("
            SELECT setting_key, setting_value
            FROM site_settings
            ORDER BY setting_key ASC
        ")->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function set_many(array $settings): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }
}
