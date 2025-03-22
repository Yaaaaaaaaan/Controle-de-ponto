<!--
---settings.php---------------------------------------------------------------------------------
    <form method="post" action="settings.php">
        <div class="image-container">
            <?php // foreach ($pictures as $picture) : ?>
                <label class="image-radio-container">
                    <input type="radio" name="selectedPicture" value="<?php //echo $picture['cod']; ?>">
                    <img src="<? // echo $picture['path']; ?>" class="d-block w-100" alt="Foto de Perfil">
                </label>
            <?php //endforeach; ?>
        </div>
        <text class="text-body-secondary">Essas são suas últimas três fotos adicionadas, Selecione uma.</text>
        <div class="d-flex justify-content-center mt-3">
            <button type="submit" style="text-align: center; display: block; margin: 0 auto;" name="updateProfilePic" class="btn btn-outline-primary w-100">Atualizar Foto de Perfil</button>
        </div>
    </form>



                <form action="settings.php" method="post" enctype="multipart/form-data">
                    <div class="input-group">
                        <input type="hidden" name="namePic" value="">
                        <input type="file" name="profilepic" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                        <button class="btn btn-outline-secondary" type="submit">Salvar</button>
                    </div>
                </form>
---end settings.php-----------------------------------------------------------------------------
-->

public function updateProfilePicture($userId, $pictureId) {
try {
// Inicie uma transação
$this->conn->beginTransaction();

// Primeiro, atualize a tabela
$updateSql = "UPDATE " . $this->tableNames['pps'] . "
SET uimageFK = :pictureId
WHERE uidUserFK = :userId";
$updateStmt = $this->conn->prepare($updateSql);
$updateStmt->bindParam(':userId', $userId);
$updateStmt->bindParam(':pictureId', $pictureId);
$updateStmt->execute();

// Em seguida, realize o SELECT
$selectSql = "SELECT description
FROM " . $this->tableNames['pic'] . "
WHERE cod = :pictureId";
$selectStmt = $this->conn->prepare($selectSql);
$selectStmt->bindParam(':pictureId', $pictureId);
$selectStmt->execute();
$row = $selectStmt->fetch(PDO::FETCH_ASSOC);

// Confirme a transação
$this->conn->commit();

// Atualize o profileUser na sessão
if ($row) {
$userData = json_decode($_SESSION['userData'], true);
$userData['profileUser'] = $row['description'];
$_SESSION['userData'] = json_encode($userData);

/* Depuração, se necessário
echo '<pre>';
                print_r(json_decode($_SESSION['userData'], true));
                echo '</pre>';*/
}

return true;
} catch (PDOException $e) {
// Reverte a transação em caso de erro
$this->conn->rollBack();
echo "Erro: " . $e->getMessage(); // Para depuração
return false;
}
}