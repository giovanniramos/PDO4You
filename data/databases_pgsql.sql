
---------------------------------
--CREATE DATABASE pdo4you;
---------------------------------
DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA public;
CREATE TABLE public.users (
	id SERIAL NOT NULL,
	firstname VARCHAR(20) NOT NULL,
	lastname VARCHAR(20) NOT NULL,
	mail VARCHAR(30) NULL,
	PRIMARY KEY (id)
);

INSERT INTO public.users (firstname, lastname, mail) VALUES 
('Giovanni', 'Ramos', 'pdo4you@gmail.com');

SELECT setval('public.users_id_seq', max("id") ) FROM public.users;

CREATE TABLE public.books (
	id SERIAL NOT NULL,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO public.books VALUES 
(1, 'Etiam tristique justo sit amet neque suscipit eget pretium lectus tempor.', 'Giovanni Ramos', NULL);

SELECT setval('public.books_id_seq', max("id") ) FROM public.books;


---------------------------------
--CREATE DATABASE bookstore;
---------------------------------
DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA public;
CREATE TABLE public.books (
	id SERIAL NOT NULL,
	title VARCHAR(100) NOT NULL,
	author VARCHAR(50) NOT NULL,
	description TEXT NULL,
	PRIMARY KEY (id)
);

INSERT INTO public.books VALUES 
(1, 'PostgreSQL: Up and Running', 'Regina Obe, Leo Hsu', NULL);

SELECT setval('public.books_id_seq', max("id") ) FROM public.books;