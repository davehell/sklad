<?php
/**
 * prodejni_ceny.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ADMIN;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

if(isset($_POST["upravit"])) {
  upravitKategorii($_POST);
}
elseif(isset($_POST["pridat"])) {
  pridatKategorii($_POST);
}
elseif(isset($_GET["odstranit"])) {
  odebratKategorii($_GET["odstranit"]);
}


uvodHTML("prodejniCeny");
echo '
<h1>'.$texty["prodejniCeny"].'</h1>';
zobrazitHlaseni();

if(isset($_GET["upravit"])) {
  $upravovaneId = odstraneniEscape($_GET["upravit"], 5);
  $vysledek3 = mysqli_Query("SELECT * FROM prodejni_kategorie WHERE id='$upravovaneId'", $SRBD) or Die(mysqli_Error());
  $data3 = mysqli_Fetch_Array($vysledek3);
}
else {
  $upravovaneId = "";
}


echo '
<h2>'.$texty["stavajiciKategorie"].'</h2>';

$vysledek = mysqli_Query("SELECT id, popis FROM prodejni_kategorie ORDER BY popis ASC", $SRBD) or Die(mysqli_Error());
if(mysqli_num_rows($vysledek) != 0) {
  $sudyRadek = false;
  $sloupce = array('','popis');
  echo '
<table>';
  printTableHeader($sloupce,"id=".$idZbozi);

  While ($data = @mysqli_Fetch_Array($vysledek)) {
    if($sudyRadek) {
      echo '
  <tr class="sudyRadek">';
    } //if sudyradek
    else {
      echo '
  <tr>';
    } //else
    echo '
    <td>
      <a href="'.$soubory["prodejniCeny"].'?odstranit='.$data["id"].'" title="'.$texty["odebratkategorieTitle"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odebrat"].'</a>
      &nbsp;&nbsp;&nbsp;
      <a href="'.$soubory["prodejniCeny"].'?upravit='.$data["id"].'" title="'.$texty["upravitkategorieTitle"].'" class="upravit">'.$texty["upravit"].'</a>
    </td>
    <td>'.$data["popis"].'</td>
  </tr>';
    $sudyRadek = !$sudyRadek;
  } //while
  echo '
</table>';
}
else {
  echo '
  <p>'.$texty["zadnaKategorie"].'</p>';
}

if(isset($_GET["upravit"])) {
  $formLegend = $texty['upravaKategorie'];
}
else {
  $formLegend = $texty['pridaniKategorie'];
}

echo '
<form method="post" action="'.$soubory['prodejniCeny'].'">
<fieldset>
<legend>'.$formLegend.'</legend>
<label for="popis">'.$texty['popis'].':</label>
<input type="text" maxlength="40" id="popis" name="popis" value="'.$data3["popis"].'" /><br />
<br />
<label for="odberatelNazev">'.$texty['odberatelNazev'].':</label>
<input type="text" name="odberatelNazev" value="'.$data3["nazev"].'" /><br />
<label for="odberatelUlice">'.$texty['odberatelUlice'].':</label>
<input type="text" name="odberatelUlice" value="'.$data3["ulice"].'" /><br />
<label for="odberatelMesto">'.$texty['odberatelMesto'].':</label>
<input type="text" name="odberatelMesto" value="'.$data3["mesto"].'" /><br />
<label for="odberatelIco">'.$texty['odberatelIco'].':</label>
<input type="text" name="odberatelIco" value="'.$data3["ico"].'" /><br />
<label for="odberatelDic">'.$texty['odberatelDic'].':</label>
<input type="text" name="odberatelDic" value="'.$data3["dic"].'" /><br />
<br />';
if(isset($_GET["upravit"])) {
  echo dejTlacitko('upravit','ulozit');
}
else {
  echo dejTlacitko('pridat','pridatKategorii');
}

echo '
<input type="hidden" name="odeslano" value="ano" />
<input type="hidden" name="id" value="'.$upravovaneId.'" />
</fieldset>
</form>';

konecHTML();


function pridatKategorii($udaje) {
  global $texty;
  global $soubory;

  $popis = odstraneniEscape($udaje["popis"], 30);
  $nazev = odstraneniEscape($udaje["odberatelNazev"], 70);
  $ulice = odstraneniEscape($udaje["odberatelUlice"], 70);
  $mesto = odstraneniEscape($udaje["odberatelMesto"], 70);
  $ico = odstraneniEscape($udaje["odberatelIco"], 15);
  $dic = odstraneniEscape($udaje["odberatelDic"], 15);

  //kontroly zadanych dat
  $korektniParametry = true;
  // nazev
  if (empty($popis)) { // nazev nesmi byt prazdny
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['prazdnyPopis'];
    $korektniParametry = false;
  }
  if (strlen($popis) > 30) { // popis nesmi byt delsi nez 30 znakù
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['popisNad30'];
    $korektniParametry = false;
  }

  if (! $korektniParametry)  { // byly chyby
    header('Location: '.$soubory['prodejniCeny']);
    exit;
  }

  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }

  mysqli_Query("INSERT INTO prodejni_kategorie (id,popis,nazev,mesto,ulice,ico,dic)
  VALUES (0, '$popis', '$nazev', '$ulice', '$mesto', '$ico', '$dic')", $SRBD);

  if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKategorieDuplicitni'];
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novaKategorieOK'];
  }

  //zabrani opetovnemu zaslani POST dat pri refreshi stranky
  header('Location: '.$soubory['prodejniCeny'], true, 303);
  exit;
}

function upravitKategorii($udaje) {
  global $texty;
  global $soubory;

  $popis = odstraneniEscape($udaje["popis"], 30);
  $nazev = odstraneniEscape($udaje["odberatelNazev"], 70);
  $ulice = odstraneniEscape($udaje["odberatelUlice"], 70);
  $mesto = odstraneniEscape($udaje["odberatelMesto"], 70);
  $ico = odstraneniEscape($udaje["odberatelIco"], 15);
  $dic = odstraneniEscape($udaje["odberatelDic"], 15);
  $upravovaneId = odstraneniEscape($udaje["id"], 5);

  //kontroly zadanych dat
  $korektniParametry = true;
  // nazev
  if (empty($popis)) { // nazev nesmi byt prazdny
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['prazdnyPopis'];
    $korektniParametry = false;
  }
  if (strlen($popis) > 30) { // popis nesmi byt delsi nez 30 znakù
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['popisNad30'];
    $korektniParametry = false;
  }

  if (! $korektniParametry)  { // byly chyby
    header('Location: '.$soubory['prodejniCeny']);
    exit;
  }

  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }

  mysqli_Query("UPDATE prodejni_kategorie SET
  popis='$popis', nazev='$nazev', ulice='$ulice', mesto='$mesto', ico='$ico', dic='$dic'
  WHERE id=$upravovaneId", $SRBD);

  if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKategorieDuplicitni'];
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['upravaKategorieOK'];
  }

  //zabrani opetovnemu zaslani POST dat pri refreshi stranky
  header('Location: '.$soubory['prodejniCeny'], true, 303);
  exit;
}//upravit


function odebratKategorii($odstranovaneID) {
  global $texty;
  global $soubory;
  
  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }

  $vysledek = mysqli_Query("SELECT id FROM prodejni_kategorie WHERE id='$odstranovaneID'", $SRBD) or Die(mysqli_Error());
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    mysqli_Query("DELETE FROM prodejni_kategorie WHERE id='$odstranovaneID'", $SRBD) or Die(mysqli_Error());
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['kategorieOdebratOK'];
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['kategorieOdebratChyba'];
  }

  header('Location: '.$soubory['prodejniCeny']);
  exit;
}
?>
