<?php
/**
 * pridat_zapis.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();


if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$datum = $_SESSION['promenneFormulare']["datum"];
$cDokladu = $_SESSION['promenneFormulare']["cDokladu"];
$skupina = $_SESSION['promenneFormulare']["skupina"];
$prodejniCena = $_SESSION['promenneFormulare']["prodejniCena"];


if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}


/*******************************************************************************
 * vkladani NOVE karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["pridatKartu"]) { //vkladani NOVEHO zajezdu
  $dotaz = "INSERT INTO zbozi (id, nazev, c_vykresu, jednotka, min_limit, cena_prace) VALUES (0, '$nazev', '$cv', '$jednotka', '$limit', '$cenaPrace')";
  mysqli_query($SRBD, $dotaz);

  if (mysqli_errno($SRBD) == 1582) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['novaKarta']);
    exit;
  }
  else {
    $dotaz = "SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    While ($data = @mysqli_fetch_array($vysledek)) {
      $id = $data["id"];
    }
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novaKartaOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$soubory['novaKarta'].'?id='.$id);
    exit;
  }

} //if pridatKartu

/*******************************************************************************
 * uprava STAVAJICI karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["ulozitZmeny"]) {
  $dotaz = "UPDATE zbozi SET nazev='$nazev', c_vykresu='$cv', jednotka='$jednotka', min_limit='$limit', cena_prace='$cenaPrace' WHERE id='$id'";
  mysqli_query($SRBD, $dotaz);
  if (mysqli_errno($SRBD) == 1582) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['editOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
} // if $texty["ulozitZmeny"]


/*******************************************************************************
 * odstraneni karty
 ******************************************************************************/
if(isset($_GET["odebrat"])) {
  $odstranovaneID = $_GET["odebrat"];
  $dotaz = "SELECT id FROM zbozi WHERE id='$odstranovaneID'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    $dotaz = "DELETE FROM zbozi WHERE id='$odstranovaneID'";
    mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['kartaOdebratOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
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
