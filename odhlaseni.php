<?php
header("Content-Type: text/html; charset=windows-utf8");
include 'iSkladObecne.php';

session_start();
if (session_is_registered('uzivatelskeJmeno')) { // je prihlasen
  //odstranenim session se provede odhlaseni uzivatele
  session_unregister('uzivatelskeJmeno');
  session_unregister('uzivatelskaPrava');
  session_unregister('rokArchiv');
  session_unregister('modul');
  if (session_is_registered('casPristupu')) {
    session_unregister('casPristupu');
  }
} // if
else { // není přihlášen
   session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['odhlaseniChyba'];
} // else

//parametr type=auto znamena, ze bylo automaticke odhlaseni...
//...tzn, nebude se zobrazovat hlaska "odhlaseni bylo uspesne"
if(!isset($_GET["type"])) {
  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['odhlaseniOK'];
}
else {
  if($_GET["type"] == "auto") {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['autoOdhlaseni'];
  }
  if($_GET["type"] == "spatnadb") {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnaDB'];
  }
/*
  if (!session_is_registered('referer'))
  { //po prihlaseni bude navrat na adresu, ktra zavolala autoodhlaseni
    session_register('referer');
  }
  $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
*/
}


header('Location: '.$soubory['prihlaseni']);
exit;

?>
