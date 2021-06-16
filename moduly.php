<?php
/**
 * moduly.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD("sklad");

if(isset($_POST["modul"])) {
  pridatModul($_POST["modul"]);
}
elseif(isset($_GET["odstranit"])) {
  odebratModul($_GET["odstranit"]);
}


uvodHTML("modulyNadpis");
echo '
<h1>'.$texty["modulyNadpis"].'</h1>';
zobrazitHlaseni();

echo '
<h2>'.$texty["prehledModulu"].'</h2>';

$dotaz = "SELECT id, modul FROM moduly ORDER BY id ASC";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
if(mysqli_num_rows($vysledek) != 0) {
  $sudyRadek = false;
  $sloupce = array('','modul');
  echo '
<table>';
  printTableHeader($sloupce);

  While ($data = mysqli_fetch_array($vysledek)) {
    if($sudyRadek) {
      echo '
  <tr class="sudyRadek">';
    } //if sudyradek
    else {
      echo '
  <tr>';
    } //else
    echo '
    <td><a href="'.$soubory["moduly"].'?odstranit='.$data["id"].'" title="'.$texty["odebratModulTitle"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odebrat"].'</a></td>
    <td>'.$data["modul"].'</td>
  </tr>';
    $sudyRadek = !$sudyRadek;
  } //while
  echo '
</table>';
}
else {
  echo '
  <p>'.$texty["zadnyModul"].'</p>';
}

echo '
<form method="post" action="'.$soubory['moduly'].'">
<fieldset>
<legend>'.$texty['pridaniModulu'].'</legend>
<label for="modul">'.$texty['nazevModulu'].':</label>
<input type="text" maxlength="40" id="modul" name="modul" /><br />
<br />'.
dejTlacitko('odeslat','pridatModul').'
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';

konecHTML();


function pridatModul($nazev) {
  global $texty;
  global $soubory;
  
  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD("sklad");
  }

  //kontroly zadanych dat
  $korektniParametry = true;
  // nazev
  if ($nazev == "sklad") { // modul se nesmi jmenovat "sklad"
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neSklad'];
    $korektniParametry = false;
  }
  if (!preg_match("/[a-zA-Z0-9]/", $nazev)) { // jméno nesmí obsahovat bílé znaky
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['deravyNazev'];
    $korektniParametry = false;
  } 
  if (empty($nazev)) { // nazev nesmi byt prazdny
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['prazdnyNazevModulu'];
    $korektniParametry = false;
  }
  if (strlen($nazev) > 30) { // popis nesmi byt delsi nez 30 znakù
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['nazevModuluNad30'];
    $korektniParametry = false;
  }

  if (! $korektniParametry)  { // byly chyby
    header('Location: '.$soubory['moduly']);
    exit;
  }

  $dotaz = "INSERT INTO moduly (id,modul) VALUES (0, '$nazev')";
  mysqli_query($SRBD, $dotaz);

  if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novyModulDuplicitni'];
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novyModulOK'];
  }

  $nazev .= $_SESSION["rokArchiv"];
  mysqli_query($SRBD, "create database `$nazev` COLLATE=latin2_czech_cs") or Die(mysqli_error($SRBD));
  mysqli_select_db($SRBD, $nazev) or Die(mysqli_error($SRBD));
  

  $query = "";
  $f = fopen("sql/novy_modul.sql", "r");
  while (!feof ($f)) {
    $query .= fgets($f, 4096);
  }
  fclose ($f);

  //vytvoreni potrebnych tabulek v databazi
  foreach (explode(';', $query) as $sql) {
    mysqli_query($SRBD, $sql);
  }
  
  $query = "";
  $f = fopen("sql/transakce_triggery.sql", "r");
  while (!feof ($f)) {
    $query .= fgets($f, 4096);
  }
  fclose ($f);
  mysqli_query($SRBD, 'delimiter //');
  
  //vytvoreni potrebnych tabulek v databazi
  foreach (explode('//', $query) as $sql) {
    if(strlen($sql) > 0) {
      mysqli_query($SRBD, $sql);
    }
  }
  
  mysqli_query($SRBD, 'delimiter ;');
/*
  //zjisteni id posledne vlozeneho modulu
  $vysledek = mysqli_query($SRBD, "SELECT id from moduly ORDER BY id DESC LIMIT 1");
  $data = mysqli_fetch_array($vysledek);
  $posledniID = $data["id"];
  //vlozeni vychoziho admin. uctu k novemu modulu
  mysqli_query($SRBD, "INSERT INTO uzivatele (id,login, heslo, prava, id_modulu)
  VALUES (0, 'admin', '211a066003eaea06511874be3918417b75069ea7', 9, '$posledniID')");
*/
  //zabrani opetovnemu zaslani POST dat pri refreshi stranky
  header('Location: '.$soubory['moduly'], true, 303);
  exit;
}

function odebratModul($odstranovaneID) {
  global $texty;
  global $soubory;

  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD("sklad");
  }

  $dotaz = "SELECT modul FROM moduly WHERE id='$odstranovaneID'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek);
  $modul = $data["modul"] ?? "";
  
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    $dotaz = "DELETE FROM moduly WHERE id='$odstranovaneID'";
    mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['modulOdebratOK'];
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['modulOdebratChyba'];
  }

  $vysledek = mysqli_query($SRBD, "show databases like '$modul%'") or Die(mysqli_error($SRBD));
  While ($data = mysqli_fetch_array($vysledek)) {
    $dotaz = "drop database if exists `$data[0]`";
    mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    //echo $data[0];
  }

  header('Location: '.$soubory['moduly']);
  exit;
}
?>
