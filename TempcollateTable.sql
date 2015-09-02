CREATE TABLE IF NOT EXISTS `tempcollate` (
  `tempcollate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tempcollate_user` varbinary(255) NOT NULL,
  `tempcollate_titles_array` varbinary(500) NOT NULL,
  `tempcollate_new_url` varbinary(500) NOT NULL,
  `tempcollate_main_title` varbinary(500) NOT NULL,
  `tempcollate_main_title_lowercase` varbinary(500) NOT NULL,
  `tempcollate_time` varbinary(255) NOT NULL,
  `tempcollate_collatex` TEXT NOT NULL,
  PRIMARY KEY (`tempcollate_id`),
  UNIQUE KEY `tempcollate_time` (`tempcollate_time`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;