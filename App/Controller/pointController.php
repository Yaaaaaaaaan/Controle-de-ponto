<?php
include_once '../../../App/Config/db.php';
include_once '../../../App/Model/pointControl.php';
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
class PointController
{
    private $db;
    private $pointControl;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pointControl = new PointControl($this->db);
    }

    public function getPointControl($id): array
    {
        return $this->pointControl->getPointControlData($id);
    }

    public function getDetailedPointControlData($id): array
    {
        return $this->pointControl->getDetailedPointControlData($id);
    }



}
?>