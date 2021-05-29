<?
include "iSkladObecne.php";


     $reserved = array();
     $nedostatek = array();

kontrolniVypis();
kontrolniVypis2();

        
function kontrolniVypis()
{
    global $reserved;
    global $nedostatek;

    for($i=1;$i<=15;$i++)
    {                
        if(lzeVyrobit($i,1,'',2))
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

function kontrolniVypis2()
{
    global $reserved;
    global $nedostatek;

    for($i=1;$i<=15;$i++)
    {                
        if(lzeVyrobit2($i,1,2))
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



/**
 * zjistuje zda lze vyrobit dany vyrobek
 * @param id_zbozi     id vyrobku z tabulky zbozi
 * @param mnozstvi     pozadovane mnozstvi na vyrobu
 * @param echo_offset  nepovinny parametr pro offset pri kontrolnim vypisu
 */  
function lzeVyrobit($id_zbozi, $mnozstvi, $echo_offset='',$max_zanor)
{
     global $reserved;
     global $nedostatek;
     $lze = true;
     echo 'MZ:'.$max_zanor;
     
     // zjistim si nazev zbozi 
     $SRBD = spojeniSRBD();
     $vysledek = mysqli_Query('SELECT nazev FROM zbozi WHERE id='.$id_zbozi, $SRBD);  
     $record=mysqli_Fetch_Array($vysledek);
     //echo "ECHO: " . $echo_offset. '<br />';
     $pridavek = '|---';
     echo $echo_offset.'ZBOZI: ' . $record['nazev']. ' id= '.$id_zbozi."<BR />";
     
     // vysledne pole
     
     // z jakych primych casti se vyrobek sklada ???
     $SRBD = spojeniSRBD();
     $vysledek = mysqli_Query('SELECT celek, soucastka, mnozstvi FROM sestavy WHERE celek='.$id_zbozi, $SRBD);  // provézt dotaz
     // pocet soucastek poslouzi k tomu zda lze soucastku vyrobit nebo ne
     // tedy pokud neni dostatek na sklade NEJDE ani vyrobit :-)
     $pocet_soucastek = mysqli_num_rows($vysledek);      
     
     if($pocet_soucastek<1)
     {
       $lze=false;
     }
     else { // pruchod jednotlivymi castmi celku, a jejich test zda jich je na skladu dost
      while ($record=mysqli_Fetch_Array($vysledek)):
     
        //kontrola poctu kazde casti na sklade
        $SRBD = spojeniSRBD();
        $vysledek2 = mysqli_Query('SELECT mnozstvi,typ FROM zbozi WHERE id='.$record['soucastka'], $SRBD) or Die(mysqli_Error());  // provézt dotaz
        $record2 = mysqli_Fetch_Array($vysledek2);
         
        if (!isset($reserved[$record['soucastka']]))
            $reserved[$record['soucastka']]=0;
        $rozdil = $record2['mnozstvi']-$reserved[$record['soucastka']]- $record['mnozstvi']*$mnozstvi;
        echo $echo_offset.$pridavek.'soucastka: ' . $record['soucastka'] . ' potrebuju: ' .$record['mnozstvi'] .'x'.$mnozstvi.'='.$record['mnozstvi']*$mnozstvi. ' je: '.($record2['mnozstvi']-$reserved[$record['soucastka']]).'---';
        
        // test poctu na sklade a pozadovaneho mnozstvi
        if($rozdil>=0)
        { // vse ok, jen do pole dam vedet o tom, ze z mnozstvi ubiram
          $reserved[$record['soucastka']] += $record['mnozstvi'];
          //echo 'RESERVED:' .$reserved['3'] .'<br />';
          echo '<font color="green">OK</font><br />';
        }
        else 
        // pokud chybi testuju zda je to material a chci ho koupit nebo vyrobek a lze ho vyrobit
        // pricitam pocet nedostatkoveho zbozi do prislusne polozky pole
        // zaroven pricitam zbytek daneho zbozi na sklade do pouziteho zbozi
        { 
          $reserved[$record['soucastka']] += $record2['mnozstvi'];
          echo '<font color="red">!CHYBI '.-$rozdil.'ks!</font>';
          if ($record2['typ'] == 'material' || $pocet_soucastek<1 || $max_zanor==1)
          {
              $lze = false;
              if (!isset($nedostatek[$record['soucastka']]))
                $nedostatek[$record['soucastka']]=0;
              $nedostatek[$record['soucastka']] += (-$rozdil);
              echo '  material ---> koupi se '.-$rozdil. 'ks<br />';
          }
          else {
            echo ' vyrobek --->koupit '.-$rozdil. 'ks / test zda lze vyrobit<br />'; 
            if(lzeVyrobit($record['soucastka'],-$rozdil,$echo_offset."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $max_zanor-1))
              $lze=true;
            else 
              $lze=false;
            }
        }
     endwhile;
     }
     return $lze;
 
}//lzeVyrobit()

/**
 * zjistuje zda lze vyrobit dany vyrobek
 * @param id_zbozi     id vyrobku z tabulky zbozi
 * @param mnozstvi     pozadovane mnozstvi na vyrobu
 * @param echo_offset  nepovinny parametr pro offset pri kontrolnim vypisu
 */  
function lzeVyrobit2($id_zbozi, $mnozstvi, $max_zanor)
{
     global $reserved;
     global $nedostatek;
     $lze = true;
     
     
     // zjistim si nazev zbozi 
     $SRBD = spojeniSRBD();
     $vysledek = mysqli_Query('SELECT nazev FROM zbozi WHERE id='.$id_zbozi, $SRBD);  
     $record=mysqli_Fetch_Array($vysledek);
     
     
     // z jakych primych casti se vyrobek sklada ???
     $SRBD = spojeniSRBD();
     $vysledek = mysqli_Query('SELECT celek, soucastka, mnozstvi FROM sestavy WHERE celek='.$id_zbozi, $SRBD);  // provézt dotaz
     // pocet soucastek poslouzi k tomu zda lze soucastku vyrobit nebo ne
     // tedy pokud neni dostatek na sklade NEJDE ani vyrobit :-)
     $pocet_soucastek = mysqli_num_rows($vysledek);      
     
     if($pocet_soucastek<1)
     {
       $lze=false;
     }
     else { // pruchod jednotlivymi castmi celku, a jejich test zda jich je na skladu dost
      while ($record=mysqli_Fetch_Array($vysledek)):
     
        //kontrola poctu kazde casti na sklade
        $SRBD = spojeniSRBD();
        $vysledek2 = mysqli_Query('SELECT mnozstvi,typ FROM zbozi WHERE id='.$record['soucastka'], $SRBD) or Die(mysqli_Error());  // provézt dotaz
        $record2 = mysqli_Fetch_Array($vysledek2);
         
        if (!isset($reserved[$record['soucastka']]))
            $reserved[$record['soucastka']]=0;
        $rozdil = $record2['mnozstvi']-$reserved[$record['soucastka']]- $record['mnozstvi']*$mnozstvi;
        
        // test poctu na sklade a pozadovaneho mnozstvi
        if($rozdil>=0)
        { // vse ok, jen do pole dam vedet o tom, ze z mnozstvi ubiram
          $reserved[$record['soucastka']] += $record['mnozstvi'];
        }
        else 
        // pokud chybi testuju zda je to material a chci ho koupit nebo vyrobek a lze ho vyrobit
        // pricitam pocet nedostatkoveho zbozi do prislusne polozky pole
        // zaroven pricitam zbytek daneho zbozi na sklade do pouziteho zbozi
        // -neuspech je rovnez pokud jsem v poslednim zanoreni ( =1 )
        { 
          $reserved[$record['soucastka']] += $record2['mnozstvi'];
          if ($record2['typ'] == 'material' || $pocet_soucastek<1 || $max_zanor==1)
          {
              $lze = false;
              if (!isset($nedostatek[$record['soucastka']]))
                $nedostatek[$record['soucastka']]=0;
              $nedostatek[$record['soucastka']] += (-$rozdil);
          }
          else {
            if(lzeVyrobit2($record['soucastka'],-$rozdil,$max_zanor-1))
              $lze=true;
            else 
              $lze=false;
            }
        }
     endwhile;
     }
     return $lze;
 
}//lzeVyrobit2()



?>
