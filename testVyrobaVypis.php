<?php

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include "iTransakceFunkce.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

uvodHTML("testVyroba");
echo '
<h1>'.$texty["testVyroba"].'</h1>';
zobrazitHlaseni();


////////////////////////////////////////////////////////////////////////////////
//             VYBER VYROBY                                                   //
////////////////////////////////////////////////////////////////////////////////

echo '
<form method="GET" action="'.$soubory['testVyroba'].'">
<fieldset>
<legend>'.$texty['vyrobek'].'</legend>
<label for="nazev">'.$texty['nazev'].':</label>
<select id="nazev" name="nazev" onchange="vyber_cv()">
<option value="">---------- vyberte ----------</option>';
  //prvni rozbalovaci seznam - vyberou se jen veci ktere maji podsestavy/material = jsou celky
  /// WHERE id IN (select celek from sestavy where celek=Z.id)
  $vysledek = MySQL_Query("SELECT Z.id, Z.nazev 
                           FROM zbozi Z
                           
                           GROUP BY nazev", $SRBD) or Die(MySQL_Error());
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  if(session_is_registered('promenneFormulare'))
    $selected = $_SESSION['promenneFormulare']['nazev'];
  else $selected = '';
  
  While ($data = MySQL_Fetch_Array($vysledek)) {
    echo '<option value="'.$data['nazev'].'"';
    if($data['nazev'] == $selected)
      echo ' selected';
    echo '>'.$data['nazev']."</option>\n";
  } //while
  echo '
</select><br />
<label for="cv">'.$texty['cv'].':</label>
<select onchange="osetri_cv();" id="cv" name="cv">
</select><br />
<label for="mnozstvi">'.$texty['transakceMnozstvi'].':</label>
<input type="text" maxlength="40" id="mnozstvi" name="mnozstvi" value="'.$_SESSION['promenneFormulare']['mnozstvi'].'" /><br />'.
dejTlacitko('odeslat','testVyroba');
echo '</fieldset></form>';

// doslo k poslani nedostatkoveho pole
if(session_is_registered('nedostatek'))
{ 
   echo '<br /><hr /><h2>Nedostatkové zbo¾í:</h2>'.
   printNedostatekTable($_SESSION['nedostatek']);
   session_unregister('nedostatek');
}


konecHTML();


function kontrolniVypis()
{
    //global $reserved;
    //global $nedostatek;

    for($i=1;$i<=15;$i++)
    {                
        if(LzeVyrobitXXX($i, 1, 1))
          echo '<h3><font color="green">LZE</font>';
        else 
          echo '<h3><font color="red">NELZE</font>';
        echo '</h3>';
    
      echo 'RESERVED: <br />'; 
        Print_r($reserved);  
      echo '<br />NEDOSTATEK: <br />';
        Print_r($nedostatek);
    
        $reserved = array('');
        $nedostatek = array('');
        
        echo '<br />------------------------------------------------------------------------------------------------------------------------------------------<br />';
    }
}//kontrolniVypis()

?>
