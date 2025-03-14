<?php
include_once '../../../App/Config/db.php';
include_once '../../../App/Model/user.php';
if (!defined('APP_RAN')) {
    die('Acesso não permitido.');
}
class pictureController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function getUserPictures() {
        if (isset($_SESSION['id'])) {
            return $this->user->getUserPictures($_SESSION['id']);
        }
        return [];
    }

}
    ?>