
---------------------------------
--CREATE DATABASE pdo4you;
---------------------------------
IF OBJECT_ID('users') IS NOT NULL DROP TABLE users;
CREATE TABLE users (
	id INT NOT NULL IDENTITY,
	firstname VARCHAR(20) NOT NULL,
	lastname VARCHAR(20) NOT NULL,
	mail VARCHAR(30) NULL,
	PRIMARY KEY (id)
);

INSERT INTO users (firstname, lastname, mail) VALUES
('Giovanni', 'Ramos', 'pdo4you@gmail.com');

CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1;


IF OBJECT_ID('books') IS NOT NULL DROP TABLE books;
CREATE TABLE books (
	id INT NOT NULL,
	title VARCHAR(50) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'Nam pharetra nisi eu leo consectetur semper.', 'Giovanni Ramos', NULL);

CREATE SEQUENCE books_id_seq START WITH 1 INCREMENT BY 1;


---------------------------------
--CREATE DATABASE bookstore;
---------------------------------
DROP TABLE IF EXISTS books;
CREATE TABLE books (
	id INT NOT NULL,
	title VARCHAR(50) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'SQL Server Developer Edition 2012', 'Microsoft', NULL);

CREATE SEQUENCE books_id_seq START WITH 1 INCREMENT BY 1;