<?php
/**
 * editace_uzivatele.php
 *
 * Udaje uzivatelu muze menit jen osoba s pravy Administratora.
 * Administrator nemuze menit uzivatelum pristupova jmena a hesla. Meni jen
 * jejich uzivatelska prava.
 * Soucasti dat odeslanych z formulare (<input type="hidden"...) musi byt id
 * uzivatele, jehoz udaje se budou menit.
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ADMIN;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD("sklad");
} // if

if(isset($_POST["id"])) {
  $id = $_POST["id"]; //id uzivatele, jehoz udaje se budou menit
}

// uzivatelska prava
if (isset($_POST['loginRights'])) {
  //uzivatelska prava nastavena ve formulari
  if($_POST['loginRights'] == "Administrátor") {
    $loginRights = ADMIN;
  }
  else {
    $loginRights = ZAMESTNANEC;
  }
}
  
//aktualizace udaju (uzivatelskych prav)
MySQL_Query("UPDATE uzivatele SET prava='$loginRights' WHERE id='$id'", $SRBD) or Die(MySQL_Error());
session_register('hlaseniOK');
$_SESSION['hlaseniOK'] = $texty['editOK'];
header('Location: '.$soubory['uzivatele']);
exit;
?>
