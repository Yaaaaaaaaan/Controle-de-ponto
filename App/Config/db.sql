-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 31/03/2025 às 22:33
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cpbd`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `history`
--

CREATE TABLE `history` (
                           `cod` int(11) NOT NULL,
                           `description` longtext DEFAULT NULL,
                           `uidUserFK` int(11) NOT NULL,
                           `dateIn` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pictures`
--

CREATE TABLE `pictures` (
                            `cod` int(11) NOT NULL,
                            `path` varchar(255) NOT NULL,
                            `description` longtext DEFAULT NULL,
                            `uidUserFK` int(11) DEFAULT NULL,
                            `dateload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pictures`
--

INSERT INTO `pictures` (`cod`, `path`, `description`, `uidUserFK`, `dateload`) VALUES
                                                                                   (23, '/Controle-de-ponto/App/Persistence/userProfileImages/202410201606041.jpg', '202410201606041.jpg', 1, '2024-10-20 11:06:04'),
                                                                                   (24, '/Controle-de-ponto/App/Persistence/userProfileImages/202410201728031.png', '202410201728031.png', 1, '2024-10-20 12:28:03'),
                                                                                   (25, '/Controle-de-ponto/App/Persistence/userProfileImages/202410201729001.jpg', '202410201729001.jpg', 1, '2024-10-20 12:29:00'),
                                                                                   (26, '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png', 'Profile.png', 2, '2025-03-21 18:11:09'),
                                                                                   (29, '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png', 'Profile.png', 6, '2025-03-25 01:29:28');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pointcontrol`
--

CREATE TABLE `pointcontrol` (
                                `cod` int(11) NOT NULL,
                                `description` longtext DEFAULT NULL,
                                `uidUserFK` int(11) NOT NULL,
                                `dateIn` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pointcontrol`
--

INSERT INTO `pointcontrol` (`cod`, `description`, `uidUserFK`, `dateIn`) VALUES
                                                                             (1, 'Verificação pendente', 1, '2025-03-18'),
                                                                             (2, 'Verificação pendente', 1, '2025-03-19'),
                                                                             (10, 'Verificação pendente', 1, '2025-03-20'),
                                                                             (39, 'Verificação pendente', 1, '2025-03-21'),
                                                                             (54, 'Verificação pendente', 1, '2025-02-18'),
                                                                             (55, 'Verificação pendente', 1, '2025-01-19'),
                                                                             (56, 'Verificação pendente', 1, '2024-12-20'),
                                                                             (57, 'Verificação pendente', 1, '2024-11-21'),
                                                                             (59, 'Verificação pendente', 1, '2025-03-17'),
                                                                             (60, 'Verificação pendente', 1, '2025-03-16'),
                                                                             (61, 'Verificação pendente', 1, '2025-03-15'),
                                                                             (62, 'Verificação pendente', 1, '2025-03-14'),
                                                                             (63, 'Verificação pendente', 1, '2025-03-13'),
                                                                             (64, 'Verificação pendente', 1, '2025-03-12'),
                                                                             (65, 'Verificação pendente', 1, '2025-03-11'),
                                                                             (66, 'Verificação pendente', 1, '2025-03-10'),
                                                                             (67, 'Verificação pendente', 1, '2025-03-09'),
                                                                             (68, 'Verificação pendente', 1, '2025-03-08'),
                                                                             (69, 'Verificação pendente', 1, '2025-03-07'),
                                                                             (70, 'Verificação pendente', 1, '2025-03-06'),
                                                                             (71, 'Verificação pendente', 1, '2025-03-01'),
                                                                             (72, 'Verificação pendente', 1, '2025-03-02'),
                                                                             (73, 'Verificação pendente', 1, '2025-03-03'),
                                                                             (74, 'Verificação pendente', 1, '2025-03-04'),
                                                                             (75, 'Verificação pendente', 1, '2025-03-05'),
                                                                             (76, 'Verificação pendente', 1, '2024-10-21'),
                                                                             (78, 'Verificação pendente', 1, '2025-03-22'),
                                                                             (81, 'Verificação pendente', 1, '2025-03-24'),
                                                                             (82, 'Verificação pendente', 1, '2025-03-25'),
                                                                             (84, 'Verificação pendente', 6, '2025-03-25'),
                                                                             (93, 'Verificação pendente', 1, '2025-03-27'),
                                                                             (94, 'Verificação pendente', 1, '2025-03-28'),
                                                                             (95, 'Verificação pendente', 1, '2025-03-29'),
                                                                             (96, 'Verificação pendente', 1, '2025-03-30'),
                                                                             (99, 'Verificação pendente', 1, '2025-03-31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `profilepictures`
--

CREATE TABLE `profilepictures` (
                                   `cod` int(11) NOT NULL,
                                   `uimageFK` int(11) NOT NULL,
                                   `dateload` datetime DEFAULT current_timestamp(),
                                   `uidUserFK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `profilepictures`
--

INSERT INTO `profilepictures` (`cod`, `uimageFK`, `dateload`, `uidUserFK`) VALUES
                                                                               (12, 24, '2024-10-20 11:06:04', 1),
                                                                               (15, 26, '2025-03-21 18:11:09', 2),
                                                                               (18, 29, '2025-03-25 01:29:28', 6);

-- --------------------------------------------------------

--
-- Estrutura para tabela `userdata`
--

CREATE TABLE `userdata` (
                            `uid` int(11) NOT NULL,
                            `uname` varchar(100) DEFAULT NULL,
                            `uemail` varchar(88) DEFAULT NULL,
                            `upassword` varchar(255) DEFAULT NULL,
                            `urank` int(2) DEFAULT NULL,
                            `username` varchar(50) DEFAULT NULL,
                            `udefaultTheme` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `userdata`
--

INSERT INTO `userdata` (`uid`, `uname`, `uemail`, `upassword`, `urank`, `username`, `udefaultTheme`) VALUES
                                                                                                         (1, 'Yan Fonseca', 'fonsecay@a.a', '1234', 1, 'fonsecay', 0),
                                                                                                         (2, 'yan fonseca1', 'yangoular@gmail.com', '1234', 1, 'yan', 0),
                                                                                                         (6, 'user', 'user@u.a', '1234', 1, 'user', 0);

--
-- Acionadores `userdata`
--
DELIMITER $$
CREATE TRIGGER `tr_insert_token` AFTER INSERT ON `userdata` FOR EACH ROW BEGIN
    INSERT INTO usertoken (token, uidUserFK)
    VALUES (UNHEX('62396231343731666461626464363266633138346665663133643938356261636161623334323665353865316366653461643430373163663838613638653262'), NEW.uid);
END
    $$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usertoken`
--

CREATE TABLE `usertoken` (
                             `token` varchar(255) NOT NULL,
                             `uidUserFK` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usertoken`
--

INSERT INTO `usertoken` (`token`, `uidUserFK`) VALUES
                                                   ('8d373c31745532684546ea33d2d39d4ac59a56d6587b679ce22912d922a13bee', 1),
                                                   ('8d373c31745532684546ea33d2d39d4ac59a56d6587b679ce22912d922a13bee', 1),
                                                   ('b9b1471fdabdd62fc184fef13d985bacaab3426e58e1cfe4ad4071cf88a68e2b', 5),
                                                   ('1028a8d723cb56abf90a4ba479cca2a37d89abe18bafa96519c16553e187e7f8', 6),
                                                   ('1028a8d723cb56abf90a4ba479cca2a37d89abe18bafa96519c16553e187e7f8', 6);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `history`
--
ALTER TABLE `history`
    ADD PRIMARY KEY (`cod`),
  ADD KEY `uidUserFK` (`uidUserFK`);

--
-- Índices de tabela `pictures`
--
ALTER TABLE `pictures`
    ADD PRIMARY KEY (`cod`),
  ADD KEY `uidUserFK` (`uidUserFK`);

--
-- Índices de tabela `pointcontrol`
--
ALTER TABLE `pointcontrol`
    ADD PRIMARY KEY (`cod`),
  ADD UNIQUE KEY `idx_usuario_data` (`uidUserFK`,`dateIn`);

--
-- Índices de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
    ADD PRIMARY KEY (`cod`),
  ADD UNIQUE KEY `uidUserFK` (`uidUserFK`) USING BTREE,
  ADD KEY `uimageFK` (`uimageFK`);

--
-- Índices de tabela `userdata`
--
ALTER TABLE `userdata`
    ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uemail` (`uemail`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Índices de tabela `usertoken`
--
ALTER TABLE `usertoken`
    ADD KEY `uidUserFK` (`uidUserFK`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `history`
--
ALTER TABLE `history`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `pictures`
--
ALTER TABLE `pictures`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `pointcontrol`
--
ALTER TABLE `pointcontrol`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `userdata`
--
ALTER TABLE `userdata`
    MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `history`
--
ALTER TABLE `history`
    ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);

--
-- Restrições para tabelas `pictures`
--
ALTER TABLE `pictures`
    ADD CONSTRAINT `pictures_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);

--
-- Restrições para tabelas `pointcontrol`
--
ALTER TABLE `pointcontrol`
    ADD CONSTRAINT `pointcontrol_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);

--
-- Restrições para tabelas `profilepictures`
--
ALTER TABLE `profilepictures`
    ADD CONSTRAINT `profilepictures_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
