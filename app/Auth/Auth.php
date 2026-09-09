<?php
declare(strict_types=1);

namespace ResellNom\Auth;

use PDO;
use RuntimeException;

final class Auth
{
    public function __construct(private PDO $db) {}

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT id, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || ($user['status'] ?? 'active') !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }

    public function user(): ?array
    {
        if (empty($_SESSION['user_id'])) return null;
        $stmt = $this->db->prepare('SELECT id, name, email, role, parent_id, balance, status FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function requireRole(array $roles): array
    {
        $user = $this->user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            throw new RuntimeException('Access denied');
        }
        return $user;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
        }
        session_destroy();
    }
}
