-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/10/2024 às 07:45
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

--
-- Despejando dados para a tabela `history`
--

INSERT INTO `history` (`cod`, `description`, `uidUserFK`, `dateIn`) VALUES
(1, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 16e72713f5fcd61d10fa08e0ad0d1344f9e1459146a3c9a4032d4aed9d9f0ea5', 1, '2024-08-14 19:54:25'),
(2, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 667bfc7ffa0606ebcb4b50857dc3bb3a17986f85be21f1f8179764d287f22c9b', 1, '2024-08-14 20:02:07'),
(3, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 38e9636c1eaf2a7d88259c71971a691b402d6eb849619134350464ca875af0e3', 1, '2024-08-14 20:02:42'),
(4, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: c1e2e2592a1979054c1a1005e8d4dcf0c7c689a865a667ab9ec2b0200adb84f6', 1, '2024-08-14 20:03:06'),
(5, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 09157159027eef466537f6442a20a30e01109e9516b35e3b6d847607006bbcc4', 1, '2024-08-14 20:34:20'),
(6, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: aaa6ab11b7377076afd9c6809750f0f9cbe6f8cbdc886ee481121dcd340620e2', 1, '2024-08-14 20:36:29'),
(7, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 6c4a365eab12a36d6b6618571b20cb5fcf9dd18210a920205fc7f114beb8617c', 1, '2024-08-14 20:46:31'),
(8, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 1d05c80e3adf1b4b2558158da5a27e41d6795018a00c7d2c778bde86a58352f9', 1, '2024-08-14 22:06:11'),
(9, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 1f3a7062f48715543e0809151507451c3f4adeedbe0c872cfbd860a2174df7b3', 1, '2024-08-14 22:16:40'),
(10, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 40380fffa69856c386c70e5466451d867c0372d7bb56372a9a3d20c72e21fa8b', 1, '2024-08-14 22:19:31'),
(11, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 36da5a2477fecebbf4ddba7c994447effab8dc324450747f6166533e24ccbe0e', 1, '2024-08-14 23:08:57'),
(12, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 1e4694e831cb5760d887b311952c6b22bc1f98adfcf142f4b50dc9c99a50f95b', 1, '2024-10-17 02:42:20'),
(13, 'login a partir do ip:::1 E criação do Hash para autenticação temporário: 0acbf20b2969fe86ac26b503debdcacd177ce6085f6c0b68f2b11d231ea0c4f5', 1, '2024-10-17 02:43:53');

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `profilepictures`
--

CREATE TABLE `profilepictures` (
  `cod` int(11) NOT NULL,
  `uimage` varchar(255) DEFAULT NULL,
  `dateload` datetime DEFAULT current_timestamp(),
  `uidUserFK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `profilepictures`
--

INSERT INTO `profilepictures` (`cod`, `uimage`, `dateload`, `uidUserFK`) VALUES
(1, NULL, '2024-08-14 19:52:58', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `userdata`
--

CREATE TABLE `userdata` (
  `uid` int(11) NOT NULL,
  `uname` varchar(100) DEFAULT NULL,
  `uemail` varchar(88) DEFAULT NULL,
  `upassword` varchar(50) DEFAULT NULL,
  `urank` int(2) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `udefaultTheme` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `userdata`
--

INSERT INTO `userdata` (`uid`, `uname`, `uemail`, `upassword`, `urank`, `username`, `udefaultTheme`) VALUES
(1, 'Yan Fonseca', 'fonsecay@pge.rj.gov.br', '1234', 1, 'fonsecay', 0);

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
('0acbf20b2969fe86ac26b503debdcacd177ce6085f6c0b68f2b11d231ea0c4f5', 1);

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
-- Índices de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
  ADD PRIMARY KEY (`cod`),
  ADD KEY `uidUserFK` (`uidUserFK`);

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
  MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `pictures`
--
ALTER TABLE `pictures`
  MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
  MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `userdata`
--
ALTER TABLE `userdata`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Restrições para tabelas `profilepictures`
--
ALTER TABLE `profilepictures`
  ADD CONSTRAINT `profilepictures_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);

--
-- Restrições para tabelas `usertoken`
--
ALTER TABLE `usertoken`
  ADD CONSTRAINT `usertoken_ibfk_1` FOREIGN KEY (`uidUserFK`) REFERENCES `userdata` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
