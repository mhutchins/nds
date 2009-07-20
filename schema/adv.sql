CREATE TABLE `adv` (
  `romid` varchar(64) DEFAULT NULL,
  `imagenumber` varchar(64) NOT NULL DEFAULT '',
  `releasenumber` varchar(64) NOT NULL DEFAULT '',
  `title` varchar(77) NOT NULL DEFAULT '',
  `romsize` varchar(64) NOT NULL DEFAULT '',
  `location` varchar(8) NOT NULL DEFAULT '',
  `romcrc` longtext NOT NULL,
  `genre` varchar(9) NOT NULL DEFAULT '',
  `version` datetime DEFAULT NULL,
  `wifi` varchar(256) DEFAULT NULL,
  `duplicateid` varchar(18) DEFAULT NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

