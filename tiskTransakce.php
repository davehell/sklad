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
$poleTypuVyroby = array('--- v¹echny ---', 'Montá¾', 'Montá¾ vozíkù', 'Montá¾ blokù', 'Obrobna bloky', 'Obrobna','Svaøovna',);
$koeficient = 10;          //hardcoded  - bude zmeneno

foreach($_GET as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach


// kontrola typu
if(isset($_GET['t']))
{ if($_GET['t']=='n')
    {$typ = "Nákup";$typAscii="Nakup";}
  elseif($_GET['t']=='p')
    {$typ = "Prodej";$typAscii="Prodej";}
  elseif($_GET['t']=='v')
    {$typ = "Výroba";$typAscii="Vyroba";}
  elseif($_GET['t']=='r')
    {$typ = "Rezervace";$typAscii="Rezervace";}
  else 
    $typ = "Chyba";
}
else $typ = "Chyba";

//osetreni chybneho zadani typu
if($typ == "Chyba")
{  session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['spatneParametry'];
}
if(!checkFormDatum($_GET['od']) || !checkFormDatum($_GET['do']))
  $datumOK = false;
else $datumOK = true;

//detekce strankovani
if($_GET['paging']=='no')
 $paging = false;
else $paging=true;

//zacatek stranky
uvodHTML('tisk'.$typAscii);
echo
'<h1>'.$texty['tisk'.$typAscii].'</h1>';
zobrazitHlaseni();
if($typ!="Chyba"){
echo '
<form type="GET" action="'.$soubory['tiskTransakce'].'" class="noPrint">
<fieldset>
<legend>'.$texty['casoveVymezeni'].'</legend>
<label for="od">'.$texty['datumOd'].':</label>
<input id="new_day" name="od" type="text" class="DatePicker" value="'.$_SESSION['promenneFormulare']['od'].'" /><br />
<label for="do">'.$texty['datumDo'].':</label>
<input id="new_day" name="do" type="text" class="DatePicker" value="'.$_SESSION['promenneFormulare']['do'].'" /><br />
<label for="c_dokladu">'.$texty['c_dokladu'].':</label>
<input type="text" name="c_dokladu" value="'.$_SESSION['promenneFormulare']['c_dokladu'].'"/><br />
<input type="hidden" name="t" value="'.$_GET['t'].'">';
if($typ == 'Výroba')
{
  echo '<label for="skupina">'.$texty['typ_vyroby'].':</label>'.
  makeArraySelectList('typVyroby',$poleTypuVyroby,$_SESSION['promenneFormulare']['typVyroby'],'','id="typVyroby"').'<br />';
}
echo dejTlacitko('odeslat','najit').
'</fieldset>
</form>';



if(isset($_GET['od']) && $datumOK){
  ////// tabulka  ////////
  $rows = POCET_RADKU;
  
  if($_GET['print']==1)
  {
    if($typ == 'Výroba')
    {    $jmena = array('c','nazev','c_vykresu','datum','c_dokladu','typ_vyroby','mnozstvi','cena_KOO','koo_mnozstvi','cenaCelkem');
         $zobrazit = array('c','nazev','c_vykresu','datum','c_dokladu','typ_vyroby','mnozstvi','cena_KOO','prace_mnozstvi','cenaCelkem');
    }
    else
    {    $jmena = array('c','nazev','c_vykresu','datum','c_dokladu','mnozstvi','cena_MJ','cenaCelkem');
         $zobrazit = array('c','nazev','c_vykresu','datum','c_dokladu','mnozstvi','cena_KOO','cena_MJ','cenaCelkem');
    }
  }
  else
  {
    if($typ == 'Výroba')
    {    $jmena = array('nazev','c_vykresu','datum','c_dokladu','typ_vyroby','mnozstvi','cena_KOO','koo_mnozstvi','cenaCelkem');
         $zobrazit = array('nazev','c_vykresu','datum','c_dokladu','typ_vyroby','mnozstvi','cena_KOO','prace_mnozstvi','cenaCelkem');
    }
    else
    {    $jmena = array('nazev','c_vykresu','datum','c_dokladu','mnozstvi','cena_MJ','cenaCelkem');
         $zobrazit = array('nazev','c_vykresu','datum','c_dokladu','mnozstvi','cena_KOO','cena_MJ','cenaCelkem');
    }
  }


  $urldodatek = 't='.$_GET['t'].'&od='.$_GET['od'].'&do='.$_GET['do'];
  
  $dotaz = udelejDotaz($typ,$_GET['od'],$_GET['do']);
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));

  $dotaz3 = udelejSumDotaz($typ,$_GET['od'],$_GET['do']);

  //zjisteni poctu radku
  $pocet = mysqli_num_rows($vysledek);
  $dodatek = '';
  if($paging)
    $dodatek .= pageOrderQuery($pocet,$rows);

  //pridani dodatku (ORDER, LIMIT)
  $dotaz .= $dodatek;
  //echo $dotaz;
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  
  //if ($pocet>$rows)
  if($paging)
    if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"]; 
  
  $urldodatek2 ='&'.$urldodatek.'&o='.$_GET['o'].'&ot='.$_GET['ot'];
  
  if($paging)
    putPaging($pocet,$rows,$from, $urldodatek2);
  echo '
  <table class="malaTabulka">';
  
  printTableHeader($jmena,$urldodatek,$zobrazit);
//   $dotaz2 = "SELECT sum(mnozstvi) as mnozstvi, sum(cena_MJ) as cena_MJ, sum(cena_KOO) as cena_KOO, sum(mnozstvi*cena_MJ) as cena_celkem
//             FROM transakce as T join doklady as D ON T.id_dokladu=D.id
//             WHERE D.skupina = '$typ'";
//   echo("dotaz: ".$dotaz2);
//   if(!empty($_GET['c_dokladu']))
//   {
//     $cd = $_GET["c_dokladu"];
//     $timestamp_datedo = strtotime($do);
//     $dotaz2 .= "AND D.c_dokladu = '$cd' ";
//   }
//   if($typ == 'Výroba' && !empty($_REQUEST['typVyroby']) && !preg_match('/-/',$_REQUEST['typVyroby']))
//   {
//     $dotaz2 .= 'AND typ_vyroby=' . "'".$_REQUEST['typVyroby']."' ";
//   }
  $vysledek2 = mysqli_query($SRBD, $dotaz3) or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek2);
  
  $sudy = false;
  if($_GET['print']==1)
    $colspan = 5;
  else $colspan = 4;
  if($typ == 'Výroba')   // je potreba posunout jeste o jedno policko
    $colspan++;
    
  echo '<tfoot>
         <tr>
           <td colspan="'.$colspan.'">'.$texty['celkem'].'</td>
           <td class="alignRight">'.number_format($data['mnozstvi'], 3, ".", " ").'</td>';
          if($typ == 'Výroba' || $typ == 'Kooperace') {
            echo '<td class="alignRight">'.number_format($data['cena_KOO'], 2, ".", " ").'</td>';
          }
          if($typ == 'Výroba') {
            echo '<td class="alignRight">'.number_format($data['koo_mnozstvi'], 3, ".", " ").'</td>';
            echo '<td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>';
          }
          else {
            echo '<td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>';
            echo '<td class="alignRight">'.number_format($data['cena_MJ'], 2, ".", " ").'</td>';
          }
             

           
          echo '</tr>';
  
  echo '</tfoot>
        
       <tbody>';
  $i=1;
  While ($data = mysqli_fetch_array($vysledek)) {
    $cenaCelkem = $data['cena_MJ']*$data['mnozstvi'];
    $tiskDatum = date("d.m.y",$data["datum"]);
    $cenaMJ = $data['cena_MJ'];
    //if($typ == 'Výroba')
    //  $cenaMJ *= $koeficient;
    echo '
    <tr';
    if($sudy)
    echo ' class="sudyRadek"';
    echo'>';
    if($_GET['print']==1)
    echo '<td>'.$i++.'</td>';
    echo
    '<td><a href="'.$soubory['nahledKarta'].'?id='.$data['id_zbozi'].'">'.$data['nazev'].'</a></td>
    <td>'.$data['c_vykresu'].'</td>
    <td>'.$tiskDatum.'</td>
    <td><a href="'.$soubory['dokladTransakce'].'?id='.$data['id_dokladu'].'">'.$data['c_dokladu'].'</a></td>';
    if($typ == 'Výroba') {
        //echo '<td>'.$data['typ_vyroby'].'</td>';
        //kvuli zkraceni sirky tabulky se zobrazi pouze prvni pismeno
        echo '<td>'.substr($data['typ_vyroby'], 0 ,1).'</td>';
    }
    echo '<td class="alignRight">'.number_format($data['mnozstvi'], 3, ".", " ").'</td>';
    
    if($typ == 'Výroba' || $typ == 'Kooperace') {
        echo '<td class="alignRight">'.number_format($data['cena_KOO'], 2, ".", " ").'</td>';
    }
    if($typ == 'Výroba') {
        echo '<td class="alignRight">'.number_format($data['koo_mnozstvi'], 3, ".", " ").'</td>';
    }
    echo ' <td class="alignRight">'.number_format($data['cenaCelkem'], 2, ".", " ").'</td>';
    if($typ != 'Výroba') {
        echo ' <td class="alignRight">'.number_format($cenaMJ, 2, ".", " ").'</td>';
    }
    echo '</tr>';
    $sudy=!$sudy;
    }
    echo '</tbody></table>';
  if($paging)
    putPaging($pocet,$rows,$from, $urldodatek2);
  //konec tabulky  
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

