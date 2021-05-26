-- plusy  a minusy

SELECT plusy.nazev, plusy.c_vykresu, (plusy.mnozstvi-minusy.mnozstvi) as mnozstvi, (plusy.cena_MJ-minusy.cena_MJ) as cena_MJ
FROM (SELECT Z.id, nazev, c_vykresu, sum(TD.mnozstvi) as mnozstvi, sum(TD.cena_MJ) as cena_MJ
      FROM zbozi as Z 
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_MJ, D.skupina 
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Nákup','Výroba','Kooperace','Inventura')) 
      as TD on Z.id = TD.id_zbozi
      WHERE Z.id in (SELECT id_zbozi
                     FROM stroje)
      GROUP BY Z.id) as plusy
      JOIN
      
      (SELECT Z.id, nazev, c_vykresu, sum(TD.mnozstvi) as mnozstvi, sum(TD.cena_MJ) as cena_MJ
      FROM zbozi as Z 
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_MJ, D.skupina 
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Prodej','Zmetkování')) 
      as TD on Z.id = TD.id_zbozi
      WHERE Z.id in (SELECT id_zbozi
                     FROM stroje)
      GROUP BY Z.id) as minusy ON plusy.id = minusy.id
      
      
      
