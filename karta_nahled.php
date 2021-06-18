<?php
/**
 * karta_nahled.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();


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
    header('Location: '.$soubory['nahledKarta'].'?id='.$id);
    exit;
  }
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['nahledKarta']);
    exit;
  }
}

//kontrola, jestli existuje karta s id, ktere je hodnotou parametru
if(isset($_GET["id"])) {
  $id = $_GET["id"];
  $dotaz = "SELECT nazev, c_vykresu FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  if(mysqli_num_rows($vysledek) != 1) { //zadane id neni v DB
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['nahledKarta']);
    exit;
  }
  else {//pozadovana karta se nachazi v DB
    $data = mysqli_fetch_array($vysledek);
    $nazev = $data["nazev"];
    $cv = $data["c_vykresu"];
  }
}


uvodHTML("nahledKartaNadpis");
echo '
<h1>'.$texty["nahledKartaNadpis"].'</h1>';
zobrazitHlaseni();
if(isset($_GET["id"])) {
echo '
<p>
  <a href="'.$soubory["nahledKarta"].'" class="hledat">'.$texty["vybratJinouKartu"].'</a>
</p>
<p>
  <a href="'.$soubory["upravitKarta"].'?id='.$_GET["id"].'" class="upravit">'.$texty["upravitKartu"].'</a>
</p>
';
}
else {
//formular pro vyber karty
echo '
<form method="post" action="'.$soubory['nahledKarta'].'" class="noPrint">
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
<select id="cv" name="cv" onchange="osetri_cv();">
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
<input type="hidden" name="id" value="'. (isset($id) ? $id : "") .'" />
<input type="hidden" name="odeslano" value="ano" />';

echo '
</fieldset>
</form>
<script>
  const autoCompleteForNazev = new autoComplete(getAutocompleteConfig("nazev"));
  const autoCompleteForCv = new autoComplete(getAutocompleteConfig("cv"));
</script>
';
}//else if(isset($_GET["id"]))

//Byla vybrana konkretni karta, tedy je nastaven parametr id, tedy bude
//zobrazen vybrana karta

//////////////////////
// prvni dl
if(isset($_GET["id"])) {
  $id = $_GET["id"];
  $dotaz = "SELECT nazev, c_vykresu, jednotka, min_limit, cena_prace, mnozstvi FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek);
  if($data["mnozstvi"] == "") $data["mnozstvi"] = "0";
  
  echo '
<dl class="floatleft">
  <dt>'.$texty["nazev"].':</dt><dd>'.$data["nazev"].'</dd>
  <dt>'.$texty["cv"].':</dt><dd>'.$data["c_vykresu"].'</dd>
  <dt>'.$texty["jednotka"].':</dt><dd>'.$data["jednotka"].'</dd>
  <dt>'.$texty["mnozstvi"].':</dt><dd>'.number_format($data["mnozstvi"], 3, ".", " ").'</dd>
  <dt>'.$texty["limit"].':</dt><dd>'.$data["min_limit"].'</dd>
</dl>';


//////////////////////
// obrazek
  $vysledek = mysqli_query($SRBD, "SELECT obrazek FROM zbozi WHERE id='$id' AND obrazek is not NULL") or Die(mysqli_error($SRBD));
  if(mysqli_num_rows($vysledek) == 0) { //zbozi nema v DB zadnou fotku
    echo '
<p class="floatleft">Toto zbo¾í nemá pøiøazen ¾ádný obrázek.</p>
<br class="clearleft" />';
  } //if
  else {
    While ($data = mysqli_fetch_array($vysledek)) {
      echo '
<a href="nahledy/'.$data["obrazek"].'" alt="'.$data["obrazek"].'"
   title="'.$nazev.' '.$cv.'"
   rel="lightbox">
  <img class="floatleft" src="nahledy/thumb_'.$data["obrazek"].'" alt="'.$data["obrazek"].'" />
</a>
<br class="clearleft" />';
    } //while
  } //else

//////////////////////
// druhy dl
  $dotaz = "SELECT nazev, c_vykresu, jednotka, min_limit, cena_prace, mnozstvi, prum_cena FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek);
  $celkovaCena = $data["mnozstvi"]*$data["prum_cena"];
  if($data["prum_cena"] == "") $data["prum_cena"] = "-";
  
    echo '
<dl class="floatleft">
  <dt>'.$texty["cenaPrace"].':</dt><dd class="alignRight">'.number_format($data["cena_prace"], 2, ".", " ").'</dd>
  <dt>cena materiálu:</dt><dd class="alignRight">'.number_format(spocitatCenuMaterialu($id), 2, ".", " ").'</dd>
  <dt>prùmìrná cena:</dt><dd class="alignRight">'. ($data["prum_cena"] == "-" ? $data["prum_cena"] : number_format($data["prum_cena"], 2, ".", " ")) .'</dd>
  <dt>celková cena:</dt><dd class="alignRight">'.number_format($celkovaCena, 2, ".", " ").'</dd>
</dl>';

//////////////////////
// prodejni ceny
if($_SESSION['uzivatelskaPrava'] > ZAMESTNANEC)
{ //zamestnanci prodejni ceny neuvidi
echo '
<div class="floatleft">
<strong>'.$texty['prodejniCeny'].'</strong><br>
<button id="btnProdejniCeny" type="button">'.$texty['zobrazit'].' / '.$texty['skryt'].'</button>
<dl id="listProdejniCeny" class="kartaNahled">
';
  //vypsani vsech prodejnich cen
  $dotaz = "SELECT id, popis FROM prodejni_kategorie ORDER BY id";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  While ($data = mysqli_fetch_array($vysledek)) {
    $idKat = $data["id"];
    $cena = "-";
    $dotaz = "SELECT cena FROM prodejni_ceny WHERE id_zbozi='$id' AND id_kategorie='$idKat'";
    $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    While ($data2 = mysqli_fetch_array($vysledek2)) {
      if($data2["cena"] == "") {$cena = "-";}
      else {$cena = $data2["cena"];}
    }
      echo '
  <dt class="' . ($cena == "-" || $cena == "0.00" ? 'nulovaCena' : '') . '">'.$data["popis"].':</dt><dd>'.$cena.'</dd>';
  }//while
echo '
</dl>
</div>
';
}//if($_SESSION['uzivatelskaPrava'] > ZAMESTNANEC)

echo '
<br class="clearleft" />';

$rezervovano = rezervovaneMnozstvi($id);
if($rezervovano)
{ //pokud je toto zbozi v rezervacich, vypise rezervovane mnosztvi
  echo '
<img class="warning" alt="rezervace" src="images/warning.png" /><strong>Rezervované mno¾ství:</strong> '
.$rezervovano;
}

  echo '
<h2>'.$texty["potrebnyMaterial"].'</h2>';
  $dotaz = "
  SELECT Z.id, Z.nazev, Z.c_vykresu, S.mnozstvi
  FROM sestavy as S, zbozi as Z
  WHERE celek='$id'
  AND S.soucastka = Z.id ";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));

  if(mysqli_num_rows($vysledek) == 0)
  { //vyrobek se nesklada ze zadnych soucastek
    echo '
<p>'.$texty["neniSlozen"].'</p>';
  }
  else { //vypise se odkaz na tabulku se soucastkami, ze kterych se vyrobek sklada
    echo '
<p><a href="'.$soubory["potrebnyMaterial"].'?id='.$id.'" title="'.$texty["vypsatSoucastkyTitle"].'">'.$texty["vypsatSoucastky"].'</a></p>';
  }

  echo '
<h2>'.$texty["prirustkyUbytky"].'</h2>';
vypsatTransakce($id);
}//if(isset($_GET["id"])) {


konecHTML();
?>
