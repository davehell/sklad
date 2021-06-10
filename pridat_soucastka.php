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

$dotaz = "SELECT id FROM zbozi WHERE nazev='$nazev' AND cv_rozmer='$cv'";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
While ($data = @mysqli_fetch_array($vysledek)) {
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


$dotaz = "INSERT INTO sestavy (id, celek, soucastka, kusy) VALUES (0, '$celek', '$soucastka', '$kusy')";
mysqli_query($SRBD, $dotaz);
if (mysqli_errno($SRBD) == 1582) { //vkladan duplicitni zaznam
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
