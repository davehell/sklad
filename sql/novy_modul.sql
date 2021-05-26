SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET max_allowed_packet = 16777216, GLOBAL max_allowed_packet = 16777216;

CREATE TABLE `doklady` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_dokladu` varchar(30) COLLATE latin2_czech_cs NOT NULL,
  `skupina` enum('Prodej','Rezervace','Zmetkování','Nákup','Kooperace','Výroba','Inventura') COLLATE latin2_czech_cs NOT NULL,
  `datum` int(11) NOT NULL,
  `prod_kategorie` int(11) DEFAULT NULL,
  `typ_vyroby` enum('Montá¾', 'Montá¾ vozíkù', 'Montá¾ blokù', 'Obrobna bloky', 'Obrobna','Svaøovna') COLLATE latin2_czech_cs DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `c_dokladu` (`c_dokladu`),
  KEY `prod_kategorie` (`prod_kategorie`),
  CONSTRAINT `doklady_ibfk_4` FOREIGN KEY (`prod_kategorie`) REFERENCES `prodejni_kategorie` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

CREATE TABLE `koeficienty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hodnota` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;
--INSERT INTO `koeficienty` VALUES ('1', '1.00');

CREATE TABLE `prodejni_ceny` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zbozi` int(11) NOT NULL,
  `id_kategorie` int(11) NOT NULL,
  `cena` decimal(11,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_zbozi_2` (`id_zbozi`,`id_kategorie`),
  KEY `id_zbozi` (`id_zbozi`),
  KEY `id_kategorie` (`id_kategorie`),
  CONSTRAINT `prodejni_ceny_ibfk_2` FOREIGN KEY (`id_kategorie`) REFERENCES `prodejni_kategorie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prodejni_ceny_ibfk_3` FOREIGN KEY (`id_zbozi`) REFERENCES `zbozi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;


CREATE TABLE `prodejni_kategorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `popis` varchar(30) NOT NULL,
  `nazev` varchar(70) DEFAULT NULL,
  `ulice` varchar(70) DEFAULT NULL,
  `mesto` varchar(70) DEFAULT NULL,
  `ico` varchar(15) DEFAULT NULL,
  `dic` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `popis` (`popis`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2;

CREATE TABLE `sestavy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `celek` int(11) NOT NULL,
  `soucastka` int(11) NOT NULL,
  `mnozstvi` decimal(11,3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `celek` (`celek`,`soucastka`),
  KEY `soucastka` (`soucastka`),
  CONSTRAINT `sestavy_ibfk_1` FOREIGN KEY (`celek`) REFERENCES `zbozi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sestavy_ibfk_2` FOREIGN KEY (`soucastka`) REFERENCES `zbozi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

CREATE TABLE `stroje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zbozi` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_zbozi` (`id_zbozi`),
  CONSTRAINT `stroje_ibfk_1` FOREIGN KEY (`id_zbozi`) REFERENCES `zbozi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

CREATE TABLE `transakce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zbozi` int(11) NOT NULL,
  `id_dokladu` int(11) NOT NULL,
  `mnozstvi` decimal(11,3) NOT NULL,
  `cena_MJ` decimal(11,2) DEFAULT NULL,
  `cena_KOO` decimal(11,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_zbozi` (`id_zbozi`),
  KEY `id_dokladu` (`id_dokladu`),
  CONSTRAINT `transakce_ibfk_7` FOREIGN KEY (`id_dokladu`) REFERENCES `doklady` (`id`),
  CONSTRAINT `transakce_ibfk_8` FOREIGN KEY (`id_zbozi`) REFERENCES `zbozi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

CREATE TABLE `vyroba_odpisy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_vyroby` int(11) NOT NULL,
  `id_zbozi` int(11) NOT NULL,
  `mnozstvi` decimal(11,3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

CREATE TABLE `zbozi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazev` varchar(50) COLLATE latin2_czech_cs NOT NULL,
  `c_vykresu` varchar(50) COLLATE latin2_czech_cs NOT NULL,
  `zac_c_vykresu` varchar(50) COLLATE latin2_czech_cs NOT NULL,
  `jednotka` enum('kus','kilogram','metr') COLLATE latin2_czech_cs NOT NULL,
  `min_limit` int(11) NOT NULL,
  `cena_prace` decimal(11,2) NOT NULL,
  `prum_cena` decimal(11,2) DEFAULT NULL,
  `mnozstvi` decimal(11,3) DEFAULT NULL,
  `typ` tinyint(4) DEFAULT NULL,
  `obrazek` varchar(50) COLLATE latin2_czech_cs DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nazev` (`nazev`,`c_vykresu`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs;

