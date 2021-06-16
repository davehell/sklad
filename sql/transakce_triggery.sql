drop procedure if exists pridej_mnozstvi //
CREATE PROCEDURE pridej_mnozstvi (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3))
BEGIN 
  UPDATE zbozi SET mnozstvi = mnozstvi + p_mnozstvi WHERE id = p_id_zbozi;
END 
//

drop procedure if exists odeber_mnozstvi //
CREATE PROCEDURE odeber_mnozstvi (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN 
  UPDATE zbozi SET mnozstvi = mnozstvi - p_mnozstvi WHERE id = p_id_zbozi;
END 
//

DROP PROCEDURE IF EXISTS count_set_prumerna //
CREATE PROCEDURE count_set_prumerna (p_id_zbozi INT) 
BEGIN
  DECLARE v_prum_cena DECIMAL(11,2);
  
  SELECT sum(mnozstvi*cena_MJ)/NULLIF(sum(mnozstvi),0) INTO v_prum_cena
  FROM transakce as T join doklady as D on T.id_dokladu = D.id
  WHERE id_zbozi=p_id_zbozi 
    AND D.skupina in ('Nákup','Inventura','Výroba','Kooperace');
    
  UPDATE zbozi SET prum_cena=v_prum_cena WHERE id=p_id_zbozi;  
  
END
//

DROP PROCEDURE IF EXISTS odpisy_vyroba //
CREATE PROCEDURE odpisy_vyroba(p_id_zbozi INT, p_mnozstvi DECIMAL(11,3), p_id_vyroby INT)
BEGIN
  DECLARE souc INT;
  DECLARE mnoz DECIMAL(11,3); 
  DECLARE l_last_souc INT DEFAULT 0;
  DECLARE cur1 CURSOR FOR SELECT soucastka, mnozstvi FROM sestavy  WHERE celek = p_id_zbozi;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_souc=1;
  
  OPEN cur1;
  
  souc_loop:LOOP
    FETCH cur1 INTO souc, mnoz;
    IF l_last_souc THEN
      LEAVE souc_loop;
    END IF;
    CALL odeber_mnozstvi(souc, mnoz*p_mnozstvi);
    IF  p_id_vyroby > 0 THEN
      INSERT INTO vyroba_odpisy(id,id_vyroby,id_zbozi, mnozstvi)
      VALUES (0,p_id_vyroby,souc, mnoz*p_mnozstvi);
    END IF;
  END LOOP souc_loop; 
  
  CLOSE cur1;  
   
END
//

DROP FUNCTION IF EXISTS suma_cen_materialu //
CREATE FUNCTION suma_cen_materialu(p_id_zbozi INT) 
 RETURNS DECIMAL(11,2) READS SQL DATA
BEGIN
  DECLARE cena_matr DECIMAL(11,2);

  SELECT sum(mnozstvi*pc.prum_cena) INTO cena_matr from sestavy as S join 
            (SELECT Z.id as id, nazev, c_vykresu, cena_prace, prum_cena
                                        FROM zbozi AS Z
                                        JOIN sestavy AS S
                                        GROUP BY Z.id) as pc on S.soucastka = pc.id
            WHERE S.celek = p_id_zbozi
            GROUP BY S.celek;
  
  RETURN cena_matr;
END
//

DROP PROCEDURE IF EXISTS vyroba_cenaMJ //
CREATE PROCEDURE vyroba_cenaMJ(p_id INT)
BEGIN
  UPDATE transakce SET cenaMJ = suma_cen_materialu(p_id) WHERE id = p_id;
END 
//


DROP PROCEDURE IF EXISTS vrat_odpisy_vyroba //
CREATE PROCEDURE vrat_odpisy_vyroba(p_id_zbozi INT, p_mnozstvi DECIMAL(11,3), p_id_vyroby INT)
BEGIN
  DECLARE souc INT;
  DECLARE mnoz DECIMAL(11,3); 
  DECLARE l_last_souc INT DEFAULT 0;
  DECLARE cur1 CURSOR FOR SELECT soucastka, mnozstvi FROM sestavy  WHERE celek = p_id_zbozi;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_souc=1;
  
  OPEN cur1;
  
  souc_loop:LOOP
    FETCH cur1 INTO souc, mnoz;
    IF l_last_souc THEN
      LEAVE souc_loop;
    END IF;
    CALL pridej_mnozstvi(souc, mnoz*p_mnozstvi);
    IF  p_id_vyroby > 0 THEN
      DELETE FROM vyroba_odpisy WHERE id_vyroby = p_id_vyroby;
    END IF;
  END LOOP souc_loop; 
  
  CLOSE cur1;  
   
END
//


drop procedure if exists vloz_rezervace //
CREATE PROCEDURE vloz_rezervace (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);
END 
//

drop procedure if exists vloz_prodej //
CREATE PROCEDURE vloz_prodej (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);
END 
//

drop procedure if exists vloz_nakup //
CREATE PROCEDURE vloz_nakup (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL count_set_prumerna(p_id_zbozi);
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);  
END 
//

drop procedure if exists vloz_inventura //
CREATE PROCEDURE vloz_inventura (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL count_set_prumerna(p_id_zbozi);
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);  
END 
//

drop procedure if exists vloz_kooperace //
CREATE PROCEDURE vloz_kooperace (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL count_set_prumerna(p_id_zbozi);
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);
  CALL odpisy_vyroba(p_id_zbozi, p_mnozstvi, -1);  
END 
//

drop procedure if exists vloz_vyroba //
CREATE PROCEDURE vloz_vyroba (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3), p_id_vyroby INT )
BEGIN
  CALL count_set_prumerna(p_id_zbozi);
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);
  CALL odpisy_vyroba(p_id_zbozi, p_mnozstvi, p_id_vyroby);  
