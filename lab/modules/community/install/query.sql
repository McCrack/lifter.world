CREATE TABLE IF NOT EXISTS gb_community(
	CommunityID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	Name VARCHAR(64) NOT NULL DEFAULT '',
	Phone INT(10) NOT NULL DEFAULT '0',
	Email VARCHAR(32) NOT NULL DEFAULT 'n/a',
	reputation INT(4) DEFAULT 1,
	options VARCHAR(512) NOT NULL DEFAULT '{}',
	PRIMARY KEY(CommunityID),
	UNIQUE(Phone)
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO gb_community (Name, Email, Phone) VALUES ('Viktor Sibibel', 'v.sibibel@gmail.com', 965216303)