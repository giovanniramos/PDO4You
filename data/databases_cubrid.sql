
---------------------------------
--CREATE DATABASE pdo4you;
---------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
	id INT(11) NOT NULL AUTO_INCREMENT,
	firstname VARCHAR(20) NOT NULL,
	lastname VARCHAR(20) NOT NULL,
	mail VARCHAR(30) NULL,
	PRIMARY KEY (id)
);

INSERT INTO users (firstname, lastname, mail) VALUES 
('Giovanni', 'Ramos', 'pdo4you@gmail.com');


DROP TABLE IF EXISTS books;
CREATE TABLE books (
	id INT(11) NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description STRING NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'Donec congue neque eget felis dapibus sollicitudin.', 'Giovanni Ramos', NULL);


---------------------------------
--CREATE DATABASE bookstore;
---------------------------------
DROP TABLE IF EXISTS books;
CREATE TABLE books (
	id INT(11) NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description STRING NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'Cubrid', 'Evelyn Columba Sara', NULL);
