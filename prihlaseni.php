<?php

//
// Přihlášení nepřihlášeného uživatele
//

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

function kontrolaHesla($uzivatelskeJmeno, $prihlasovaciHeslo) {
   // kontrola hesla
   //
   // vstupní parametry:  $uzivatelskeJmeno
   //                     $prihlasovaciHeslo
   // návratová hodnota:  není
   // globální proměnné:
   global $texty;
   global $soubory;
   global $dotazy;

   $salt = substr($uzivatelskeJmeno, 0, 2); // dva první znaky ze jména pro zakódování hesla
   $zakodovaneHeslo = sha1($prihlasovaciHeslo.$salt); // zakódování hesla
   $SRBD = spojeniSRBD("sklad");
   $idModulu = dejIdModulu($_SESSION['modul']);
   
   if($uzivatelskeJmeno == "admin") {//u admina neni nutne kontrolovat id_modulu (tento ucet slouzi k prihlaseni do vsech modulu)
      $dotaz = "SELECT heslo FROM uzivatele WHERE login = '$uzivatelskeJmeno'  AND heslo = '$zakodovaneHeslo'";
      $vysledek = mysqli_query($SRBD, $dotaz);
   }
   else {
      $dotaz = "SELECT heslo FROM uzivatele WHERE login = '$uzivatelskeJmeno'  AND heslo = '$zakodovaneHeslo' AND id_modulu = '$idModulu'";
      $vysledek = mysqli_query($SRBD, $dotaz);
   }

   if (mysqli_num_rows($vysledek) == 1) { // právě jeden řádek je správné nalezení uživatele
      session_register('uzivatelskeJmeno'); // registrace uživatele indikuje přihlášení
      $_SESSION['uzivatelskeJmeno'] = $uzivatelskeJmeno;
      session_register('casPristupu'); // sem se uklada cas posledni uzivatelem provedene akce
      $_SESSION["casPristupu"] = time();
      $dotaz = "SELECT prava FROM uzivatele WHERE login = '$uzivatelskeJmeno'  AND heslo = '$zakodovaneHeslo'";
      $vysledek = mysqli_query($SRBD, $dotaz);
     	While ($data = mysqli_fetch_array($vysledek)) {
        $prava = $data["prava"];
      }
      session_register('uzivatelskaPrava');
      $_SESSION['uzivatelskaPrava'] = $prava;
      if (session_is_registered('promenneFormulare')) { // odregistrace případného kontextu proměnných formuláře
         session_unregister('promenneFormulare');
         } // if

      if (session_is_registered('referer')) { // je zaregistrován volající
        $referer=$_SESSION['referer'];
        session_unregister('referer');
        header('Location: '.$referer, true, 303); // návrat k volajícímu
        exit;
      } // je registrován volající
      else {
       header('Location: '.$soubory["hlavniStranka"], true, 303); // návrat na hlavní stránku
       exit;
      }

      } // if právě jeden řádek
   else  { // není právě jeden řádek - uživatel není registrován
      if (session_is_registered('uzivatelskeJmeno')) {  // odregistrace případného kontextu uživatele
        session_unregister('uzivatelskeJmeno');
        if (session_is_registered('casPristupu')) {
          session_unregister('casPristupu');
        }
      }
      session_register('hlaseniChyba'); // hlášení
      $_SESSION['hlaseniChyba'] = $texty['hesloSpatne'];
      prihlasovaciStranka();
      } // else
   } // of kontrolaHesla

