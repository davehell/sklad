<?php

/**
 * iTransakceFunkce.php
 * [pomocne funkce pro praci s transakcemi]
 */  

//include 'iSkladObecne.php';

/**
 *@param id     id zbozi
 *@return       prumernou cenu ID
 */ 
function getPrumernaCena($id)
{

  $SRBD=spojeniSRBD();
  $dotaz = "SELECT prum_cena FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  While ($data = mysqli_fetch_array($vysledek)) {
    $result = $data['prum_cena'];
  } 
  return $result;
}//getPrumernaCena()

/**
 * zjisti posledni cenu daneho zbozi a typu
 * @param id    identifikace zbozi
 * @param typ   typ transakce (KOO/MJ)
 * @return      posledni cenu
 */ 
function getPosledniCena($id, $typ)
{

  if($typ == 'MJ')
    $skupina = "('Nákup','Inventura')";
  else
    $skupina = "('Kooperace')";
    
  $SRBD=spojeniSRBD();
  $dotaz = " SELECT *
             FROM transakce AS T
             JOIN doklady AS D ON T.id_dokladu = D.id
             WHERE id_zbozi = '$id'
             AND D.skupina in $skupina
             GROUP BY T.id
             ORDER BY datum DESC , T.id DESC
             LIMIT 1";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  $data = mysqli_fetch_array($vysledek);
  if($typ == 'MJ')
    return $data['cena_MJ'];
  else
    return $data['cena_KOO'];
}//getPosledniCena()

/**
 * spocita prumernou cenu
 */ 
