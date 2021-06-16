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

uvodHTML("dokladTransakce");
echo '
<h1 class="noPrint">'.$texty["dokladTransakce"].'</h1>';
zobrazitHlaseni();
echo'
 <h2>'.$texty["doklad"].'</h2>';
if (!isset($SRBD)) { // uz jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}
$id = $_GET['id'];
//*** UKAZANI INFORMACI O UPRAVOVANEM DOKLADU ***/
$dotaz = "SELECT * FROM doklady WHERE id='$id'";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
While ($data = @mysqli_fetch_array($vysledek)) {
  $cDokladu = $data["c_dokladu"];
  $datum = date("d.m.Y",$data["datum"]);
  $skupina = $data["skupina"];
  $prodKategorie = $data["prod_kategorie"];
  $typVyroby = $data["typ_vyroby"];

  if($prodKategorie) {
    // zjisteni popisku prodejni kategorie
    $dotaz2 = "SELECT id, popis FROM prodejni_kategorie WHERE id='$prodKategorie'";
    $vysledek2 = mysqli_query($SRBD, $dotaz2) or Die(mysqli_error($SRBD));
    $data2 = @mysqli_fetch_array($vysledek2);
    $prodKategoriePop = $data2["popis"] ?? "";
  }

echo '
<p class="noPrint">
  <a href="'.$soubory["odebratDoklad"].'?odebrat='.$_GET["id"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')">'.$texty["odstranitDoklad"].'</a>
</p>';

echo '
<p class="noPrint">
  <a href="'.$soubory["dodaciList"].'?id='.$id.'">'.$texty["dodaciList"].'</a>
</p>';

  if($skupina=='Rezervace')
  {
     echo '
      <form method="post" action="rezervace.php" class="rezerv">
  
      <dl>
        <dt>'.$texty["cDokladu"].':</dt><dd><input type="text" name="cDokladu" value="'.$cDokladu.'" /></dd>
        <dt>'.$texty["datum"].':</dt><dd><input type="text" name="datum" value="'.$datum.'"/></dd>
        <dt>'.$texty["skupina"].':</dt><dd>'.$skupina.'</dd>
        <dt class="noPrint">'.$texty["prodejKomu"].':</dt><dd class="noPrint">'.$prodKategoriePop.'</dd>
      </dl>
      <input type="hidden" name="id" value="'.$id.'" />
      <input type="submit" class="submit" name="rezNaProdej" value="'.$texty['rezervaceNaProdej'].'" id="odeslat"/>
      <input type="submit" class="submit" name="ruseniRez" value="'.$texty['zrusRezervaci'].'" id="odeslat"/><br />
     </form><br /><br /><hr />';
  }
  else
  {      
     echo '
      <dl>
      <dt>'.$texty["cDokladu"].':</dt><dd>'.$cDokladu.'</dd>
      <dt>'.$texty["datum"].':</dt><dd>'.$datum.'</dd>
      <dt>'.$texty["skupina"].':</dt><dd>'.$skupina.'</dd>';
      if($skupina=='Prodej' || $skupina=='Rezervace')
      echo '
      <dt class="noPrint">'.$texty["prodejKomu"].':</dt><dd class="noPrint">'.$prodKategoriePop.'</dd>';
      if($skupina=='Výroba')
      echo '
      <dt>'.$texty["typVyroby"].':</dt><dd>'.$typVyroby.'</dd>';
      echo '
    </dl>';
  }
}

//*** VKLADANI NOVYCH POLOZEK DO DOKLADU ***/
if($skupina == "Výroba")
  $dotaz_nazev="SELECT id, nazev FROM zbozi GROUP BY nazev";
// elseif($skupina == "Prodej")
//   $dotaz_nazev="SELECT Z.id, nazev FROM zbozi as Z, prodejni_ceny as PC
//                 WHERE PC.id_zbozi = Z.id
//                 AND PC.cena > 0";
else
  $dotaz_nazev="SELECT id, nazev FROM zbozi GROUP BY nazev";

/*
  //zobrazuje jenom polozky, ktere se skladaji ze soucastek
  if($skupina == "Kooperace" || $skupina == "Výroba")
  $dotaz_nazev="SELECT Z.id, Z.nazev
                           FROM zbozi Z
                           WHERE id IN (select celek from sestavy where celek=Z.id)
                           GROUP BY nazev";
*/
/*
  //zobrazuje polozky, ktere maji nastavenou cenu prace
  if($skupina == "Kooperace" || $skupina == "Výroba")
  $dotaz_nazev="SELECT Z.id, Z.nazev
                           FROM zbozi Z
                           WHERE cena_prace > 0
                           GROUP BY nazev";
*/
echo'
 <h2 class="noPrint">'.$texty['novePolozky'].'</h2>';

echo '
<form name="form" method="post" action="'.$soubory['vlozTransakci'].'" class="noPrint">
<fieldset>
<legend>'.$skupina.'</legend>
<label for="nazev">'.$texty['nazev'].':</label>
<select onchange="vyber_cv();" id="nazev" name="nazev">
<option value="">---------- vyberte ----------</option>';
  //prvni rozbalovaci seznam (nazev / rozmer)
  $vysledek = mysqli_query($SRBD, $dotaz_nazev) or Die(mysqli_error($SRBD));
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['nazev'];
  else $selected = '';

  While ($data = mysqli_fetch_array($vysledek)) {
    echo '<option value="'.$data['nazev'].'"';
    if($data['nazev'] == $selected)
      echo ' selected';
    echo '>'.$data['nazev']."</option>\n";
  } //while
  echo '
</select><br />
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

  While ($data = mysqli_fetch_array($vysledek)) {
    echo '<option value="'.$data['c_vykresu'].'"';
    if($data['c_vykresu'] == $selected)
      echo ' selected';
    echo '>'.$data['c_vykresu']."</option>\n";
  } //while
  echo '</select><br />
<br />
<label for="mnozstvi">'.$texty['transakceMnozstvi'].':</label>
<input type="text" maxlength="40" id="mnozstvi" name="mnozstvi" value="'.$_SESSION['promenneFormulare']['mnozstvi'].'" /><br />
';

////////////// PODMINKY - POLOZKY JEN PRO URCITE SKUPINY /////////////////////
if($skupina == 'Nákup' || $skupina == 'Inventura')
{

  echo '
<fieldset id="cenaMJ">
<legend>'.$texty['cenaMJ'].'</legend>
<label for="cenaMJ">'.$texty['posledniCena'].':</label>
<input type="radio" name="cenaMJ" id="radioposl" value="posledni"><span id="posln"></span><br />
<label for="cenaMJ">'.$texty['prumernaCena'].':</label>
<input type="radio" name="cenaMJ" id="radioprum" value="prumerna"><span id="prumn"></span><br />
<label for="cenaMJ">'.$texty['vlastniCena'].':</label>
<input type="radio" name="cenaMJ" value="vlastni" checked >
<input type="text" maxlength="40" name="cenaMJvlastni" value='.$_SESSION['promenneFormulare']['cenaMJvlastni'].'>
</fieldset>
';
}//konecNakup
elseif($skupina=='Výroba')
{
}
/*elseif($skupina=='Rezervace')
{ echo '
<label for="prodejniCena">'.$texty['prodejniCena'].' ('.$prodKategoriePop.'):</label>'.$prodKategorie.
'<input type="hidden" name="cenaMJ" value="'.$prodKategorie.'" />';
}*/
elseif($skupina=='Prodej' || $skupina=='Rezervace')
{echo '

<fieldset id="prodejniCena">
<legend>'.$texty['prodejniCena'].'</legend>
<label for="prodejniCena">'.$prodKategoriePop.':</label>
<input type="radio" name="prodSkupina" id="radioprum" value="skupina" checked onclick="osetriRadio()"><span id="prod_cena"></span><br />
<label for="prodejniCena">'.$texty['vlastniCena'].':</label>
<input type="radio" name="prodSkupina" value="vlastni" onclick="osetriRadio()">
<input type="text" maxlength="40" name="cenaMJvlastni" value="'.($_SESSION['promenneFormulare']['cenaMJvlastni'] ?? '').'">
<input type="hidden" id="prod_kat" name="prod_kat" value="'.$prodKategorie.'" />
<input type="hidden" id="cenaMJ" name="cenaMJ" value="" />
</fieldset>';

}
elseif($skupina=='Kooperace')
{ echo '
<fieldset id="cenaKOO">
<legend>'.$texty['cenaKOO'].'</legend>
<label for="cenaKOO">'.$texty['posledniCena'].':</label>
<input type="radio" name="cenaKOO" id="radioposl" value="posledni"><span id="poslk"></span><br />
<label for="cenaKOO">'.$texty['vlastniCena'].':</label>
<input type="radio" name="cenaKOO" value="vlastni" checked>
<input type="text" maxlength="40" name="cenaKOOvlastni" value='.($_SESSION['promenneFormulare']['cenaKOOvlastni'] ?? '').'>
</fieldset>
';
}
elseif($skupina=='Inventura')
{
  // nic dalsi neni treba - jen bezne naskladnovani na pocatku
}
elseif($skupina=='Zmetkování')
{
}

echo '<br />
<input type="submit" class="submit" name="odeslat" value="'.$texty['pridatPolozku'].'" id="odeslat"/>
<input type="hidden" name="skupina" value="'.$skupina.'" />
<input type="hidden" name="id_dokladu" value="'.$id.'" />
<input type="hidden" name="odeslano" value="ano" />
</fieldset>
</form>';


//*** VYPSANI UZ VLOZENYCH POLOZEK V DOKLADU ***/
echo'
 <h2>'.$texty["polozkyVDokladu"].'</h2>';


echo
'<table>
  <thead>
  <tr>
    <th>'.$texty['c'].'</th>
    <th>'.$texty['nazev'].'</th>
    <th>'.$texty['cv'].'</th>
    <th>'.$texty['transakceMnozstvi'].'</th>';
  if($skupina == "Kooperace")
  echo
    '<th>'.$texty['cenaKOOzkr'].'</th>';
  elseif($skupina == "Výroba")
  echo
    '<th>'.$texty['cenaPrace'].'</th>';
  if($skupina == "Nákup" || $skupina == 'Inventura')
  echo
    '<th>'.$texty['cenaMJ'].'</th>';
  if($skupina=='Zmetkování')
  echo
    '<th>'.$texty['cenaVyr'].'</th>';
  elseif($skupina == "Prodej" || $skupina == "Výroba" || $skupina == "Kooperace" || $skupina == "Rezervace")
      echo '<th>'.$texty['cenaKS'].'</th>';
  echo
   '<th>'.$texty['cenaCelkem'].'</th>
   <th class="noPrint"></th>
  </tr>
  </thead>
  <tfoot>';
    $dotaz = "SELECT sum(cena_MJ) as cena_MJ, sum(cena_KOO) as cena_KOO,sum(mnozstvi) as mnozstvi, sum(cena_MJ*mnozstvi) as cena_celkem FROM transakce WHERE id_dokladu='$id'";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    $data = @mysqli_fetch_array($vysledek);
   echo'
   <tr>
    <td colspan="3">'.$texty['celkem'].'</td>
    <td class="alignRight">'.number_format($data['mnozstvi'], 3, ".", " ").'</td>';
    if($skupina == "Kooperace" || $skupina == "Výroba" )
    echo
    '<td class="alignRight">'.number_format($data['cena_KOO'], 2, ".", " ").'</td>';
    echo '
    <td class="alignRight">'.number_format($data['cena_MJ'], 2, ".", " ").'</td>
    <td class="alignRight">'.number_format($data['cena_celkem'], 2, ".", " ").'</td>
    <td class="noPrint"></td>
   </tr>
  </tfoot>
  <tbody>
  ';
  $sudyRadek = false;
  $i=1;
  $dotaz = "SELECT *, (cena_MJ*mnozstvi) as cenaCelkem FROM transakce WHERE id_dokladu='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));

While ($data = @mysqli_fetch_array($vysledek)) {
    $dotaz = 'SELECT * FROM zbozi WHERE id='.$data["id_zbozi"];
    $vysledek2 = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    $data2 = @mysqli_fetch_array($vysledek2);
    echo '
  <tr';
  if($sudyRadek)
      echo ' class="sudyRadek"';
    echo
    '>
    <td>'.$i++.'</td>
    <td><a href="'.$soubory['nahledKarta'].'?id='.$data2['id'].'">'.$data2["nazev"].'</a></td>
    <td>'.$data2["c_vykresu"].'</td>
    <td class="alignRight">'.number_format($data['mnozstvi'], 2, ".", " ").'</td>';
    //if($skupina == "Nákup")
  if($skupina == "Kooperace" || $skupina == "Výroba" )
  echo
    '<td class="alignRight">'.number_format($data['cena_KOO'], 2, ".", " ").'</td>';
  echo
    '<td class="alignRight">'.number_format($data['cena_MJ'], 2, ".", " ").'</td>';
  echo
  '<td class="alignRight">'.number_format($data['cenaCelkem'], 2, ".", " ").'</td>
  <td class="noPrint"><a class="odebrat" title="odebrat" href="'.$soubory['smazTransakci'].'?id='.$data['id'].'&skupina='.$skupina.'">'.$texty["odebrat"].'</a></td>
</tr>';
  $sudyRadek=!$sudyRadek;
  }
echo '
</tbody></table>';


konecHTML();

session_unregister('promenneFormulare');
?>