END 
//

drop procedure if exists vloz_zmetkovani //
CREATE PROCEDURE vloz_zmetkovani (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);  
END 
//



drop procedure if exists smaz_rezervace //
CREATE PROCEDURE smaz_rezervace (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);
END 
//

drop procedure if exists smaz_prodej //
CREATE PROCEDURE smaz_prodej (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);
END 
//

drop procedure if exists smaz_nakup //
CREATE PROCEDURE smaz_nakup (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);  
  CALL count_set_prumerna(p_id_zbozi);
END 
//

drop procedure if exists smaz_inventura //
CREATE PROCEDURE smaz_inventura (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);  
  CALL count_set_prumerna(p_id_zbozi);
END 
//

drop procedure if exists smaz_kooperace //
CREATE PROCEDURE smaz_kooperace (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);
  CALL vrat_odpisy_vyroba(p_id_zbozi, p_mnozstvi, -1);
  CALL count_set_prumerna(p_id_zbozi);  
END 
//

drop procedure if exists smaz_vyroba //
CREATE PROCEDURE smaz_vyroba (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3), p_id_vyroby INT )
BEGIN
  CALL odeber_mnozstvi(p_id_zbozi, p_mnozstvi);
  CALL vrat_odpisy_vyroba(p_id_zbozi, p_mnozstvi, p_id_vyroby);
  CALL count_set_prumerna(p_id_zbozi);  
END 
//

drop procedure if exists smaz_zmetkovani //
CREATE PROCEDURE smaz_zmetkovani (p_id_zbozi INT, p_mnozstvi DECIMAL(11,3) )
BEGIN
  CALL pridej_mnozstvi(p_id_zbozi, p_mnozstvi);  
END 
//


drop trigger if exists bi_transakce//
CREATE TRIGGER bi_transakce
BEFORE INSERT ON transakce
FOR EACH ROW
BEGIN
  DECLARE t_skupina varchar(30);
  DECLARE pom varchar(20);
  
  SELECT skupina INTO t_skupina 
  FROM doklady as d
  WHERE id = new.id_dokladu;
  
  IF t_skupina = 'Výroba' THEN
    IF new.cena_MJ <= 0 THEN
      SET new.cena_MJ = suma_cen_materialu(new.id_zbozi);
    END IF;
  END IF;   
END
//

drop trigger if exists ai_transakce//
CREATE TRIGGER ai_transakce
AFTER INSERT ON transakce 
FOR EACH ROW
BEGIN
  DECLARE t_skupina varchar(30);
  DECLARE pom varchar(20);
  
  SELECT skupina INTO t_skupina 
  FROM doklady as d
  WHERE id = new.id_dokladu;
                                   
  IF t_skupina = 'Prodej' THEN           
    CALL vloz_prodej(new.id_zbozi, new.mnozstvi);
  ELSEIF t_skupina = 'Rezervace' THEN
    CALL vloz_rezervace(new.id_zbozi, new.mnozstvi);
  ELSEIF t_skupina = 'Nákup' THEN
    CALL vloz_nakup(new.id_zbozi, new.mnozstvi);
  ELSEIF t_skupina = 'Výroba' THEN
    CALL vloz_vyroba(new.id_zbozi, new.mnozstvi, new.id);
  ELSEIF t_skupina = 'Inventura' THEN
    CALL vloz_inventura(new.id_zbozi, new.mnozstvi);
  ELSEIF t_skupina = 'Zmetkování' THEN
    CALL vloz_zmetkovani(new.id_zbozi, new.mnozstvi);
  ELSEIF t_skupina = 'Kooperace' THEN
    CALL vloz_kooperace(new.id_zbozi, new.mnozstvi);
  END IF;
END //

drop trigger if exists ad_transakce//
CREATE TRIGGER ad_transakce
AFTER DELETE ON transakce 
FOR EACH ROW
BEGIN
  DECLARE t_skupina varchar(30);
  DECLARE pom varchar(20);
  
  SELECT skupina INTO t_skupina 
  FROM doklady as d
  WHERE id = old.id_dokladu;
                                   
  IF t_skupina = 'Prodej' THEN           
    CALL smaz_prodej(old.id_zbozi, old.mnozstvi);
  ELSEIF t_skupina = 'Rezervace' THEN
    CALL smaz_rezervace(old.id_zbozi, old.mnozstvi);
  ELSEIF t_skupina = 'Nákup' THEN
    CALL smaz_nakup(old.id_zbozi, old.mnozstvi);
  ELSEIF t_skupina = 'Výroba' THEN
    CALL smaz_vyroba(old.id_zbozi, old.mnozstvi, old.id);
  ELSEIF t_skupina = 'Inventura' THEN
    CALL smaz_inventura(old.id_zbozi, old.mnozstvi);
  ELSEIF t_skupina = 'Zmetkování' THEN
    CALL smaz_zmetkovani(old.id_zbozi, old.mnozstvi);
  ELSEIF t_skupina = 'Kooperace' THEN
    CALL smaz_kooperace(old.id_zbozi, old.mnozstvi);
  END IF;
END;

//