CREATE TABLE IF NOT EXISTS `networks` (
  `id` int(11) NOT NULL auto_increment,
  `network` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `network` (`network`)
);