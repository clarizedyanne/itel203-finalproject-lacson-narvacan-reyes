<?php
// includes/Auth.php - OOP Authentication Class

require_once 'db.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    // Login method with session handling
    public function login(string $email, string $password): array {
        $email = $this->db->real_escape_string(trim($email));
        $stmt = $this->db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                return ['success' => true, 'role' => $user['role']];
            }
        }
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Logout - destroy session
    public function logout(): void {
        session_unset();
        session_destroy();
    }

    // Check if user is logged in
    public static function check(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Check if user is admin
    public static function isAdmin(): bool {
        return self::check() && $_SESSION['user_role'] === 'admin';
    }

    // Require login - redirect if not
    public static function requireLogin(string $redirect = 'login.php'): void {
        if (!self::check()) {
            header("Location: $redirect");
            exit();
        }
    }

    // Require admin
    public static function requireAdmin(string $redirect = 'login.php'): void {
        if (!self::isAdmin()) {
            header("Location: $redirect");
            exit();
        }
    }
}
?>
