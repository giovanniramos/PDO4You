
CREATE TABLE IF NOT EXISTS `users` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(50) NOT NULL,
	`lastname` varchar(50) NOT NULL,
	`mail` varchar(100) NOT NULL,
	`status` tinyint(4) NOT NULL default 1,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

INSERT INTO `users` VALUES
(1, 'Giovanni', 'Ramos', 'email@gmail.com', 1);
