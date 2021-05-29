<?php


header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

if (!isset($_GET['id']))
  header("Location: " . $soubory['novyDoklad']);

uvodHTML("dodaciList");
echo '
<h1 class="noPrint">'.$texty["dodaciList"].'</h1>';
zobrazitHlaseni();

if (!isset($SRBD)) { // uz jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}
$id = $_GET['id'];

$vysledek = mysqli_Query("SELECT * FROM doklady WHERE id='$id'", $SRBD) or Die(mysqli_Error());
While ($data = @mysqli_Fetch_Array($vysledek)) {
  $prodKategorie = $data["prod_kategorie"];
    // zjisteni udaju o odberateli
    $vysledek2 = mysqli_Query("SELECT * FROM prodejni_kategorie WHERE id='$prodKategorie'", $SRBD) or Die(mysqli_Error());
    $data2 = @mysqli_Fetch_Array($vysledek2);
    $prodKategoriePop = $data2["popis"];
}

echo '
<p class="noPrint">
  <a href="'.$soubory["dokladTransakce"].'?id='.$id.'">'.$texty["zpetNaDoklad"].'</a>
</p>';


echo '

<form method="post" action="'.$soubory["dodaciListTisk"].'?id='.$id.'&amp;print=1&amp;paging=no">
<fieldset>
<legend>'.$texty["odberatel"].'</legend>

<label for="odberatelNazev">'.$texty['odberatelNazev'].':</label>
<input type="text" id="odberatelNazev" name="odberatelNazev" value="'.$data2["nazev"].'" /><br />
<label for="odberatelUlice">'.$texty['odberatelUlice'].':</label>
<input type="text" id="odberatelUlice" name="odberatelUlice" value="'.$data2["ulice"].'" /><br />
<label for="odberatelMesto">'.$texty['odberatelMesto'].':</label>
<input type="text" id="odberatelMesto" name="odberatelMesto" value="'.$data2["mesto"].'" /><br />
<label for="odberatelIco">'.$texty['odberatelIco'].':</label>
<input type="text" id="odberatelIco" name="odberatelIco" value="'.$data2["ico"].'" /><br />
<label for="odberatelDic">'.$texty['odberatelDic'].':</label>
<input type="text" id="odberatelDic" name="odberatelDic" value="'.$data2["dic"].'" /><br />

<br />

<label for="cObjednavky">'.$texty['cObjednavky'].':</label>
<input type="text" id="cObjednavky" name="cObjednavky" /><br />

<br />
<input type="submit" class="submit" name="vytisknout" value="'.$texty['zobrazitDodaciList'].'" />
</fieldset>
</form>
<br />
';

konecHTML();

session_unregister('promenneFormulare');

