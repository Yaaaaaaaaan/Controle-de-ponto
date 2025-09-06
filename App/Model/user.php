<?php



if (!defined('APP_RAN')) {
    die('Acesso não permitido');
}

require_once __DIR__ . '/history.php';


#[AllowDynamicProperties] class User{
    private $conn;
    private $tableNames = [
        'usr' => 'usuarios',
        'alb' => 'albuns',
        'fot' => 'fotos',
        'his' => 'historicos_acoes',
        'tok' => 'tokens_autenticacao',
        'reg' => 'registros_ponto'
    ];

    public $userToken;
    public $registro;

    public function __construct($db){
        $this->conn = $db;
    }

    public function createUser(): bool{
        // Define valor padrão para a imagem de perfil
        $this->profilePicture = 'Profile.png';
        $this->directory = '/Public/Assets/img/userProfileImages/Profile.png';
        $this->isProfile = '1';
        $this->albumName = 'Foto de perfil';
        $this->albumType = '1';

        $this->conn->beginTransaction();
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        try {
            // 1. Inserir usuário
            $queryUser = "INSERT INTO {$this->tableNames['usr']} (nome_completo, nome_usuario, email, senha_hash, nivel_acesso) VALUES (:name, :nickname, :email, :password, :rank)";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':name', $this->name);
            $stmtUser->bindParam(':nickname', $this->nickname);
            $stmtUser->bindParam(':email', $this->email);
            $stmtUser->bindParam(':password', $this->password);
            $stmtUser->bindParam(':rank', $this->rank);
            $stmtUser->execute();
            $newUserId = $this->conn->lastInsertId();

            // 2. Inserir álbum
            $queryAlbum = "INSERT INTO {$this->tableNames['alb']} (id_usuario, nome_album, tipo_album) VALUES (:newUserId, :albumName, :albumType)";
            $stmtAlbum = $this->conn->prepare($queryAlbum);
            $stmtAlbum->bindParam(':newUserId', $newUserId);
            $stmtAlbum->bindParam(':albumName', $this->albumName);
            $stmtAlbum->bindParam(':albumType', $this->albumType);
            $stmtAlbum->execute();
            $newAlbumId = $this->conn->lastInsertId();

            // 3. Inserir foto
            $queryProfilePic = "INSERT INTO {$this->tableNames['fot']} (album_id, id_usuario, caminho_arquivo, nome_foto, perfil) VALUES (:newAlbumId, :newUserId, :directory, :profilePicture, :isProfile)";
            $stmtProfilePic = $this->conn->prepare($queryProfilePic);
            $stmtProfilePic->bindParam(':newAlbumId', $newAlbumId);
            $stmtProfilePic->bindParam(':newUserId', $newUserId);
            $stmtProfilePic->bindParam(':directory', $this->directory);
            $stmtProfilePic->bindParam(':profilePicture', $this->profilePicture);
            $stmtProfilePic->bindParam(':isProfile', $this->isProfile);
            $stmtProfilePic->execute();

            // --- INÍCIO DA LÓGICA DO TOKEN (O PONTO PRINCIPAL DA CORREÇÃO) ---

            // 4. Gerar o token de autenticação
            $userToken = bin2hex(random_bytes(32)); // Gera um token seguro de 64 caracteres

            // 5. Calcular a data de expiração (7 dias a partir de agora)
            $expiryDate = (new DateTime())->add(new DateInterval('P7D'))->format('Y-m-d H:i:s');

            // 6. Inserir o token na tabela de tokens
            $queryToken = "INSERT INTO {$this->tableNames['tok']} (id_usuario, token, data_expiracao) VALUES (:id_usuario, :token, :data_expiracao)";
            $stmtToken = $this->conn->prepare($queryToken);
            $stmtToken->bindParam(':id_usuario', $newUserId);
            $stmtToken->bindParam(':token', $userToken);
            $stmtToken->bindParam(':data_expiracao', $expiryDate);
            $stmtToken->execute();

            // --- FIM DA LÓGICA DO TOKEN ---

            $this->conn->commit(); // Confirma todas as operações (usuário, album, foto e token)
            $this->createUserHistory('Criação de conta: ', $newUserId, date('Y-m-d H:i:s'));
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            // Lembre-se de restaurar o código de log de erro aqui!
            error_log("Erro em User->createUser: " . $e->getMessage());
            return false;
        }
    }

    public function __destruct(){
        // Forma correta de fechar uma conexão PDO
        if ($this->conn) {
            $this->conn = null;
        }
    }

    public function authenticateUser(): ?array
    {
        try {
            $userId = $this->verifyCredentials();
            if (!$userId) return null;

            // 1. Busca os dados já separados (userData e tokenData)
            $structuredData = $this->getUserById($userId);
            if (!$structuredData) return null;

            // 2. Extrai os objetos para variáveis locais para maior clareza
            $profileData = $structuredData['userData'];
            $tokenDataFromQuery = $structuredData['tokenData'];

            // 3. A função manageUserToken agora recebe apenas os dados do token, como esperado.
            $finalTokenData = $this->manageUserToken($userId, $tokenDataFromQuery);

            // 4. Popula a sessão com os dados corretos e separados.
            $this->populateSession($userId, $profileData, $finalTokenData);
            $this->createUserHistory('Login bem-sucedido', $userId,  date('Y-m-d H:i:s'), 'online');

            // 5. Retorna a estrutura aninhada final para o front-end.
            return [
                'userData' => $profileData,
                'tokenData' => $finalTokenData
            ];

        } catch (Exception $e) {
            error_log("Erro no fluxo de autenticação: " . $e->getMessage());
            return null;
        }
    }

    private function manageUserToken(int $userId, array $tokenDataFromQuery): array
    {
        $existingToken = $tokenDataFromQuery['userToken'] ?? null;
        $tokenExpiryStr = $tokenDataFromQuery['tokenExpiry'] ?? null;
        $tokenCreationStr = $tokenDataFromQuery['tokenDate'] ?? null;
        $tokenExpiry = $tokenExpiryStr ? new DateTime($tokenExpiryStr) : null;
        $now = new DateTime();

        $isExpired = $tokenExpiry ? $tokenExpiry < $now : true;

        if (!$existingToken || !$tokenExpiry || $isExpired) {
            // A sua lógica de criar um novo token está correta e pode continuar a mesma...
            $userToken = bin2hex(random_bytes(32));
            $nowForDb = $now->format('Y-m-d H:i:s');
            $newExpiryDate = (clone $now)->add(new DateInterval('P7D'))->format('Y-m-d H:i:s');

            $tokenQuery = "INSERT INTO {$this->tableNames['tok']} (id_usuario, token, data_expiracao, data_criacao)
                   VALUES (:id, :token, :expiry, :created)
                   ON DUPLICATE KEY UPDATE token = VALUES(token), data_expiracao = VALUES(data_expiracao)";

            $tokenStmt = $this->conn->prepare($tokenQuery);
            $tokenStmt->execute([':id' => $userId, ':token' => $userToken, ':expiry' => $newExpiryDate, ':created' => $nowForDb]);

            return ['userToken' => $userToken, 'tokenDate' => $nowForDb, 'tokenExpiry' => $newExpiryDate];
        }

        // Se o token for válido, retorna os dados existentes.
        return ['userToken' => $existingToken, 'tokenDate' => $tokenCreationStr, 'tokenExpiry' => $tokenExpiryStr];
    }

    public function getUserById(int $userId): ?array
    {
        // ALTERAÇÃO 1: Na lista de colunas, troque 'f.nome_foto' por 'f.caminho_arquivo'
        $query = "SELECT u.id_usuario, u.nome_completo, u.nome_usuario, u.nivel_acesso, u.email, u.tema_padrao,
                 f.caminho_arquivo, -- ANTES ESTAVA f.nome_foto
                 t.token AS userToken, t.data_criacao AS tokenDate, t.data_expiracao AS tokenExpiry
          FROM {$this->tableNames['usr']} u
          LEFT JOIN {$this->tableNames['fot']} f ON u.id_usuario = f.id_usuario AND f.perfil = 1
          LEFT JOIN {$this->tableNames['tok']} t ON u.id_usuario = t.id_usuario
          WHERE u.id_usuario = :userId";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $profileData = [
                    'userId'      => (int)$row['id_usuario'], 'name' => $row['nome_completo'],
                    'email'       => $row['email'], 'rank' => (int)$row['nivel_acesso'],
                    'nickname'    => $row['nome_usuario'], 'theme' => (int)$row['tema_padrao'],
                    // ALTERAÇÃO 2: Use a coluna correta que foi selecionada
                    'profileUser' => $row['caminho_arquivo'], // ANTES ESTAVA $row['nome_foto']
                ];
                $tokenData = [
                    'userToken'   => $row['userToken'], 'tokenDate'   => $row['tokenDate'],
                    'tokenExpiry' => $row['tokenExpiry'],
                ];
                return ['userData' => $profileData, 'tokenData' => $tokenData];
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro em User->getUserById: " . $e->getMessage());
            return null;
        }
    }

    private function verifyCredentials(): ?int
    {
        if (empty($this->nickname) || empty($this->password)) {
            return null;
        }
        $query = "SELECT id_usuario, senha_hash FROM {$this->tableNames['usr']} WHERE nome_usuario = :nickname";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nickname', $this->nickname);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password, $row['senha_hash'])) {
            return (int)$row['id_usuario'];
        }
        return null;
    }

    private function populateSession(int $userId, array $profileData, array $tokenData): void
    {
        $_SESSION['id'] = $userId;
        $_SESSION['logged'] = true;

        // A sessão 'userData' está correta.
        $_SESSION['userData'] = json_encode([
            'userId'      => $profileData['userId'],
            'name'        => $profileData['name'],
            'email'       => $profileData['email'],
            'rank'        => $profileData['rank'],
            'nickname'    => $profileData['nickname'],
            'theme'       => $profileData['theme'],
            'profileUser' => $profileData['profileUser'],
        ]);

        $_SESSION['tokenUserData'] = json_encode([
            'userToken'   => $tokenData['userToken'],
            'tokenDate'   => $tokenData['tokenDate'],
            'tokenExpiry' => $tokenData['tokenExpiry'],
            'userId'      => $userId,
        ]);
    }

