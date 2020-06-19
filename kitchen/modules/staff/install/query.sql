CREATE TABLE IF NOT EXISTS gb_staff(
	`UserID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`CommunityID` INT UNSIGNED,
	`Login` VARCHAR(24) NOT NULL,
	`Passwd` CHAR(32) NOT NULL,
	`Group` ENUM('admin', 'developer', 'editor', 'manager', 'author', 'designer', 'partner', 'surfer') DEFAULT 'author',
	`Departament` VARCHAR(24) NOT NULL DEFAULT '',
	`settings` VARCHAR(4096) NOT NULL DEFAULT '{}',
	PRIMARY KEY(UserID),
	UNIQUE(login),
	FOREIGN KEY (CommunityID) REFERENCES gb_community(CommunityID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO gb_staff (`CommunityID`, `Login`, `Passwd`, `Group`, `Departament` ) VALUES (1, 'McCrack', MD5('gfhjdjpbr'), 'developer', 'C-BBLe');

CREATE TABLE IF NOT EXISTS gb_sessions(
	CommunityID INT UNSIGNED NOT NULL,
	Token CHAR(32) NOT NULL,
	Variables VARCHAR(1024) NOT NULL DEFAULT '{"IPs":[]}',
	EntryON TIMESTAMP,

	PRIMARY KEY(CommunityID),
	FOREIGN KEY (CommunityID) REFERENCES gb_staff(CommunityID)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;