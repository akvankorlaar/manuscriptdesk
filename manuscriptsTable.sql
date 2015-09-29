CREATE TABLE IF NOT EXISTS `manuscripts` (
  `manuscripts_id` int(10) unsigned NOT NULL,
  `manuscripts_title` varbinary(255) NOT NULL,
  `manuscripts_user` varbinary(255) NOT NULL,
  `manuscripts_url` varbinary(255) NOT NULL,
  `manuscripts_date` varbinary(255) NOT NULL,
  `manuscripts_lowercase_title` varbinary(255) NOT NULL,
  `manuscripts_collection` varbinary(255) NOT NULL,
  `manuscripts_lowercase_collection` varbinary(255) NOT NULL,
  `manuscripts_datesort` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;