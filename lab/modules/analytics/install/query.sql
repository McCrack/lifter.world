
CREATE TABLE IF NOT EXISTS `gb_user-analytics`(
	day INT UNSIGNED,
	views MEDIUMINT(6) UNSIGNED DEFAULT 1, 
	reviews MEDIUMINT(6) UNSIGNED DEFAULT 1, 
	PRIMARY KEY(day)
)ENGINE=InnoDB COMMENT='analytics';

CREATE TABLE IF NOT EXISTS gb_pages(
	PageID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	views MEDIUMINT(6) UNSIGNED DEFAULT 0,
	time INT UNSIGNED DEFAULT 0,
	votess INT(5) UNSIGNED NOT NULL DEFAULT 1,
	rating INT(5) UNSIGNED NOT NULL DEFAULT 4,
	type ENUM('material','category','showcase','post','story') NOT NULL DEFAULT 'material',
	created INT UNSIGNED NOT NULL DEFAULT 1,
	modified INT UNSIGNED NOT NULL DEFAULT 1,
	customizer VARCHAR(1024) NOT NULL DEFAULT '{}',
	INDEX time(created),
	PRIMARY KEY(PageID)
)ENGINE=InnoDB COMMENT='analytics';