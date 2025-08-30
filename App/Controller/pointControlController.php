<?php
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}

require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/PointControl.php';

class PointController {
    private $db;
    private $pointControl;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pointControl = new PointControl($this->db);
    }

    public function insertPointControl(int $userId, string $status, ?string $obs = null, ?string $date = null): bool {
        if (empty($userId) || empty($status)) {
            return false;
        }
        return $this->pointControl->insertPointControl($userId, $status, $obs, $date);
    }

    public function getPointControlByUserId(int $userId): array {
        return $this->pointControl->getByUserId($userId);
    }
}
?>