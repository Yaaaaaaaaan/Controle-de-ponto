-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/10/2024 às 06:28
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
(25, '/Controle-de-ponto/App/Persistence/userProfileImages/202410201729001.jpg', '202410201729001.jpg', 1, '2024-10-20 12:29:00');

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
(12, 25, '2024-10-20 11:06:04', 1);

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
(1, 'Yan Fonseca', 'fonsecay@a.a', '1234', 1, 'fonsecay', 0);

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
('55c18030a047c2bc6ceaaec86ad6431d0a12dfb8c1fc60207e56e87d1fcd1d9b', 1);

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
  MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
  MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
