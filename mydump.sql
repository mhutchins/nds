-- MySQL dump 10.11
--
-- Host: localhost    Database: nds
-- ------------------------------------------------------
-- Server version	5.0.51b-community-nt

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `card`
--

DROP TABLE IF EXISTS `card`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `card` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) default NULL,
  `name` varchar(45) default NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `card_userid_fk` (`userid`),
  CONSTRAINT `card_userid_fk` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `card`
--

LOCK TABLES `card` WRITE;
/*!40000 ALTER TABLE `card` DISABLE KEYS */;
INSERT INTO `card` VALUES (1,1,'Martins card 1',900),(2,1,'sophie',900),(3,4,'siobhann\'s card',900),(5,3,'Fi Card',1739),(6,5,'Gary',900),(7,2,'Sam',1739),(8,6,'Sophie',900),(10,8,'Card one',900),(11,7,'Card one',900),(12,2,'nds',1739),(13,9,'Card one',900),(14,10,'Rhys',900),(15,10,'Charlie',900);
/*!40000 ALTER TABLE `card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_rom`
--

DROP TABLE IF EXISTS `card_rom`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `card_rom` (
  `cardid` int(11) NOT NULL,
  `romid` int(11) NOT NULL,
  PRIMARY KEY  (`cardid`,`romid`),
  KEY `cardrom_cardid_fk` (`cardid`),
  KEY `cardrom_romid_fk` (`romid`),
  CONSTRAINT `cardrom_cardid_fk` FOREIGN KEY (`cardid`) REFERENCES `card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `cardrom_romid_fk` FOREIGN KEY (`romid`) REFERENCES `rom` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `card_rom`
--

LOCK TABLES `card_rom` WRITE;
/*!40000 ALTER TABLE `card_rom` DISABLE KEYS */;
INSERT INTO `card_rom` VALUES (1,580),(1,2262),(2,52),(2,55),(2,79),(2,84),(2,105),(2,111),(2,218),(2,224),(2,225),(2,226),(2,229),(2,284),(2,347),(2,352),(2,389),(2,396),(2,440),(2,491),(2,492),(2,569),(2,572),(2,580),(2,589),(2,597),(2,665),(2,684),(2,703),(2,713),(2,769),(2,810),(2,813),(2,819),(2,827),(2,833),(2,835),(2,842),(2,843),(2,865),(2,901),(2,930),(2,942),(2,948),(2,1054),(2,1125),(2,1128),(2,1130),(2,1202),(2,1215),(2,1291),(2,1378),(2,1388),(2,1423),(2,1428),(2,1449),(2,1452),(2,1455),(2,1459),(2,1465),(2,1468),(2,1481),(2,1487),(2,1492),(2,1516),(2,1536),(2,1544),(2,1553),(2,1555),(2,1557),(2,1586),(2,1609),(2,1629),(2,1632),(2,1634),(2,1673),(2,1691),(2,1693),(2,1703),(2,1714),(2,1717),(2,1740),(2,1753),(2,1754),(2,1785),(2,1812),(2,1821),(2,1830),(2,1836),(2,1848),(2,1862),(2,1896),(2,1910),(2,1964),(2,1966),(2,1968),(2,2005),(2,2013),(2,2020),(2,2024),(2,2038),(2,2039),(2,2043),(2,2059),(2,2071),(2,2081),(2,2091),(2,2101),(2,2109),(2,2112),(2,2114),(2,2121),(2,2126),(2,2186),(2,2194),(2,2214),(2,2221),(2,2261),(2,2262),(2,2267),(2,2276),(2,2289),(2,2295),(3,368),(3,413),(3,479),(3,491),(3,719),(3,1368),(3,1831),(3,1909),(3,1924),(3,1977),(3,2011),(3,2186),(5,413),(5,480),(5,481),(5,580),(5,631),(5,654),(5,810),(5,811),(5,833),(5,965),(5,1062),(5,1124),(5,1131),(5,1177),(5,1255),(5,1364),(5,1388),(5,1408),(5,1445),(5,1459),(5,1465),(5,1530),(5,1541),(5,1575),(5,1581),(5,1622),(5,1705),(5,1736),(5,1752),(5,1796),(5,1804),(5,1827),(5,1873),(5,1879),(5,2021),(5,2043),(5,2060),(6,22),(6,87),(6,297),(6,327),(6,413),(6,479),(6,810),(6,811),(6,1130),(6,1194),(6,1202),(6,1468),(6,1547),(6,1575),(6,1577),(6,1634),(6,1964),(6,1968),(6,1997),(6,2039),(6,2229),(7,153),(8,396),(8,457),(8,459),(8,491),(8,694),(8,695),(8,867),(8,868),(8,910),(8,965),(8,1083),(8,1142),(8,1177),(8,1343),(8,1459),(8,1605),(8,1873),(8,1940),(8,1966),(8,1980),(8,2043),(8,2066),(8,2101),(8,2124),(8,2224),(8,2235),(8,2276),(12,51),(12,457),(12,479),(12,573),(12,604),(12,701),(12,718),(12,978),(12,1015),(12,1016),(12,1051),(12,1083),(12,1115),(12,1185),(12,1612),(12,1618),(12,1623),(12,1674),(12,1675),(12,1676),(12,1704),(12,1969),(14,22),(14,123),(14,201),(14,466),(14,516),(14,769),(14,811),(14,1012),(14,1202),(14,1423),(14,1514),(14,1546),(14,1633),(14,1714),(14,1830),(14,1872),(14,2035),(14,2269);
/*!40000 ALTER TABLE `card_rom` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-07-16 14:43:38
