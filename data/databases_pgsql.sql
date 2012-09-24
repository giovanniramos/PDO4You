
---------------------------------
--CREATE DATABASE pdo4you;
---------------------------------

DROP TABLE IF EXISTS users;
CREATE TABLE users (
	id SERIAL NOT NULL,
	firstname VARCHAR(20) NOT NULL,
	lastname VARCHAR(20) NOT NULL,
	mail VARCHAR(30) NULL,
	PRIMARY KEY (id)
);

INSERT INTO users (firstname, lastname, mail) VALUES 
('Giovanni', 'Ramos', 'pdo4you@gmail.com');

SELECT setval('public."users_id_seq"', max("id") ) FROM users;


DROP TABLE IF EXISTS books;
CREATE TABLE books (
	id SERIAL NOT NULL,
	title VARCHAR(50) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'Lorem ipsum dolor sit amet.', 'Giovanni Ramos', NULL);

SELECT setval('public."books_id_seq"', max("id") ) FROM books;


---------------------------------
--CREATE DATABASE bookstore;
---------------------------------

DROP TABLE IF EXISTS  books;
CREATE TABLE books (
	id SERIAL NOT NULL,
	title VARCHAR(50) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO books VALUES 
(1, 'PostgreSQL: Up and Running', 'Regina Obe, Leo Hsu', NULL);

SELECT setval('public."books_id_seq"', max("id") ) FROM books;