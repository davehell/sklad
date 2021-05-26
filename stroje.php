<?php
/**
 * stroje.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

if($_POST["odeslat"] == $texty["pridatStroj"]) {
  if(isset($_POST["nazev"])) $nazev = $_POST["nazev"];
  if(isset($_POST["cv"])) $cv = $_POST["cv"];
  pridatStroj($nazev, $cv);
}
elseif(isset($_GET["odstranit"])) {
  odebratStroj($_GET["odstranit"]);
}


uvodHTML("stroje");
echo '
<h1>'.$texty["stroje"].'</h1>';
zobrazitHlaseni();

//formular pro vyber stroje
echo '
<form method="post" action="'.$soubory['stroje'].'" class="noPrint">
<fieldset>
<legend>'.$texty['pridaniStroje'].'</legend>
<label for="nazev">'.$texty['nazev'].':</label>
<select id="nazev" name="nazev" onchange="vyber_cv()">
<option value="">---------- vyberte ----------</option>
';
  //prvni rozbalovaci seznam (nazev / rozmer)
  $vysledek = MySQL_Query("SELECT id, nazev FROM zbozi GROUP BY nazev", $SRBD) or Die(MySQL_Error());
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['nazev'];
  else $selected = '';

  While ($data = MySQL_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['nazev'].'"';
    if($data['nazev'] == $selected)
      echo ' selected';
    echo '>'.$data['nazev']."</option>\n";
  } //while
  echo '</select><br />
<label for="cv">'.$texty['cv'].':</label>
<select onchange="osetri_cv();" id="cv" name="cv">
<option value="">----- vyberte -----</option>
';
  //druhy rozbalovaci seznam (c. vykresu / jakost)
  $vysledek = MySQL_Query("SELECT id, c_vykresu FROM zbozi GROUP BY c_vykresu", $SRBD) or Die(MySQL_Error());
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['cv'];
  else $selected = '';

  While ($data = MySQL_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['c_vykresu'].'"';
    if($data['c_vykresu'] == $selected)
      echo ' selected';
    echo '>'.$data['c_vykresu']."</option>\n";
  } //while
  echo '</select>
<br />'.
dejTlacitko('odeslat','pridatStroj').'
<input type="hidden" name="id" value="'.$id.'" />';
echo '
</fieldset>
</form>
';

echo '
<h2>'.$texty["prehledStroju"].'</h2>';

$dotaz = "SELECT nazev, c_vykresu, S.id as id FROM stroje as S, zbozi as Z WHERE S.id_zbozi = Z.id";

if(mysql_num_rows($vysledek) != 0) {
  $sudyRadek = false;
  $sloupce = array('','nazev', 'c_vykresu');
  echo '
<table>';
  printTableHeader($sloupce,"id=".$idZbozi);

  $vysledek = MySQL_Query($dotaz, $SRBD) or Die(MySQL_Error());
  While ($data = @MySQL_Fetch_Array($vysledek)) {
    if($sudyRadek) {
      echo '
  <tr class="sudyRadek">';
    } //if sudyradek
    else {
      echo '
  <tr>';
    } //else
    echo '
    <td><a href="'.$soubory["stroje"].'?odstranit='.$data["id"].'" title="'.$texty["odebratStrojTitle"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odebrat"].'</a></td>
    <td>'.$data["nazev"].'</td>
    <td>'.$data["c_vykresu"].'</td>
  </tr>';
    $sudyRadek = !$sudyRadek;
  } //while
  echo '
</table>';
}
else {
  echo '
  <p>'.$texty["zadnyStroj"].'</p>';
}
konecHTML();



function pridatStroj($nazev, $cv) {
  global $texty;
  global $soubory;

  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }

  $vysledek = MySQL_Query("SELECT id FROM zbozi
  WHERE nazev='$nazev' AND c_vykresu='$cv'", $SRBD) or Die(MySQL_Error());
  if(mysql_num_rows($vysledek) == 1) {
    $data = @MySQL_Fetch_Array($vysledek);
    $idZbozi = $data["id"];
  }
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['stroje']);
    exit;
  }


  MySQL_Query("INSERT INTO stroje (id,id_zbozi) VALUES (0, '$idZbozi')", $SRBD);

  if (mysql_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novyStrojDuplicitni'];
  }
  else {
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novaKategorieOK'];
  }

  //zabrani opetovnemu zaslani POST dat pri refreshi stranky
  header('Location: '.$soubory['stroje'], true, 303);
  exit;
}

function odebratStroj($odstranovaneID) {
  global $texty;
  global $soubory;

  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }

  $vysledek = MySQL_Query("SELECT id FROM stroje WHERE id='$odstranovaneID'", $SRBD) or Die(MySQL_Error());
  if(mysql_num_rows($vysledek) == 1) { //vse v poradku
    MySQL_Query("DELETE FROM stroje WHERE id='$odstranovaneID'", $SRBD) or Die(MySQL_Error());
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['strojOdebratOK'];
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['strojOdebratChyba'];
  }

  header('Location: '.$soubory['stroje']);
  exit;
}
?>
