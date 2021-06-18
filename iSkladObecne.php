<?php
//
//   Obecn� pou��van� funkce
//


include 'iSkladSoubory.php';                   // katalog jmen pou�it�ch soubor� - tady mus� b�t p��m� konstanta
include $soubory['includeTexty'];               // katalog textov�ch konstant
include $soubory['includeKonstanty'];           // r�zn� syst�mov� a jin� konstanty

function session_register(){ 
    $args = func_get_args(); 
    foreach ($args as $key) {
        if(isset($GLOBALS[$key])) {
            $_SESSION[$key] = $GLOBALS[$key];
        } 
    } 
} 
function session_is_registered($key){ 
    return isset($_SESSION[$key]); 
} 
function session_unregister($key){ 
    unset($_SESSION[$key]); 
}

/**
 * odstran�n� escape znak� (nej�ast�ji ze vstupn�ho parametru)
 * @param $co               vstupn� �et�zec
 * @param $maximalniDelka   maxim�ln� d�lka pro odstran�n�
 * @return                  �et�zec s odstran�n�mi escape znaky
 */
function odstraneniEscape($co, $maximalniDelka) {
   $co = substr($co, 0, $maximalniDelka);      // zkr�cen� vstupn�ho �et�zce na po�adovan� po�et znak�
   //$co = EscapeShellCmd($co);                  // odstran�n� escape zank� standardn� funkc�
   return ($co);
} // of odstraneniEscape

/**
 * zobrazen� hl�en� v glob�ln� prom�nn�
 * @return         nen�, ale vytvo�� p��mo form�tovan� HTML k�d
 */
function zobrazitHlaseni() {
  if (session_is_registered('hlaseniOK'))  { // potvrzujici hlaseni v sezeni existuje
    echo "<p class=\"hlaseniOK\">".$_SESSION['hlaseniOK']."</p>\n";
    $_SESSION['hlaseniOK'] = "";
    session_unregister('hlaseniOK');      // vymazani hlaseni
  } // if session
  if (session_is_registered('hlaseniChyba'))  { // chybove hlaseni v sezeni existuje
    echo "<p class=\"hlaseniChyba\">".$_SESSION['hlaseniChyba']."</p>\n";
    $_SESSION['hlaseniChyba'] = "";
    session_unregister('hlaseniChyba');      // vymazani hlaseni
  } // if session
} // of zobrazitHlaseni()


/**
 * zobrazen� informace o tom, zda je u�ivatel p�ihl�en nebo nikoliv
 * zobrazen� roku, se kter�m se pracuje
 * zobrazen� odkazu na verzi pro tisk
 * @return         nen�, ale vytvo�� p��mo form�tovan� HTML k�d
 */
function zobrazitLogin() {
  global $texty;             // glob�ln� texty
  global $soubory;
  if (session_is_registered('uzivatelskeJmeno'))
  {// uzivatel je prihlasen
    echo
    '  <p class="zobrazitLogin">
    '.$texty['jePrihlasen'].'<strong>'.$_SESSION['uzivatelskeJmeno'].'</strong>
    '.tlacitkaLogin().'<br />
    Pracujete s modulem <strong>'.$_SESSION['modul'].'</strong> rok <strong>'.$_SESSION['rokArchiv'].'</strong><br />
    ';

    $suffix = (strpos($_SERVER["REQUEST_URI"], "?") === false ? "?" : "&amp;") . "print=1&amp;paging=no";
    echo
    '<a href="'.$_SERVER["REQUEST_URI"].$suffix.'" title="'.$texty["verzeTiskTitle"].'">'.$texty["verzeTisk"].'</a>
  </p>
  ';
  } // if
  else { // u�ivatel nen� p�ihl�en
    echo
       "<p class=\"zobrazitLogin\">".
          $texty['neniPrihlasen']."<br />".tlacitkaLogin().
       "</p>\n";
    } // else
} // of zobrazitLogin


/**
 * zobrazuje u�ivateli p�ihla�ovac� tal��tko a odkaz na �pravu profilu
 * nebo odhla�ovac� tla��tko
 * @return      nen�, ale vytvo�� p��mo form�tovan� HTML k�d
 */
function tlacitkaLogin() {
  global $texty;             // glob�ln� texty
  global $soubory;             // glob�ln� texty

  $vysledek='';
  if (session_is_registered('uzivatelskeJmeno'))
  {// u�ivatel je p�ihl�en
    $vysledek.=
    "<a title=\"".$texty["zmenitDetailyTitle"]."\" href=\"".$soubory["upravitProfil"]."\">".$texty["upravitProfil"]."</a> | ".
    "<a title=\"".$texty["odhlaseniTitle"]."\" href=\"".$soubory["odhlaseni"]."\">".$texty["odhlaseni"]."</a>";
  } // if
  else {// u�ivatel nen� p�ihl�en
    $vysledek.=
    "<a title=\"".$texty["prihlaseniTitle"]."\" href=\"".$soubory["prihlaseni"]."\">".$texty["prihlaseni"]."</a>";
  } // else

  return $vysledek;
} // of tlacitkaLogin