// /App/Model/User.php

    public function updateUser(): bool {
        // Verificar os campos obrigatórios
        if (empty($this->name) || empty($this->email) || empty($this->nickname)) {
            return false;
        }

        $query = "UPDATE {$this->tableNames['usr']}
    SET nome_completo = :name,
        email = :email,
        nome_usuario = :nickname,
        tema_padrao = :defaultTheme ";

        $passwordUpdated = false;
        if (!empty($this->newPassword) && !empty($this->confirmPassword) && !empty($this->oldPassword)) {
            $checkPasswordQuery = "SELECT senha_hash FROM {$this->tableNames['usr']} WHERE id_usuario = :id";
            $checkStmt = $this->conn->prepare($checkPasswordQuery);
            $checkStmt->bindParam(':id', $this->id);
            $checkStmt->execute();
            $currentPasswordHash = $checkStmt->fetchColumn();

            if ($currentPasswordHash && password_verify($this->oldPassword, $currentPasswordHash) && $this->newPassword == $this->confirmPassword) {
                $query .= ", senha_hash = :newPassword";
                $passwordUpdated = true;
            } else {
                return false;
            }
        }

        $query .= " WHERE id_usuario = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':nickname', $this->nickname);
            $stmt->bindParam(':defaultTheme', $this->defaultTheme, PDO::PARAM_INT);
            $stmt->bindParam(':id', $this->id);

            if ($passwordUpdated) {
                // --- CORREÇÃO APLICADA AQUI ---
                // 1. Primeiro, criamos o hash e o salvamos em uma variável.
                $newPasswordHash = password_hash($this->newPassword, PASSWORD_DEFAULT);
                // 2. Então, passamos a variável para o bindParam.
                $stmt->bindParam(':newPassword', $newPasswordHash);
            }

            if ($stmt->execute()) {
                $this->createUserHistory('Alteração de informações de perfil.', $this->id, date('Y-m-d H:i:s')); // Adiciona a data atual para ações online
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro em User->updateUser: " . $e->getMessage());
            return false;
        }
    }

    //TODO: a fazer FUNCIONALIDADE DELETEACCOUNT.
    public function deleteAccount() {
        if (!empty($this->email) && !empty($this->password)) {
            $query = "SELECT id_usuario, uemail, upassword FROM " . $this->tableNames['usr'] . " 
            WHERE uemail = :email AND upassword = :upassword";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $this->email);
                $stmt->bindParam(':upassword', $this->password);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    //Inserir registro no histórico
                    $description = 'Exclusão de conta, ';
                    $this->createUserHistory($description, $this->id);
                    return true;
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }

        return false;
    }

    public function createUserHistory($description, $userId, $occurrenceDate, $obs): bool {
        try {
            $historyModel = new History($this->conn);
            // Repassa os novos parâmetros
            return $historyModel->create($userId, $description, $occurrenceDate, $obs);
        } catch (Exception $e) {
            error_log("Erro ao delegar criação de histórico: " . $e->getMessage());
            return false;
        }
    }

    //TODO: Verificar possibilidades de fazer o theme chegar ao banco de dados via menu. Mas, sem ser via AJAX. Precisa ser na padronização atual, e/ou via javascript.
    public function getIdByToken(string $userToken): ?int
    {
        error_log("Model/User.php - getIdByToken: userToken recebido: " . $userToken);
        try {
            $query = "SELECT id_usuario FROM {$this->tableNames['tok']} WHERE token = :userToken";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userToken', $userToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['id_usuario'])) {
                return $result['id_usuario'];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log("Model/User.php - getIdByToken: Erro PDO - " . $e->getMessage());
            return null;
        }
    }

    public function getUserIdByTokenForSync(string $userToken, int $gracePeriodHours = 24): ?int
    {
        try {
            $query = "SELECT id_usuario, data_expiracao FROM {$this->tableNames['tok']} WHERE token = :userToken";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userToken', $userToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $expiryDate = new DateTime($result['data_expiracao']);
                $now = new DateTime();

                // Calcula o fim do período de carência (data de expiração + X horas)
                $gracePeriodEndDate = (clone $expiryDate)->add(new DateInterval("PT{$gracePeriodHours}H"));

                // O token é válido se:
                // 1. A data atual for ANTES da data de expiração, OU
                // 2. A data atual for ANTES do FIM do período de carência.
                if ($now <= $expiryDate || $now <= $gracePeriodEndDate) {
                    return (int)$result['id_usuario'];
                }
            }
            // Se o token não existe ou está além do período de carência, retorna nulo.
            return null;

        } catch (Exception $e) {
            error_log("Model/User.php - getUserIdByTokenForSync: Erro - " . $e->getMessage());
            return null;
        }
    }

    public function updateTheme(int $userId, int $theme): bool
    {
        error_log("Model/User.php - updateTheme: userId recebido: " . $userId . ", theme recebido: " . $theme);
        try {
            // CORREÇÃO: Altera 'udefaultTheme' para 'tema_padrao' e 'uid' para 'id_usuario'
            $query = "UPDATE {$this->tableNames['usr']} SET tema_padrao = :theme WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':theme', $theme, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $result = $stmt->execute();

            if (!$result) {
                error_log("Model/User.php - updateTheme: Erro SQL - " . print_r($stmt->errorInfo(), true));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Model/User.php - updateTheme: Erro PDO - " . $e->getMessage());
            return false;
        }
    }

    public function deleteTokenForUser(int $userId): bool
    {
        try {
            $query = "DELETE FROM {$this->tableNames['tok']} WHERE id_usuario = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar token: " . $e->getMessage());
            return false;
        }
    }

    public function insertNewUserPicture(int $userId, string $relativePath, string $originalFileName): ?int
    {
        $this->conn->beginTransaction();
        try {
            // 1. Procura pelo álbum padrão "Foto de perfil" do usuário.
            $albumQuery = "SELECT album_id FROM {$this->tableNames['alb']} WHERE id_usuario = :userId AND nome_album = 'Foto de perfil'";
            $albumStmt = $this->conn->prepare($albumQuery);
            $albumStmt->execute([':userId' => $userId]);
            $albumId = $albumStmt->fetchColumn();

            // 2. Se o álbum não for encontrado, cria-o.
            if (!$albumId) {
                // tipo_album = 1 para "Foto de perfil"
                $insertAlbumQuery = "INSERT INTO {$this->tableNames['alb']} (id_usuario, nome_album, tipo_album) VALUES (:userId, 'Foto de perfil', 1)";
                $this->conn->prepare($insertAlbumQuery)->execute([':userId' => $userId]);
                $albumId = $this->conn->lastInsertId();
            }

            // 3. Agora com um album_id garantido, insere a foto na tabela 'fotos'.
            $query = "INSERT INTO {$this->tableNames['fot']} (album_id, id_usuario, caminho_arquivo, nome_foto, perfil) 
                  VALUES (:albumId, :userId, :caminho, :nomeFoto, 0)"; // perfil = 0 por padrão

            $stmt = $this->conn->prepare($query);
            $params = [
                ':albumId' => $albumId,
                ':userId' => $userId,
                ':caminho' => $relativePath,
                ':nomeFoto' => $originalFileName
            ];

            if ($stmt->execute($params)) {
                $photoId = (int)$this->conn->lastInsertId();
                $this->conn->commit(); // Confirma a transação
                $this->createUserHistory('Upload de nova foto: ' . $originalFileName, $userId);
                return $photoId; // Retorna o ID da foto inserida
            }

            // Se a inserção da foto falhar, reverte tudo.
            $this->conn->rollBack();
            error_log("Erro ao inserir foto no DB: " . print_r($stmt->errorInfo(), true));
            return null;

        } catch (PDOException $e) {
            $this->conn->rollBack(); // Garante que tudo seja revertido em caso de erro.
            error_log("Erro PDO em User->insertNewUserPicture: " . $e->getMessage());
            return null;
        }
    }