function udelejDotaz($skupina, $od, $do)
{

$dotaz ='';
$dotaz .= "SELECT T.id, T.id_dokladu,T.id_zbozi, T.cena_KOO, Z.nazev, Z.c_vykresu, D.c_dokladu,D.typ_vyroby, D.datum, T.cena_MJ, T.mnozstvi, T.cena_KOO*T.mnozstvi as koo_mnozstvi, T.cena_MJ*T.mnozstvi as cenaCelkem
           FROM transakce T, doklady D, zbozi Z
           WHERE T.id_dokladu=D.id AND 
           T.id_zbozi = Z.id AND
           D.skupina='$skupina' ";
if(!empty($_GET['od']))
{ 
  $timestamp_dateod = strtotime($od); 
  $dotaz .= "AND D.datum >= '$timestamp_dateod' ";
}

if(!empty($_GET['do']))
{
  $timestamp_datedo = strtotime($do); 
  $dotaz .= "AND D.datum <= '$timestamp_datedo' ";
}

if(!empty($_GET['c_dokladu']))
{
  $cd = $_GET["c_dokladu"];
  $timestamp_datedo = strtotime($do);
  $dotaz .= "AND D.c_dokladu = '$cd' ";
}

if($skupina == 'Výroba' && !empty($_REQUEST['typVyroby']) && !preg_match('/-/',$_REQUEST['typVyroby']))
{
  $dotaz .= 'AND typ_vyroby=' . "'".$_REQUEST['typVyroby']."' ";
}

//echo $dotaz;

return $dotaz;
}//udelejDotaz()