/**
 * zajist� spojen� se S�BD
 * podle roku, se kterym se pracuje, vybere patricnou databazi
 * @return      otev�en� spojen�, pokud se poda��
 */
function spojeniSRBD($databaze='') {
  global $texty;             // glob�ln� texty
  global $soubory;             // glob�ln� soubory
  //nebyl nastaven zadny rok, pouzije se tedy aktualni rok

  if (!session_is_registered('rokArchiv')) {
    session_register('rokArchiv');
    //$_SESSION['rokArchiv'] = date("Y");
  }

  include 'iSkladDatabaze.php';
  $SRBD = mysqli_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or Die(mysqli_error($SRBD));

  if($databaze != "") {
    $vysledek = mysqli_select_db($SRBD, $databaze);
  }
  else {
    $vysledek = mysqli_select_db($SRBD, SQL_DBNAME); // or Die(mysqli_error($SRBD));
  }

  if($vysledek == 0)   { //nepovedlo se pripojeni k DB
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnaDB'];
    header('Location: '.$soubory["odhlaseni"].'?type=spatnadb'); // n�vrat k volaj�c�mu
    exit;
  }

  mysqli_query($SRBD, "SET NAMES 'latin2';");

  return $SRBD;
} // of spojeniSRBD


/**
 * vytv��� tla��tko
 * @param $jmeno  jm�no INPUT prvku
 * @param $text   k�d textu
 * @return        nen�, ale vytvo�� p��mo form�tovan� HTML k�d tabulky
 */
function dejTlacitko($jmeno, $text) {
  global $texty;             // glob�ln� texty

  $vysledek=
       '<input'.
          ' type="submit" class="submit"';
  if ($jmeno!='') {
    $vysledek.= ' name="'.$jmeno.'"';
  }
  $vysledek.=' value="'.$texty[$text].'"'.
       ' />';

  return $vysledek;
} // of dejTlacitko


/**
 * Kontroluje, jestli je nekdo prihlasen. Pokud ne, presmeruje
 * na prihlasovaci stranku.
 * Po 1 hodine provede automaticke odhlaseni.
 * Pri kazdem zavolani aktualizuje cas posledni uzivatelovy aktivity.
 */
function kontrolaPrihlaseni() {
  global $soubory;
  global $texty;

  if (!(session_is_registered('uzivatelskeJmeno')))
  { //zadny uzivatel neni prihlasen
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neprihlasen'];
    header("Location: ".$soubory["prihlaseni"]);
    exit;
  } // if

  if ($_SESSION["casPristupu"] < strtotime("-30 minute")) {
    //k odhlaseni.php se prida paramer type=auto
    header("Location: ".$soubory["odhlaseni"]."?type=\"auto\"");
    exit;
  } // if

  //aktualizace casu posledni uzivatelovy aktivity.
  $_SESSION["casPristupu"] = time();
} // of kontrolaPrihlaseni

/**
 * Kontroluje, jestli ma prihlaseny uzivatel dostatecna uziv. prava.
 * Pokud nema, provede presmerovani na hlavni stranku.
 * @param $potrebnaPrava    prava potrebna pro zobrazeni obsahu stranky
 */
function kontrolaPrav($potrebnaPrava) {
  global $soubory;
  global $texty;

  if($_SESSION['uzivatelskaPrava'] < $potrebnaPrava) {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['nedostatecnaPrava'];
    header("Location: ".$soubory['hlavniStranka']);
    exit;
  }
} //kontrolaPrav


/**
 * vytiskne zacatek HTML stranky
 * @param $indexTextu    titulek stranky
 * @return               neni, tiskne rovnou HTML
 */
