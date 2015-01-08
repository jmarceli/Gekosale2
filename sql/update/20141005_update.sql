DROP TABLE IF EXISTS `dispatchmethodtranslation`;
CREATE TABLE IF NOT EXISTS `dispatchmethodtranslation` (
`iddispatchmethodtranslation` int(10) unsigned NOT NULL,
  `dispatchmethodid` int(10) unsigned NOT NULL,
  `languageid` int(10) unsigned NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

INSERT INTO `dispatchmethodtranslation` (`iddispatchmethodtranslation`, `dispatchmethodid`, `languageid`, `name`) VALUES (1, 15, 1, 'Kurier Standard'), (2, 17, 1, 'Poczta Polska');
ALTER TABLE `dispatchmethodtranslation`
 ADD PRIMARY KEY (`iddispatchmethodtranslation`), ADD UNIQUE KEY `dispatchmethodid_2` (`dispatchmethodid`,`languageid`), ADD KEY `dispatchmethodid` (`dispatchmethodid`), ADD KEY `languageid` (`languageid`);

ALTER TABLE `dispatchmethodtranslation`
MODIFY `iddispatchmethodtranslation` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;

ALTER TABLE `dispatchmethodtranslation`
  ADD CONSTRAINT `dispatchmethodtranslation_ibfk_2` FOREIGN KEY (`languageid`) REFERENCES `language` (`idlanguage`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dispatchmethodtranslation_ibfk_1` FOREIGN KEY (`dispatchmethodid`) REFERENCES `dispatchmethod` (`iddispatchmethod`) ON DELETE CASCADE ON UPDATE CASCADE;

DROP TABLE IF EXISTS `paymentmethodtranslation`;
CREATE TABLE `paymentmethodtranslation` (
`idpaymentmethodtranslation` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `paymentmethodid` int(10) unsigned NOT NULL,
  `languageid` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`idpaymentmethodtranslation`),
  CONSTRAINT `paymentmethodtranslation_ibfk_2` FOREIGN KEY (`languageid`) REFERENCES `language` (`idlanguage`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `paymentmethodtranslation_ibfk_1` FOREIGN KEY (`paymentmethodid`) REFERENCES `paymentmethod` (`idpaymentmethod`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

INSERT INTO `paymentmethodtranslation` (`idpaymentmethodtranslation`, `paymentmethodid`, `languageid`, `name`) VALUES (1, 2, 1, 'platnosci.pl'), (6, 4, 1, 'Przelew bankowy'), (13, 5, 1, 'Płatność za pobraniem'), (14, 6, 1, 'Płatność przy odbiorze'), (15, 8, 1, 'Żagiel'), (16, 11, 1, 'Przelewy24'), (17, 12, 1, 'PayU'), (18, 15, 1, 'Transferuj.pl'), (19, 16, 1, 'Dotpay'), (20, 17, 1, 'PayByNet');
ALTER TABLE `paymentmethodtranslation` ADD UNIQUE (`paymentmethodid`, `languageid`) COMMENT '';
