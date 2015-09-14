CREATE TABLE IF NOT EXISTS `collections` (
  `collections_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collections_title` varbinary(255) NOT NULL,
  `collections_user` varbinary(255) NOT NULL,
  `collections_date` varbinary(255) NOT NULL,
  `collections_metatitle` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaname` varbinary(255) NOT NULL DEFAULT '',
  `collections_metayear` varbinary(255) NOT NULL DEFAULT '',
  `collections_metapages` varbinary(255) NOT NULL DEFAULT '',
  `collections_metanumbering` varbinary(255) NOT NULL DEFAULT '',
  `collections_metacategory` varbinary(255) NOT NULL DEFAULT '',
  `collections_metapenner` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaproduced` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaproducer` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaid` varbinary(255) NOT NULL DEFAULT '',
  `collections_metanotes` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`collections_id`),
  UNIQUE KEY `collections_title` (`collections_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;
