<?php

//
// katalog textov�ch �et�zc� u�it�ch v informa�n�m syst�mu
//

$texty = array (
   //
   // index - indexov� str�nka
   //
   
//uvodni strana - $soubory["hlavniStranka"]
   'index'               =>      'Evidence skladu',
   'indexNadpis'         =>      'Evidence skladu v. 1.0',
   'uvodniStrana'        =>      '�vodn� strana',
   

//vsude
   'jePrihlasen'         =>      'Jste p�ihl�en jako ',
   'neniPrihlasen'       =>      'Moment�ln� nejste p�ihl�en',
   'upravitProfil'       =>      '�prava profilu',
   'zmenitDetailyTitle'  =>      'Upravit informace o u�ivateli',
   'prihlaseni'          =>      'P�ihl�en�',
   'prihlaseniTitle'     =>      'P�ihl�en� do syst�mu',
   'odhlaseni'           =>      'Odhl�en�',
   'autoOdhlaseni'       =>      'Bylo provedeno automatick� odhl�en� po del�� dob� ne�innosti.',
   'odhlaseniTitle'      =>      'Odhl�en� ze syst�mu',
   'nedostatecnaPrava'   =>      'Pro vstup na po�adovanou str�nku nem�te dostate�n� u�ivatelsk� pr�va.',
   'neprihlasen'         =>      'Pro vstup na po�adovanou str�nku se mus�te p�ihl�sit.',
   'prazdnaDB'           =>      'Datab�ze neobsahuje ��dn� polo�ky.',
   'opravdu'             =>      'Opravdu?',
   'ok'                  =>      'OK',
   'verzeTisk'           =>      'Verze pro tisk',
   'verzeTiskTitle'      =>      'P�iprav� tuto str�nku pro tisk',
   'odpocitavani'        =>      'Automatick� odhl�en� za: ',
   'spatnyModul'         =>      'Nebyl vybr�n modul.',
   'spatnaDB'            =>      'Vybran� modul neobsahuje data z po�adovan�ho roku.',

//moduly
  'modulyNadpis'         =>      'Spr�va modul�',
  'pridaniModulu'        =>      'P�id�n� nov�ho modulu',
  'prehledModulu'        =>      'P�ehled modul�',
  'modul'                =>      'Modul',
  'odebratModulTitle'    =>      'Odstranit tento modul',
  'zadnyModul'           =>      'Nen� ulo�en ��dn� modul',
  'pridatModul'          =>      'P�idat modul',
  'nazevModulu'          =>      'N�zev modulu',
  'prazdnyNazevModulu'   =>      'Mus� b�t zad�n n�zev modulu',
  'nazevModuluNad30'     =>      'N�zev modulu nesm� b�t del�� ne� 30 znak�',
  'deravyNazev'          =>      'N�zev modulu m��e obsahovat znaky bez diakritiky.',
  'neSklad'              =>      'N�zev modulu nesm� b�t "sklad".',
  'novyModulDuplicitni'  =>      'Modul se stejn�m n�zvem u� existuje.',
  'novyModulOK'          =>      'Nov� modul byl �spe�n� p�id�n.',
  'modulOdebratOK'       =>      'Modul byl �spe�n� odebr�n',
  'modulOdebratChyba'    =>      'Vybran� modul neexistuje. Odstran�n� nebylo provedeno.',
  'kontrolaMnozstvi'     =>      'Kontrola mno�stv�',

  

//prihlasovaci obrazovka
   'prihlaseniTitle'     =>      'Evidence skladu - p�ihl�en� u�ivatele',
   'prihlaseniNadpis'    =>      'P�ihl�en� u�ivatele',
   'prihlaseniFormular'  =>      'P�ihla�ovac� �daje',
   'jmenoIHeslo'         =>      'Mus� b�t zad�no jak u�ivatelsk� jm�no tak i heslo.',
   'hesloSpatne'         =>      'U�ivatelsk� jm�no nebo heslo chybn�. P�ihl�en� nebylo provedeno.',
   'zadaniJmena'         =>      'U�ivatelsk� jm�no:',
   'zadaniHesla'         =>      'Heslo:',
   'prihlaseniPotvrzeni' =>      'P�ihl�sit se',
   'zadaniModulu'        =>      'Modul:',


//odhlaseni
  'odhlaseniChyba' =>      'Chyba p�i odhl�en�.',
  'odhlaseniOK'    =>      'Odhl�en� bylo �sp�n�.',

//menu
  'uzivatele'         =>    'U�ivatel�',
  'prehledUzivatelu'  =>    'P�ehled u�ivatel�',
  'novyUzivatel'      =>    'P�idat nov�ho',
  'skladovaKarta'     =>    'Skladov� karty',
  'novaKarta'         =>    'Vytvo�it novou',
  'novaKartaTitle'    =>    'Vytvo�it novou skladovou kartu',
  'upravitKarta'      =>    'Upravit st�vaj�c�',
  'upravitKartaTitle' =>    'Upravit st�vaj�c� skladovou kartu',
  'nahledKarta'       =>    'Prohl�et',
  'nahledKartaTitle'  =>    'Prohl�en� skladov�ch karet',
  'lonskaInventura'   =>    'Inventura podle konce lo�sk�ho roku',
  'potvrzeniInventury'=>    'Opravdu chcete do aktu�ln�ho roku p�ev�st data z lo�sk�ho roku?',
  'inventuraZLonskaOK'=>    'Inventura prob�hla v po��dku',
  'jenJednaInventura' =>    'M��e existovat pouze jeden doklad typu Inventura',
  
  
  
  

  
  
//uzivatele
  'uzivateleNadpis'       =>      'Spr�va u�ivatel�',
  'uzivateleTitle'        =>      'Spr�va u�ivatel�',
  'uzivateleTitleEdit'    =>      'Editace u�ivatele',
  'uzivateleTitleAdd'     =>      'P�id�n� nov�ho u�ivatele',
  'uzivateleTitleProfil'  =>      '�prava profilu',
  'pridatUzivatele'       =>      'P�idat nov�ho u�ivatele',
  'editovatUzivatele'     =>      'Editovat u�ivatele',
  'infoUzivatele'         =>      'Informace o u�ivateli',
  'formNovyUzivatel'      =>      'Nov� u�ivatel',
  'odebratUzivatele'      =>      'Odebrat u�ivatele',
  'admin'                 =>      'administr�tor',
  'zamestnanec'           =>      'zam�stnanec',

//pridani noveho uzivatele
  'pridat'             =>    'P�idat u�ivatele',
  'nespravneParametry' =>    'Nebyly spr�vn� vypln�ny v�echny polo�ky!',
  'uzivJmeno'          =>    'U�ivatelsk� jm�no',
  'uzivHeslo'          =>    'Heslo:',
  'uzivHesloZnovu'     =>    'Heslo znovu (pro kontrolu):',
  'uzivPrava'          =>    'P��stupov� pr�va:',
  'uzivPrava2'         =>    'P��stupov� pr�va',
  'duplicitniLogin'    =>    'U�ivatel se stejn�m jm�nem u� existuje. Zadejte jin� jm�no.',
  'novyUzivatelOK'     =>    'U�ivatel byl �sp�n� p�id�n do datab�ze.',
  'prazdneJmeno'       =>    'Jm�no nesm� b�t pr�zdn�.',
  'deraveJmeno'        =>    'Jm�no sm� obsahovat pouze alfanumerick� znaky.',
  'jmenoNad30'         =>    'Jm�no nesm� b�t del�� ne� 30 znak�.',
  'jmenoPod3'          =>    'Jm�no nesm� b�t krat�� ne� 3 znaky.',
  'formatPassword'     =>    'D�lka hesla mus� b�t 4 a� 10 znak�.',
  'wrongPassword'      =>    'Zadan� hesla nejsou stejn�.',
//editace udaju
  'ulozitZmeny'        =>    'Ulo�it zm�ny',
  'noveUzivJmeno'      =>    'Nov� u�ivatelsk� jm�no (3 - 30 znak�):',
  'editOK'             =>    'Proveden� zm�ny byly �sp�n� ulo�eny.',
//odebrani uzivatele
  'odebraniUzivateleOK'          =>    'U�ivatel byl �sp�n� odebr�n z datab�ze.',
  'odebraniUzivateleChyba'       =>    'Po�adovan� u�ivatel nebyl nalezen. Odstran�n� nebylo provedeno.',
  'odebraniAdmina'       =>    'Administr�torsk� ��et nem��e b�t smaz�n.',
  

//karta
  'kartaTitulek'             =>    'ES - skladov� karta',
//nova karta
  'novaKartaNadpis'       =>      'Nov� skladov� karta',
  'infoKarta'             =>      'Hlavi�ka karty',
  'pridatKartu'           =>      'Vytvo�it kartu',
  'nazev'                 =>      'n�zev/rozm�r',
  'cv'                    =>      '�. v�kresu/ jakost',
  'c_vykresu'             =>      '�. v�kresu/ jakost',
  'mnozstviSkl'           =>      'mno�stv� skladem',
  'skladem'               =>      'skladem',
  'chybi'                 =>      'chyb�',
  'rezervovano'           =>      'rezervov�no',
  'mnozstvi'              =>      'mno�stv�',
  'jednotka'              =>      'm�rn� jednotka',
  'limit'                 =>      'minim�ln� limit',
  'cenaPrace'             =>      'cena pr�ce',
  'kusy'                  =>      'mno�stv�',
  'prazdnyNazev'          =>      'N�zev/Rozm�r v�robku nesm� b�t pr�zdn�.',
  'nazevNad30'            =>      'N�zev/Rozm�r v�robku nesm� b�t del�� ne� 30 znak�.',
  'nazevMaPlus'           =>      'N�zev/Rozm�r v�robku nesm� obsahovat znak "+".',
  'prazdneCV'             =>      '�. v�kresu/Jakost nesm� b�t pr�zdn�.',
  'cvNad30'               =>      '�. v�kresu/Jakost nesm� b�t del�� ne� 30 znak�',
  'prazdneMnozstvi'       =>      'Nebylo zad�no mno�stv�.',
  'spatneMnozstvi'        =>      'Mno�stv� mus� b�t kladn� ��slo.',
  'prazdnyLimit'          =>      'Nebyl zad�n minim�ln� limit.',
  'spatnyLimit'           =>      'Minim�ln� limit mus� b�t kladn� ��slo.',
  'prazdnaCenaPrace'      =>      'Nebyla zad�na cena pr�ce.',
  'spatnaCenaPrace'       =>      'Cena pr�ce mus� b�t kladn� ��slo.',
  'novaKartaOK'           =>      'Skladov� karta byla �sp�n� vytvo�ena.',
  'novaKartaDuplicitni'   =>      'Stejn� skladov� karta u� existuje.',
  'vyrobenoZ'             =>      'Sou��sti v�robku',
  'obrazek'               =>      'Obr�zek zbo��',
  'upload'                =>      'Nahr�t obr�zek',
  'novyObrazekOK'         =>      'Obr�zek zbo�� byl �sp�n� p�id�n.',
  'novyObrazekChyba'      =>      'Chyba p�i nahr�v�n� obr�zku. Obr�zek nebyl p�id�n.',
  'odebratObrazekOK'      =>      'Obr�zek byl �sp�n� odebr�n.',
  'potvrditNazev'         =>      'Potvrdit n�zev',
  'vyberNazev'            =>      'Nebyla vybr�na ��dn� polo�ka.',
  'vyplnitHlavickuKarty'  =>      'vypln�n� hlavi�ky karty',
  'pridaniSoucastek'      =>      'p�id�n� sou��stek, ze kter�ch je v�robek slo�en',
  'vyberteSoucastky'      =>      'Vyberte sou��stky, ze kter�ch je v�robek slo�en.',

  
  
//upravit kartu
  'upravitKartuNadpis'    =>      '�prava skladov� karty',
  'upravitKartu'          =>      'Upravit tuto kartu',
  'pridatSoucastku'       =>      'P�idat sou��stku',
  'spatneKusy'            =>      'Mno�stv� mus� b�t kladn� ��slo.',
  'soucastkaDuplicitni'   =>      'V�robek u� zadanou sou��stku obsahuje.',
  'soucastkaOK'           =>      'Sou��stka byla �sp�n� p�id�na.',
  'vybratKartu'           =>      'V�b�r skladov� karty',
  'vybratJinouKartu'      =>      'Vybrat jinou skladovou kartu',
  'zobrazitKartu'         =>      'Zobrazit kartu',
  'neexistujiciKarta'     =>      'Po�adovan� skladov� karta neexistuje.',
  'neniSlozen'            =>      'Tento v�robek nen� slo�en ze ��dn�ch sou��stek.',
  'odebrat'               =>      'Odebrat',
  'odebratTitle'          =>      'Odebere tuto sou��stku z v�robku',
  'odebratObrazekTitle'   =>      'Odebere ulo�en� obr�zek',
  'soucastkaOdebratOK'    =>      'Sou��stka byla z v�robku �sp�n� odebr�na.',
  'soucastkaOdebratChyba' =>      'V�robek po�adovanou sou��stku neobsahuje.',
  'kartaOdebratOK'        =>      'Skladov� karta byla �sp�n� odebr�na.',
  'kartaOdebratChyba'     =>      'Tato karta nem��e b�t odstran�na. Zbo�� je na n�kter�m dokladu.',
  'odstranitKartu'        =>      'Odstranit tuto skladovou kartu',
  'potvrditNazevSoucastky' =>      'Potvrdit n�zev sou��stky',
  
  
//nahled karty
  'nahledKartaNadpis'        =>      'N�hled skladov� karty',
  'vyberKarty'               =>      'Vyberte skladovou kartu, kterou chcete zobrazit:',
  'potrebnyMaterial'         =>      'Pot�ebn� materi�l',
  'prirustkyUbytky'          =>      'P��r�stky a �bytky',
  'zmenaStavu'               =>      'Zm�na stavu',
  'nejsouTranskace'          =>      'K tomuto zbo�� nejsou ulo�eny ��dn� transakce.',
  'prohlizeniKaret'          =>      'Prohl�en� skladov�ch karet',
  'vypsatSoucastky'          =>      'Vypsat materi�l',
  'vypsatSoucastkyTitle'     =>      'Vyp�e v�echen materi�l, ze kter�ho se toto zbo�� skl�d�.',
  'potrebnyMaterialNadpis'   =>      'Skladov� karta - pot�ebn� materi�l',
  'zpetNaKartu'              =>      'Zp�t na skladovou kartu',
  
  

//stroje
  'stroje'                   =>      'Stroje',
  'strojeTitle'              =>      'Evidence stroj�',
  'prehledStroju'            =>      'P�ehled stroj�',
  'odebratStrojTitle'        =>      'Odstran� tento stroj',
  'pridaniStroje'            =>      'P�id�n� nov�ho stroje',
  'pridatStroj'              =>      'P�idat stroj',
  'novyStrojDuplicitni'      =>      'Tebto stroj u� existuje',
  'novyStrojOK'              =>      'Stroj byl �sp�n� p�id�n.',
  'strojOdebratOK'           =>      'Stroj byl �sp�n� odstran�n.',
  'strojOdebratChyba'        =>      'Vybran� stroj neexistuje. Odstran�n� nebylo provedeno.',
  'zadnyStroj'               =>      'Nen� ulo�en ��dn� stroj.',
  
  
//prodejni ceny
  'prodejniCeny'               =>      'Prodejn� ceny',
  'prodejniCena'               =>      'Prodejn� cena',
  'prodejniCenyTitle'          =>      'Nastaven� kategori� prodejn�ch cen',
  'pridaniKategorie'           =>      'P�id�n� nov� kategorie',
  'upravaKategorie'            =>      '�pravy kategorie',
  'stavajiciKategorie'         =>      'Kategorie prodejn�ch cen',
  'pridatKategorii'            =>      'P�idat kategorii',
  'popis'                      =>      'Popis kategorie',
  'popisNad10'                 =>      'Popis kategorie nesm� b�t del�� ne� 10 znak�.',
  'prazdnyPopis'               =>      'Mus� b�t zad�n popis kategorie.',
  'novaKategorieDuplicitni'    =>      'Tato kategorie u� existuje.',
  'novaKategorieOK'            =>      'Nov� kategorie byla �sp�n� p�id�na.',
  'upravaKategorieOK'          =>      'Zm�ny byly �sp�n� ulo�eny.',
  'ulozit'                     =>      'Ulo�it',
  'novaKategorieChyba'         =>      'Nastala chyba p�i ukl�d�n� zm�n.',
  'zadnaKategorie'             =>      'Nen� ulo�ena ��dn� kategorie.',
  'odebratkategorieTitle'      =>      'Odstran� tuto kategorii prodejn�ch cen',
  'upravitkategorieTitle'      =>      '�pravy t�to kategorie',
  'kategorieOdebratOK'         =>      'Kategorie byla �sp�n� odstran�na.',
  'kategorieOdebratChyba'      =>      'Vybran� kategorie neexistuje. Odstran�n� nebylo provedeno.',
  'prazdnaProdejniCena'        =>      'Mus� b�t vypln�ny v�echny prodejn� ceny.',
  'spatnaProdejniCena'         =>      'Prodejn� ceny mus� b�t kladn� ��sla.',
  'spatnaCenaMJ'               =>      'Cena za MJ mus� b�t kladn� ��sla.',
  
  

//zapis
  'zapis'                   =>      'P��r�stky a �bytky',
  'zapisTitle'              =>      'Z�pis p��r�stk� a �bytk�',
  'novyDoklad'              =>      'Nov� doklad',
  'editDokladTitle'         =>      '�prava st�vaj�c�ho dokladu',
  'novyDokladTitle'         =>      'P�id�n� nov�ho dokladu',
  'zalozeniDokladu'         =>      'Zalo�en� nov�ho dokladu',
  'editDoklad'              =>      'Upravit doklad',
  'zapisTitulek'            =>      'Z�pis p��r�stk� a �bytk�',
  'infoZapis'               =>      'Hlavi�ka dokladu',
  'datum'                   =>      'Datum',
  'cDokladu'                =>      '��slo dokladu',
  'c_dokladu'                =>     '�. dokladu',
  'skupina'                 =>      'Skupina',
  'prodejKomu'              =>      'Prodejn� cena pro',
  'prod_kategorie'          =>      'Odb�ratel',
  'ubytek'                  =>      '�bytek',
  'prirustek'               =>      'P��r�stek',
  'vytvoritDoklad'          =>      'Vytvo�it doklad',
  'transakceVlozit'         =>      'Vlo�it transakci',
  'dokladVlozen'            =>      'Doklad byl �sp�n� vytvo�en.',
  'vyplnitHlavicku'         =>      'vypln�n� informac� o dokladu',
  'pridavaniPolozek'        =>      'p�id�v�n� polo�ek na doklad',
  'typ_vyroby'               =>      'Typ v�roby',
  'typVyroby'               =>      'Typ v�roby',
  
  ///chyby ve formulari
  'prazdneDatum'            =>      'Nebylo zad�no datum.',
  'prazdneCDokladu'         =>      'Nebylo zad�no ��slo dokladu.',
  'prazdnaSkupina'          =>      'Nebyla vybr�na ��dn� skupina.',
  'nespravneDatum'          =>      'Datum nen� ve spr�vn� form�tu den.m�s�c.rok',
  'prazdnaProdejniCena'     =>      'Nebyla vybr�na ��dn� prodejn� cena',
  'NovyDokladDuplicitni'    =>      'Doklad s t�mto ��slem u� v datab�zi je.',
  'prazdnyTypVyroby'        =>      'Nebyl zad�n typ v�roby.',

//dokladTransakce
  'dokladTransakce'         =>      'P�id�n�/editace polo�ek v dokladu',
  'doklad'                  =>      'Doklad',
  'transakceMnozstvi'       =>      'Mno�stv�',
  'pridatPolozku'           =>      'P�idat polo�ku',
  'cenaMJ'                  =>      'Cena za MJ',
  'cena_MJ'                 =>      'Cena/MJ',
  'vlastniCena'             =>      'Vlastn�',
  'posledniCena'            =>      'Posledn�',
  'prumernaCena'            =>      'Pr�m�rn�',
  'cenaKOO'                 =>      'Cena/kooperace',
  'cenaKOOzkr'              =>      'Cena/koop.',
  'maloZbozi'               =>      'Na sklad� je m�lo zbo��',
  'pozadovano'              =>      'po�adov�no',
  'skladem'                 =>      'skladem',
  'chybi'                   =>      'chyb�',
  'nevybranaCenaKOO'        =>      'Nebyla vybr�na cena kooperace',
  'prazdnaVlastniCenaKOO'   =>      'Zvolena vlastn� cena, ale polo�ka je nevypln�na.',
  'nejsouSoucastky'         =>      'Nen� dostatek sou��stek, viz.tabulka.',
  'nakupOK'                 =>      'Polo�ka n�kupu �sp�n� p�id�na do dokladu',
  'prodejOK'                =>      'Polo�ka prodeje �sp�n� p�id�na do dokladu',
  'vyrobaOK'                =>      'Polo�ka v�roby �sp�n� p�id�na do dokladu',
  'rezervaceOK'             =>      'Polo�ka rezervace �sp�n� p�id�na do dokladu',
  'zmetkovaniOK'            =>      'Polo�ka zmetkov�n� �sp�n� p�id�na do dokladu',
  'kooperaceOK'             =>      'Polo�ka kooperace �sp�n� p�id�na do dokladu',
  'inventuraOK'             =>      'Polo�ka �sp�n� naskladn�na',
  'c'                       =>      '�.',
  'cenaKS'                  =>      'cena/ks',
  'cenaVyr'                 =>      'cena v�robku',
  'odstranitDoklad'         =>      'Odstranit tento doklad',
  'tiskDodaciList'          =>      'Tisk dodac�ho listu',
  'dodaciList'              =>      'Dodac� list',
  'zpetNaDoklad'            =>      'Zp�t na doklad',
  'odberatel'               =>      'Odb�ratel',
  'zobrazitDodaciList'      =>      'Vytisknout',
  'odberatelNazev'          =>      'N�zev firmy',
  'odberatelUlice'          =>      'Ulice, �p.',
  'odberatelMesto'          =>      'ps�, m�sto',
  'odberatelIco'            =>      'I�O',
  'odberatelDic'            =>      'DI�',
  'cObjednavky'             =>      '��slo objedn�vky',
  'zpetKHlavicce'           =>      'Zp�t k hlavi�ce dodac�ho listu',


  'zobrazit'               =>      'Zobrazit',
  'skryt'                  =>      'Skr�t',
  ''               =>      '',
  ''               =>      '',

  

  
  
  
  //rezervace
  'zrusRezervaci'           =>      'Zru�it',
  'rezervaceNaProdej'       =>      'P�eve� na prodej',
  'rezervaceSmazana'        =>      'Rezervace �sp�n� zru�ena',
  'chybaMazaniRezervace'    =>      'Chyba p�i ru�en� rezervace',
  'rezervacePrevedena'      =>      'Rezervace �sp�n� p�evedena na prodej.',
  'chybaVlastniCena'        =>      'Nevypln�n� nebo chybn� hodnota vlastn� ceny.',
  
  
//editDoklady
  'najitDoklad'             =>      'Naj�t doklad',
  'hledatDoklad'            =>      'Hledat doklad',
  'najit'                   =>      'Naj�t',
  'id'                      =>      'ID',
  'nevybranaCenaMJ'         =>      'Nebyla vybr�na cena MJ (m�rn� jednotky)',
  'prazdnaVlastniCenaMJ'    =>      'Zvolena vlastn� cena, ale polo�ka je nevypln�na.',
  'neniPrumCena'            =>      'V DB nen� je�t� v ��dn� transakci vkl�dan� polo�ka, je tedy nutn� zadat vlastn� cenu.',
  'ChybaVlozeniTransakce'   =>      'Chyba p�i vkl�d�n� transakce',
  'zrusitRezervaci'         =>      'Zru�it rezervaci',
  'MazaniTransakceOK'       =>      'Polo�ka dokladu �sp�n� smaz�na.',
  'ChybaMazaniTransakce'    =>      'Chyba p�i maz�n� polo�ky dokladu.',

//odebratDoklad
  'ChybaMazaniDokladu'      =>      'Chyba p�i maz�n� dokladu.',
  'neprazdnyDoklad'         =>      'Doklad nesm� obsahovat ��dn� polo�ky.',
  'MazaniDokladuOK'         =>      'Doklad byl �sp�n� odebr�n.',
  
  


// test V�roby
  'testVyroba'              =>      'Test v�roby',
  'testVyrobaTitle'         =>      'ES - test v�roby',
  'lzeVyrobit'              =>      'Zbo�� lze vyrobit v po�adovan�m mno�stv�.',
  'vyrobek'                 =>      'V�robek',
  'casoveVymezeni'          =>      '�asov� vymezen�',
  'neniVyrobek'             =>      'Nejedn� se o v�robek.',

//podlimitni polozky
  'podlimitniPolozky'       =>      'Podlimitn� polo�ky',
  'podlimitniTitle'         =>      'P�ehled polo�ek, kter�ch je na sklad� m�n�, ne� nastaven� minim�ln� limit.',
  'podlimitniNadpis'        =>      'P�ehled',
  'podlimitniTitulek'       =>      'Podlimitn� polo�ky',
  'nicPodLimit'             =>      'Na sklad� nen� ��dn� podlimitn� polo�ka.',


//archiv
  'archiv'               =>      'Archiv',
  'archivOK'             =>      'Po�adovan� rok byl �sp�n� nastaven.',
  'archivChyba'          =>      'Po�adovan� rok se v archivu nenach�z�.',
  'novyArchiv'           =>      'Zalo�en� nov�ho ro�n�ku',
  'rok'                  =>      'rok',
  'zalozitRocnik'        =>      'Zalo�it ro�n�k',
  'spatnyRok'            =>      'Rok nebyl zad�n spr�vn�.',
  'novyArchivOK'         =>      'Nov� ro�n�k byl �sp�n� zalo�en.',
  'novyRokDuplicitni'    =>      'Zadan� ro�n�k u� existuje. Vyberte jin� rok.',

  
///----- tiskove sestavy------
//koeficient
  'koeficienty'         =>       'Koeficienty',
  'editovatKoeficienty' =>       'Upravit koeficienty',
  'nazevKoeficientu'    =>       'Koeficient',
  'hodnotaKoeficientu'  =>       'Hodnota',
  'spatnaHodnota'       =>       'Hodnota mus� b�t zad�na.',
  ''         =>       '',
  ''         =>       '',


// stav skladu
  'stavSkladuTitle'         =>       'Stav skladu',
  'cenovyStavSkladu'        =>       'Cenov� stav skladu',
  'mnozstevniStavSkladu'    =>       'Mno�stevn� stav skladu',
  'zac_c_vykresu'           =>       'skupina',
  'cena_krat_koef'          =>       'Cena * koef',
  'cena_celkem_koef'        =>       'Celkov� cena * koef',
  'rozprac_vyroba'          =>       'Rozpracovan� v�roba',
//prehledy transakci
  'tisk'                    =>       'Tiskov� sestavy',
  'tiskNakup'               =>       'Tiskov� sestavy - N�kupy',
  'tiskProdej'              =>       'Tiskov� sestavy - Prodej',
  'tiskVyroba'              =>       'Tiskov� sestavy - V�roba',
  'tiskRezervace'           =>       'Tiskov� sestavy - Rezervace',
  'Nakup'                   =>       'N�kupy',
  'Vyroba'                  =>       'V�roba',
  'Prodej'                  =>       'Prodej',
  'Rezervace'               =>       'Rezervace',
  'datumOd'                 =>       'Datum od',
  'datumDo'                 =>       'Datum do',
  'spatneParametry'         =>       'Chybne parametry str�nky.',
  'cenaCelkem'              =>       'Cena celkem',
  'cena_celkem'             =>       'cena celkem',
  'cenaMJkratka'            =>       'Cena/MJ',
  'cena_MJ'                 =>       'Cena/MJ',
  'celkem'                  =>       'celkem',
  'mnozstevniStav'          =>       'Mno�stevn� stav skladu',
  'stroje'                  =>       'Stroje',
  'sestavy'                 =>       'Sestavy',
  'novePolozky'             =>       'Vlo�en� nov�ch polo�ek',
  'polozkyVDokladu'         =>       'Polo�ky v dokladu',
  'cena_KOO'                =>       'Cena pr�ce',
  'prace_mnozstvi'          =>       'Pr�ce*mn.',
  
  

//rezervace
  'zrusRezervaci'           =>      'Zru�it',
  'rezervaceNaProdej'       =>      'P�eve� na prodej',
  'rezervaceSmazana'        =>      'Rezervace �sp�n� zru�ena',
  'chybaMazaniRezervace'    =>      'Chyba p�i ru�en� rezervace',
  'rezervacePrevedena'      =>      'Rezervace �sp�n� p�evedena na prodej.',
  'infoDoklad'              =>      'Informace o dokladu',
  
  
  'upravit'                 =>      'Upravit',

  'sentinel'            =>      ''
);

?>
