
CREATE DATABASE IF NOT EXISTS pdo4you;
USE pdo4you;

DROP TABLE IF EXISTS  pdo4you.users;
CREATE TABLE pdo4you.users (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`firstname` VARCHAR(20) NOT NULL,
	`lastname` VARCHAR(20) NOT NULL,
	`mail` VARCHAR(30) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO pdo4you.users (firstname, lastname, mail) VALUES
('Giovanni', 'Ramos', 'pdo4you@gmail.com');

DROP TABLE IF EXISTS  pdo4you.books;
CREATE TABLE pdo4you.books (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(50) NOT NULL,
	`author` VARCHAR(30) NOT NULL,
	`description` TINYTEXT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO pdo4you.books VALUES
(1, 'Learning PHP, MySQL, and JavaScript', 'Robin Nixon', NULL);




CREATE DATABASE IF NOT EXISTS bookstore;
USE bookstore;

DROP TABLE IF EXISTS  bookstore.books;
CREATE TABLE bookstore.books (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(50) NOT NULL,
	`author` VARCHAR(30) NOT NULL,
	`description` TINYTEXT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO bookstore.books VALUES
(1, 'Head First PHP & MySQL', 'Lynn Beighley, Michael Morrison', NULL),
(2, 'Head First JavaScript ', 'Michael Morrison', NULL),
(3, 'Head First Ajax', 'Rebecca M. Riordan', NULL),
(4, 'Head First jQuery', 'Ryan Benedetti', NULL),
(5, 'Head First Java, 2nd Edition', 'Kathy Sierra', NULL),
(6, 'Head First Python', 'Paul Barry', NULL),
(7, 'Head First Networking', 'Al Anderson', NULL),
(8, 'Head First Web Design', 'Ethan Watrall', NULL);
