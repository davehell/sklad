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
<h1 class="noPrint">'.$texty["tiskDodaciList"].'</h1>';
zobrazitHlaseni();

if (!isset($SRBD)) { // uz jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}
$id = $_GET['id'];


$dotaz = "SELECT * FROM doklady WHERE id='$id'";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
While ($data = @mysqli_fetch_array($vysledek)) {
  $cDokladu = $data["c_dokladu"];
  $datum = date("d.m.Y",$data["datum"]);
  $prodKategorie = $data["prod_kategorie"];
    // zjisteni udaju o odberateli
    $dotaz = "SELECT id, popis FROM prodejni_kategorie WHERE id='$prodKategorie'";
    $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    $data2 = @mysqli_fetch_array($vysledek2);
    $prodKategoriePop = $data2["popis"];
}

echo '
<p class="noPrint">
  <a href="'.$soubory["dodaciList"].'?id='.$id.'">'.$texty["zpetKHlavicce"].'</a>
</p>';


echo '
<h1>DODACÍ LIST èíslo: '.$cDokladu.'</h1>

<table id="dodaciListHlavicka">
  <tr>
    <td>
        <strong>Dodavatel:</strong><br />
        LMR s.r.o.<br />
        Svazarmovská 2287<br />
        738 01 Frýdek-Místek<br />
        <br />
        DIÈ: CZ25395068<br />
        IÈO: 25395068
    </td>
    <td>
        <strong>Odbìratel:</strong><br />
        '.$_POST["odberatelNazev"].'<br />
        '.$_POST["odberatelUlice"].'<br />
        '.$_POST["odberatelMesto"].'<br />
        <br />
        DIÈ: '.$_POST["odberatelDic"].'<br />
        IÈO: '.$_POST["odberatelIco"].'
    </td>
  </tr>
  <tr>
    <td>
        <strong>Datum vystavení:</strong><br />
        '.$datum.'
    </td>
    <td>
        <strong>Èíslo objednávky/smlouvy:</strong><br />
        '.$_POST["cObjednavky"].'<br />
    </td>
  </tr>
</table>


<table id="dodaciListPolozky">
  <tr>
    <th class="cislo">'.$texty['c'].'</th>
    <th class="nazev">'.$texty['nazev'].'</th>
    <th class="cv">'.$texty['cv'].'</th>
    <th class="mnozstvi">'.$texty['mnozstvi'].'</th>
  </tr>
';

$dotaz = '
SELECT nazev, c_vykresu, t.mnozstvi as mnozstvi
FROM zbozi z, transakce t
WHERE t.id_dokladu='.$id.'
AND t.id_zbozi = z.id
';
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
$i=1;
While ($data = mysqli_fetch_array($vysledek)) {
    echo '
      <tr>
        <td>'.$i++.'</td>
        <td>'.$data["nazev"].'</td>
        <td>'.$data["c_vykresu"].'</td>
        <td>'.number_format($data["mnozstvi"], 3, ".", " ").'</td>
      </tr>
    ';
}
/*

*/
echo '
</table>

<p class="dodakLeft">
Expedoval/Podpis: ....................<br /><br /><br />
SPZ pøepravce: ....................
<br /><br /><br /><br />
Razítko: ....................
</p>

<p class="dodakRight">
Pøevzal/Podpis: ....................<br /><br /><br />
Dne: ....................
<br /><br /><br /><br />
Razítko: ....................
</p>
';

konecHTML();

session_unregister('promenneFormulare');

