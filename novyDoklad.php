<?php
/**
 * novyDoklad.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

if(isset($_GET["id"])) {$id = $_GET["id"];}
if(!isset($_SESSION['promenneFormulare']["datum"]))
  $_SESSION['promenneFormulare']["datum"] = date("j.n.Y");


uvodHTML("zapisTitulek");
echo '
<h1>'.$texty["zapis"].'</h1>';
zobrazitHlaseni();

$poleSkupin = array('Prodej','Rezervace','Zmetkování','Nákup','Kooperace','Výroba','Inventura');
$poleTypuVyroby = array('Montá¾', 'Montá¾ vozíkù', 'Montá¾ blokù', 'Obrobna bloky', 'Obrobna','Svaøovna',);


echo '
<h2>1. '.$texty["vyplnitHlavicku"].'</h2>';
?>
		
<?php

 if(!isset($_GET["id"])) {
//Hlavicka karty jeste nebyla vyplnena, tedy v url neni nastaven parametr id,
//tedy se zobrazi formular pro zadani hlavicky karty
  echo '
<form method="post" action="'.$soubory['vlozDoklad'].'">
<script src="js/prodej_komu.js" type="text/javascript"></script>
<fieldset>
<legend>'.$texty['infoZapis'].'</legend>
<label for="datum">'.$texty['datum'].':</label>
<input id="new_day" name="datum" type="text" class="DatePicker" value="'.($_SESSION['promenneFormulare']["datum"] ?? '').'" /><br />
<label for="cv">'.$texty['cDokladu'].':</label>
<input type="text" maxlength="40" id="cDokladu" name="cDokladu" value="'.($_SESSION['promenneFormulare']['cDokladu'] ?? '').'" /><br />
<label for="skupina">'.$texty['skupina'].':</label>'.
makeArraySelectList('skupina',$poleSkupin,($_SESSION['promenneFormulare']['skupina'] ?? ''),'','id="skupina" onchange="ukazProdejKomuVyroba(this);"').
'<br />

<label for="prodejniCena">'.$texty['prodejKomu'].':</label>
<select id="prodejniCena" name="prodejniCena">
<option value="">----- vyberte -----</option>';
  $dotaz = "SELECT id, popis FROM prodejni_kategorie ORDER BY popis";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['prodejniCena'] ?? '';
  else $selected = '';
  
  While ($data = mysqli_fetch_array($vysledek)) {
    echo '<option value="'.$data['id'].'"';
    if($data['id'] == $selected)
      echo ' selected';
    echo '>'.$data['popis']."</option>\n";
  } //while'<br />
echo '</select>
<br />
<label for="skupina">'.$texty['typ_vyroby'].':</label>'.
makeArraySelectList('typVyroby',$poleTypuVyroby,$_SESSION['promenneFormulare']['typVyroby'] ?? '','','id="typVyroby"').
dejTlacitko('odeslat','vytvoritDoklad').'
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';
} //if(!isset($_GET["id"]))



echo '
<h2>2. '.$texty["pridavaniPolozek"].'</h2>
';
if(!isset($_GET["id"])) {
//Hlavicka karty jeste nebyla vyplnena, tedy v url neni nastaven parametr id,
//tedy se zobrazi vyzva k vyplneni formulare s hlavickou
  echo "<p>Nejdøíve zadejte informace o dokladu vyplnìním v¹ech údajù a stiskem tlaèítka ".$texty["vytvoritDoklad"].".</p>";
}

session_unregister("promenneFormulare");

konecHTML();







?>
