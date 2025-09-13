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

    public function insertPointControl(int $userId, string $status, ?string $obs = null, $occurrenceDate, $longitude, $latitude ): array {
        if (empty($userId) || empty($status)) {
            return ['success' => false, 'message' => 'Dados insuficientes.', 'http_code' => 400];
        }

        $result = $this->pointControl->insertPointControl($userId, $status, $obs, $occurrenceDate, $longitude, $latitude);

        if ($result['success']) {
            return ['success' => true, 'message' => 'Ponto registrado com sucesso.', 'http_code' => 200];
        }

        // TRATAMENTO DE ERROS ESPECÍFICOS VINDO DO MODEL
        if (isset($result['error'])) {
            switch ($result['error']) {
                case 'duplicate_entry':
                    return ['success' => false, 'message' => 'O registro de ponto para hoje já existe no servidor.', 'http_code' => 409]; // 409 Conflict
                case 'db_error':
                    return ['success' => false, 'message' => 'Erro no banco de dados. Contacte um administrador.', 'http_code' => 500];
                default:
                    return ['success' => false, 'message' => 'Falha desconhecida ao inserir registro.', 'http_code' => 500];
            }
        }

        // Fallback genérico
        return ['success' => false, 'message' => 'Falha ao inserir registro.', 'http_code' => 400];
    }

    public function getPointControlByUserId(int $userId): array {
        return $this->pointControl->getByUserId($userId);
    }
}
?>