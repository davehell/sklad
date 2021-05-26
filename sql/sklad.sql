SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;
SET TIME_ZONE = 'SYSTEM';

CREATE TABLE `moduly` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modul` varchar(30) COLLATE latin2_czech_cs NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modul` (`modul`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

INSERT INTO `moduly` VALUES ('1', 'lmr');

CREATE TABLE `uzivatele` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE latin2_czech_cs NOT NULL,
  `heslo` varchar(50) COLLATE latin2_czech_cs NOT NULL,
  `prava` tinyint(4) NOT NULL,
  `id_modulu` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`,`id_modulu`),
  KEY `id_modulu` (`id_modulu`),
  CONSTRAINT `uzivatele_ibfk_1` FOREIGN KEY (`id_modulu`) REFERENCES `moduly` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

INSERT INTO `uzivatele` VALUES ('1', 'admin', '211a066003eaea06511874be3918417b75069ea7', '9', '1');
