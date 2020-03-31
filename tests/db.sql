--
--  Simple management of users' permissions.
-- -----------------------------------------

-- ---- create roles table:
CREATE TABLE `roles` (
    `roleId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(40) NOT NULL,
    `parent` int(11) NOT NULL,
    PRIMARY KEY (`roleId`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---- insert data to roles table:
INSERT INTO `roles` (`roleId`, `name`, `parent`)
VALUES (null, 'guest',	 0), (null, 'member', 1), (null, 'admin',  2);