function uvodHTML($indexTextu) {
global $texty;
global $soubory;

echo '
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-2" />
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="js/autocomplete/autocomplete.min.js"></script>
  <link rel="stylesheet" href="js/autocomplete/css/autoComplete.02.css">
';
  if (isset($_GET["print"])) {
    echo '<link rel="stylesheet" href="css/styleprint.css" media="screen" />';
  }
  else {
    echo '<link rel="stylesheet" href="css/style.css" media="screen" />';
  }
echo '
  <link rel="stylesheet" href="css/styleprint.css" media="print" />
  <link rel="stylesheet" href="css/styleie.css" media="screen" />
  <script type="text/javascript" src="js/mootools.js"></script>
  <script type="text/javascript" src="slimbox/js/slimbox.js"></script>
  <link rel="stylesheet" href="slimbox/css/slimbox.css" type="text/css" media="screen" />
  <script type="text/javascript" src="js/calendar.js"></script>
  <link rel="stylesheet" href="css/calendar.css" type="text/css" media="screen" />
<script>
window.addEvent(\'domready\', function(){
	$$(\'input.DatePicker\').each( function(el){
		new DatePicker(el);
	});
});
</script>

  <script type="text/javascript" src="js/nazev_cv.js"></script>
  <script type="text/javascript" src="js/odpocitavani.js"></script>
  <script type="text/javascript" src="js/prod_cena.js"></script>
  <script type="text/javascript" src="js/kartaAutocomplete.js"></script>
  <title>'.$texty[$indexTextu].'</title>
</head>
<body onload="';
if($indexTextu == "testVyroba" || $indexTextu=='dokladTransakce')   //onLoad budou ruzne skripty
  echo 'vyber_cv();';
elseif($indexTextu == "zapisTitulek" || $indexTextu=="najitDoklad")
  echo 'ukazProdejKomuVyroba(document.getElementById(\'skupina\'));';
if (session_is_registered('uzivatelskeJmeno'))
{
 echo 'countDown();';
}
echo '">

<div id="page"><div class="column-in">
<div id="header"><div class="column-in">
';
  zobrazitLogin();
echo '<div class="odpocitavani">';if (session_is_registered('uzivatelskeJmeno')) echo $texty['odpocitavani']; echo '<span id="odpocitavani"></span></div>
  <div class="clearleft"></div>
</div></div> <!-- end of header -->

<div id="main"><div class="column-in">
';
}


/**
 * vytiskne konec HTML stranky
 * @return               neni, tiskne rovnou HTML
 */
function konecHTML() {
  global $texty;
  global $soubory;

echo "\n</div></div> <!-- end of main -->\n\n".
"<div id=\"sidebar\"><div class=\"column-in\">\n";
if(isset($_SESSION['uzivatelskaPrava'])) {
  ukazMenu($_SESSION['uzivatelskaPrava']);
}
echo
"\n</div></div> <!-- end of sidebar -->\n".
"<div id=\"cleaner\"></div>\n".
"\n</div></div> <!-- end of page -->\n".
"</body>\n".
"</html>\n";
}

/**
 * Pokud je uzivatel prihlasen, vytiskne navigacni menu.
 * Na zaklade prav prihlaseneho uzivatele pripadne nevytiskne nektere polozky.
 * @param $prava    uziv. prava prihlaseneho uzivatele
 * @return          neni, tiskne rovnou HTML
 */
