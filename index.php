<?php
header("Content-Type: text/html; charset=iso-8859-2");
include 'iSkladObecne.php';

session_start();    // inicializace sezen�, bu�to se vytvo�� nov� sezen� nebo se znovuvytvo�� st�vaj�c�
kontrolaPrihlaseni();
$SRBD = spojeniSRBD();
uvodHTML('index');

?>
<h1><?php echo $texty["indexNadpis"]; ?></h1>
<?php zobrazitHlaseni(); ?>
<?php
echo '
<!--
<h3>Rychl� odkazy </h3>
<ul>
  <li><a href="'.$soubory['nahledKarta'].'"  title="'.$texty['nahledKartaTitle'].'">Prohl�en� skladov�ch karet</a></li>
  <li><a href="'.$soubory["novyDoklad"].'" title="'.$texty["novyDokladTitle"].'">Zalo�en� nov�ho dokladu</a></li>
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