function prihlasovaciStranka() {
  // zobrazení přihlašovací stránky
  //
  // vstupní parametry:  nejsou
  // návratová hodnota:  HTML
  // globální proměnné:
  global $texty;
  global $soubory;

  $SRBD = spojeniSRBD("sklad");
  uvodHTML("prihlaseniTitle", "admin");
?>
<h1><?php echo $texty['indexNadpis']; ?></h1>
<h2><?php echo $texty['prihlaseniNadpis']; ?></h2>
<form method="post" action="<?php echo $soubory['prihlaseni']; ?>">
<?php zobrazitHlaseni(); ?>
<fieldset>
  <label for="rok"><?php echo $texty['rok']; ?></label>
  <input type="text" size="20" maxlength="30" id="rok" name="rok" value="<?php echo date("Y"); ?>" /><br />
  <label for="db"><?php echo $texty['zadaniModulu']; ?></label>
  <select id="moduly" name="moduly" onchange="vyber_cv()">
  <option value="">-------- vyberte --------</option>
<?php
  $dotaz = "SELECT modul FROM moduly ORDER BY id ASC";
  $vysledek = mysqli_query($SRBD, $dotaz);
  While ($data = mysqli_fetch_array($vysledek)) {
    echo '<option value="'.$data['modul'].'" '. ($data['modul'] == "lmr" ? 'selected' : '') .'>'.$data['modul']."</option>\n";
  } //while
?>
  </select><br />
  <label for="loginUsername"><?php echo $texty['zadaniJmena']; ?></label>
  <input type="text" size="20" maxlength="30" id="loginUsername" name="loginUsername" /><br />
  <label for="loginPassword"><?php echo $texty['zadaniHesla']; ?></label>
  <input type="password" size="20" maxlength="10" id="loginPassword" name="loginPassword" /><br />
<?php echo dejTlacitko('','prihlaseniPotvrzeni');?>
  <input type="hidden" name="odeslano" />
</fieldset>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById("loginUsername");
    if(input) {
      input.focus();
    }
  });
</script>

<?php
  konecHTML();
} // of prihlasovaciStranka

// ------------------
session_start();
//session_unregister('modul');
//session_unregister('rokArchiv');
if (isset($_POST["moduly"])) {
  if($_POST["moduly"] != "") {
    $_SESSION['modul'] = $_POST["moduly"];
  }
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnyModul'];
    session_unregister('modul');
    //header('Location: '.$soubory["prihlaseni"], true, 303); // návrat k volajícímu
    //exit;
  }
} // if

if (isset($_POST["rok"])) {
  if (!session_is_registered('rokArchiv')) {
    session_register('rokArchiv');
  }
  $_SESSION['rokArchiv'] = odstraneniEscape($_POST["rok"],4);
}

if (isset($_POST["loginUsername"])) { // parametr uživatelské jméno přes POST
   $uzivatelskeJmenoUsch = odstraneniEscape($_POST["loginUsername"], 30);
   } // if
if (isset($_POST["loginPassword"])) { // heslo přes POST
   $prihlasovaciHeslo = odstraneniEscape($_POST["loginPassword"], 100);
   } // if

if (session_is_registered('uzivatelskeJmeno')) { // pokud je uživatel již přihlášen
   if (session_is_registered('referer')) { // je zaregistrován volající
      $referer=$_SESSION['referer'];
      session_unregister('referer');
      header('Location: '.$referer); // návrat k volajícímu
      exit;
      } // je registrován volající
  else { // standardní návrat na homepage
      header('Location: index.php');
      exit;
      } // else
  } // je již přihlášen

if ((empty($_POST["loginUsername"]) && !empty($_POST["loginPassword"])) ||
   (!empty($_POST["loginUsername"]) && empty($_POST["loginPassword"]))) {  // pouze jméno nebo pouze heslo
   session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['jmenoIHeslo'];
   } // if

if (!isset($uzivatelskeJmenoUsch) ||  // byla zde chyba nebo jsme ještě nezadali nic
   !isset($prihlasovaciHeslo) ||
   session_is_registered('hlaseniChyba')) {
   prihlasovaciStranka();
   } // if rekurze
else { // nebyla chyba
   kontrolaHesla($uzivatelskeJmenoUsch, $prihlasovaciHeslo);
   } // else

//pokud dojde az sem (i kdyz by nemel)
//header('Location: '.$soubory["hlavniStranka"]); // návrat na hlavní stránku
//exit;
?>