function udelejSumDotaz($skupina, $od, $do)
{

    $dotaz ='';
    $dotaz .= "SELECT sum(T.cena_MJ) as cena_MJ, sum(T.mnozstvi) as mnozstvi, sum(cena_KOO) as cena_KOO, sum(T.cena_MJ*T.mnozstvi) as cena_celkem, sum(T.cena_KOO*T.mnozstvi) as koo_mnozstvi
           FROM transakce T, doklady D, zbozi Z
           WHERE T.id_dokladu=D.id AND
           T.id_zbozi = Z.id AND
           D.skupina='$skupina' ";


if(!empty($_GET['od']))
{
  $timestamp_dateod = strtotime($od);
  $dotaz .= "AND D.datum >= '$timestamp_dateod' ";
}

if(!empty($_GET['do']))
{
  $timestamp_datedo = strtotime($do);
  $dotaz .= "AND D.datum <= '$timestamp_datedo' ";
}

if(!empty($_GET['c_dokladu']))
{
  $cd = $_GET["c_dokladu"];
  $timestamp_datedo = strtotime($do);
  $dotaz .= "AND D.c_dokladu = '$cd' ";
}

if($skupina == 'Výroba' && !empty($_REQUEST['typVyroby']) && !preg_match('/-/',$_REQUEST['typVyroby']))
{
  $dotaz .= 'AND typ_vyroby=' . "'".$_REQUEST['typVyroby']."' ";
}
//echo $dotaz;
return $dotaz;
}

?>
