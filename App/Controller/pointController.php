<?php
include_once '../../../App/Config/db.php';
include_once '../../../App/Model/user.php';
if (!defined('APP_RAN')) {
    die('Direct access not permitted');
}
class PointController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

       public function getPointControlData($id) {
        $query = "SELECT DATE_FORMAT(dateIn, '%Y-%m') as month, COUNT(*) as count FROM pointControl WHERE uidUserFK = :id GROUP BY month ORDER BY month";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>