function ukazMenu($prava) {
  global $texty;
  global $soubory;

  $SRBD=spojeniSRBD();

  echo '
<h4>menu</h4>
<ul id="menu">
  <li><a href="'.$soubory["hlavniStranka"].'">'.$texty['uvodniStrana'].'</a></li>
  <li><a href="'.$soubory["hlavniStranka"].'">'.$texty['skladovaKarta'].'</a>
    <ul>
      <li><a href="'.$soubory['novaKarta'].'" title="'.$texty['novaKartaTitle'].'">'.$texty['novaKarta'].'</a></li>
      <li><a href="'.$soubory['upravitKarta'].'" title="'.$texty['upravitKartaTitle'].'">'.$texty['upravitKarta'].'</a></li>
      <li><a href="'.$soubory['nahledKarta'].'"  title="'.$texty['nahledKartaTitle'].'">'.$texty['nahledKarta'].'</a></li>
      <li><a href="'.$soubory['stroje'].'"  title="'.$texty['strojeTitle'].'">'.$texty['stroje'].'</a></li>';
      if($prava > ZAMESTNANEC) {
        echo '
      <li><a href="'.$soubory['prodejniCeny'].'"  title="'.$texty['prodejniCenyTitle'].'">'.$texty['prodejniCeny'].'</a></li>';
      }
echo'
    </ul>
  </li>
  <li><a href="'.$soubory["hlavniStranka"].'">'.$texty["zapis"].'</a>
    <ul>
      <li><a href="'.$soubory["novyDoklad"].'" title="'.$texty["novyDokladTitle"].'">'.$texty["novyDoklad"].'</a></li>
      <li><a href="'.$soubory["editDoklad"].'" title="'.$texty["editDokladTitle"].'">'.$texty["editDoklad"].'</a></li>
      <li><a href="'.$soubory["testVyrobaVypis"].'" title="'.$texty["testVyrobaTitle"].'">'.$texty["testVyroba"].'</a></li>
    </ul>
  </li>
  <li><a href="'.$soubory["hlavniStranka"].'">'.$texty['tisk'].'</a>
    <ul>
      <li><a href="'.$soubory['cenovyStavSkladu'].'">'.$texty['cenovyStavSkladu'].'</a></li>
      <li><a href="'.$soubory['mnozstevniStavSkladu'].'">'.$texty['mnozstevniStavSkladu'].'</a></li>
      <li><a href="'.$soubory['tiskNakupy'].'">'.$texty['Nakup'].'</a></li>
      <li><a href="'.$soubory['tiskVyroba'].'">'.$texty['Vyroba'].'</a></li>
      <li><a href="'.$soubory['tiskProdej'].'">'.$texty['Prodej'].'</a></li>
      <li><a href="'.$soubory['tiskRezervace'].'">'.$texty['Rezervace'].'</a></li>
      <li><a href="'.$soubory["podlimitniPolozky"].'" title="'.$texty["podlimitniTitle"].'">'.$texty["podlimitniPolozky"].'</a></li>';
      if($prava > ZAMESTNANEC) {
        echo '
      <li><a href="'.$soubory['koeficient'].'">'.$texty['koeficienty'].'</a></li>';
      }
echo '
    </ul>
  </li>
  <li><a href="'.$soubory["hlavniStranka"].'">'.$texty["archiv"].'</a>
    <ul>
      <li><a href="'.$soubory['archiv'].'?action=add">'.$texty['novyArchiv'].'</a></li>
      <li><a href="'.$soubory['inventura'].'" onclick="return confirm(\''.$texty["potvrzeniInventury"].'\')">'.$texty['lonskaInventura'].'</a></li>';
    $vysledek = mysqli_query($SRBD, "show databases like 'lmr%'") or Die(mysqli_error($SRBD));
    While ($data = mysqli_fetch_array($vysledek)) {
      $rok = substr($data[0],-4);
      echo '
      <li><a href="'.$soubory['archiv'].'?rok='.$rok.'">'.$rok.'</a></li>';
    }
    echo '
    </ul>
  </li>
  ';
if($prava > ZAMESTNANEC) {
  echo '
  <li><a href="'.$soubory["uzivatele"].'" title="'.$texty["uzivateleNadpis"].'">'.$texty["uzivateleNadpis"].'</a></li>';
}
  echo '
  <li><a href="'.$soubory["moduly"].'" title="'.$texty["modulyNadpis"].'">'.$texty["modulyNadpis"].'</a></li>
  <li><a href="'.$soubory["kontrolaMnozstvi"].'" title="'.$texty["kontrolaMnozstvi"].'">'.$texty["kontrolaMnozstvi"].'</a></li>
</ul>';
} // of ukazMenu


/**
 * Vypise do tabulky vsechny soucastky (nazev, c. vykresu, mnozstvi), ze kterych
 * se dany celek sklada.
 * @param $idCelku    id celku, u ktereho zjsitujeme soucastky
 * @param $rezim      nahled: neukazuje se sloupec "odebrat"
                      uprava: ukazuje se sloupec "odebrat"
 * @return            neni, tiskne rovnou HTML
 */
function vypisSoucastky($idCelku, $rezim)
{
  global $texty;
  global $soubory;

  $SRBD=spojeniSRBD();

  //tabulka se soucastkami, ze kterych se vyrobek sklada
  $dotaz = "
SELECT Z.id, Z.nazev, Z.c_vykresu, S.mnozstvi
FROM sestavy as S, zbozi as Z
WHERE celek='$idCelku'
  AND S.soucastka = Z.id ";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  $pocet = mysqli_num_rows($vysledek);

  if(mysqli_num_rows($vysledek) == 0)
  { //celek neobsahuje zadne soucastky
    echo '
<p>'.$texty["neniSlozen"].'</p>';
  }
  else { //celek obsahuje soucsatky
    $sudyRadek = false;
    if($rezim == "uprava") {
      $sloupce = array('','nazev','c_vykresu','mnozstvi');
    }
    elseif($rezim == "nahled") {
      $sloupce = array('nazev','c_vykresu','mnozstvi');
    }

  $urldodatek = '&id='.$idCelku;
  if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"];
  putPaging($pocet,POCET_RADKU,$from, $urldodatek);
  echo '
<table>';

    $dotaz.=pageOrderQuery($pocet);
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    printTableHeader($sloupce,"id=".$idCelku);

    While ($data = mysqli_fetch_array($vysledek)) {
      if($sudyRadek) {
        echo '
  <tr class="sudyRadek">';
      } //if sudyradek
      else {
        echo '
  <tr>';
      } //else
    if($rezim == "uprava") {
      echo '
    <td><a href="'.$soubory["frmPridatSoucastka"].'?odebrat='.$data["id"]."&amp;celek=".$_GET["id"].'" class="odebrat" onclick="return confirm(\''.$texty["opravdu"].'\')" title="'.$texty["odebratTitle"].'">'.$texty["odebrat"].'</a></td>';
    }
      echo '
    <td><a href="'.$soubory["nahledKarta"].'?id='.$data["id"].'">'.$data["nazev"].'</a></td>
    <td>'.$data["c_vykresu"].'</td>
    <td>'.$data["mnozstvi"].'</td>
  </tr>';
      $sudyRadek = !$sudyRadek;
    } //while
    echo '
</table>';
  putPaging($pocet,POCET_RADKU,$from, $urldodatek);
  }//else
}//vypisSoucastky()


