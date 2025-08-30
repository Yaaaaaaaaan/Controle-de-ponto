<?php


if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}

require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/History.php';

class HistoryController{
    private $db;
    private $history;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->history = new History($this->db);
    }

    /**
     * Ponto de entrada público para criar um registo de histórico.
     * @param int $userId O ID do utilizador.
     * @param string $description A descrição da ação.
     * @return bool O resultado da operação de criação.
     */
    public function createHistoryEntry(int $userId, string $description): bool {
        if (empty($userId) || empty($description)) {
            return false;
        }

        return $this->history->create($userId, $description);
    }

    public function getEntriesByUserId(int $userId, int $limit = 20): array {
        // Apenas repassa a chamada para o Model.
        return $this->history->getHistoryByUserId($userId, $limit);
    }
}