<?php
/**
 * tiskNakupy.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();
$datumOK = false;
$cena_skladu = 0;
$mnozstvi_skladu = 0;                  //celkove hodnoty vypisovane na konci v 'celkem'

foreach($_GET as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach


if(!checkFormDatum($_GET['od']) || !checkFormDatum($_GET['do']))
  $datumOK = false;
else $datumOK = true;

//detekce strankovani
if($_GET['paging']=='no')
 $paging = false;
else $paging=true;

//zacatek stranky
uvodHTML('cenovyStavSkladu');
echo
'<h1>'.$texty['cenovyStavSkladu'].'</h1>';
zobrazitHlaseni();
if($typ!="Chyba"){
echo '
<form type="GET" action="'.$soubory['cenovyStav'].'" class="noPrint">
<fieldset>
<legend>'.$texty['casoveVymezeni'].'</legend>
<label for="od">'.$texty['datumOd'].':</label>
<input id="new_day" name="od" type="text" class="DatePicker" value="'.$_SESSION['promenneFormulare']['od'].'" /><br />
<label for="do">'.$texty['datumDo'].':</label>
<input id="new_day" name="do" type="text" class="DatePicker" value="'.$_SESSION['promenneFormulare']['do'].'" /><br />'.
dejTlacitko('odeslat','najit').
'</fieldset>
</form>';



if(isset($_GET['od']) && $datumOK){

  ////// tabulka  ////////
  $rows = 5;
  
  if($_GET['print']==1)
  {  $jmena = array('c','nazev','c_vykresu','mnozstvi','cena_MJ','cena_celkem');
     $zobrazit = array('c','nazev','c_vykresu','mnozstvi','cena_MJ','cena_celkem');
  } 
  else
  {
    $jmena = array('nazev','c_vykresu','mnozstvi','cena_MJ','cena_celkem');
    $zobrazit = array('nazev','c_vykresu','mnozstvi','cena_MJ','cena_celkem');
  }
  $urldodatek = '&od='.$_GET['od'].'&do='.$_GET['do'];
  
  // prvni dotaz (normalni)
  $dotaz = udelejDotaz($_GET['od'],$_GET['do'],"stroje");
  $dodatek = '';
    $dodatek .= pageOrderQuery($pocet,$rows);
  //pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  
  //echo $dotaz;
  
  // dodatek o puvodnim URL
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'];
  
  echo '
  <h1>'.$texty['stroje'].'</h1>
  <table>';
  
  printTableHeader($jmena,$urldodatek,$zobrazit);
  //druhy dotaz (suma)
  $dotaz2 = udelejDotaz($_GET['od'],$_GET['do'],$typ='sumstroje');
  $vysledek2 = mysqli_query($SRBD, $dotaz2) or Die(mysqli_error());
  $data2 = mysqli_Fetch_Array($vysledek2);
  
  // pocet slucovvanych radku se lisi u print
  if($_GET['print']==1)
    $colspan = 3;
  else $colspan = 2;
  //tisk zapati (suma cen)
  echo '<tfoot>
         <tr>
           <td colspan="'.$colspan.'">'.$texty['celkem'].'</td>
           <td class="alignRight">'.number_format($data2['mnozstvi'], 3, ".", " ").'</td>
           <td></td>
           <td class="alignRight">'.number_format($data2['cena_celkem'], 2, ".", " ").'</td>
          </tr>';
  echo '</tfoot>
      <tbody>';
  $cena_skladu += $data2['cena_celkem'];
  $mnozstvi_skladu += $data2['mnozstvi'];
  $sudy = false;
  $i=1;
  While ($data = mysqli_Fetch_Array($vysledek)) 
  {
    //$cenaMJ = $data['cena_celkem']/$data['mnozstvi'];
    $cenaMJ = number_format($data['prum_cena'], 2, ".", " ");
    echo '
    <tr';
    if($sudy)
      echo ' class="sudyRadek"';
    echo'>';
    if($_GET['print']==1)
      echo '<td>'.$i++.'</td>';
      echo
    '<td><a href="'.$soubory['nahledKarta'].'?id='.$data['id'].'">'.$data['nazev'].'</a></td>
      <td>'.$data['c_vykresu'].'</td>
      <td class="alignRight">'.number_format($data['mnozstvi'], 2, ".", " ").'</td>
      <td class="alignRight">'.$cenaMJ.'</td>
      <td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>
    </tr>';
    $sudy=!$sudy;
  }
  echo '</tbody></table>';
  //konec tabulky  

/////////////////konec tabulky stroje //////////////////////////////////////////


//////////////// sestavy ///////////////////////////////////////////////////////
  
  // prvni dotaz (normalni)
  $dotaz = udelejDotaz($_GET['od'],$_GET['do'],$typ="sestavy");
  $dodatek = '';
    $dodatek .= pageOrderQuery($pocet,$rows);
  //pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  //echo $dotaz;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  
  // dodatek o puvodnim URL
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'];
  
  //nadpis
  echo '<h1>'.$texty['sestavy'].'</h1>
  <table>';
  
  if($_GET['print']==1)
  {  $jmena = array('c','zac_c_vykresu','mnozstvi','cena_celkem');
     $zobrazit = array('c','zac_c_vykresu','mnozstvi','cena_celkem');
  } 
  else
  {  $jmena = array('zac_c_vykresu','mnozstvi','cena_celkem');
     $zobrazit = array('zac_c_vykresu','mnozstvi','cena_celkem');
  }
  
  printTableHeader($jmena,$urldodatek, $zobrazit);
  //druhy dotaz (suma)
  $dotaz2 = udelejDotaz($_GET['od'],$_GET['do'],$typ='sumsestavy');
  $vysledek2 = mysqli_query($SRBD, $dotaz2) or Die(mysqli_error());
  $data2 = mysqli_Fetch_Array($vysledek2);
  
  // pocet slucovvanych radku se lisi u print
  if($_GET['print']==1)
    $colspan = 2;
  else $colspan = 1;
  //tisk zapati (suma cen)
  echo '<tfoot>
         <tr>
           <td colspan="'.$colspan.'">'.$texty['celkem'].'</td>
           <td class="alignRight">'.number_format($data2['mnozstvi'], 3, ".", " ").'</td>
           <td class="alignRight">'.number_format($data2['cena_celkem'], 2, ".", " ").'</td>
          </tr>';
  echo '</tfoot>
      <tbody>';
  $cena_skladu += $data2['cena_celkem'];
  $mnozstvi_skladu += $data2['mnozstvi'];
  $sudy = false;
  $i=1;
  While ($data = mysqli_Fetch_Array($vysledek)) 
  {
    //$cenaMJ = $data['cena_celkem']/$data['mnozstvi'];
    $cenaMJ = number_format($data['cena_MJ'], 2, ".", " ");
    echo '
    <tr';
    if($sudy)
      echo ' class="sudyRadek"';
    echo'>';
    if($_GET['print']==1)
      echo '<td>'.$i++.'</td>';
      echo
    ' <td>'.$data['zac_c_vykresu'].'</td>
      <td class="alignRight">'.number_format($data['mnozstvi'], 3, ".", " ").'</td>
      <td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>
    </tr>';
    $sudy=!$sudy;
  }
  echo '</tbody></table>';
  //konec tabulky  

///////////////////////////// konec druhe tabulky //////////////////////////////

///////////////////////////  rozpracovana vyroba ///////////////////////////////
//nadpis
   
  // prvni dotaz (normalni)
  $dotaz = udelejDotaz($_GET['od'],$_GET['do'],$typ="rozprac");
  $dodatek = '';
    $dodatek .= pageOrderQuery($pocet,$rows);
  //pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  //echo $dotaz;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  
  // dodatek o puvodnim URL
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'];
  
  //nadpis
  echo '<h1>'.$texty['rozprac_vyroba'].'</h1>
  <table>';
  
  if($_GET['print']==1)
  {  $jmena = array('c','zac_c_vykresu','mnozstvi','cena_celkem');
     $zobrazit = array('c','zac_c_vykresu','mnozstvi','cena_celkem');
  } 
  else
  {  $jmena = array('zac_c_vykresu','mnozstvi','cena_celkem');
     $zobrazit = array('zac_c_vykresu','mnozstvi','cena_celkem');
  }
  
  printTableHeader($jmena,$urldodatek, $zobrazit);
  //druhy dotaz (suma)
  $dotaz2 = udelejDotaz($_GET['od'],$_GET['do'],$typ='sumrozprac');
  $vysledek2 = mysqli_query($SRBD, $dotaz2) or Die(mysqli_error());
  $data2 = mysqli_Fetch_Array($vysledek2);
  
  // pocet slucovvanych radku se lisi u print
  if($_GET['print']==1)
    $colspan = 2;
  else $colspan = 1;
  //tisk zapati (suma cen)
  echo '<tfoot>
         <tr>
           <td colspan="'.$colspan.'">'.$texty['celkem'].'</td>
           <td class="alignRight">'.number_format($data2['mnozstvi'], 3, ".", " ").'</td>
           <td class="alignRight">'.number_format($data2['cena_celkem'], 2, ".", " ").'</td>
          </tr>';
  echo '</tfoot>
      <tbody>';
  $cena_skladu += $data2['cena_celkem'];
  $mnozstvi_skladu += $data2['mnozstvi'];
  $sudy = false;
  $i=1;
  While ($data = mysqli_Fetch_Array($vysledek)) 
  {
    //$cenaMJ = $data['cena_celkem']/$data['mnozstvi'];
    $cenaMJ = $data['cena_MJ'];
    echo '
    <tr';
    if($sudy)
      echo ' class="sudyRadek"';
    echo'>';
    if($_GET['print']==1)
      echo '<td>'.$i++.'</td>';
      echo
    ' <td>'.$data['zac_c_vykresu'].'</td>
      <td class="alignRight">'.number_format($data['mnozstvi'], 3, ".", " ").'</td>
      <td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>
    </tr>';
    $sudy=!$sudy;
  }
  echo '</tbody></table>';
  //konec tabulky  

  


////////////////////// konec rozpracovana vyroba ///////////////////////////////



////////////////////////////celkem ////////////////////////////////////////////
//nadpis


  echo '<h1>'.$texty['celkem'].'</h1>
      <dl>
        <dt>'.$texty["mnozstvi"].':</dt><dd>'.number_format($mnozstvi_skladu, 3, ".", " ").'</dd>
        <dt>'.$texty["cena_celkem"].':</dt><dd>'.number_format($cena_skladu, 3, ".", " ").'</dd>';
      echo '</dl>';
 }
}
konecHTML();


function checkFormDatum($datum)
{
global $texty;
$korektniParametry=true;

if(empty($datum)) ;
elseif (!preg_match("/^([0-9][0-9]?).([0-9][0-9]?).([0-9]{4})$/",$datum, $dateParts))
{  session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['nespravneDatum'];
   $korektniParametry = false;
}
else
{
   $datum = $dateParts[3].'-'.$dateParts[2].'-'.$dateParts[1];
}

return $korektniParametry;

}

/**
 * tvori dotaz na cenovy stav skladu
 * @param od     
 * @param do     
 * @param typ    
 */  
