<?php
/**
 * uzivatele.php
 *
 * Obsahuje - prehled uzivatelu (tabulka se vsemi uzivateli).
 *          - odkaz na pridani noveho
 *          - odkaz na editaci/smazani stavajiciho
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ADMIN;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);


if(isset($_GET["action"])) { //pokud je v url nastaven parametr action (add nebo edit)
  formAddEdit($_GET["action"]); //zobrazi se prislusny formular
}
else {
  uzivatele(); //zobrazi se prehled uzivatelu
}

function formAddEdit($akce) {
   // vytiskne formular pro pridani noveho nebo editaci stavajiciho uzivatele.
   //
   // vstupní parametry: $akce - "add" nebo "edit"
   //
   // návratová hodnota: neni, tiskne rovnou HTML
   // globální promìnné:
      global $texty;
      global $soubory;
      
  $SRBD = spojeniSRBD("sklad");

/*******************************************************************************
 * formular pro editaci STAVAJICIHO uzivatele
 ******************************************************************************/
  if($akce == "edit") {
    uvodHTML("uzivateleTitleEdit", "admin");
    echo "<h1>".$texty["uzivateleNadpis"]."</h1>\n";
    echo "<h2>".$texty["editovatUzivatele"]."</h2>\n";
?>
<form method="post" action="<?php echo $soubory['editaceUzivatele']; ?>">
<?php zobrazitHlaseni(); ?>
<fieldset>
  <legend><?php echo $texty['infoUzivatele']; ?></legend>
  <?php echo $texty['uzivJmeno']." ".$_GET["login"]; ?><br /><br />
  <label for="loginRights"><?php echo $texty['uzivPrava']; ?></label>
  <select id="loginRights" name="loginRights">
    <option <?php if($_GET["uzivPrava"] == 8) echo "selected=\"selected\""; ?>>Zamìstnanec</option>
    <option <?php if($_GET["uzivPrava"] == 9) echo "selected=\"selected\""; ?>>Administrátor</option>
  </select><br />
<br />
<?php echo dejTlacitko('odeslat','ulozitZmeny'); ?>
<input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>" />
</fieldset>
</form>
<?php
  } //if($akce == "edit")
  
/*******************************************************************************
 * formular pro vkladani NOVEHO uzivatele
 ******************************************************************************/
  if($akce == "add") {
    uvodHTML("uzivateleTitleAdd", "admin");
    echo "<h1>".$texty["uzivateleNadpis"]."</h1>\n";
    echo "<h2>".$texty["pridatUzivatele"]."</h2>\n";
?>
<form method="post" action="<?php echo $soubory['novyUzivatel']; ?>">
<?php zobrazitHlaseni(); ?>
<fieldset>
  <legend><?php echo $texty['infoUzivatele']; ?></legend>
  <label for="loginUsername"><?php echo $texty['zadaniJmena']; ?></label>
  <input type="text" size="30" maxlength="30" id="loginUsername" name="loginUsername" value="<?php if(isset($_GET["login"])) echo $_GET["login"]; ?>" /> (èíslice, velka a malá písmena bez háèkù a èárek)<br />
  <label for="loginRights"><?php echo $texty['uzivPrava']; ?></label>
  <select id="loginRights" name="loginRights">
    <option selected="selected">Zamìstnanec</option>
    <option>Administrátor</option>
  </select><br />
  <label for="loginPassword"><?php echo $texty['uzivHeslo']; ?></label>
  <input type="password" size="30" maxlength="30" id="loginPassword" name="loginPassword" /><br />
  <label for="loginPassword2"><?php echo $texty['uzivHesloZnovu']; ?></label>
  <input type="password" size="30" maxlength="30" id="loginPassword2" name="loginPassword2" /><br />
<br />
<?php echo dejTlacitko('odeslat','pridat'); ?>
</fieldset>
</form>
<?php
  }//if($akce == "add") {
?>

<?php    
} //formAddEdit($akce)

function uzivatele() {
   // vytiskne tabulku s prehledem uzivatelu
   //
   // vstupní parametry: 
   //
   // návratová hodnota: neni, tiskne rovnou HTML
   // globální promìnné:
      global $texty;
      global $soubory;

  $SRBD = spojeniSRBD("sklad");
  
  uvodHTML("uzivateleTitle");
?>
<h1><?php echo $texty["uzivateleNadpis"]; ?></h1>
<?php zobrazitHlaseni(); ?>
<h2><?php echo $texty["prehledUzivatelu"]; ?></h2>
<p>
  <img src="images/<?php echo $soubory["userAdd"]; ?>" alt="<?php echo $texty["pridatUzivatele"]; ?>" />
  <a title="<?php echo $texty["pridatUzivatele"];?>" href="<?php echo $soubory["uzivatele"]."?action=add"; ?>"><?php echo $texty["pridatUzivatele"]; ?></a>
</p>
<br />
<?php
  $idModulu = dejIdModulu($_SESSION['modul']);
  $vysledek = mysqli_Query("SELECT id, login, prava FROM uzivatele WHERE id_modulu='$idModulu' AND login!='admin'", $SRBD) or Die(mysqli_Error());
  if (mysqli_num_rows($vysledek) == 0) { // v DB neni ulozeny zadny uzivatel
    echo "<p>V databázi nejsou ulo¾eni ¾ádní u¾ivatelé.<p>";
  }
  else { //databaze obsahuje aspon jeden zaznam
    $sudyRadek = false;
    $sloupce = array('','uzivJmeno','uzivPrava2');
    echo '
<table>';
    printTableHeader($sloupce,"id=".$idZbozi);

    While ($data = mysqli_Fetch_Array($vysledek)) {
      if($data["prava"] == 9) {
        $prava = $texty["admin"];
      }
      elseif($data["prava"] == 8) {
        $prava = $texty["zamestnanec"];
      }
      else {
        $prava = "";
      }
?>
  <tr <?php if($sudyRadek) echo "class=\"sudyRadek\""; ?>>
    <td>
      <a title="<?php echo $texty["editovatUzivatele"]." ".$data["login"];?>" href="<?php echo $soubory["uzivatele"]."?action=edit&amp;login=".$data["login"]."&amp;id=".$data["id"]."&amp;uzivPrava=".$data["prava"]; ?>"><img src="images/<?php echo $soubory["userEdit"]; ?>" alt="<?php echo $texty["editovatUzivatele"]." ".$data["login"];?>" /></a>
      <a title="<?php echo $texty["odebratUzivatele"]." ".$data["login"];?>" href="<?php echo $soubory["odebraniUzivatele"]."?id=".$data["id"]; ?>" onclick="return confirm('<?php echo $texty["opravdu"]; ?>')"><img src="images/<?php echo $soubory["userDelete"]; ?>" alt="<?php echo $texty["odebratUzivatele"]." ".$data["login"];?>" /></a>
    </td>
    <td><?php echo $data["login"]; ?></td>
    <td><?php echo $prava; ?></td>
  </tr>
<?php
    $sudyRadek = !$sudyRadek;
    } // while
?>
</table>
<?php
  } //else
} //uzivatele()

konecHTML();
?>
