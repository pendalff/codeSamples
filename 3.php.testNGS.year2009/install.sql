DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS roles_users;

DROP TABLE IF EXISTS tp_posts;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS forums;

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` char(50) NOT NULL DEFAULT '',
  `email` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `roles` (
  `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `roles_users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`user_id`,`role_id`),
  KEY `fk_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `forums` (
  `forum_id` int(11) unsigned NOT NULL  AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `sort` int(11) unsigned NOT NULL,
  PRIMARY KEY (`forum_id`),
  KEY (`forum_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
  
CREATE TABLE `posts` (
  `post_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `forum_id` int(11) unsigned NULL default NULL,
  `is_topic` tinyint(3) NOT NULL DEFAULT '0',
  `post` varchar(150)  NOT NULL,
  `content` text ,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`,`user_id`),
  KEY `post_id`  (`post_id`),
  KEY `user_id`  (`user_id`),
  KEY `forum_id` (`forum_id`),
  CONSTRAINT `forum_id` FOREIGN KEY (`forum_id`) REFERENCES `forums` (`forum_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
  
CREATE TABLE `tp_posts` (
  `ancestor` bigint(20) unsigned NOT NULL,
  `descendant` bigint(20) unsigned NOT NULL,
  `depth` tinyint(3) NOT NULL DEFAULT '0',
  `weight` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ancestor`,`descendant`),
  KEY `ancestor` (`ancestor`),
  KEY `descendant` (`descendant`),
  CONSTRAINT `ancestor` FOREIGN KEY (`ancestor`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `descendant` FOREIGN KEY (`descendant`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin',     '098f6bcd4621d373cade4e832627b4f6', 'sem1@mylivepage.net'),
(2, 'moderator', '098f6bcd4621d373cade4e832627b4f6', 'sem2@mylivepage.net'),
(3, 'user', 	 '098f6bcd4621d373cade4e832627b4f6', 'sem3@mylivepage.net') ,
(4, 'ban', 	 '098f6bcd4621d373cade4e832627b4f6', 'sem4@mylivepage.net'); 

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'login', 'Зарегистрированный пользователь'),
(2, 'admin', 'Администратор'),
(3, 'moder', 'Модератор');

INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 3),
(3, 1);

INSERT INTO `forums` VALUES ('1', 'Первый форум', 'краткое описание', '1');
INSERT INTO `forums` VALUES ('2', 'Второй форум', 'краткое описание', '2');

INSERT INTO `posts` (`post_id`, `user_id`,  `forum_id`,`is_topic`, `post`, `content`, `ts`) VALUES
(1, 1, 1,1, 'топик 1 форума 1', 'сообщение', '2011-04-13 12:09:52'),
(2, 2, 1,1, 'топик 2 форума 1', 'сообщение', '2011-04-13 12:09:52'),
(3, 3, 2,1, 'топик 1 форума 2', 'сообщение', '2011-04-13 12:09:52'),
(4, 1, 1,1, 'топик 3 форума 1', 'сообщение', '2011-04-13 12:09:52'),
(5, 2, 1,1, 'топик 4 форума 1', 'сообщение', '2011-04-13 12:09:52'),
(6, 3, 2,1, 'топик 2 форума 2', 'сообщение', '2011-04-13 12:09:52'),
(8, 1, 1,0, 'test', 'Nam at ante id enim condimentum rutrum.', '2011-04-13 13:10:20');

INSERT INTO `tp_posts` (`ancestor`, `descendant`, `depth`, `weight`) VALUES
(1, 1, 0, 1),
(1, 8, 1, 1),
(2, 2, 0, 1),
(3, 3, 0, 1),
(4, 4, 0, 1),
(5, 5, 0, 1),
(6, 6, 0, 1),
(8, 8, 0, 0);