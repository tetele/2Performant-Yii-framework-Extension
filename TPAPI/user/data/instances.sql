CREATE TABLE IF NOT EXISTS `instances` (
  `id` int(11) NOT NULL auto_increment,
  `token` char(20) NOT NULL,
  `secret` char(40) NOT NULL,
  `network` varchar(255) NOT NULL,
  `public_token` char(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `token` (`token`,`network`),
  UNIQUE KEY `public_token` (`public_token`,`network`)
);
