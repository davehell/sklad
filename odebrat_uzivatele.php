<?php
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ADMIN;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD("sklad");
}

if(isset($_GET["id"])) {
  $odstranovaneID = $_GET["id"];
  if($odstranovaneID == 1) { //pokus o smazani admina
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['odebraniAdmina'];
    header('Location: '.$soubory['uzivatele']);
    exit;
  }
}

$vysledek = MySQL_Query("SELECT login FROM uzivatele WHERE id='$odstranovaneID'", $SRBD) or Die(MySQL_Error());
if (mysql_num_rows($vysledek) == 1) { // v DB je uzivatel s ID, ktere chceme smazat
  MySQL_Query("DELETE FROM uzivatele WHERE id = '$odstranovaneID'", $SRBD) or Die(MySQL_Error());

  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['odebraniUzivateleOK'];
}
else { //ID, ktere chceme smazat, nebylo nalezeno v DB
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['odebraniUzivateleChyba'];
}
header('Location: '.$soubory['uzivatele']);
exit;


?>
