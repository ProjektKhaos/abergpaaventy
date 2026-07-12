<?php
// Auth.php – sessionsbaserad inloggning för admin Ⓐ Style

class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Starta session om den inte redan är igång
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Försöker logga in en användare med användarnamn och lösenord.
     * Returnerar true vid lyckad inloggning, annars false.
     */
    public function login(string $username, string $password): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, username, email, password_hash, role FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return false; // Användaren hittades inte
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false; // Fel lösenord
        }

        // Spara inloggad användare i sessionen
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_role'] = $user['role'];
        // Regenerera session-ID för att förhindra session fixation
        session_regenerate_id(true);

        return true;
    }

    /**
     * Loggar ut användaren och förstör sessionen.
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Kontrollerar om en användare är inloggad.
     */
    public function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Kräver inloggning – skickar till login om användaren inte är inloggad.
     */
    public function require_login(): void
    {
        if (!$this->check()) {
            header('Location: ' . url('admin/login.php'));
            exit;
        }
    }

    /**
     * Returnerar inloggad användares namn.
     */
    public function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }

    /**
     * Returnerar inloggad användares roll.
     */
    public function role(): string
    {
        return $_SESSION['user_role'] ?? '';
    }

    /**
     * Returnerar inloggad användares e-post.
     */
    public function email(): string
    {
        return $_SESSION['user_email'] ?? '';
    }

    /**
     * Kontrollerar om användaren har en viss roll.
     */
    public function has_role(string $role): bool
    {
        return $this->role() === $role;
    }
}
