DROP DATABASE ondetemDB_us;
CREATE DATABASE ondetemDB_us;
use ondetemDB_us;
CREATE TABLE users(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(88) UNIQUE,
    upassword VARCHAR(50),
    urank int(2) null
);
CREATE TABLE userdata(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    username varchar(50) UNIQUE NOT NULL,
    location VARCHAR(255),
    cpf varchar(50) UNIQUE NOT NULL,
    image varchar(255) null,
    defaultTheme BOOLEAN NOT NULL,
    idUserFK INT NOT NULL,
    FOREIGN KEY(idUserFK) REFERENCES users(id)
);
CREATE TABLE friendship(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    idUser1FK INT NOT NULL,
    idUser2FK INT NOT NULL,
    datein datetime DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(idUser1FK) REFERENCES users(id),
    FOREIGN KEY(idUser2FK) REFERENCES users(id)
);
CREATE TABLE history(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    description longtext,
    idUserFK INT NOT NULL,
    dateIn datetime default CURRENT_TIMESTAMP,
    FOREIGN KEY(idUserFK) REFERENCES users(id)
);

INSERT INTO `friendship` (`cod`, `idUser1FK`, `idUser2FK`, `datein`) VALUES
(1, 10, 12, '2024-06-24 00:00:00'),
(2, 10, 13, '2024-06-23 23:59:59'),
(3, 12, 14, '2024-06-22 23:59:58'),
(4, 12, 15, '2024-06-21 23:59:57'),
(5, 16, 14, '2024-06-20 23:59:56');

INSERT INTO `userdata` (`cod`, `username`, `location`, `cpf`, `image`, `idUserFK`) VALUES
(21, 'fonsecay', 'Nova Iguaçu, Rio de Janeiro, Brazil', '12345678900', '/Persistence/userProfileImages/profilePicFonsecay.jpg', 19),
(22, 'janesilva', 'Rio de Janeiro, Rio de Janeiro, Brazil', '98765432100', 'profile_pic2.jpg', 10),
(23, 'peterjones', 'São Paulo, São Paulo, Brazil', '34567890123', 'profile_pic3.jpg', 11),
(24, 'marybrown', 'Belo Horizonte, Minas Gerais, Brazil', '56789012345', 'profile_pic4.jpg', 12),
(25, 'davidwilliams', 'Brasília, Distrito Federal, Brazil', '78901234567', 'profile_pic5.jpg', 13),
(26, 'sarahmiller', 'Salvador, Bahia, Brazil', '90123456789', 'profile_pic6.jpg', 14),
(27, 'pauljohnson', 'Fortaleza, Ceará, Brazil', '01234567890', 'profile_pic7.jpg', 15),
(28, 'emilydavis', 'Curitiba, Paraná, Brazil', '23456789012', 'profile_pic8.jpg', 16),
(29, 'richardwilson', 'Porto Alegre, Rio Grande do Sul, Brazil', '45678901234', 'profile_pic9.jpg', 17),
(30, 'susantaylor', 'Manaus, Amazonas, Brazil', '67890123456', 'profile_pic10.jpg', 18);

INSERT INTO `users` (`id`, `name`, `email`, `upassword`, `urank`) VALUES
(9, 'John Doe', 'johndoe@email.com', '$2y$10$y2z23zZf5fK4aJ8lU0b.zQ', 1),
(10, 'Jane Silva', 'janesilva@email.com', '$2y$10$L3456789K0L987654321', 2),
(11, 'Peter Jones', 'peterjones@email.com', '$2y$10$9876543210L987654321', 3),
(12, 'Mary Brown', 'marybrown@email.com', '$2y$10$AABBCCDDEEFFGGHHIIJJ', 4),
(13, 'David Williams', 'davidwilliams@email.com', '$2y$10$1234567890ABCDEFGHIJKLM', 5),
(14, 'Sarah Miller', 'sarahmiller@email.com', '$2y$10$POIUYTREWQASDFGHJKLZXCV', 6),
(15, 'Paul Johnson', 'pauljohnson@email.com', '$2y$10$NBVCXZASDFGHJKLPOIUYTREWQ', 7),
(16, 'Emily Davis', 'emilydavis@email.com', '$2y$10$MNBVCXZASDFGHJKLPOIUYTREWQ', 8),
(17, 'Richard Wilson', 'richardwilson@email.com', '$2y$10$OPQRSTUVWXYZABCDEFGHIJKLM', 9),
(18, 'Susan Taylor', 'susantaylor@email.com', '$2y$10$LKJHGFEDCBAZYXWVUTSRQPONM', 10),
(19, 'Yan Goulart Fonseca', 'yan@mail.com', '12', 1);
CREATE DATABASE ondetemDB_pd;
use ondetemDB_pd;
CREATE TABLE enterprise(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    description LONGTEXT
);
create table enterprisedata(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    CNPJ varchar(50) UNIQUE NOT NULL,
    location VARCHAR(255),
    image varchar(255) null,
    identerpriseFK INT NOT NULL,
    FOREIGN KEY(identerpriseFK) REFERENCES enterprise(id)
);
CREATE TABLE control(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    idUserFK INT NOT NULL,
    rank INT(2) NOT NULL, 
    codEnterprisedataFK INT NOT NULL,
    FOREIGN KEY(codEnterprisedataFK) REFERENCES enterprisedata(cod)
);
CREATE TABLE manufacture(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    description LONGTEXT,
    brand varchar(100)
);
CREATE TABLE products(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255), 
    description LONGTEXT,
    idManufacFK INT NOT NULL,
    FOREIGN KEY(idManufacFK) REFERENCES manufac(id)
);
CREATE TABLE productdata(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    GTIN varchar(88) NULL,
    EAN varchar(88) NULL,
    PLU varchar(88) NULL,
    NCM varchar(88) NULL,
    CEST varchar(88) NULL,
    CNAE varchar(88) NULL,
    image varchar(255) null,
    idProductFK INT NOT NULL,
    FOREIGN KEY(idProductFK) REFERENCES products(cod)
);
CREATE TABLE productprice(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    lastvalue DECIMAL(9,2) NULL DEFAULT  '0.00' ,
    idCommerceFK INT NOT NULL,
    idProductFK INT NOT NULL,
    FOREIGN KEY(idCommerceFK) REFERENCES enterprise(cod),
    FOREIGN KEY(idProductFK) REFERENCES products(cod)
);
CREATE DATABASE ondetemDB_dc;
use ondetemDB_dc;
CREATE TABLE history(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    description longtext,
    idUserFK INT NOT NULL,
    dateIn datetime default CURRENT_TIMESTAMP
);
CREATE TABLE pictures(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    path varchar(255) not null,
    description longtext,
    idUserFK INT NULL,
    idEnterpriseFK INT NULL,
    dateIn datetime default CURRENT_TIMESTAMP
);