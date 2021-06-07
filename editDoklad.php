<?php
/**
 * editDoklad.php
 *
 */

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

uvodHTML("najitDoklad");
echo '
<h1>'.$texty["najitDoklad"].'</h1>';
zobrazitHlaseni();

$poleSkupin = array('--- v¹echny ---','Prodej','Rezervace','Zmetkování','Nákup','Kooperace','Výroba','Inventura');
$poleTypuVyroby = array('--- v¹echny ---', 'Montá¾', 'Montá¾ vozíkù', 'Montá¾ blokù', 'Obrobna bloky', 'Obrobna','Svaøovna',);

/*** FORMULAR PRO VYHLEDAVANI DOKLADU ***/
  echo '
<form  method="post" action="'.$soubory['editDoklad'].'">
<script src="js/prodej_komu.js" type="text/javascript"></script>
<fieldset>
<legend>'.$texty['hledatDoklad'].'</legend>
<label for="datum">'.$texty['datum'].':</label>
<input id="new_day" name="datum" type="text" class="DatePicker" value="'.$_POST['datum'].'" /><br />
<label for="cv">'.$texty['cDokladu'].':</label>
<input type="text" maxlength="40" id="cDokladu" name="cDokladu" value="'.$_POST['cDokladu'].'" /><br />
<label for="skupina">'.$texty['skupina'].':</label>'.
makeArraySelectList('skupina',$poleSkupin,$_POST['skupina'],'','id="skupina" onchange="ukazProdejKomuVyroba(this)"',$vyberte=false).
'<br />

<label for="prodejniCena">'.$texty['prodejKomu'].':</label>
<select id="prodejniCena" name="prodejniCena">
<option value="-">--- V¹echno ---</option>';
  //druhy rozbalovaci seznam (c. vykresu / jakost)
  $dotaz = "SELECT id, popis FROM prodejni_kategorie ";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  
  $selected = $_POST['prodejniCena'];
  
  While ($data = mysqli_fetch_array($vysledek)) {
    echo '<option value="'.$data['id'].'"';
    if($data['id'] == $selected)
      echo ' selected';
    echo '>'.$data['popis']."</option>\n";
  } //while
echo '</select>
<br />
<label for="skupina">'.$texty['typ_vyroby'].':</label>'.
makeArraySelectList('typVyroby',$poleTypuVyroby,$_SESSION['promenneFormulare']['typVyroby'],'','id="typVyroby"',$vyberte=false).'<br />'.
dejTlacitko('odeslat','najit').'<br />
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';

/*** VYHLEDANI  DOKLADU ***/
if(isset($_REQUEST['odeslat']) || isset($_REQUEST['o']) || isset($_REQUEST['tod'])) //hledani jen pokud bylo odeslano tlacitko
{
  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }
  $id = $_POST['id'];
  $paging = true;
  
  $rows = POCET_RADKU;
  $dotaz = sestavDotaz();
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  $pocet = mysqli_num_rows($vysledek);
  $jmena = array('c_dokladu','datum','skupina','prod_kategorie','typ_vyroby');
  $dodatek = '';
  //echo $pocet.'XXX'.$rows;
    $dodatek .= pageOrderQuery($pocet,$rows);
  
  //pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  //echo $dotaz;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'].'&odeslat';
  
  //if ($pocet>$rows)
  if($paging)
    if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"]; 
  
  if($paging)
    putPaging($pocet,$rows,$from);
  
  
  echo 
'<table>';
printTableHeader($jmena,$urldodatek);
  
  /*<thead>
  <tr>
    <th>'.$texty['cDokladu'].'</th>
    <th>'.$texty['datum'].'</th>
    <th>'.$texty['skupina'].'</th>
    <th>'.$texty['prodejKomu'].'</th>
    <th>'.$texty['zrusitRezervaci'].'</th>
  </tr>
  </thead>
  ';*/
  
  $sudyRadek = false;
  //echo sestavDotaz();
  //*** UKAZANI INFORMACI O UPRAVOVANEM DOKLADU ***/
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error());
  While ($data = @mysqli_fetch_array($vysledek)) {
    $cDokladu = $data["c_dokladu"];
    $datum = date("d.m.Y",$data["datum"]);
    $skupina = $data["skupina"];
  echo '
  <tr';
  if($sudyRadek)
      echo ' class="sudyRadek"';
    echo 
    '>
    <td><a href="'.$soubory['dokladTransakce'].'?id='.$data['id'].'">'.$data["c_dokladu"].'</a></td>
    <td>'.date("d.m.Y",$data["datum"]).'</td>
    <td>'.$data["skupina"].'</td>
    <td>'.$data["popis"].'</td>
    <td>'.$data["typ_vyroby"].'</td>
  </tr>';
  $sudyRadek=!$sudyRadek;
  }

echo 
'</table>';
if($paging)
    putPaging($pocet,$rows,$from);
  

}

/**
 * Sestavi prislusny SELECT
 */ 
function sestavDotaz()
{
  $vysledek = '';
  $vysledek .= 'SELECT D.id, D.datum, D.c_dokladu, D.skupina, D.typ_vyroby, PK.popis FROM doklady as D left join prodejni_kategorie as PK on D.prod_kategorie=PK.id ';
  $prvni = true;          // detekce prvni podminky, nebude AND a bude WHERE
  $where = '';

  if(!empty($_REQUEST['cDokladu']))
  { $where .= 'c_dokladu="' . $_POST['cDokladu'].'" ';
    $prvni = false;
  }
  
  if(!ereg('-',$_REQUEST['skupina']) && !empty($_REQUEST['skupina']))
  {
    if(!$prvni) $where .= 'AND ';
    $where .= 'skupina=' . "'".$_REQUEST['skupina']."' ";
    $prvni = false;
  }
  
  if(($_REQUEST['skupina']=='Rezervace' || $_REQUEST['skupina']=='Prodej') &&
      !ereg('-',$_REQUEST['prodejniCena']))
  {
    if(!$prvni) $where .= 'AND ';
    $where .= 'prod_kategorie=' . "'".$_REQUEST['prodejniCena']."' ";
    $prvni = false;
  }

  if($_REQUEST['skupina']=='Výroba'  && !empty($_REQUEST['typVyroby']) && !ereg('-',$_REQUEST['typVyroby']))
  {
    if(!$prvni) $where .= 'AND ';
    $where .= 'typ_vyroby=' . "'".$_REQUEST['typVyroby']."' ";
    $prvni = false;
  }
  
  if(!empty($_REQUEST['datum']))
  {
    if(!$prvni) $where .= 'AND ';
    ereg("^([0-9][0-9]?).([0-9][0-9]?).([0-9]{4})$",$_REQUEST['datum'], $dateParts);
    $datum = $dateParts[3].'-'.$dateParts[2].'-'.$dateParts[1];
    $timestamp_date = strtotime($datum);
    $where .= 'datum=' . $timestamp_date;
    $prvni = false;
  }
  
  
  
  // pokud uz neni prvni bude se vypisovat i cast s WHERE
  if(!$prvni) $vysledek .= "WHERE ".$where;
  
  //kontrolni vypis dotazu

  //echo $vysledek;
  
  return $vysledek;
}//sestavDotaz()



konecHTML();

?>







