<?php
/**
 * archiv.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

//byl odeslan formular s vytvorenim noveho rocniku
//if($_POST["odeslat"] == $texty["zalozitRocnik"]) {
if($_POST) {
  if(isset($_POST["rok"])) $rok = $_POST["rok"];
  
  //kontroly zadanych dat
  $korektniParametry = true;
  // nazev
  if (!preg_match("/[0-9]/", $rok)) {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnyRok'];
    $korektniParametry = false;
  }
  if (empty($rok)) { // rok nesmi byt prazdny
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnyRok'];
    $korektniParametry = false;
  }
  if (! $korektniParametry)  { // byly chyby
    header('Location: '.$soubory['archiv']);
    exit;
  }
  
  //vytvoreni nove db
  $db = $_SESSION["modul"].$rok;
  $dotaz = "create database `$db` COLLATE=latin2_czech_cs";
  mysqli_query($SRBD, $dotaz);
  if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novyRokDuplicitni'];
    header('Location: '.$soubory['archiv'], true, 303);
    exit;
  }
  mysqli_select_db($SRBD, $db) or Die("query 1:" . mysqli_error($SRBD));

  //nacte ze souboru sql skript pro vytvoreni tabulek
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


  //zkopirovani potrebnych dat ze stare db
  $soubor=$_SERVER["DOCUMENT_ROOT"]."/sql/data.txt";
  $tabulky = array('zbozi', 'sestavy', 'stroje', 'prodejni_kategorie', 'prodejni_ceny', 'koeficienty');
  foreach($tabulky as $tab) {
    if(file_exists($soubor)) unlink($soubor);
    $stareSRBD=spojeniSRBD($_SESSION["modul"].$_SESSION["rokArchiv"]);
    $dotaz = "SELECT * FROM $tab INTO OUTFILE '".$soubor."'";
    // echo $dotaz . "<br>";
    mysqli_query($stareSRBD, $dotaz, MYSQLI_USE_RESULT) or Die("query 2:" . mysqli_error($stareSRBD)); //returns a mysqli_result object with unbuffered result set

    $noveSRBD=spojeniSRBD($_SESSION["modul"].$rok);
    $dotaz = "LOAD DATA INFILE '".$soubor."' INTO TABLE $tab";
    // echo $dotaz . "<br>";
    mysqli_query($noveSRBD, $dotaz, MYSQLI_USE_RESULT) or Die("query 3:" . mysqli_error($noveSRBD)); //returns a mysqli_result object with unbuffered result set
    if($tab == "zbozi") {
      $dotaz = "UPDATE zbozi SET mnozstvi=0";
      mysqli_query($noveSRBD, $dotaz) or Die("query 4:" . mysqli_error($noveSRBD));
    }
  }

  //pridani procedur
  mysqli_query($SRBD, "SET NAMES 'latin2';");
  mysqli_query($SRBD, 'delimiter //');
  $query2 = "";
  $f = fopen("sql/transakce_triggery.sql", "r");
  while (!feof ($f)) {
    $query2 .= fgets($f, 4096);
  }
  fclose ($f);

  foreach (explode('//', $query2) as $dotaz) {
    if(strlen($dotaz) > 0) {
      // echo $dotaz . "<br>";
      mysqli_query($SRBD, $dotaz) or Die("query 5:" . mysqli_error($SRBD));
    }
  }
  mysqli_query($SRBD, 'delimiter ;');


  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['novyArchivOK'];

  //zabrani opetovnemu zaslani POST dat pri refreshi stranky
  header('Location: '.$soubory['archiv'], true, 303);
  exit;
}//if($_POST["odeslat"] == $texty["zalozitRocnik"]) {



//ulozeni roku, se kterym se bude pracovat, do seesion
if(isset($_GET["rok"])) {
  if (!session_is_registered('rokArchiv')) {
    session_register('rokArchiv');
  } // if
  $_SESSION['rokArchiv'] = $_GET["rok"];

  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['archivOK'];
  header('Location: '.$soubory['hlavniStranka']);
  exit;
}

//formular pro zadani noveho rocniku
uvodHTML("archiv");
echo '
<h1>'.$texty["archiv"].'</h1>';
zobrazitHlaseni();

 echo '
<form method="post" action="'.$soubory['archiv'].'">
<fieldset>
<legend>'.$texty['novyArchiv'].'</legend>
<p>Do nového roèníku budou pøenesena data z roku <strong>'.$_SESSION["rokArchiv"].'</strong></p>
<br />
<label for="rok">'.$texty['rok'].'</label>
<input type="text" maxlength="40" id="rok" name="rok" />
<br />'.
dejTlacitko('odeslat','zalozitRocnik').'
</fieldset>
</form>';

konecHTML();

/*
else {
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['archivChyba'];
  header('Location: '.$soubory['hlavniStranka']);
  exit;
}
*/
?>