function countPrumerna($id)
{
  //echo 'bbb'.$id;
  $SRBD=spojeniSRBD();
  $dotaz =
  $vysledek = mysqli_query("SELECT sum(mnozstvi*cena_MJ)/sum(mnozstvi) as prumer 
                           FROM transakce as T join doklady as D on T.id_dokladu = D.id
                           WHERE id_zbozi='$id' AND D.skupina in ('Nákup','Inventura','Výroba','Kooperace')");
  $data = mysqli_fetch_array($vysledek);
  
  return $data['prumer'];

}//countPrumerna()

/**
 * spocita a nastavi prumernou cenu u id 
 * @param id      identifikace zbozi  
 */  
function countAndSetPrumerna($id)
{
  $prumernaCena = countPrumerna($id);
  if (!isset($SRBD)) $SRBD=spojeniSRBD();
  $vysledek = mysqli_query("UPDATE zbozi SET prum_cena='$prumernaCena' WHERE id='$id'"); 
     
}//countAndSetPrumerna()

/**
 * odecte ze skladu soucatky daneho celku (pouziva se pri vyrobe)
 * @param id          id celku
 * @param mnozstvi    mnozstvi
 */  
function akceSoucastkyCelku($typ_akce,$id,$mnozstvi,$id_vyroby='')
{
  $SRBD=spojeniSRBD();
  $dotaz = "SELECT soucastka, mnozstvi FROM sestavy WHERE celek=$id";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  while ($data = mysqli_fetch_array($vysledek)) {
     $id_souc = $data['soucastka'];
     $souc_mnozstvi = $data['mnozstvi'];
     $celk_mnozstvi = ($mnozstvi*$souc_mnozstvi);
     
     if($typ_akce=='pridej')
     {  zmenaMnozstviZbozi('pridej', $id_souc, ($mnozstvi*$souc_mnozstvi));
        if($id_vyroby!='')
        {
          $dotaz = "DELETE FROM vyroba_odpisy WHERE id_vyroby = '$id_vyroby'";
          $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
        }
     }
     elseif($typ_akce=='odeber')
     {  
        zmenaMnozstviZbozi('odeber', $id_souc, ($mnozstvi*$souc_mnozstvi));
        if($id_vyroby!='')
        {
          $dotaz = "INSERT INTO vyroba_odpisy(id,id_vyroby,id_zbozi, mnozstvi) VALUES (0,'$id_vyroby','$id_souc','$celk_mnozstvi')";
          $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
        }
     }
     else 
       return false;
  }
  return true;
}//odectiSoucastkyCelku()

/**
 *  prida do skladu k danemu id pozadovane mnozstvi
 *  @param id         id zbozi
 *  @param mnozstvi   vkladane mnozstvi
 */ 
function pridejDoSkladu($id,$mnozstvi)
{
  $SRBD=spojeniSRBD();
  
  $vysledek = mysqli_query("SELECT mnozstvi FROM zbozi WHERE id='$id'");
  $data = mysqli_fetch_array($vysledek);
  $noveMnozstvi = $data["mnozstvi"] + $mnozstvi;   
  
  $vysledek3 = mysqli_query("UPDATE zbozi SET mnozstvi=$noveMnozstvi WHERE id=$id") or Die(mysqli_error());

}//pridejDoSkladu()

/**
 * zjistuje zda lze vyrobit dany vyrobek
 * @param id_zbozi     id vyrobku z tabulky zbozi
 * @param mnozstvi     pozadovane mnozstvi na vyrobu
 * @param echo_offset  nepovinny parametr pro offset pri kontrolnim vypisu
 * @return             true pokud lze jinak false 
 */  
function lzeVyrobit2($id_zbozi, $mnozstvi, $max_zanor)
{
     global $reserved;
     global $nedostatek;
     $lze = true;
     
     // zjistim si nazev zbozi 
     $SRBD = spojeniSRBD();
     $dotaz = 'SELECT nazev FROM zbozi WHERE id='.$id_zbozi;
     $vysledek = mysqli_query($SRBD, $dotaz);  
     $record=mysqli_fetch_array($vysledek);
     
     
     // z jakych primych casti se vyrobek sklada ???
     $SRBD = spojeniSRBD();
     $dotaz = 'SELECT celek, soucastka, mnozstvi FROM sestavy WHERE celek='.$id_zbozi;
     $vysledek = mysqli_query($SRBD, $dotaz);
     // pocet soucastek poslouzi k tomu zda lze soucastku vyrobit nebo ne
     // tedy pokud neni dostatek na sklade NEJDE ani vyrobit :-)
     $pocet_soucastek = mysqli_num_rows($vysledek);      
     
     if($pocet_soucastek<1)
     {
       $lze=true;  //pozor bylo zmeneno z false, to bylo vyuzito asi v rekurzvnim volani
     }
     else { // pruchod jednotlivymi castmi celku, a jejich test zda jich je na skladu dost
      while ($record=mysqli_fetch_array($vysledek)):
     
        //kontrola poctu kazde casti na sklade
        $SRBD = spojeniSRBD();
        $dotaz = 'SELECT mnozstvi,typ FROM zbozi WHERE id='.$record['soucastka'];
        $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
        $record2 = mysqli_fetch_array($vysledek2);
         
        if (!isset($reserved[$record['soucastka']]))
            $reserved[$record['soucastka']]=0;
        $rozdil = $record2['mnozstvi']-$reserved[$record['soucastka']]- $record['mnozstvi']*$mnozstvi;
        
        // test poctu na sklade a pozadovaneho mnozstvi
        if($rozdil>=0)
        { // vse ok, jen do pole dam vedet o tom, ze z mnozstvi ubiram
          $reserved[$record['soucastka']] += $record['mnozstvi'];
        }
        else 
        // pokud chybi testuju zda je to material a chci ho koupit nebo vyrobek a lze ho vyrobit
        // pricitam pocet nedostatkoveho zbozi do prislusne polozky pole
        // zaroven pricitam zbytek daneho zbozi na sklade do pouziteho zbozi
        // -neuspech je rovnez pokud jsem v poslednim zanoreni ( =1 )
        { 
          $reserved[$record['soucastka']] += $record2['mnozstvi'];
          if ($record2['typ'] == 'material' || $pocet_soucastek<1 || $max_zanor==1)
          {
              $lze = false;
              if (!isset($nedostatek[$record['soucastka']]))
                $nedostatek[$record['soucastka']]=0;
              $nedostatek[$record['soucastka']] += (-$rozdil);
          }
          else {
            if(lzeVyrobit2($record['soucastka'],-$rozdil,$max_zanor-1))
              $lze=true;
            else 
              $lze=false;
            }
        }
     endwhile;
     }
     return $lze;
 
}//lzeVyrobit2()


   
//$mojePole = array($reserved, $nedostatek);
/**
 * tiskne tabulku nedostatkoveho zbozi
 */ 
function printNedostatekTable($nedostatek)
{
   //global $nedostatek;
   global $texty;
   global $soubory;
   $result = '';
   
   
   $result.= '
<table>
  <tr>
    <th>'.$texty['nazev'].'</th>
    <th>'.$texty['c_vykresu'].'</th>
    <th>'.$texty['pozadovano'].'</th>
    <th>'.$texty['skladem'].'</th>
    <th>'.$texty['chybi'].'</th>
    <th>'.$texty['rezervovano'].'</th>
  </tr>';
   $sudy = false;
   while(list($i, $prvek) = each($nedostatek))
   {
      if($prvek!=0)
      {
        $SRBD = spojeniSRBD();
        $dotaz = 'SELECT id, nazev, c_vykresu, mnozstvi FROM zbozi WHERE id='.$i;
        $vysledek = mysqli_query($SRBD, $dotaz);
        $data = mysqli_fetch_array($vysledek);
        $pozadovano = $data['mnozstvi']+$prvek;
        $dotaz = "SELECT celek FROM sestavy WHERE celek=$i";
        $vysledek = mysqli_query($SRBD, $dotaz);
        if(mysqli_num_rows($vysledek)!=0)
          $celek = true;
        else $celek = false;
        
        $dotaz = "SELECT sum(T.mnozstvi) as rezervovano
                  FROM transakce as T join doklady as D on T.id_dokladu=D.id 
                  WHERE D.skupina = 'Rezervace'  AND T.id_zbozi='$i' 
                  GROUP BY T.id_zbozi";
        $vysledek2 = mysqli_query($SRBD, $dotaz);
        $data2 = mysqli_fetch_array($vysledek2);
        if(mysqli_num_rows($vysledek2)==0)
          $rezervovano = '0';
        else 
          $rezervovano = $data2['rezervovano'];
        
        $result.= '<tr';
        if($sudy) $result .= ' class="sudyRadek"';
  $result.='>
    <td>';
    if($celek)
     $result.='<a href="'.$soubory['testVyroba'].'?nazev='.$data['nazev'].'&cv='.$data['c_vykresu'].'&mnozstvi='.$prvek.'">'.$data['nazev'].'</a>';
    else 
     $result.= $data['nazev'];
    $result.='</td>
    <td>'.$data['c_vykresu'].'</td>
    <td>'.$pozadovano.'</td>
    <td>'.$data['mnozstvi'].'</td>
    <td>'.$prvek.'</td>
    <td>'.$rezervovano.'</td>
  </tr>';
      $sudy = !$sudy;
      }
   }
$result.= '
</table>';

return $result;
}//printNedostatek


function LzeVyrobitXXX($id_zbozi, $mnozstvi, $max_zanor)
{

   $reserved = array();
   $nedostatek = array();
   $mojePole = array($reserved,$nedostatek);

   lzeVyrobit2($id_zbozi, $mnozstvi, $max_zanor);

   return $mojePole;
}

/**
 * kontrola validity formularovych dat
 * @return TRUE pokud je vse v poradku jinak FALSE a nastavenu SESSION s chybou 
 */ 
function checkFormData($skupina)
{
global $texty;
global $datum;
$korektniParametry = true;

// nazev
if (empty($_SESSION['promenneFormulare']["nazev"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnyNazev'];
  $korektniParametry = false;
}

// cislo vykresu
if (empty($_SESSION['promenneFormulare']["cv"])) { // cislo vykresu nesmi byt prazdne
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneCV'];
  $korektniParametry = false;
}

// mnozstvi
if (empty($_SESSION['promenneFormulare']["mnozstvi"])) { // cislo vykresu nesmi byt prazdne
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneMnozstvi'];
  $korektniParametry = false;
}

///// TEST JEN PRO URCITE SKUPINY
if($skupina=="Nákup")
{
    if (empty($_SESSION['promenneFormulare']["cenaMJ"])) { 
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['nevybranaCenaMJ'];
      $korektniParametry = false;
    }
    else if(($_SESSION['promenneFormulare']["cenaMJ"]=="vlastni")
             && empty($_SESSION['promenneFormulare']["cenaMJvlastni"]))
    {
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['prazdnaVlastniCenaMJ'];
      $korektniParametry = false;
    }   
}
elseif(skupina=="Prodej")
{
   //zadne dalsi kontroly, az pocty zbozi - to se dela jinde (akceProSkupinu)
}
//je to skoro stejne jako Nákup===PREKONTROLOVAT
elseif($skupina=="Kooperace")
{
    if (empty($_SESSION['promenneFormulare']["cenaKOO"])) { 
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['nevybranaCenaKOO'];
      $korektniParametry = false;
    }
    else if(($_SESSION['promenneFormulare']["cenaKOO"]=="vlastni")
             && empty($_SESSION['promenneFormulare']["cenaKOOvlastni"]))
    {
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['prazdnaVlastniCenaKOO'];
      $korektniParametry = false;
    }   
}


//cenaMJ
if (!empty($_SESSION['promenneFormulare']["cenaMJvlastni"])) {
  if ((!ereg("[0-9]", $_SESSION['promenneFormulare']["cenaMJvlastni"])) || // cena muze obsahovat pouze cislice
      ($_SESSION['promenneFormulare']["cenaMJvlastni"] < 0)) { //a nesmi byt zaporna
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnaCenaMJ'];
    $korektniParametry = false;
  }
}


return $korektniParametry;
}//chcekFormData()


/**
 * pocita sumu cen materialu daneho celku 
 * @param id_celku      identifikace celku zbozi 
 * @return sumu prumernych cen vseho materialu ze ktereho je slozen celek dany ID 
 */ 
function sumaCenMaterialu($id_celku)
{
  //pripad ze se bude ukladat cena_matr
/*  $dotaz = "SELECT sum( cena_matr ) AS cena_matr, cena_prace
                            FROM zbozi AS Z
                            JOIN sestavy AS S
                            WHERE Z.id='$id_celku'
                            GROUP BY Z.id";
  */
  
  //pripad kdy se vse vypocitava
  $dotaz = "SELECT sum(mnozstvi*pc.prum_cena) as cena_matr from sestavy as S join 
            (SELECT Z.id as id, nazev, c_vykresu, cena_prace, prum_cena
                                        FROM zbozi AS Z
                                        JOIN sestavy AS S
                                        GROUP BY Z.id) as pc on S.soucastka = pc.id
            WHERE S.celek = '$id_celku'
            GROUP BY S.celek";
  
  //echo $dotaz;
  $SRBD=spojeniSRBD();
  $vysledek = mysqli_query($SRBD, $dotaz);
  $data = mysqli_fetch_array($vysledek);
  
  return $data['cena_matr'];

}//sumaCenMaterialuCelku()

/**
 *  zjisti cenu prace
 *  @param    id     identifikace zbozi
 *  @return   cenu prace daneho zbozi
 */  
function cenaPrace($id)
{
  $SRBD=spojeniSRBD();
  $dotaz = "SELECT cena_prace FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz);
  $data = mysqli_fetch_array($vysledek);
  
  return $data['cena_prace'];
}//cenaPrace()

/**
 * podle typu akce provede zmenu mnozstvi ve skladu
 * @param typ_akce       pridej/odeber
 * @param id             identifikace zbozi
 * @param mnozstvi       mnozstvi zmeny zbozi 
 */  
function zmenaMnozstviZbozi($typ_akce, $id, $mnozstvi)
{
   if (!isset($SRBD)) $SRBD=spojeniSRBD();
   $dotaz = "SELECT mnozstvi FROM zbozi WHERE id='$id'";
   $vysledek = mysqli_query("SELECT mnozstvi FROM zbozi WHERE id='$id'");
   $data = mysqli_fetch_array($vysledek);
   if($typ_akce=='pridej')
     $noveMnozstvi = $data["mnozstvi"] + $mnozstvi;
   elseif($typ_akce=='odeber')
     $noveMnozstvi = $data["mnozstvi"] - $mnozstvi;
   //vsechno se ulozi
   $vysledek = mysqli_query("UPDATE zbozi SET mnozstvi='$noveMnozstvi' WHERE id='$id'");
   
}//mnozstviZbozi

/**
 * vrati koeficient z tabulky koeficienty
 * @param SRBD - pripojeni k databazi
 * @return koeficient  
 */ 
function getCoefficient($nSRBD)
{
  global $SRBD;
 
  $dotaz = "SELECT hodnota FROM koeficienty WHERE id = 1";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());;
  $data = mysqli_fetch_array($vysledek);
  $hodnota = $data["hodnota"];
  
  return $hodnota;
}






?>
