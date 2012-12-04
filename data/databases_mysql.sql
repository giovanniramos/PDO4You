
---------------------------------
--CREATE DATABASE pdo4you;
---------------------------------
DROP TABLE IF EXISTS pdo4you.users;
CREATE TABLE pdo4you.users (
	id INT(11) NOT NULL AUTO_INCREMENT,
	firstname VARCHAR(20) NOT NULL,
	lastname VARCHAR(20) NOT NULL,
	mail VARCHAR(30) NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO pdo4you.users (firstname, lastname, mail) VALUES 
('Giovanni', 'Ramos', 'pdo4you@gmail.com');


DROP TABLE IF EXISTS pdo4you.books;
CREATE TABLE pdo4you.books (
	id INT(11) NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO pdo4you.books VALUES 
(1, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'Giovanni Ramos', NULL);


---------------------------------
--CREATE DATABASE bookstore;
---------------------------------
DROP TABLE IF EXISTS bookstore.books;
CREATE TABLE bookstore.books (
	id INT(11) NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
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