function udelejDotaz($od, $do, $typ)
{
$dotaz ='';



if($typ=='sumstroje' || $typ=='vsechno')
  {
   $id = '';
  }
elseif($typ=='stroje') {
  $id = 'id, nazev, c_vykresu, prum_cena,';
  }
elseif($typ=='sestavy' || $typ=='rozprac') {
  $id = 'zac_c_vykresu, ';
}
  
if($typ=='sestavy' || $typ=='sumsestavy' || $typ=='rozprac' || $typ=='sumrozprac')
{
  $not = 'not';
}
else
{
  $not = '';
}

// pri rozpracovane vyrobe se prida omezeni pouze na karty ktere maji cenu prace 
if($typ=='rozprac' || $typ=='sumrozprac' )
{
  $rozprac = 'AND Z.cena_prace > 0';
}
else if($typ=='stroje' || $typ=='sumstroje')
{
  $rozprac = '';
}
else
{
    $rozprac = 'AND Z.cena_prace < 1';
}

//rozliseni jestli se omezuju jenom na stroje,nebo sestavy
if($typ!='vsechno')
$omezeni_zbozi = "WHERE Z.id $not in (SELECT id_zbozi
                     FROM stroje) $rozprac";
else {$omezeni_zbozi = '';}



$datum_dotaz = '';
if(!empty($_GET['od']))
{
  $timestamp_dateod = strtotime($od);
  $datum_dotaz .= "AND D.datum >= '$timestamp_dateod' ";
}

