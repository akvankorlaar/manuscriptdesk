CREATE TABLE IF NOT EXISTS `collations` (
  `collations_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collations_user` varbinary(255) NOT NULL,
  `collations_url` varbinary(500) NOT NULL,
  `collations_date` varbinary(255) NOT NULL, 
  `collations_main_title` varbinary(500) NOT NULL,
  `collations_main_title_lowercase` varbinary(500) NOT NULL,
  `collations_titles_array` varbinary(500) NOT NULL,
  `collations_collatex` TEXT NOT NULL,
  PRIMARY KEY (`collations_id`),
  UNIQUE KEY `collations_url` (`collations_url`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;