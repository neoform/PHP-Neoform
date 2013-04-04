
CREATE TABLE `auth` (
  `hash` binary(40) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `expires_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  KEY `expires` (`expires_on`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `locale` (
  `iso2` char(2) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`iso2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

LOCK TABLES `locale` WRITE;
INSERT INTO `locale` VALUES ('en','English'),('fr','Fran√ßais');
UNLOCK TABLES;


CREATE TABLE `locale_key` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `body` varchar(255) NOT NULL,
  `locale` char(2) NOT NULL,
  `namespace_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `locale` (`locale`),
  KEY `word` (`body`),
  KEY `namespace_id` (`namespace_id`),
  CONSTRAINT `locale_key_ibfk_locale` FOREIGN KEY (`locale`) REFERENCES `locale` (`iso2`) ON UPDATE CASCADE,
  CONSTRAINT `locale_key_ibfk_namespace` FOREIGN KEY (`namespace_id`) REFERENCES `locale_namespace` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `locale_key_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_id` int(10) unsigned NOT NULL,
  `body` varchar(255) NOT NULL,
  `locale` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`,`key_id`),
  KEY `word` (`body`),
  KEY `key_id` (`key_id`),
  CONSTRAINT `locale_key_message_ibfk_key` FOREIGN KEY (`key_id`) REFERENCES `locale_key` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `locale_key_message_ibfk_locale` FOREIGN KEY (`locale`) REFERENCES `locale` (`iso2`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `locale_namespace` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

LOCK TABLES `locale_namespace` WRITE;
INSERT INTO `locale_namespace` VALUES (1,'main');
UNLOCK TABLES;


CREATE TABLE `permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


CREATE TABLE `site` (
  `id` smallint(5) unsigned NOT NULL,
  `name` char(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

LOCK TABLES `site` WRITE;
INSERT INTO `site` VALUES (1,'main');
UNLOCK TABLES;


CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varbinary(255) NOT NULL,
  `password_hashmethod` tinyint(3) unsigned NOT NULL,
  `password_cost` int(10) unsigned NOT NULL,
  `password_salt` varbinary(40) NOT NULL,
  `status_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_email` (`email`),
  KEY `hash_method` (`password_hashmethod`),
  KEY `status` (`status_id`),
  CONSTRAINT `user_ibfk_hashmethod` FOREIGN KEY (`password_hashmethod`) REFERENCES `user_hashmethod` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_ibfk_status` FOREIGN KEY (`status_id`) REFERENCES `user_status` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `user_date` (
  `user_id` int(10) unsigned NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `email_verified_on` datetime DEFAULT NULL,
  `password_updated_on` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_date_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `user_hashmethod` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

LOCK TABLES `user_hashmethod` WRITE;
INSERT INTO `user_hashmethod` VALUES (1,'whirlpool');
UNLOCK TABLES;


CREATE TABLE `user_lostpassword` (
  `hash` char(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `posted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hash`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `posted_on` (`posted_on`),
  CONSTRAINT `user_lostpassword_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `user_permission` (
  `user_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `user_permission` (`permission_id`),
  CONSTRAINT `user_permission_ibfk_acl_role` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_permission_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `user_site` (
  `user_id` int(10) unsigned NOT NULL,
  `site_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`site_id`,`user_id`),
  KEY `site_id` (`site_id`),
  KEY `user_site_ibfk_user` (`user_id`),
  CONSTRAINT `user_site_ibfk_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_site_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


CREATE TABLE `user_status` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

LOCK TABLES `user_status` WRITE;
INSERT INTO `user_status` VALUES (1,'active');
UNLOCK TABLES;

