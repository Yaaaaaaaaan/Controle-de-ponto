-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/05/2025 às 18:52
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
                                                                        (29, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: 0e04e7b07b57be8cfd80459a8208c92d0bc4031eeb672a9b49b0692c6a014992', 13, '2025-05-07 03:14:52'),
                                                                        (30, 'Criação de conta partir do ip: ::1', 14, '2025-05-07 03:27:30'),
                                                                        (31, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: 2ea227852cb61b0a80754656370f022f29e7d9d32b0ab3b17c5cac01d750bac0', 14, '2025-05-07 03:27:36'),
                                                                        (32, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:42:16'),
                                                                        (33, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:42:21'),
                                                                        (34, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:45:08'),
                                                                        (35, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:45:34'),
                                                                        (36, 'Alterou para a foto de perfil id: 49 , a partir do ip: ::1', 14, '2025-05-07 03:56:10'),
                                                                        (37, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:56:18'),
                                                                        (38, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:58:46'),
                                                                        (39, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 03:59:07'),
                                                                        (40, 'Alterou para a foto de perfil id: 51 , a partir do ip: ::1', 14, '2025-05-07 03:59:22'),
                                                                        (41, 'Alterou para a foto de perfil id: 49 , a partir do ip: ::1', 14, '2025-05-07 03:59:27'),
                                                                        (42, 'Alterou para a foto de perfil id: 53 , a partir do ip: ::1', 14, '2025-05-07 04:01:39'),
                                                                        (43, 'Consulta de histórico partir do ip: ::1', 14, '2025-05-07 04:08:51'),
                                                                        (44, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: 6d4dbeec228d4f69f057295c0e010f3f938c560f09a5aad181af9d1579094e90', 13, '2025-05-07 04:11:33'),
                                                                        (45, 'Consulta de histórico partir do ip: ::1', 13, '2025-05-07 04:11:45'),
                                                                        (46, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: f4759d5eebbfff0b09ece247acbedb6a5b9fc873b9d1b508cf28d4b8a9f9caab', 14, '2025-05-07 04:14:41'),
                                                                        (47, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: b397f90e4c0d4ee58173e36625a07a2cc38ddf5e7230f272ba0e619ca7f71991', 13, '2025-05-07 04:14:50'),
                                                                        (48, 'login a partir do ip: ::1 e criação do Hash para autenticação temporário: 3552dd2f00985eca3833e31243286118558bbc439397d2a727769d9a98335ff3', 13, '2025-05-07 11:57:24'),
                                                                        (49, 'Alteração de informações partir do ip: ::1', 13, '2025-05-07 19:58:55'),
                                                                        (50, 'Alteração de informações partir do ip: ::1', 13, '2025-05-07 20:12:46'),
                                                                        (51, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 00:20:08'),
                                                                        (52, 'Inserção de presença no controle-de-ponto. Endereço IP: ::1', 13, '2025-05-08 00:22:53'),
                                                                        (53, 'Inserção de presença no controle-de-ponto. Endereço IP: ::1', 13, '2025-05-08 00:24:13'),
                                                                        (54, 'Alterou para a foto de perfil id: 45 . Endereço IP: ::1', 13, '2025-05-08 00:26:25'),
                                                                        (55, 'Alterou para a foto de perfil cod: 47 . Endereço IP: ::1', 13, '2025-05-08 00:26:58'),
                                                                        (56, 'Inseriu a imagem: 2025050805273413.png ao sistema, Endereço IP: ::1', 13, '2025-05-08 00:27:34'),
                                                                        (57, 'Alterou para a foto de perfil cod: 54 . Endereço IP: ::1', 13, '2025-05-08 00:27:47'),
                                                                        (58, 'Alterou para a foto de perfil cod: 47 . Endereço IP: ::1', 13, '2025-05-08 00:40:47'),
                                                                        (59, 'Inserção de imagem de usuário. Endereço IP: ::1', 13, '2025-05-08 00:40:53'),
                                                                        (60, 'Inseriu a imagem: 2025050805430213.jpg ao sistema, Endereço IP: ::1', 13, '2025-05-08 00:43:02'),
                                                                        (61, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 00:43:37'),
                                                                        (62, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 01:01:16'),
                                                                        (63, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 01:01:20'),
                                                                        (64, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 01:01:22'),
                                                                        (65, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 15:43:31'),
                                                                        (66, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 21:05:54'),
                                                                        (67, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 21:36:06'),
                                                                        (68, 'Alterou para a foto de perfil cod: 55 . Endereço IP: ::1', 13, '2025-05-08 21:36:52'),
                                                                        (69, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 21:37:05'),
                                                                        (70, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-08 21:37:08'),
                                                                        (71, 'Inseriu a imagem: 2025050902400413.jpg ao sistema. Endereço IP: ::1', 13, '2025-05-08 21:40:04'),
                                                                        (72, 'Alterou para a foto de perfil cod: 57 . Endereço IP: ::1', 13, '2025-05-08 21:40:10'),
                                                                        (73, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 23:50:32'),
                                                                        (74, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 23:58:17'),
                                                                        (75, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-08 23:59:33'),
                                                                        (76, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:10:42'),
                                                                        (77, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:12:52'),
                                                                        (78, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:13:44'),
                                                                        (79, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:14:27'),
                                                                        (80, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:19:25'),
                                                                        (81, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:29:39'),
                                                                        (82, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:32:27'),
                                                                        (83, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:36:06'),
                                                                        (84, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:52:31'),
                                                                        (85, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:53:35'),
                                                                        (86, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 00:58:05'),
                                                                        (87, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 01:01:03'),
                                                                        (88, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-09 01:01:36'),
                                                                        (89, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 01:01:59'),
                                                                        (90, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 01:08:08'),
                                                                        (91, 'Inserção de presença no controle-de-ponto. Endereço IP: ::1', 13, '2025-05-09 01:08:11'),
                                                                        (92, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-09 01:08:25'),
                                                                        (93, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 01:24:21'),
                                                                        (94, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 01:24:34'),
                                                                        (95, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 11:59:52'),
                                                                        (96, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 12:51:40'),
                                                                        (97, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:04:23'),
                                                                        (98, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:04:53'),
                                                                        (99, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:10:54'),
                                                                        (100, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:13:58'),
                                                                        (101, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:15:25'),
                                                                        (102, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 13:39:34'),
                                                                        (103, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 14:01:36'),
                                                                        (104, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 14:03:02'),
                                                                        (105, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 14:03:15'),
                                                                        (106, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 14:04:19'),
                                                                        (107, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 19:08:38'),
                                                                        (108, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 19:48:20'),
                                                                        (109, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 19:48:29'),
                                                                        (110, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:13:25'),
                                                                        (111, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:15:01'),
                                                                        (112, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:18:53'),
                                                                        (113, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:20:11'),
                                                                        (114, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:22:13'),
                                                                        (115, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:23:40'),
                                                                        (116, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:25:47'),
                                                                        (117, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:26:20'),
                                                                        (118, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:26:58'),
                                                                        (119, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:28:09'),
                                                                        (120, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:28:52'),
                                                                        (121, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:58:14'),
                                                                        (122, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 20:58:23'),
                                                                        (123, 'Alterou para a foto de perfil cod: 57 . Endereço IP: ::1', 13, '2025-05-09 20:59:44'),
                                                                        (124, 'Alteração de informações, Endereço IP: ::1', 13, '2025-05-09 21:00:16'),
                                                                        (125, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 21:01:33'),
                                                                        (126, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 21:04:56'),
                                                                        (127, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 21:34:31'),
                                                                        (128, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-09 21:34:55'),
                                                                        (129, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 21:46:57'),
                                                                        (130, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 22:07:42'),
                                                                        (131, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-09 22:08:22'),
                                                                        (132, 'login e atualização de tokenEndereço IP: ::1', 13, '2025-05-10 00:54:12'),
                                                                        (133, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 01:07:29'),
                                                                        (134, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 02:01:36'),
                                                                        (135, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 02:02:14'),
                                                                        (136, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 03:09:37'),
                                                                        (137, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 05:06:04'),
                                                                        (138, 'Inserção de presença no controle-de-ponto. Endereço IP: ::1', 13, '2025-05-10 06:02:26'),
                                                                        (139, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 07:04:05'),
                                                                        (140, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 07:04:09'),
                                                                        (141, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 07:04:11'),
                                                                        (142, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 07:04:20'),
                                                                        (143, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 07:04:33'),
                                                                        (144, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 17:55:09'),
                                                                        (145, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 17:55:16'),
                                                                        (146, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 17:55:56'),
                                                                        (147, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 17:56:40'),
                                                                        (148, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:00:05'),
                                                                        (149, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:00:21'),
                                                                        (150, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:00:28'),
                                                                        (151, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:18:01'),
                                                                        (152, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:27:04'),
                                                                        (153, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:33:25'),
                                                                        (154, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:35:08'),
                                                                        (155, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:36:00'),
                                                                        (156, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:37:45'),
                                                                        (157, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:43:23'),
                                                                        (158, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:46:02'),
                                                                        (159, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 18:46:38'),
                                                                        (160, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:00:10'),
                                                                        (161, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:00:13'),
                                                                        (162, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:00:19'),
                                                                        (163, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:12:07'),
                                                                        (164, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:12:17'),
                                                                        (165, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:15:03'),
                                                                        (166, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:15:31'),
                                                                        (167, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:15:52'),
                                                                        (168, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-10 19:32:58'),
                                                                        (169, 'Alterou para a foto de perfil cod: 56 . Endereço IP: ::1', 13, '2025-05-10 21:15:41'),
                                                                        (170, 'Alterou para a foto de perfil cod: 56 . Endereço IP: ::1', 13, '2025-05-10 21:15:51'),
                                                                        (171, 'Inserção de presença no controle-de-ponto. Endereço IP: ::1', 13, '2025-05-11 01:39:41'),
                                                                        (172, 'Alterou para a foto de perfil cod: 56 . Endereço IP: ::1', 13, '2025-05-11 01:40:01'),
                                                                        (173, 'Alterou para a foto de perfil cod: 56 . Endereço IP: ::1', 13, '2025-05-11 01:40:10'),
                                                                        (174, 'Alterou para a foto de perfil cod: 57 . Endereço IP: ::1', 13, '2025-05-11 01:40:30'),
                                                                        (175, 'Alterou para a foto de perfil cod: 56 . Endereço IP: ::1', 13, '2025-05-11 01:40:39'),
                                                                        (176, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-11 02:21:51'),
                                                                        (177, 'login e criação de hash em Endereço IP: 127.0.0.1', 13, '2025-05-11 02:23:16'),
                                                                        (178, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-11 02:26:08'),
                                                                        (179, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-11 02:26:58'),
                                                                        (180, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-17 13:46:42'),
                                                                        (181, 'login e criação de hash em Endereço IP: 127.0.0.1', 13, '2025-05-18 18:43:36'),
                                                                        (182, 'Alterou para a foto de perfil cod: 57 . Endereço IP: 127.0.0.1', 13, '2025-05-18 18:43:49'),
                                                                        (183, 'Inserção de presença no controle-de-ponto. Endereço IP: 127.0.0.1', 13, '2025-05-18 18:44:09'),
                                                                        (184, 'login e criação de hash em Endereço IP: ::1', 13, '2025-05-18 18:44:33');

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
                                                                                   (45, '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png', 'Profile.png', 13, '2025-05-07 02:55:25'),
                                                                                   (46, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050707565413.png', '2025050707565413.png', 13, '2025-05-07 02:56:54'),
                                                                                   (47, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050708021313.jpg', '2025050708021313.jpg', 13, '2025-05-07 03:02:13'),
                                                                                   (48, '/Controle-de-ponto/App/Persistence/userProfileImages/Profile.png', 'Profile.png', 14, '2025-05-07 03:27:30'),
                                                                                   (49, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050708560414.jpg', '2025050708560414.jpg', 14, '2025-05-07 03:56:04'),
                                                                                   (50, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050708580414.jpg', '2025050708580414.jpg', 14, '2025-05-07 03:58:04'),
                                                                                   (51, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050708591314.png', '2025050708591314.png', 14, '2025-05-07 03:59:13'),
                                                                                   (52, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050709002914.jpg', '2025050709002914.jpg', 14, '2025-05-07 04:00:29'),
                                                                                   (53, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050709013314.png', '2025050709013314.png', 14, '2025-05-07 04:01:33'),
                                                                                   (54, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050805273413.png', '2025050805273413.png', 13, '2025-05-08 00:27:34'),
                                                                                   (55, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050805405313.png', '2025050805405313.png', 13, '2025-05-08 00:40:53'),
                                                                                   (56, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050805430213.jpg', '2025050805430213.jpg', 13, '2025-05-08 00:43:02'),
                                                                                   (57, '/Controle-de-ponto/App/Persistence/userProfileImages/2025050902400413.jpg', '2025050902400413.jpg', 13, '2025-05-08 21:40:04');

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
                                                                             (270, 'Recusado', 13, '2025-05-07'),
                                                                             (272, 'Já verificado', 14, '2025-05-07'),
                                                                             (284, 'Verificação pendente', 13, '2025-05-08'),
                                                                             (288, 'Verificação pendente', 13, '2025-05-09'),
                                                                             (318, 'Verificação pendente', 13, '2025-05-10'),
                                                                             (331, 'Verificação pendente', 13, '2025-05-11'),
                                                                             (332, 'Verificação pendente', 13, '2025-05-18');

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
                                                                               (34, 57, '2025-05-07 02:55:25', 13),
                                                                               (37, 53, '2025-05-07 03:27:30', 14);

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
                                                                                                         (13, 'Yan Fonseca', 'yan@teste.com', '$2y$10$Pm/R3hyqM4G3FYWHdK4VAe2lmhxenzdvGmGfWNw7GQVOHbYshlmyi', 1, 'fonsecay', 0),
                                                                                                         (14, 'batata &atilde;o', 'batata@a.b', '$2y$10$46RmOgEnAvP5jV15ycrBA.48iB5UaqqqUM4woO./CENdGs2XUIgim', 1, 'batata', 0);

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
                                                   ('fb5cf55f2b2041e1c74f0b10b049da421308f735ba376cde40a46899088dcb87', 13),
                                                   ('fb5cf55f2b2041e1c74f0b10b049da421308f735ba376cde40a46899088dcb87', 13),
                                                   ('f4759d5eebbfff0b09ece247acbedb6a5b9fc873b9d1b508cf28d4b8a9f9caab', 14),
                                                   ('f4759d5eebbfff0b09ece247acbedb6a5b9fc873b9d1b508cf28d4b8a9f9caab', 14);

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
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT de tabela `pictures`
--
ALTER TABLE `pictures`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de tabela `pointcontrol`
--
ALTER TABLE `pointcontrol`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=333;

--
-- AUTO_INCREMENT de tabela `profilepictures`
--
ALTER TABLE `profilepictures`
    MODIFY `cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `userdata`
--
ALTER TABLE `userdata`
    MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
