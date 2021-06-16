<?php
/**
 * novy_uzivatel.php
 *
 * Pri vlozeni noveho uzivatele nebo pri uprave profilu se musi kontrolovat
 * stejne polozky (jmeno, heslo). Proto jsou tyto dve cinnosti ve stejnem
 * skriptu.
 *
 * - Provede se kontrola zadanych dat.
 * - Zakoduje se heslo.
 * - Podle tlacitka, kterym byl formular odeslan, se provede vlozeni noveho nebo
 *   uprava stavajiciho uzivatele.
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();


//uziv. jmeno
if (isset($_POST['loginUsername'])) { // promenna loginUsername je zadana
  $loginUsername = odstraneniEscape($_POST['loginUsername'], 100);
}
//heslo
if (isset($_POST['loginPassword'])) { // promenna loginPassword je zadana
  $loginPassword = odstraneniEscape($_POST['loginPassword'], 100);
}
//heslo znovu
if (isset($_POST['loginPassword2'])) { // promenna loginPassword2 je zadana
  $loginPassword2 = odstraneniEscape($_POST['loginPassword2'], 100);
}
// uzivatelska prava
if (isset($_POST['loginRights'])) {
  if($_POST['loginRights'] == "Administrátor") {
    $loginRights = ADMIN;
  }
  else {
    $loginRights = ZAMESTNANEC;
  }
} // if

//kontroly
$korektniParametry = true;
// uzivatelske jmeno

if (!preg_match('/^[a-zA-Z0-9_]+$/', $loginUsername)) {// jméno nesmí obsahovat bílé znaky
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['deraveJmeno'];
  $korektniParametry = false;
} // elseif

elseif (strlen($loginUsername) < 3) { // jméno nesmí být kratsi nez 3 znaky
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['jmenoPod3'];
  $korektniParametry = false;
} // elseif
elseif (strlen($loginUsername) > 30) { // jméno nesmí být del¹í, ne¾li 30 znakù
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['jmenoNad30'];
  $korektniParametry = false;
} // elseif

// heslo
if($loginPassword != $loginPassword2) { //obe hesla nejsou stejna
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['wrongPassword'];
  $korektniParametry = false;
}
if ((strlen($loginPassword) > 100) ||
    (strlen($loginPassword)  <  4)) {
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['formatPassword']; // chybné heslo
  $korektniParametry = false;
}


if (! $korektniParametry)  {    // parametry nebyly naèteny korektnì
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}  // if !korektni parametry

//zakodovani hesla
$salt = substr($loginUsername, 0, 2); // dva první znaky ze jména pro zakódování hesla
$zakodovaneHeslo = sha1($loginPassword.$salt); // zakódování hesla

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD("sklad");
} // if


/*******************************************************************************
 * vkladani NOVEHO uzivatele
 ******************************************************************************/
if($_POST["odeslat"] == $texty["pridat"]) { //vkladani NOVEHO uzivatele

  $idModulu = dejIdModulu($_SESSION['modul']);
  mysqli_query($SRBD, "INSERT INTO uzivatele (id, login, heslo, prava, id_modulu)
  VALUES (0, '$loginUsername', '$zakodovaneHeslo', $loginRights, $idModulu)");

  if(mysqli_errno($SRBD) != 0) { //uzivatelem se stejnym loginem uz v modulu je
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['duplicitniLogin'];
    header('Location: '.$soubory['uzivatele']);
    exit;
  }
  else { //uzivatel uspesne pridan
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novyUzivatelOK'];
    header('Location: '.$soubory['uzivatele']);
    exit;
  }
}

/*******************************************************************************
 * uprava profilu
 * prihlasenemu uzivateli se meni login, heslo
 ******************************************************************************/
if($_POST["odeslat"] == $texty["ulozitZmeny"]) {
  $puvodniLogin = $_SESSION["uzivatelskeJmeno"]; //zjisteni loginu prihlaseneho uzivatele

  if($loginUsername != $puvodniLogin) { //uzivatel si zmenil login
  //je treba se podivat, jestli uz se novy login v DB nevyskytuje
    $vysledek = mysqli_query($SRBD, "SELECT login FROM uzivatele WHERE login = '$loginUsername'");  // provézt dotaz
    if (mysqli_num_rows($vysledek) == 1) { // v DB uz je uzivatel se stejnym loginem
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['duplicitniLogin'];
      header('Location: '.$_SERVER['HTTP_REFERER']);
      exit;
    }
  }

  //aktualizace udaju v profilu
  mysqli_query($SRBD, "UPDATE uzivatele SET login='$loginUsername', heslo='$zakodovaneHeslo' WHERE login='$puvodniLogin'") or Die(mysqli_error($SRBD));
  $_SESSION["uzivatelskeJmeno"] = $loginUsername; //aktualizuje se login prihlaseneho uzivatele
  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['editOK'];
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

?>
