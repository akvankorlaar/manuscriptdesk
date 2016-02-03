CREATE TABLE IF NOT EXISTS `alphabetnumbers` (
  `a` int(10) unsigned NOT NULL DEFAULT '0',
  `b` int(10) unsigned NOT NULL DEFAULT '0',
  `c` int(10) unsigned NOT NULL DEFAULT '0',
  `d` int(10) unsigned NOT NULL DEFAULT '0',
  `e` int(10) unsigned NOT NULL DEFAULT '0',
  `f` int(10) unsigned NOT NULL DEFAULT '0',
  `g` int(10) unsigned NOT NULL DEFAULT '0',
  `h` int(10) unsigned NOT NULL DEFAULT '0',
  `i` int(10) unsigned NOT NULL DEFAULT '0',
  `j` int(10) unsigned NOT NULL DEFAULT '0',
  `k` int(10) unsigned NOT NULL DEFAULT '0',
  `l` int(10) unsigned NOT NULL DEFAULT '0',
  `m` int(10) unsigned NOT NULL DEFAULT '0',
  `n` int(10) unsigned NOT NULL DEFAULT '0',
  `o` int(10) unsigned NOT NULL DEFAULT '0',
  `p` int(10) unsigned NOT NULL DEFAULT '0',
  `q` int(10) unsigned NOT NULL DEFAULT '0',
  `r` int(10) unsigned NOT NULL DEFAULT '0',
  `s` int(10) unsigned NOT NULL DEFAULT '0',
  `t` int(10) unsigned NOT NULL DEFAULT '0',
  `u` int(10) unsigned NOT NULL DEFAULT '0',
  `v` int(10) unsigned NOT NULL DEFAULT '0',
  `w` int(10) unsigned NOT NULL DEFAULT '0',
  `x` int(10) unsigned NOT NULL DEFAULT '0',
  `y` int(10) unsigned NOT NULL DEFAULT '0',
  `z` int(10) unsigned NOT NULL DEFAULT '0',
  `zero` int(10) unsigned NOT NULL DEFAULT '0',
  `one` int(10) unsigned NOT NULL DEFAULT '0',
  `two` int(10) unsigned NOT NULL DEFAULT '0',
  `three` int(10) unsigned NOT NULL DEFAULT '0',
  `four` int(10) unsigned NOT NULL DEFAULT '0',
  `five` int(10) unsigned NOT NULL DEFAULT '0',
  `six` int(10) unsigned NOT NULL DEFAULT '0',
  `seven` int(10) unsigned NOT NULL DEFAULT '0',
  `eight` int(10) unsigned NOT NULL DEFAULT '0',
  `nine` int(10) unsigned NOT NULL DEFAULT '0',
  `alphabetnumbers_context` varbinary(255) NOT NULL, 
  UNIQUE KEY `alphabetnumbers_context` (`alphabetnumbers_context`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

INSERT into `alphabetnumbers` (alphabetnumbers_context) VALUES ('SingleManuscriptPages'); 
INSERT into `alphabetnumbers` (alphabetnumbers_context) VALUES ('AllCollections'); 
INSERT into `alphabetnumbers` (alphabetnumbers_context) VALUES ('AllCollations'); 

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

CREATE TABLE IF NOT EXISTS `collections` (
  `collections_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collections_title` varbinary(255) NOT NULL,
  `collections_title_lowercase` varbinary(255) NOT NULL,
  `collections_user` varbinary(255) NOT NULL,
  `collections_date` varbinary(255) NOT NULL,
  `collections_metatitle` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaauthor` varbinary(255) NOT NULL DEFAULT '',
  `collections_metayear` varbinary(255) NOT NULL DEFAULT '',
  `collections_metapages` varbinary(255) NOT NULL DEFAULT '',
  `collections_metacategory` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaproduced` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaproducer` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaeditors` varbinary(255) NOT NULL DEFAULT '',
  `collections_metajournal` varbinary(255) NOT NULL DEFAULT '',
  `collections_metajournalnumber` varbinary(255) NOT NULL DEFAULT '',
  `collections_metatranslators` varbinary(255) NOT NULL DEFAULT '',
  `collections_metawebsource` varbinary(255) NOT NULL DEFAULT '',
  `collections_metaid` varbinary(255) NOT NULL DEFAULT '',
  `collections_metanotes` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`collections_id`),
  UNIQUE KEY `collections_title` (`collections_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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

CREATE TABLE IF NOT EXISTS `tempcollate` (
  `tempcollate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tempcollate_time` bigint(20) unsigned NOT NULL,
  `tempcollate_user` varbinary(255) NOT NULL,
  `tempcollate_titles_array` varbinary(500) NOT NULL,
  `tempcollate_new_url` varbinary(500) NOT NULL,
  `tempcollate_main_title` varbinary(500) NOT NULL,
  `tempcollate_main_title_lowercase` varbinary(500) NOT NULL,
  `tempcollate_collatex` TEXT NOT NULL,
  PRIMARY KEY (`tempcollate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE IF NOT EXISTS `tempstylometricanalysis` (
  `tempstylometricanalysis_time` bigint(20) unsigned NOT NULL,
  `tempstylometricanalysis_user` varbinary(255) NOT NULL,
  `tempstylometricanalysis_fulloutputpath1` varbinary(500) NOT NULL,
  `tempstylometricanalysis_fulloutputpath2` varbinary(500) NOT NULL,
  `tempstylometricanalysis_json_config_array` TEXT NOT NULL,
  `tempstylometricanalysis_new_page_url` varbinary(500) NOT NULL,
  `tempstylometricanalysis_date` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE IF NOT EXISTS `stylometricanalysis` (
  `stylometricanalysis_time` bigint(20) unsigned NOT NULL,
  `stylometricanalysis_user` varbinary(255) NOT NULL,
  `stylometricanalysis_fulloutputpath1` varbinary(500) NOT NULL,
  `stylometricanalysis_fulloutputpath2` varbinary(500) NOT NULL,
  `stylometricanalysis_json_config_array` TEXT NOT NULL,
  `stylometricanalysis_new_page_url` varbinary(500) NOT NULL,
  `stylometricanalysis_date` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;