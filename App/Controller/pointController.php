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




    /*public function getAllAvailableMonths($id) {
        return $this->user->getAllAvailableMonths($id);
    }

    public function getPointControlData($id, $months) {
        return $this->user->getPointControlData($id, $months);
    }

    public function converterMonthFromName($monthYear) {
        $parts = explode('-', $monthYear);
        $year = $parts[0];
        $month = $parts[1];
        $MonthNames = [
            '01' => 'Jan',
            '02' => 'Fev',
            '03' => 'Mar',
            '04' => 'Abr',
            '05' => 'Mai',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ago',
            '09' => 'Set',
            '10' => 'Out',
            '11' => 'Nov',
            '12' => 'Dez',
        ];
        return $MonthNames[$month] . ' - ' . $year;
    }*/

}
?>