/**
 * Spocita cenu veskereho materialu potrebneho na vyrobu celku
 * cena materialu = (prumerna cena soucastky * potrebne mnozstvi soucastky) pro
 * vsechny soucastky potrebne k sestaveni celku
 * @param $idCelku    id celku, u ktereho zjistujeme cenu materialu
 * @return            cena materialu
 */
function spocitatCenuMaterialu($idCelku)
{
  global $texty;
  global $soubory;

  $SRBD=spojeniSRBD();
  $cenaMaterialu = 0;

//soucastky, ze kterych se vyrobek sklada
  $vysledek = mysqli_query($SRBD, "
  SELECT Z.id, S.mnozstvi, Z.prum_cena
  FROM sestavy as S, zbozi as Z
  WHERE celek='$idCelku'
  AND S.soucastka = Z.id") or Die(mysqli_error($SRBD));

  While ($data = mysqli_fetch_array($vysledek)) {
    $cenaMaterialu += $data["mnozstvi"]*$data["prum_cena"];
  }//while

  return $cenaMaterialu;

}//spocitatCenuMaterialu()


/**
 * Vypise do tabulky vsechny transakce (prirustky, ubytky) daneho zbozi.
 * @param $idZbozi    id zbozi, u ktereho zjsitujeme transakce
 * @return            neni, tiskne rovnou HTML
 */
function vypsatTransakce($idZbozi)
{
  global $texty;
  global $soubory;
//$rezim

  $SRBD=spojeniSRBD();

//tabulka se soucastkami, ze kterych se vyrobek sklada
  /*$dotaz = "
  SELECT T.mnozstvi, T.cena_MJ, D.c_dokladu, D.skupina, D.datum
  FROM transakce as T, doklady as D
  WHERE id_zbozi='$idZbozi'
  AND T.id_dokladu = D.id ";
  */
  
    $dotaz = "
  SELECT mnozstvi, id_dokladu, c_dokladu, skupina, datum
  FROM
  (
    (
     SELECT T.mnozstvi, D.id as id_dokladu, D.c_dokladu, D.skupina, D.datum
     FROM transakce as T, doklady as D
     WHERE id_zbozi='$idZbozi'
     AND T.id_dokladu = D.id 
    )
    UNION ALL
    (
     select VO.mnozstvi, T.id_dokladu, D.c_dokladu, 'odpis p�i v�rob�' as skupina,  D.datum
     from vyroba_odpisy VO
     join transakce T ON T.id = VO.id_vyroby 
     join doklady D on D.id = T.id_dokladu
     WHERE VO.id_zbozi = '$idZbozi'
    )
  ) as vysl ";
  
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
  $pocet = mysqli_num_rows($vysledek);

  if(mysqli_num_rows($vysledek) == 0) {
    echo '
<p>'.$texty["nejsouTranskace"].'</p>';
  }
  else {
    $sudyRadek = false;
    $sloupce = array('datum','skupina','c_dokladu', 'mnozstvi');

    $urldodatek = '&id='.$idZbozi;
    if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"];
    putPaging($pocet,POCET_RADKU,$from, $urldodatek);

    echo '
<table>';
    $dotaz.=pageOrderQuery($pocet);

    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    printTableHeader($sloupce,"id=".($idZbozi ?? ""));

    While ($data = mysqli_fetch_array($vysledek)) {
      //pokud neni zadana cenaMJ, tiskne se misto ni pomlcka
      //if($data["cena_MJ"] == "") $data["cena_MJ"] = "-";

      if($data["skupina"] == "N�kup" || $data["skupina"] == "Kooperace" ||
      $data["skupina"] == "V�roba" || $data["skupina"] == "Inventura")
        $znamenko = "+";
      else
        $znamenko = "-";

      if($sudyRadek) {
        echo '
  <tr class="sudyRadek">';
      } //if sudyradek
      else {
        echo '
  <tr>';
      } //else
      echo '
    <td>'.date("d.m.",$data["datum"]).'</td>
    <td>'.$data["skupina"].'</td>
    <td><a href="'.$soubory["dokladTransakce"].'?id='.$data["id_dokladu"].'">'.$data["c_dokladu"].'</a></td>
    <td class="alignRight">'.$znamenko.' '.number_format($data["mnozstvi"], 3, ".", " ").'</td>
  </tr>';
      $sudyRadek = !$sudyRadek;
    } //while
    //<td>'.$data["cena_MJ"].'</td>
    
    echo '
</table>';
    putPaging($pocet,POCET_RADKU,$from, $urldodatek);
  }//else
}//vypsatTransakce()


/**
 * Spocita, kolik ks/kg/m daneho zbozi je rezervovano
 * @param $idZbozi    id zbozi, u ktereho zjistujeme pocet rezervaci
 * @return            rezervovane mnozstvi
 */
function rezervovaneMnozstvi($idZbozi)
{
  global $texty;
  global $soubory;

  $SRBD=spojeniSRBD();


  $vysledek = mysqli_query($SRBD, "
  SELECT SUM(T.mnozstvi) as suma
  FROM transakce as T, doklady as D
  WHERE id_zbozi	='$idZbozi'
  AND D.id = T.id_dokladu
  AND D.skupina = 'Rezervace'") or Die(mysqli_error($SRBD));

  $data = mysqli_fetch_array($vysledek);

  return $data["suma"];

}//spocitatCenuMaterialu()

/**
 * vytvari select list z pole ktere se mu predlozi
 * @param name jmeno polozky
 * @param opt_values option items, jmena jednotlivych polozek
 * @param value hodnota ktera bude SELECTED, implicitne ''
 * @param opt_show vypisovane polozky, pokud nezadano tak je to podle opt_names
 */
function makeArraySelectList($name,$opt_values,$value='', $opt_show='', $opt_attributes='',$vyberte=true)
{

    $result = '';
    //pokud nezadany opt_values priradi se jim opt names
    if ($opt_show == '' )
      $opt_show = $opt_values;
    if(isset($_POST[$name]) && $value == '')
      $value = $_POST[$name];

    $result .= '<select name="'.$name.'" '.$opt_attributes.'>'."\n";
    if($value=='' && $vyberte)
      $result .= "\t<option value='' selected>--- Vyberte ---</option>\n";

    $i=0;
    foreach ($opt_values as $item)
    {
      $result .= "\t<option ";
      if($value!='' && $value==$item)
        $result .= 'selected';
      $result .= ' value="'.$item.'">'.$opt_show[$i].'</option>'."\n";
      $i++;
    }
    $result .='</select>'."\n";
    return $result;

}//makeArraySelect()


/**
 * funkce na sestaveni dotazu nad jednou tabulkou, bude vybirat jen vybrane sloupce
 * a podle vybranych sloupcu radit
 * @param $table jmeno tabulky (jediny povinny parametr funkce)
 * @param $att_names pole - nazvy atributu, sloupcu tabulky
 * @param $att_values pole hodnot atributu, ma stejnou velikost jako att_names
 * @param $order_arr pole podle ktereho se ma radit
 * @param order_type pole typu razeni, ma stejnou velikost jako order_arr a pro
 *        jeho prvky urcuje zda bude razeni ASC / DESC
 */
function genericSQL($table, $att_names='', $att_values='',$order_arr='',$order_type='')
{
  $select = '';            //cast za selectem
  $where = '';

  //cast za selectem
  if(empty($att_names))
    $select = '*';
  else
  { foreach($att_names as $att)
    {
      if(!empty($select))
        $select .= ', ';
      $select .= $att;
    }
  }

  //cast za from
  $from = 'FROM '.$table;              //cast za from

  //cast za where
  if(!empty($att_values))
  {
     $i=0;
     foreach($att_values as $attval)
     {
      if(!empty($where))
        $where .= ' AND';
      $where .= ' '.$att_names[$i].'='.$attval;
      $i++;
    }
  }


  $query = 'SELECT '.$select.' '.$from.' WHERE '.$where;

  //cast za order
  $order = '';
  if(!empty($order_arr))
  {
     $i=0;
     foreach($order_arr as $ordval)
     {
      if(!empty($order))
        $order .= ' ,';
      $order .= ' '.$ordval;

      if(!empty($order_type))
      {
        $order .= ' '.$order_type[$i];
      }
      $i++;
     }
  $query .= ' ORDER BY '.$order;
  }
  echo $query;

  return $query;
}//genericSQL


/* vytvori v pripade potreby navigaci strankovani
 * @param count - pocet radku v tabulce
 * @param rows - pocet radku ktere se vypisuji
 * @param from - od jakeho zaznamu se bude vypisovat
 */
function putPaging($count,$rows,$from, $urldodatek='')
{
    global $texts;

    echo '<div class="tab_nav">';
    //za��tek - vytvo� odkaz pouze pokud nejsme na za��tku
     if ($from==1) echo '&lt;&lt; Za��tek&nbsp;|&nbsp;';
    else echo '<a href="'.$_SERVER['PHP_SELF'].'?tod=1'.$urldodatek.'"'.($texts['page_begin'] ?? '').'">&lt;&lt; Za��tek</a>&nbsp;|&nbsp;';
    //zp�t - vytvo� odkaz pouze pokud nejsme v prvn�ch ROWS
     if ($from<$rows) echo '&lt; P�edchoz�&nbsp;|&nbsp;';
    else echo '<a href="'.$_SERVER['PHP_SELF'].'?tod='.($from-$rows).$urldodatek.'" title="'.($texts['page_previous'] ?? '').'">&lt; P�edchoz�</a>&nbsp;|&nbsp;';
    //dal�� - vytvo� odkaz, pouze pokud nejsme v posledn�ch ROWS
     if ($from+$rows>$count) echo 'Dal�� &gt;&nbsp;|&nbsp;';
    else echo '<a href="'.$_SERVER['PHP_SELF'].'?tod='.($from+$rows).$urldodatek.'"  title="'.($texts['page_next'] ?? '').'">Dal�� &gt;</a>&nbsp;|&nbsp;';
    //posledn� - to je posledn�ch (zbytek po d�len� ROWS) z�znam�
     if ($from>$count-$rows) echo 'Konec &gt;&gt;&nbsp;';
    else echo '<a href="'.$_SERVER['PHP_SELF'].'?tod='.($count-$count%$rows+1).$urldodatek.'" title="'.($texts['page_end'] ?? '').'">Konec &gt;&gt;</a>';
    echo '</div>';

}//putPaging()

/**
 *  vrati dodatek k dotazu podle toho pokud se radi a strankuje (ORDER, LIMIT)
 *  @param $table    pro jakou tabulku
 *  @param $rows     deafultni pocet zobrazovanych radku
 */
function pageOrderQuery($count,$rows=POCET_RADKU)
{
  global $soubory;
  global $texty;

  $dodatek = '';                       //navratova hodnota

  //zpracovani ORDER
  if(!empty($_GET['o']))
  {
    $dodatek .= 'ORDER BY '.$_GET['o'];
    if(!empty($_GET['ot']))   //zadan typ razeni
    {
      if($_GET['ot']=='a')
        $dodatek .= ' ASC';
      else
        $dodatek .= ' DESC';
    }
  }

  //zpracovani strankovani LIMIT
  //$pocet = getTableCount($table);      //pocet radku v tabulce

   // strankovani dotazu, pokud je jich vice nez v ROWS je to osetreno
   if ($count>$rows)
   {
    // tod = tabulka od
    if (!isset($_GET["tod"])) $from=1; else $from=$_GET["tod"];
      $dodatek .= ' LIMIT '.($from-1).','.$rows;
   }

  return $dodatek;
}//pageOrderQuery()

/**
 * tiskne zahlavi k tabulce s pozadovanymi odkazy na razeni
 * @param names         pole jmen v zahlavi
 * @param order_col     jmeno sloupce podle ktereho se momentalne radi
 * @param cur_ord_type  typ kterym se momentalne radi ASC/DESC, slouzi pro prevraceni
 */
function printTableHeader($names,$dodatek='',$zobrazit='')
{
   global $texty;

if($zobrazit == '')
 $zobrazit = $names;

if(!empty($dodatek))
   echo '<tr>';
   if(!empty($_GET['o']))
     if($_GET['ot']=='d')
     { $ot = 'a';
       $class = 'desc';
     }
     else
     { $class = 'asc';
       $ot = 'd';
     }

   $i = 0;
   foreach($names as $name)
   {
     echo '<th><a class="';
     if(isset($_GET['o']) && $name == $_GET['o'])
       echo $class;
     else
       ;//echo 'desc';
     echo '" href="'.$_SERVER['PHP_SELF'].'?o='.$name.'&ot=';
     if(isset($_GET['o']) && $name == $_GET['o'])
       echo $ot;
     else
       echo 'd';
     if(!empty($dodatek))
      echo '&'.$dodatek;
     echo '">'.$texty[$zobrazit[$i++]].'</a></th>';

   }
   echo '</tr>';

}

/**
 * vrati pocet radku v dane tabulce
 * @param $table     nazev tabulky
 * @param $where     volitelne podminky
 */
function getTableCount($table )
{
  $SRBD=spojeniSRBD();

  $vysledek = mysqli_query($SRBD, 'SELECT count(*) as pocet FROM '.$table.$wh) or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek);
  return $data['pocet'];
}//getTableCount()


/**
 * zmensi zadany obrazek
 * @param $file     jmeno souboru
 * @param $type     thumb - vytvori se nahled (maximalni rozmer 160px)
                    normal - zmensi se obrazek (maximalni rozmer 640px)
 */
function zmensiObrazek($file, $type) {
//type: thumb - vytvori se nahled (maximalni rozmer 160px)
//type: normal - vytvori se nahled (maximalni rozmer 640px)

  if($type == "normal") {
    $maxSize = 640;          //Velikost strany n�hledu v pixelech
    $newName = $file;
  }
  elseif($type == "thumb") {
    $maxSize = 160;          //Velikost strany n�hledu v pixelech
    $newName = "thumb_".$file;
  }
  $thumbDir="./nahledy";  //Adres�� pro ulo�en� n�hled�

  if(!file_exists($thumbDir))mkdir($thumbDir,"777"); //Pokud neexistuje adres�� pro n�hledy, vytvo��me jej


  $FullSize=GetImageSize($thumbDir.'/'.$file); //Zji�t�n� p�vodn�ch rozm�r� obr�zku
  $FullPic=ImageCreateFromJPEG($thumbDir.'/'.$file); //Na�ten� p�vodn�ho obr�zku ve form�tu JPEG


  //V�po�et velikosti n�hledu
  //Funkce GetImageSize n�m vr�tila do pole $FullSize ���ku (0) a v��ku (1) obr�zku
  $origWidth = $FullSize[0];
  $origHeight = $FullSize[1];

  if($origWidth>$origHeight)  //Pokud je ���ka v�t�� ne� v��ka
  {
    $width = $maxSize;       //���ka se rovn� velikosti strany n�hledu
    $height = intval(($maxSize/$origWidth)*$origHeight); //Nastaven� v��ky n�hledu tak, aby byla v p�vodn�m pom�ru k ���ce
  }
  else                           //Pokud je ���ka v�t�� ne� v��ka
  {
    $height = $maxSize;       //V��ka se rovn� velikosti strany n�hledu
    $width = intval(($maxSize/$origHeight)*$origWidth); //Nastaven� ���ky n�hledu tak, aby byla v p�vodn�m pom�ru k v��ce
  }


  $src = imagecreatefromjpeg($thumbDir.'/'.$file);
  $dst = imagecreatetruecolor($width,$height);

  imagecopyresampled($dst, $src, 0, 0, 0, 0, $width,$height,$origWidth,$origHeight);
  header("Content-type: image/jpeg");


  imagejpeg($dst,$thumbDir.'/'.$newName,80);
  imagedestroy($src);
  imagedestroy($dst);
}//zmensiObrazek()

function ulozObrazek($name) {
  global $texty;
  global $soubory;
  $SRBD=spojeniSRBD();

  move_uploaded_file($_FILES["jmeno_souboru"]["tmp_name"], "./nahledy/$name");
  mysqli_query($SRBD, "UPDATE zbozi SET obrazek='$name' WHERE id='$id'") or Die(mysqli_error($SRBD));
  zmensiObrazek($name, "thumb");
  zmensiObrazek($name, "normal");
}

function dejIdModulu($nazev) {
  $SRBD=spojeniSRBD("sklad");
  $vysledek = mysqli_query($SRBD, "SELECT id FROM moduly WHERE modul='$nazev'") or Die(mysqli_error($SRBD));
  $data = mysqli_fetch_array($vysledek);

  return $data["id"];
}

function dejZacVykresu($cv) {
  $pos1 = strpos($cv, "."); //hleda vyskyt prvni tecky
  if($pos1 === false) $pos1 = 999; //999 je velmi velke cislo, takze se pouzije pozice pomlcky
  $pos2 = strpos($cv, "-"); //hleda vyskyt prvni pomlcky
  if($pos2 === false) $pos2 = 999;
  //jako oddelovac zacatku cisla vykresu pouzije prvni nalezenou tecku nebo pomlcku
  //podle toho, ktery z techto dvou znaku se v cisle vykresu objevi drive
  $pos = ($pos1 < $pos2) ? $pos1 : $pos2;

  return substr($cv,0,$pos); //pouze prvni cast c. vykresu
}
?>
