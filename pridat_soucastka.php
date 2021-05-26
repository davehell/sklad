<?php
/**
 * pridat_soucastka.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}

if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$nazev = $_SESSION['promenneFormulare']["nazev"];                //soucastka
$cv = $_SESSION['promenneFormulare']["cv"]; //cislo vykresu      //soucastka
$kusy = $_SESSION['promenneFormulare']["kusy"];                  //soucastka
$celek = odstraneniEscape($_POST["id"], 100);                    //celek

$vysledek = MySQL_Query("SELECT id FROM zbozi WHERE nazev='$nazev' AND cv_rozmer='$cv'", $SRBD) or Die(MySQL_Error());
While ($data = @MySQL_Fetch_Array($vysledek)) {
  $soucastka = $data["id"];
}

//kontroly
$korektniParametry = true;
//kusy
if ((!ereg("[0-9]", $kusy)) || // limit muze obsahovat pouze cislice
    ($kusy < 0)) { //a nesmi byt zaporny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['spatneKusy'];
  $korektniParametry = false;
}


if (! $korektniParametry)  { // byly chyby
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}



MySQL_Query("INSERT INTO sestavy (id, celek, soucastka, kusy) VALUES (0, '$celek', '$soucastka', '$kusy')", $SRBD);
if (mysql_errno() == 1582) { //vkladan duplicitni zaznam
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['soucastkaDuplicitni'];
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}
else { //vse v poradku
  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['soucastkaOK'];
  session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

?>
