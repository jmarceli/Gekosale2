DROP TABLE IF EXISTS `productwarranty`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productwarranty` (
  `idproductwarranty` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productid` int(10) unsigned NOT NULL,
  `warrantyid` int(10) unsigned NOT NULL,
  `adddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idproductwarranty`),
  KEY `FK_productwarranty_warrantyid` (`warrantyid`),
  KEY `FK_productwarranty_productid` (`productid`),
  CONSTRAINT `FK_productwarranty_warrantyid` FOREIGN KEY (`warrantyid`) REFERENCES `file` (`idfile`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_productwarranty_productid` FOREIGN KEY (`productid`) REFERENCES `product` (`idproduct`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `translation` (`name`) VALUES ('TXT_WARRANTY');
INSERT INTO `translationdata` (`translation`, `translationid`, `languageid`) VALUES ('Gwarancja', (SELECT idtranslation FROM `translation` WHERE name = 'TXT_WARRANTY' LIMIT 1), 1);