if(!empty($_GET['do']))
{
  $timestamp_datedo = strtotime($do); 
  $datum_dotaz .= "AND D.datum <= '$timestamp_datedo' ";
}

$dotaz .= "select $id  sum(mnozstvi) as mnozstvi, sum(mnozstvi*cena_MJ) as cena_celkem from
      ((SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z 
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_MJ, D.skupina 
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Nákup','Inventura') $datum_dotaz ) 
      as TD on Z.id = TD.id_zbozi
      $omezeni_zbozi
      GROUP BY Z.id) 
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z 
      JOIN (SELECT T.id_zbozi, -(T.mnozstvi) as mnozstvi, T.cena_MJ, D.skupina 
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Prodej','Zmetkování') $datum_dotaz ) 
      as TD on Z.id = TD.id_zbozi
      $omezeni_zbozi
      GROUP BY Z.id)
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z 
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_KOO, D.skupina 
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Výroba','Kooperace') $datum_dotaz ) 
      as TD on Z.id = TD.id_zbozi
      $omezeni_zbozi
      GROUP BY Z.id) 
      UNION ALL
       (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
       FROM zbozi as Z
       JOIN (SELECT VOT.id_zbozi, -(VOT.mnozstvi) as mnozstvi, VOT.d_id, D.datum
             FROM doklady as D 
             JOIN (SELECT VO.id_zbozi, VO.mnozstvi, T.id_dokladu as d_id
                   FROM vyroba_odpisy as VO 
                   JOIN transakce as T ON T.id = VO.id_vyroby
             ) as VOT ON D.id = VOT.d_id $datum_dotaz)
       AS TD on Z.id = TD.id_zbozi
       $omezeni_zbozi
       GROUP BY Z.id)
      ) as TSUMY
";


// (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD2.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
//       FROM zbozi as Z
//       JOIN (SELECT S.soucastka as id_zbozi, -(S.mnozstvi*TD.mnozstvi) as mnozstvi, TD.id_dokladu
//             FROM ((SELECT T.id_zbozi, T.id_dokladu, T.mnozstvi FROM transakce as T
//                   JOIN doklady as D ON D.id=T.id_dokladu
//                   WHERE D.skupina in('Výroba','Kooperace') $datum_dotaz ) as TD
//                   JOIN (SELECT soucastka,celek, mnozstvi
//                         FROM sestavy
//                         ) as S ON S.celek=TD.id_zbozi)) 
//       as TD2 on Z.id = TD2.id_zbozi
//       $omezeni_zbozi
//       GROUP BY Z.id)
//       


if($typ=='stroje')
  $dotaz.=' GROUP BY id ';
elseif($typ=='sestavy' || $typ=='rozprac')
    $dotaz.=' GROUP BY zac_c_vykresu ';

//echo $dotaz;
return $dotaz;
}//udelejDotaz()



/*      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_KOO, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Výroba','Kooperace') $datum_dotaz )
      as TD on Z.id = TD.id_zbozi
      $omezeni_zbozi
      GROUP BY Z.id)*/






?>
