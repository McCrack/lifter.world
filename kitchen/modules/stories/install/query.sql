CREATE TABLE IF NOT EXISTS gb_stories(
	PageID INT UNSIGNED NOT NULL,
	published ENUM('Not published','Published') NOT NULL DEFAULT 'Not published',
	header VARCHAR(256) NOT NULL DEFAULT '', 
	subheader VARCHAR(1024) NOT NULL DEFAULT '', 
	landscape VARCHAR(256),
	portrait VARCHAR(256),
	Ads ENUM('YES','NO') NOT NULL DEFAULT 'YES',
	PRIMARY KEY(PageID),
	FOREIGN KEY (PageID) REFERENCES gb_pages(PageID)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;