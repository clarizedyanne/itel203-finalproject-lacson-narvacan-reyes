<?php
// includes/MenuItem.php - OOP Menu Item CRUD Class

require_once 'db.php';

class MenuItem {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    // READ - Get all items by category/subcategory
    public function getBySubcategory(string $subcategory): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM menu_items WHERE subcategory = ? AND is_available = 1 ORDER BY price ASC"
        );
        $stmt->bind_param("s", $subcategory);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ - Get all main menu items
    public function getMainMenu(): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM menu_items WHERE category = 'Main' AND is_available = 1 ORDER BY subcategory, price ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ - Get all for admin (including unavailable), grouped by category
    public function getAll(): array {
        $result = $this->db->query("SELECT * FROM menu_items ORDER BY category, subcategory, name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // READ - Get all grouped by category
    public function getAllGrouped(): array {
        $all = $this->getAll();
        $grouped = [];
        foreach ($all as $item) {
            $cat = $item['category'];
            $sub = $item['subcategory'] ?: 'General';
            $grouped[$cat][$sub][] = $item;
        }
        return $grouped;
    }

    // READ - Get single item
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows ? $result->fetch_assoc() : null;
    }

    // CREATE
    public function create(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO menu_items (name, category, subcategory, description, price, price_variant, is_available, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $image = $data['image'] ?? null;
        $stmt->bind_param("ssssdsis",
            $data['name'], $data['category'], $data['subcategory'],
            $data['description'], $data['price'], $data['price_variant'],
            $data['is_available'], $image
        );
        return $stmt->execute();
    }

    // UPDATE
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE menu_items SET name=?, category=?, subcategory=?, description=?, price=?, price_variant=?, is_available=?, image=?
             WHERE id=?"
        );
        $image = $data['image'] ?? null;
        $stmt->bind_param("ssssdsssi",
            $data['name'], $data['category'], $data['subcategory'],
            $data['description'], $data['price'], $data['price_variant'],
            $data['is_available'], $image, $id
        );
        return $stmt->execute();
    }

    // DELETE
    public function delete(int $id): bool {
        // Remove image file if exists
        $item = $this->getById($id);
        if ($item && !empty($item['image'])) {
            $path = dirname(__DIR__) . '/uploads/menu/' . basename($item['image']);
            if (file_exists($path)) unlink($path);
        }
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Toggle availability
    public function toggleAvailability(int $id): bool {
        $stmt = $this->db->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
