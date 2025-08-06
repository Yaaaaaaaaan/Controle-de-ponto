<?php
//include_once '../../../App/Config/db.php';
//include_once '../../../App/Model/pointControl.php';
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Model/pointControl.php';

if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
class PointController{
    private $db;
    private $pointControl;

    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pointControl = new PointControl($this->db);
    }

    public function insertPointControl($id, $status): bool
    {
        // Validação simples dos dados recebidos
        if (empty($id) || !isset($status)) {
            return false;
        }

        // Chama o método corrigido no Model de Ponto
        return $this->pointControl->insertPointControl($id, $status);
    }

    public function getPointControl($id): array {
        return $this->pointControl->getPointControl($id);
    }

    public function getPointControlUsers(): array{
        try {
            $resultados = $this->pointControl->getPointControlUsersData();

            $labels = [];
            $dataPoints = [];
            $detalhes = [];

            foreach ($resultados as $row) {
                $labels[] = $row['description'];
                $dataPoints[] = (int)$row['count'];
                $detalhes[$row['description']] = json_decode('[' . $row['detalhes'] . ']', true);
            }

            return [
                'labels' => $labels,
                'dataPoints' => $dataPoints,
                'detalhes' => $detalhes
            ];

        } catch (Exception $e) {
            error_log("Erro no controller ao processar dados: " . $e->getMessage());
            return [
                'labels' => [],
                'dataPoints' => [],
                'detalhes' => []
            ];
        }
    }

    public function validatePresence($userId, $userIdToValidate, $description, $code){ //Tudo aqui é transformação
        $currentDate = date("Y-m-d");
        $descriptionToValidate = "Já verificado";
        $this->user->validatePresence($userId);
        $this->user->validatePresence($userIdToValidate);
        $this->user->validatePresence($description);
        $this->user->validatePresence($code);
        $this->user->validatePresence($currentDate);
        $this->user->validatePresence($descriptionToValidate);


        return true;
    }

    public function updatePresenceHousekeeping($userIdToValidate, $code, $description){
        if ($description == 1) {
            $descriptionTranslated = "Verificação pendente";
        } else if ($description == 2) {
            $descriptionTranslated = "Já verificado";
        } else if ($description == 3) {
            $descriptionTranslated = "Recusado";
        } else {
            error_log("Valor inválido para descrição: " . $description);
            return false;
        }
        $result = $this->pointControl->updatePresenceHousekeeping($userIdToValidate, $code, $descriptionTranslated);
        if ($result) {
            echo "200 OK.";
        } else {
            echo "400 Bad Request.";
        }
        return $result;
    }
}
?>