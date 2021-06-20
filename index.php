<?php
header("Content-Type: text/html; charset=iso-8859-2");
include 'iSkladObecne.php';

session_start();    // inicializace sezení, buïto se vytvoøí nové sezení nebo se znovuvytvoøí stávající
kontrolaPrihlaseni();
$SRBD = spojeniSRBD();
uvodHTML('index');

?>
<h1><?php echo $texty["indexNadpis"]; ?></h1>
<?php zobrazitHlaseni(); ?>
<?php
echo '
<!--
<h3>Rychlé odkazy </h3>
<ul>
  <li><a href="'.$soubory['nahledKarta'].'"  title="'.$texty['nahledKartaTitle'].'">Prohlí¾ení skladových karet</a></li>
  <li><a href="'.$soubory["novyDoklad"].'" title="'.$texty["novyDokladTitle"].'">Zalo¾ení nového dokladu</a></li>
  <li><a href="'.$soubory["testVyrobaVypis"].'" title="'.$texty["testVyrobaTitle"].'">'.$texty["testVyroba"].'</a></li>
  <li><a href="'.$soubory["podlimitniPolozky"].'" title="'.$texty["podlimitniTitle"].'">'.$texty["podlimitniPolozky"].'</a></li>
</ul>
-->
<div class="obal">
<a class="tlacitko" href="'.$soubory['nahledKarta'].'"  title="'.$texty['nahledKartaTitle'].'">
  <img src="images/nahled_karta.png" />
  '.$texty["prohlizeniKaret"].'
</a>
<a class="tlacitko" href="'.$soubory["novyDoklad"].'" title="'.$texty["novyDokladTitle"].'">
    <img src="images/novy_doklad.png" />
    '.$texty["zalozeniDokladu"].'
</a>
<a class="tlacitko" href="'.$soubory["testVyrobaVypis"].'" title="'.$texty["testVyrobaTitle"].'">
    <img src="images/test_vyroba.png" />
    '.$texty["testVyroba"].'
</a>
<a class="tlacitko" href="'.$soubory["podlimitniPolozky"].'" title="'.$texty["podlimitniTitle"].'">
    <img src="images/podlimitni.png" />
    '.$texty["podlimitniPolozky"].'
</a>
<br class="clearleft" />
</div>
';

konecHTML();
?>
