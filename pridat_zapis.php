<?php
/**
 * pridat_zapis.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();


if (!session_is_registered('promenneFormulare')) { // mus� existovat registrace kontextu prom�nn�ch formul��e
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // prom�nn� formul��e jsou p�ed�v�ny p�es POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$datum = $_SESSION['promenneFormulare']["datum"];
$cDokladu = $_SESSION['promenneFormulare']["cDokladu"];
$skupina = $_SESSION['promenneFormulare']["skupina"];
$prodejniCena = $_SESSION['promenneFormulare']["prodejniCena"];


if (!isset($SRBD)) { // u� jsme p�ipojeni k datab�zi
  $SRBD=spojeniSRBD();
}


/*******************************************************************************
 * vkladani NOVE karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["pridatKartu"]) { //vkladani NOVEHO zajezdu
  MySQL_Query("INSERT INTO zbozi (id, nazev, c_vykresu, jednotka, min_limit, cena_prace)
  VALUES (0, '$nazev', '$cv', '$jednotka', '$limit', '$cenaPrace')", $SRBD);

  if (mysql_errno() == 1582) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['novaKarta']);
    exit;
  }
  else {
    $vysledek = MySQL_Query("SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'", $SRBD) or Die(MySQL_Error());
    While ($data = @MySQL_Fetch_Array($vysledek)) {
      $id = $data["id"];
    }
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novaKartaOK'];
    session_unregister('promenneFormulare');  // zru�en� kontextu formul��e
    header('Location: '.$soubory['novaKarta'].'?id='.$id);
    exit;
  }

} //if pridatKartu

/*******************************************************************************
 * uprava STAVAJICI karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["ulozitZmeny"]) {
  MySQL_Query("UPDATE zbozi SET nazev='$nazev', c_vykresu='$cv', jednotka='$jednotka', min_limit='$limit', cena_prace='$cenaPrace' WHERE id='$id'", $SRBD);
  if (mysql_errno() == 1582) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['editOK'];
    session_unregister('promenneFormulare');  // zru�en� kontextu formul��e
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
} // if $texty["ulozitZmeny"]


/*******************************************************************************
 * odstraneni karty
 ******************************************************************************/
if(isset($_GET["odebrat"])) {
  $odstranovaneID = $_GET["odebrat"];

  $vysledek = MySQL_Query("SELECT id FROM zbozi WHERE id='$odstranovaneID'", $SRBD) or Die(MySQL_Error());
  if(mysql_num_rows($vysledek) == 1) { //vse v poradku
    MySQL_Query("DELETE FROM zbozi WHERE id='$odstranovaneID'", $SRBD) or Die(MySQL_Error());
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['kartaOdebratOK'];
    session_unregister('promenneFormulare');  // zru�en� kontextu formul��e
    header('Location: '.$soubory['upravitKarta']);
    exit;
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
}
?>
