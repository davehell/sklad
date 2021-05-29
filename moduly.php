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

$vysledek = mysqli_Query("SELECT id, modul FROM moduly ORDER BY id ASC", $SRBD) or Die(mysqli_Error());
if(mysqli_num_rows($vysledek) != 0) {
  $sudyRadek = false;
  $sloupce = array('','modul');
  echo '
<table>';
  printTableHeader($sloupce);

  While ($data = @mysqli_Fetch_Array($vysledek)) {
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
  if (!ereg("[a-zA-Z0-9]", $nazev)) { // jméno nesmí obsahovat bílé znaky
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

  mysqli_Query("INSERT INTO moduly (id,modul) VALUES (0, '$nazev')", $SRBD);

  if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novyModulDuplicitni'];
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novyModulOK'];
  }

  $nazev .= $_SESSION["rokArchiv"];
  mysqli_Query("create database `$nazev` COLLATE=latin2_czech_cs") or Die(mysqli_Error());
  mysqli_Select_Db($nazev, $SRBD) or Die(mysqli_Error());
  

  $query = "";
  $f = fopen("sql/novy_modul.sql", "r");
  while (!feof ($f)) {
    $query .= fgets($f, 4096);
  }
  fclose ($f);

  //vytvoreni potrebnych tabulek v databazi
  foreach (explode(';', $query) as $sql) {
    mysqli_query($sql, $SRBD);
  }
  
  $query = "";
  $f = fopen("sql/transakce_triggery.sql", "r");
  while (!feof ($f)) {
    $query .= fgets($f, 4096);
  }
  fclose ($f);
  
  mysqli_query('delimiter //', $SRBD);
  
  //vytvoreni potrebnych tabulek v databazi
  foreach (explode('//', $query) as $sql) {
    mysqli_query($sql, $SRBD);
  }
  
  mysqli_query('delimiter ;', $SRBD);
/*
  //zjisteni id posledne vlozeneho modulu
  $vysledek = mysqli_Query("SELECT id from moduly ORDER BY id DESC LIMIT 1", $SRBD);
  $data = @mysqli_Fetch_Array($vysledek);
  $posledniID = $data["id"];
  //vlozeni vychoziho admin. uctu k novemu modulu
  mysqli_Query("INSERT INTO uzivatele (id,login, heslo, prava, id_modulu)
  VALUES (0, 'admin', '211a066003eaea06511874be3918417b75069ea7', 9, '$posledniID')", $SRBD);
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

  $vysledek = mysqli_Query("SELECT modul FROM moduly WHERE id='$odstranovaneID'", $SRBD) or Die(mysqli_Error());
  $data = @mysqli_Fetch_Array($vysledek);
  $modul = $data["modul"];
  
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    mysqli_Query("DELETE FROM moduly WHERE id='$odstranovaneID'", $SRBD) or Die(mysqli_Error());
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['modulOdebratOK'];
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['modulOdebratChyba'];
  }

  $vysledek = mysqli_Query("show databases like '$modul%'", $SRBD) or Die(mysqli_Error());
  While ($data = mysqli_Fetch_Array($vysledek)) {
    mysqli_Query("drop database if exists `$data[0]`") or Die(mysqli_Error());
    //echo $data[0];
  }

  header('Location: '.$soubory['moduly']);
  exit;
}
?>
