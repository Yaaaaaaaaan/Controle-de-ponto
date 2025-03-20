<!--
---settings.php---------------------------------------------------------------------------------
    <form method="post" action="settings.php">
        <div class="image-container">
            <?php// foreach ($pictures as $picture) : ?>
                <label class="image-radio-container">
                    <input type="radio" name="selectedPicture" value="<?php echo $picture['cod']; ?>">
                    <img src="<?// echo $picture['path']; ?>" class="d-block w-100" alt="Foto de Perfil">
                </label>
            <?php// endforeach; ?>
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