// Define uma foto existente como a de perfil (e desmarca as outras).
    public function setActiveProfilePicture(int $userId, int $photoId): bool
    {
        $this->conn->beginTransaction();
        try {
            $clearProfileQuery = "UPDATE {$this->tableNames['fot']} SET perfil = 0 WHERE id_usuario = :userId";
            $this->conn->prepare($clearProfileQuery)->execute([':userId' => $userId]);

            $setProfileQuery = "UPDATE {$this->tableNames['fot']} SET perfil = 1 WHERE foto_id = :photoId AND id_usuario = :userId";
            $setStmt = $this->conn->prepare($setProfileQuery);
            $setStmt->execute([':photoId' => $photoId, ':userId' => $userId]);

            if ($setStmt->rowCount() > 0) {
                $this->conn->commit();
                $this->createUserHistory(
                    'Definiu nova foto de perfil (ID da Foto: ' . $photoId . ')',
                    $userId,
                    date('Y-m-d H:i:s'), // Data atual
                    'online'              // Origem da ação
                );
                return true;
            }

            $this->conn->rollBack();
            return false;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro em User->setActiveProfilePicture: " . $e->getMessage());
            return false;
        }
    }

// Busca todas as fotos de um usuário para exibir na galeria.
    public function getAllUserPictures(int $userId): array
    {
        try {
            // Usa aliases (AS) para renomear as colunas para corresponder ao novo padrão do JS
            $query = "SELECT 
                    foto_id         AS picId, 
                    id_usuario      AS userId, 
                    caminho_arquivo AS path, 
                    nome_foto       AS name, 
                    perfil          AS isProfile 
                  FROM {$this->tableNames['fot']} 
                  WHERE id_usuario = :userId ORDER BY data_upload DESC limit 3";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em User->getAllUserPictures: " . $e->getMessage());
            return [];
        }
    }

    public function getUserHistory($userId, $limit) {
        // CORREÇÃO: Usando os nomes de colunas corretos (id_usuario, historico_id)
        $querySelect = "SELECT h.descricao, h.data_ocorrencia 
                    FROM {$this->tableNames['his']} h
                    WHERE h.id_usuario = :id 
                    ORDER BY h.historico_id DESC 
                    LIMIT :limitValue";
        try {
            $stmt = $this->conn->prepare($querySelect);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limitValue', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erro em User->getUserHistory: " . $e->getMessage());
            return false;
        }
    }
}
?>