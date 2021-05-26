<?php
/**
 * uprava_profilu.php
 *
 * Profil se upravuje prave prihlasene osobe.
 * Tato stranka obsahuje pouze formular. Protoze kontrola zadaneho jmena a hesla
 * je stejna, jako pri vkladani noveho uzivatele, zpracovava tento formular
 * skript novy_uzivatel.php.
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();    // inicializace sezení, buďto se vytvoří nové sezení nebo se znovuvytvoří stávající
kontrolaPrihlaseni();


uvodHTML("uzivateleTitleProfil");
echo "<h1>".$texty["upravitProfil"]."</h1>\n";
echo "<h2>".$texty["editovatUzivatele"]." \"".$_SESSION["uzivatelskeJmeno"]."\"</h2>\n";
?>
<form method="post" action="<?php echo $soubory['novyUzivatel']; ?>">
<?php zobrazitHlaseni(); ?>
<fieldset>
  <legend><?php echo $texty['infoUzivatele']; ?></legend>
  <label for="loginUsername"><?php echo $texty['uzivJmeno']; ?></label>
  <input type="text" size="30" maxlength="30" id="loginUsername" name="loginUsername" value="<?php if(isset($_GET["login"])) echo $_GET["login"]; ?>" /><br />
  <label for="loginPassword"><?php echo $texty['uzivHeslo']; ?></label>
  <input type="password" size="30" maxlength="30" id="loginPassword" name="loginPassword" /><br />
  <label for="loginPassword2"><?php echo $texty['uzivHesloZnovu']; ?></label>
  <input type="password" size="30" maxlength="30" id="loginPassword2" name="loginPassword2" /><br />
<br />
<?php echo dejTlacitko('odeslat','ulozitZmeny'); ?>
</fieldset>
</form>
<?php
konecHTML();
?>
