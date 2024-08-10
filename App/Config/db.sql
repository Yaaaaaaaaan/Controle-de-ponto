DROP DATABASE cpbd;
CREATE DATABASE cpbd;
use cpbd;
CREATE TABLE userdata(
    uid INT PRIMARY KEY AUTO_INCREMENT,
    uname VARCHAR(100),
    uemail VARCHAR(88) UNIQUE,
    upassword VARCHAR(50),
    urank int(2) null
    username varchar(50) UNIQUE NOT NULL,
    udefaultTheme BOOLEAN NOT NULL,
);
CREATE TABLE profilePictures(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    image varchar(255) null,
    
    dateIn datetime default CURRENT_TIMESTAMP
);
CREATE TABLE history(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    description longtext,
    idUserFK INT NOT NULL,
    dateIn datetime default CURRENT_TIMESTAMP,
    FOREIGN KEY(idUserFK) REFERENCES users(id)
);
CREATE TABLE pictures(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    path varchar(255) not null,
    description longtext,
    idUserFK INT NULL,
    dateIn datetime default CURRENT_TIMESTAMP
);