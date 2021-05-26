<?php
/**
 * karta_nova.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();
if(isset($_GET["id"])) {$id = $_GET["id"];}

uvodHTML("novaKartaNadpis");
echo '
<h1>'.$texty["novaKartaNadpis"].'</h1>';
zobrazitHlaseni();

echo '
<h2>1. '.$texty["vyplnitHlavickuKarty"].'</h2>';

if(!isset($_GET["id"])) {
  if($_SESSION['promenneFormulare']['limit'] == "") {
    //pokud nebyl drive zadan limit, nastavi se jako defaultni hodnota nula
    $_SESSION['promenneFormulare']['limit']=0;
  }
//Hlavicka karty jeste nebyla vyplnena, tedy v url neni nastaven parametr id,
//tedy se zobrazi formular pro zadani hlavicky karty
  echo '
<form method="post" action="'.$soubory['frmPridatKarta'].'" enctype="multipart/form-data">
<fieldset>
<legend>'.$texty['infoKarta'].'</legend>
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
  //vypsani textovych poli pro vsechny prodejni ceny
  $vysledek = MySQL_Query("SELECT id, popis FROM prodejni_kategorie ORDER BY popis ASC", $SRBD) or Die(MySQL_Error());
  While ($data = @MySQL_Fetch_Array($vysledek)) {
    $idProdejni = $data["id"];
    echo '
<label for="prodejniCena'.$idProdejni.'">'.$texty['prodejniCena'].' '.$data["popis"].':</label>
<input type="text" maxlength="40" id="cenaPrace'.$idProdejni.'" name="cenaPrace'.$idProdejni.'" value="'.$_SESSION['promenneFormulare']['cenaPrace'.$idProdejni].'" /><br />
';
  }//while
echo '
<br />
<label for="jmeno_souboru">'.$texty['obrazek'].':</label>
<input type="file" maxlength="40" id="jmeno_souboru" name="jmeno_souboru" accept="image/jpeg" enctype="multipart/form-data" /><br />
<br />'.
dejTlacitko('odeslat','pridatKartu').'
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';
} //if(!isset($_GET["id"]))
else {
//Hlavicka karty uz byla vyplnena, tedy se zobrazi misto formulare zobrazi
//pouze vypis hlavicky karty
  $vysledek = MySQL_Query("SELECT nazev, c_vykresu, jednotka, min_limit, cena_prace
  FROM zbozi WHERE id='$id'", $SRBD) or Die(MySQL_Error());
  $data = @MySQL_Fetch_Array($vysledek);
    echo '
<dl class="floatleft">
  <dt>'.$texty["nazev"].':</dt><dd>'.$data["nazev"].'</dd>
  <dt>'.$texty["cv"].':</dt><dd>'.$data["c_vykresu"].'</dd>
  <dt>'.$texty["jednotka"].':</dt><dd>'.$data["jednotka"].'</dd>
  <dt>'.$texty["limit"].':</dt><dd>'.$data["min_limit"].'</dd>
  <dt>'.$texty["cenaPrace"].':</dt><dd>'.$data["cena_prace"].'</dd>
</dl>';

//////////////////////
// obrazek
  $vysledek = MySQL_Query("SELECT obrazek, nazev, c_vykresu FROM zbozi WHERE id='$id' AND obrazek is not NULL", $SRBD) or Die(MySQL_Error());
  if(mysql_num_rows($vysledek) == 0) { //zbozi nema v DB zadnou fotku
    echo '
<p class="floatleft">Toto zbo¾í nemá pøiøazen ¾ádný obrázek.</p>
<br class="clearleft" />';
  } //if
  else {
    $data = @MySQL_Fetch_Array($vysledek);
      echo '
<a href="nahledy/'.$data["obrazek"].'" alt="'.$data["obrazek"].'"
   title="'.$data['nazev'].' '.$data['c_vykresu'].'"
   rel="lightbox">
  <img class="floatleft" src="nahledy/thumb_'.$data["obrazek"].'" alt="'.$data["obrazek"].'" />
</a>
<br class="clearleft" />';
  } //else

//////////////////////
// prodejni ceny
  if($_SESSION['uzivatelskaPrava'] > ZAMESTNANEC)
  { //zamestnanci prodejni ceny neuvidi
  echo '
<dl>';
    //vypsani vsech prodejnich cen
    $vysledek = MySQL_Query("SELECT id, popis FROM prodejni_kategorie ORDER BY id", $SRBD) or Die(MySQL_Error());
    While ($data = @MySQL_Fetch_Array($vysledek)) {
      $idKat = $data["id"];
      $cena = "-";
      $vysledek2 = MySQL_Query("SELECT cena FROM prodejni_ceny WHERE id_zbozi='$id' AND id_kategorie='$idKat'", $SRBD) or Die(MySQL_Error());
      While ($data2 = @MySQL_Fetch_Array($vysledek2)) {
        if($data2["cena"] == "") {$cena = "-";}
        else {$cena = $data2["cena"];}
      }
        echo '
  <dt>'.$texty['prodejniCena'].' '.$data["popis"].':</dt><dd>'.$cena.'</dd>';
    }//while
  echo '
</dl>';
  }//if($_SESSION['uzivatelskaPrava'] > ZAMESTNANEC)
} //else


echo
'<h2>2. '.$texty["pridaniSoucastek"].'</h2>
<a name="soucasti-vyrobku"></a>
';
if(!isset($_GET["id"])) {
//Hlavicka karty jeste nebyla vyplnena, tedy v url neni nastaven parametr id,
//tedy se zobrazi vyzva k vyplneni formulare s hlavickou
  echo "<p>Nejdøíve vytvoøte novou skladovou kartu vyplnìním v¹ech údajù a stiskem tlaèítka ".$texty["pridatKartu"].".</p>";
}
else {
//Hlavicka karty uz byla vyplnena, tedy se misto vyzvy pro vyplneni hlavicky
//zobrazi formular pro pridavani soucastek a pod nim jsou vypsany vsechny
//soucastky, ze kterych se vyrobek sklada
  echo '
<form method="post" action="'.$soubory['frmPridatSoucastka'].'">
<fieldset>
<legend>'.$texty['vyrobenoZ'].'</legend>
<p>'.$texty["vyberteSoucastky"].'</p><br />
<label for="nazev">'.$texty['nazev'].':</label>
<select id="nazev" name="nazev" onchange="vyber_cv()">
<option value="">---------- vyberte ----------</option>
';
  //prvni rozbalovaci seznam (nazev / rozmer)
  //neobsahuje vyrobek, ktery je prave editovan (jinak by slo nastavit, ze
  //tento vyrobek je slozen z toho sameho vyrobku)
  $vysledek = MySQL_Query("SELECT id, nazev FROM zbozi WHERE id<>'$id' GROUP BY nazev", $SRBD) or Die(MySQL_Error());
  While ($data = MySQL_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['nazev'].'">'.$data['nazev']."</option>\n";
  } //while
  echo '
</select><br />
<label for="cv">'.$texty['cv'].':</label>
<select id="cv" name="cv" onchange="osetri_cv();">
<option value="">----- vyberte -----</option>
';
  //druhy rozbalovaci seznam (c. vykresu / jakost)
  $vysledek = MySQL_Query("SELECT id, c_vykresu FROM zbozi GROUP BY c_vykresu", $SRBD) or Die(MySQL_Error());
  While ($data = MySQL_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['c_vykresu'].'">'.$data['c_vykresu']."</option>\n";
  } //while
  echo '
</select><br />
<label for="mnozstvi">'.$texty['kusy'].':</label>
<input type="text" maxlength="40" id="mnozstvi" name="mnozstvi" /><br />
<br />'.
  dejTlacitko('odeslat','pridatSoucastku').'
<br /><hr />
<input type="hidden" name="id" value="'.$id.'" />';

vypisSoucastky($id, "uprava");

  echo '
</fieldset>
</form>';
}//else

if(session_register('promenneFormulare')) {
  session_unregister('promenneFormulare');
}
konecHTML();
?>
