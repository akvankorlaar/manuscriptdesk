CREATE TABLE IF NOT EXISTS `collections` (
  `collections_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collections_title` varbinary(255) NOT NULL,
  `collections_user` varbinary(255) NOT NULL,
  `collections_date` varbinary(255) NOT NULL,
  PRIMARY KEY (`collations_id`),
  UNIQUE KEY `collections_title` (`collections_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;
