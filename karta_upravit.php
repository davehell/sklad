<?php
/**
 * karta_upravit.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

//session_unregister("promenneFormulare");

if((isset($_POST["nazev"])) && ($_POST["nazev"] == "Vyberte")) {
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['vyberNazev'];
}


//zpracovani formulare pro vyber konkretni karty
if(isset($_POST["odeslat"]) && $_POST["odeslat"] == $texty["zobrazitKartu"]) {
  if(isset($_POST["nazev"])) $nazev = $_POST["nazev"];
  if(isset($_POST["cv"])) $cv = $_POST["cv"];

  $dotaz = "SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  if(mysqli_num_rows($vysledek) == 1) {
    While ($data = mysqli_fetch_array($vysledek)) {
      $id = $data["id"];
    }
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['upravitKarta']);
    exit;
  }
}

//kontrola, jestli existuje karta s id, ktere je hodnotou parametru
if(isset($_GET["id"])) {
  $id = $_GET["id"];

  $dotaz = "SELECT nazev, c_vykresu, jednotka, min_limit, cena_prace FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));

  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    While ($data = mysqli_Fetch_Array($vysledek)) {
      if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
        session_register('promenneFormulare');
      }
        $_SESSION['promenneFormulare']['nazev_input'] = $data["nazev"];
        $_SESSION['promenneFormulare']['cv_input'] = $data["c_vykresu"];
        $_SESSION['promenneFormulare']['jednotka'] = $data["jednotka"];
        $_SESSION['promenneFormulare']['limit'] = $data["min_limit"];
        $_SESSION['promenneFormulare']['cenaPrace'] = $data["cena_prace"];
        
        $dotaz = "SELECT cena, id_kategorie FROM prodejni_ceny WHERE id_zbozi='$id' ORDER BY id_kategorie ASC";
        $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
        While ($data2 = mysqli_fetch_array($vysledek2)) {
          $_SESSION['promenneFormulare']['prodejniCena'.$data2['id_kategorie']] = $data2['cena'];
        }
      

     }//while

  }//if
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['upravitKarta']);
    exit;
  }
}//if

uvodHTML("upravitKartuNadpis");
echo
'<h1>'.$texty["upravitKartuNadpis"].'</h1>';
zobrazitHlaseni();

//Nebyla vybrana konkretni karta, tedy neni nastaven parametr id, tedy bude
//zobrazen formular pro vyber karty
if(!isset($_GET["id"])) {
echo '
<form method="post" action="'.$soubory['upravitKarta'].'" class="noPrint">
<fieldset>
<legend>'.$texty['vybratKartu'].'</legend>
<p>'.$texty["vyberKarty"].'</p><br />

<input id="nazevAutoComplete" type="search" autocomplete="off"><br />
<label for="nazev">'.$texty['nazev'].':</label>
<select id="nazev" name="nazev" onchange="vyber_cv()">
<option value="">---------- vyberte ----------</option>
';
  //prvni rozbalovaci seznam (nazev / rozmer)
  $dotaz = "SELECT id, nazev FROM zbozi GROUP BY nazev";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['nazev'];
  else $selected = '';

  While ($data = mysqli_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['nazev'].'"';
    if($data['nazev'] == $selected)
      echo ' selected';
    echo '>'.$data['nazev']."</option>\n";
  } //while
  echo '</select><br />

<input id="cvAutoComplete" type="search" autocomplete="off"><br />
<label for="cv">'.$texty['cv'].':</label>
<select onchange="osetri_cv();" id="cv" name="cv">
<option value="">----- vyberte -----</option>
';
  //druhy rozbalovaci seznam (c. vykresu / jakost)
  $dotaz = "SELECT id, c_vykresu FROM zbozi GROUP BY c_vykresu";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['cv'];
  else $selected = '';

  While ($data = mysqli_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['c_vykresu'].'"';
    if($data['c_vykresu'] == $selected)
      echo ' selected';
    echo '>'.$data['c_vykresu']."</option>\n";
  } //while
  echo '</select><br />
<br />'.
dejTlacitko('odeslat','zobrazitKartu').'
<input type="hidden" name="id" value="'.$id.'" />
<input type="hidden" name="odeslano" value="ano" />';

echo '
</fieldset>
</form>
<script>
  const autoCompleteForNazev = new autoComplete(getAutocompleteConfig("nazev"));
  const autoCompleteForCv = new autoComplete(getAutocompleteConfig("cv"));
</script>
';
}//if(!isset($_GET["id"]))

//Byla vybrana konkretni karta, tedy je nastaven parametr id, tedy bude
//zobrazena vybrana karta
if(isset($_GET["id"])) {
  echo'
<p>
  <a href="'.$soubory["upravitKarta"].'" class="hledat">'.$texty["vybratJinouKartu"].'</a>
</p>
<p>
  <a href="'.$soubory["frmPridatKarta"].'?odebrat='.$_GET["id"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odstranitKartu"].'</a>
</p>


<form method="post" action="'.$soubory['frmPridatKarta'].'">
<fieldset>
<legend>'.$texty['infoKarta'].'</legend>

<br />
<label for="nazev_input">'.$texty['nazev'].':</label>
<input type="text" maxlength="40" id="nazev_input" name="nazev_input" value="'.$_SESSION['promenneFormulare']['nazev_input'].'" /><br />
<label for="cv_input">'.$texty['cv'].':</label>
<input type="text" maxlength="40" id="cv_input" name="cv_input" value="'.$_SESSION['promenneFormulare']['cv_input'].'" /><br />
<label for="jednotka">'.$texty['jednotka'].':</label>
<select id="jednotka" name="jednotka">
  <option>kus</option>
  <option>metr</option>
  <option>kilogram</option>
</select><br />
<label for="limit">'.$texty['limit'].':</label>
<input type="text" maxlength="40" id="limit" name="limit" value="'.$_SESSION['promenneFormulare']['limit'].'" /><br />
<label for="cenaPrace">'.$texty['cenaPrace'].':</label>
<input type="text" maxlength="40" id="cenaPrace" name="cenaPrace" value="'.$_SESSION['promenneFormulare']['cenaPrace'].'" /><br />
';

echo '
<fieldset>
<legend>'.$texty['prodejniCeny'].'</legend>
<button id="btnProdejniCeny" type="button">'.$texty['zobrazit'].' / '.$texty['skryt'].'</button>
<div id="listProdejniCeny">
';
if($_SESSION['uzivatelskaPrava'] > ZAMESTNANEC)
{ //zamestnanci prodejni ceny neuvidi
  //vypsani textovych poli pro vsechny prodejni ceny
  $dotaz = "SELECT id, popis FROM prodejni_kategorie ORDER BY id";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  While ($data = mysqli_fetch_array($vysledek)) {
    $idProdejni = $data["id"];
    echo '
<label for="prodejniCena'.$idProdejni.'">'.$data["popis"].':</label>
<input type="text" maxlength="40" id="prodejniCena'.$idProdejni.'" name="prodejniCena'.$idProdejni.'" value="'. (isset($_SESSION['promenneFormulare']['prodejniCena'.$idProdejni]) ? $_SESSION['promenneFormulare']['prodejniCena'.$idProdejni] : "") .'" /><br />
';
  }//while
}
echo '
</div> <!-- #listProdejniCeny -->
</fieldset>
';

echo '
<br />'.
dejTlacitko('odeslat','ulozitZmeny').'
<input type="hidden" name="id" value="'.$_GET["id"].'" />
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';
/////////////////////////
//formular pro nahrani obrazku
/////////////////////////
echo '
<form method="post" action="'.$soubory['frmNovyObrazek'].'" enctype="multipart/form-data">
<fieldset>
<legend>'.$texty['obrazek'].'</legend>';

$dotaz = "SELECT obrazek FROM zbozi WHERE id='$id' AND obrazek is not NULL";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
if(mysqli_num_rows($vysledek) == 0) { //zbozi nema v DB zadnou fotku
  echo '
  <p>Toto zbo¾í nemá pøiøazen ¾ádný obrázek.</p>';
}
else {
  While ($data = mysqli_fetch_array($vysledek)) {
    echo '
<p>Souèasný obrázek: '.$data["obrazek"].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$soubory["frmNovyObrazek"].'?odstranit='.$id.'" class="odebrat" title="'.$texty["odebratObrazekTitle"].'" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odebrat"].'</a></p>
<br />
<a href="nahledy/'.$data["obrazek"].'" alt="'.$data["obrazek"].'"
   title="'.$_SESSION['promenneFormulare']['nazev_input'].' '.$_SESSION['promenneFormulare']['cv_input'].'"
   rel="lightbox">
  <img src="nahledy/thumb_'.$data["obrazek"].'" alt="'.$data["obrazek"].'" />
</a>

<br />';
  }
}

echo '
<br />
<label for="jmeno_souboru">Nahrát nový obrázek zbo¾í:</label>
<input type="file" maxlength="40" id="jmeno_souboru" name="jmeno_souboru" accept="image/jpeg" enctype="multipart/form-data" /><br />
<br />'.
dejTlacitko('odeslat','upload').'
<input type="hidden" name="id" value="'.$id.'" />
</fieldset>
</form>';



echo '
<a name="soucasti-vyrobku"></a>
<form method="post" action="'.$soubory['frmPridatSoucastka'].'">
<fieldset>
<legend>'.$texty['vyrobenoZ'].'</legend>
<p>'.$texty["vyberteSoucastky"].'</p><br />

<input id="nazevAutoComplete" type="search" autocomplete="off"><br />
<label for="nazev">'.$texty['nazev'].':</label>
<select id="nazev" name="nazev" onchange="vyber_cv()">
<option value="">---------- vyberte ----------</option>
';
  //prvni rozbalovaci seznam (nazev / rozmer)
  $dotaz = "SELECT id, nazev FROM zbozi WHERE id<>'$id' GROUP BY nazev";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['nazev'];
  else $selected = '';

  While ($data = mysqli_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['nazev'].'"';
    if($data['nazev'] == $selected)
      echo ' selected';
    echo '>'.$data['nazev']."</option>\n";
  } //while
  echo '</select><br />

<input id="cvAutoComplete" type="search" autocomplete="off"><br />
<label for="cv">'.$texty['cv'].':</label>
<select onchange="osetri_cv();" id="cv" name="cv">
<option value="">----- vyberte -----</option>
';
  //druhy rozbalovaci seznam (c. vykresu / jakost)
  $dotaz = "SELECT id, c_vykresu FROM zbozi GROUP BY c_vykresu";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['cv'];
  else $selected = '';

  While ($data = mysqli_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['c_vykresu'].'"';
    if($data['c_vykresu'] == $selected)
      echo ' selected';
    echo '>'.$data['c_vykresu']."</option>\n";
  } //while
  echo '</select><br />
<label for="mnozstvi">'.$texty['kusy'].':</label>
<input type="text" maxlength="40" id="mnozstvi" name="mnozstvi" />
<br />'.
  dejTlacitko('odeslat','pridatSoucastku').'
<br />';

echo '
<hr />
<input type="hidden" name="id" value="'.$id.'" />';

//tabulka se soucastkami, ze kterych se vyrobek sklada
vypisSoucastky($id, "uprava");


  echo '
</fieldset>
</form>
<script>
  const autoCompleteForNazev = new autoComplete(getAutocompleteConfig("nazev"));
  const autoCompleteForCv = new autoComplete(getAutocompleteConfig("cv"));
</script>
';


}//if


if(session_is_registered('promenneFormulare')) {
  session_unregister('promenneFormulare');
}
konecHTML();
?>
