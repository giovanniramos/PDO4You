
CREATE DATABASE IF NOT EXISTS pdo4you;
USE pdo4you;

DROP TABLE IF EXISTS  pdo4you.users;
CREATE TABLE IF NOT EXISTS pdo4you.users (
	id int(11) NOT NULL PRIMARY KEY auto_increment,
	firstname varchar(20) NOT NULL,
	lastname varchar(20) NOT NULL,
	mail varchar(30) NOT NULL,
	datecreate timestamp NOT NULL default CURRENT_TIMESTAMP,
	status char(1) NOT NULL default '1'
) ENGINE=InnoDB;

INSERT INTO pdo4you.users (firstname, lastname, mail) VALUES
('Giovanni', 'Ramos', 'pdo4you@gmail.com');

DROP TABLE IF EXISTS  pdo4you.books;
CREATE TABLE pdo4you.books (
	id int(11) NOT NULL PRIMARY KEY auto_increment,
	title varchar(50) NOT NULL,
	author varchar(30) NOT NULL
) ENGINE=InnoDB;

INSERT INTO pdo4you.books VALUES
(1, 'Learning PHP, MySQL, and JavaScript', 'Robin Nixon');




CREATE DATABASE IF NOT EXISTS bookstore;
USE bookstore;

DROP TABLE IF EXISTS  bookstore.books;
CREATE TABLE bookstore.books (
	id int(11) NOT NULL PRIMARY KEY auto_increment,
	title varchar(50) NOT NULL,
	author varchar(30) NOT NULL
) ENGINE=InnoDB;

INSERT INTO bookstore.books VALUES
(1, 'Head First PHP & MySQL', 'Lynn Beighley, Michael Morrison'),
(2, 'Head First JavaScript ', 'Michael Morrison'),
(3, 'Head First Ajax', 'Rebecca M. Riordan'),
(4, 'Head First jQuery', 'Ryan Benedetti'),
(5, 'Head First Java, 2nd Edition', 'Kathy Sierra'),
(6, 'Head First Python', 'Paul Barry'),
(7, 'Head First Networking', 'Al Anderson'),
(8, 'Head First Web Design', 'Ethan Watrall');
