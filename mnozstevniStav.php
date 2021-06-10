<?php
/**
 * mnozstevniStav.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();


//detekce strankovani
if($_GET['paging']=='no')
 $paging = false;
else $paging=true;

foreach($_GET as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

if(!checkFormDatum($_GET['od']) || !checkFormDatum($_GET['do']))
  $datumOK = false;
else $datumOK = true;

//zacatek stranky
uvodHTML('mnozstevniStav');
echo
'<h1>'.$texty['mnozstevniStav'].'</h1>';
zobrazitHlaseni();


if($typ!="Chyba"){    //formular s datem od-do
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



//if(isset($_GET['od']) && $datumOK){
if($datumOK){

  /*
    detekce jestli je vybrán stav za nìjaké èasové období nebo celkový. v pøípadì
    celkového je jednodu‘‘í dotaz pro db
    dotaz = vsechny polozky
    dotaz2 = suma vsech polozek, celkove mnozstvi
   */
  if((empty($_GET['od'])  && empty($_GET['do'])))
  {
     $dotaz = "select id, nazev, c_vykresu, (Z.mnozstvi + IFNULL(T2.mnozstvi,0)) AS mnozstvi
            from zbozi as Z left join (SELECT T.id_zbozi, T.mnozstvi as mnozstvi
                                  FROM transakce as T
                                  join doklady D on T.id_dokladu=D.id
                                  WHERE D.skupina = 'Rezervace') as T2
            on Z.id=T2.id_zbozi ";
     $dotaz2 = "select (sum(Z.mnozstvi)+ sum(IFNULL(T2.mnozstvi,0))) as mnozstvi
                from zbozi as Z left join (SELECT T.id_zbozi, T.mnozstvi as mnozstvi
                                           FROM transakce as T
                                           JOIN doklady D on T.id_dokladu=D.id
                                           WHERE D.skupina = 'Rezervace') as T2
                on Z.id=T2.id_zbozi";
  }
  else
  {
     $dotaz = udelejDotaz($_GET['od'],$_GET['do'],"normal");
     $dotaz2 = udelejDotaz($_GET['od'],$_GET['do'],"sum");
  }
  ////// tabulka  ////////
  $rows = POCET_RADKU;


  if($_GET['print']==1)
    $jmena = array('c','nazev','c_vykresu','mnozstvi');
  else
    $jmena = array('nazev','c_vykresu','mnozstvi');

  $urldodatek = 't='.$_GET['t'].'&od='.$_GET['od'].'&do='.$_GET['od'];
            
  
  if($paging)
  {$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());

  //zjisteni poctu radku
  $pocet = mysqli_num_rows($vysledek);
  $dodatek = '';
  $dodatek .= pageOrderQuery($pocet,$rows);

  }//pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  //echo $dotaz;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
  
  
  if($paging)
    if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"]; 
  
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'];
  
  if($paging)
    putPaging($pocet,$rows,$from, $urldodatek2);
  echo '
  <table>';
  
  printTableHeader($jmena,$urldodatek);

  $vysledek2 = mysqli_query($SRBD, $dotaz2) or Die(mysqli_Error());
  $data2 = mysqli_Fetch_Array($vysledek2);
  
  $sudy = false;
  if($_GET['print']==1)
    $colspan = 3;
  else $colspan = 2;
  
  $mnozstvi2 = $data2['mnozstvi']+$data2['rezervace'];
  echo ' <tfoot>
         <tr>
           <td colspan="'.$colspan.'">'.$texty['celkem'].'</td>
           <td class="alignRight">'.number_format($mnozstvi2, 3, ".", " ").'</td>
          </tr>
       </tfoot>
  
       <tbody>';
  $i=1;
  While ($data = mysqli_Fetch_Array($vysledek)) {
  //$cenaCelkem = $data['cena_MJ']*$data['mnozstvi'];
  $mnozstvi = $data['mnozstvi']+$data['rezervace'];
  
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
    <td class="alignRight">'.number_format($mnozstvi, 3, ".", " ").'</td>
  </tr>';
  $sudy=!$sudy;
  }
  echo '</tbody></table>';
  if($paging)
    putPaging($pocet,$rows,$from, $urldodatek2);
  
}

}
//konec tabulky
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

}//checkFormDatum


/**
 * tvori dotaz na cenovy stav skladu
 * @param od
 * @param do
 * @param typ
 */
function udelejDotaz($od, $do, $typ)
{
$dotaz ='';


if($typ=='sum')
  {
   $id = '';
  }
else
  $id = 'id, nazev, c_vykresu, prum_cena,';

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
      GROUP BY Z.id)
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      JOIN (SELECT T.id_zbozi, -(T.mnozstvi) as mnozstvi, T.cena_MJ, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Prodej','Zmetkování') $datum_dotaz )
      as TD on Z.id = TD.id_zbozi
      GROUP BY Z.id)
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_KOO, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Výroba','Kooperace') $datum_dotaz )
      as TD on Z.id = TD.id_zbozi
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
       GROUP BY Z.id)
      ) as TSUMY
";


if($typ=='normal')
  $dotaz.=' GROUP BY id ';


//echo $dotaz;
return $dotaz;
}//udelejDotaz()

?>
