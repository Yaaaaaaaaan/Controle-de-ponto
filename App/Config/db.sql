DROP DATABASE cpbd;
CREATE DATABASE cpbd;
use cpbd;
CREATE TABLE userdata(
    uid INT PRIMARY KEY AUTO_INCREMENT,
    uname VARCHAR(100),
    uemail VARCHAR(88) UNIQUE,
    upassword VARCHAR(50),
    urank int(2) null,
    username varchar(50) UNIQUE,
    udefaultTheme BOOLEAN NOT NULL
);
CREATE TABLE profilePictures(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    uimage varchar(255) null,
    dateload datetime default CURRENT_TIMESTAMP,
    uidUserFK INT NOT NULL,
    FOREIGN KEY(uidUserFK) REFERENCES userdata(uid)
);
CREATE TABLE history(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    description longtext,
    uidUserFK INT NOT NULL,
    dateIn datetime default CURRENT_TIMESTAMP,
    FOREIGN KEY(uidUserFK) REFERENCES userdata(uid)
);
CREATE TABLE pictures(
    cod INT PRIMARY KEY AUTO_INCREMENT,
    path varchar(255) not null,
    description longtext,
    uidUserFK INT NULL,
    dateload datetime default CURRENT_TIMESTAMP,
    FOREIGN KEY(uidUserFK) REFERENCES userdata(uid)
);

ALTER TABLE `userdata` ADD `utoken` VARCHAR(32) NOT NULL AFTER `uid`;