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

    public function getPointControl($id): array{
        return $this->pointControl->getPointControlData($id);
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

    /*public function getDetailedPointControlData($id): array //Ainda não existe.
    {
        return $this->pointControl->getDetailedPointControlData($id);
    }*/



}
?>