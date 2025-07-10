--
-- Banco de dados: `controle_ponto_db`
--
CREATE DATABASE IF NOT EXISTS `controle_ponto_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `controle_ponto_db`;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `usuarios`
-- Armazena os dados de login e informações básicas dos usuários.
--
CREATE TABLE IF NOT EXISTS `usuarios` (
                            `id_usuario` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                            `nome_completo` varchar(100) DEFAULT NULL,
                            `nome_usuario` varchar(50) DEFAULT NULL,
                            `email` varchar(88) DEFAULT NULL,
                            `senha_hash` varchar(255) DEFAULT NULL,
                            `nivel_acesso` int(2) DEFAULT NULL COMMENT 'Ex: 1 para Admin, 2 para Usuário Padrão',
                            `tema_padrao` tinyint(1) NOT NULL DEFAULT 0,
                            PRIMARY KEY (`id_usuario`),
                            UNIQUE KEY `idx_email_unico` (`email`),
                            UNIQUE KEY `idx_nome_usuario_unico` (`nome_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `tokens_autenticacao`
-- Armazena tokens para sessões persistentes ou "lembrar de mim".
--
CREATE TABLE IF NOT EXISTS `tokens_autenticacao` (
    `token_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `data_criacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (`token_id`),
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `albuns`
-- Associa uma ou mais fotos a um album.
--
CREATE TABLE IF NOT EXISTS `albuns` (
                          `album_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `id_usuario` BIGINT UNSIGNED NOT NULL,
                          `nome_album` varchar(255) NOT NULL,
                          `tipo_album` text,
                          `data_definicao` datetime NOT NULL DEFAULT current_timestamp(),
                          PRIMARY KEY (`album_id`),
                          CONSTRAINT `fk_albuns_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `fotos`
-- Armazena metadados de todas as imagens enviadas pelos usuários.
--
CREATE TABLE IF NOT EXISTS `fotos` (
                         `foto_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                         `album_id` BIGINT UNSIGNED NOT NULL,
                         `id_usuario` BIGINT UNSIGNED NOT NULL,
                         `caminho_arquivo` varchar(255) NOT NULL,
                         `nome_foto` text NOT NULL,
                         `legenda_foto` text DEFAULT NULL,
                         `perfil` tinyint(1) DEFAULT 0,
                         `data_upload` datetime NOT NULL DEFAULT current_timestamp(),
                         PRIMARY KEY (`foto_id`),
                         FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
                         FOREIGN KEY (`album_id`) REFERENCES `albuns` (`album_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `historicos_acoes`
-- Registra um log de ações importantes realizadas pelos usuários no sistema.
--
CREATE TABLE IF NOT EXISTS `historicos_acoes` (
                                    `historico_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                    `id_usuario` BIGINT UNSIGNED NOT NULL,
                                    `descricao` text DEFAULT NULL,
                                    `data_ocorrencia` datetime NOT NULL DEFAULT current_timestamp(),
                                    PRIMARY KEY (`historico_id`),
                                    KEY `fk_historico_usuario` (`id_usuario`),
                                    CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela: `registros_ponto`
-- Armazena as marcações de ponto dos usuários.
--
CREATE TABLE IF NOT EXISTS `registros_ponto` (
                                   `registro_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `id_usuario` BIGINT UNSIGNED NOT NULL,
                                   `data_registro` date NOT NULL,
                                   `status` varchar(50) DEFAULT 'Verificação pendente',
                                   `observacao` text DEFAULT NULL,
                                   PRIMARY KEY (`registro_id`),
                                   UNIQUE KEY `idx_usuario_data_unica` (`id_usuario`, `data_registro`),
                                   CONSTRAINT `fk_ponto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Gatilho (Trigger): `trg_criar_token_novo_usuario`
-- Cria um token inicial para um usuário assim que ele é inserido na tabela `usuarios`.
--
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_criar_token_novo_usuario` AFTER INSERT ON `usuarios` FOR EACH ROW
BEGIN
    -- Gera um token aleatório e o insere na tabela de tokens.
    INSERT INTO tokens_autenticacao (id_usuario, token)
    VALUES (NEW.id_usuario, SHA2(UUID(), 256));
END $$
DELIMITER ;

COMMIT;