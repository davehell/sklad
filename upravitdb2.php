<?php
define ("SQL_HOST","localhost");
define ("SQL_USERNAME","root");
define ("SQL_PASSWORD","");
//$dbs = array("lmr2008","lmr2009","obrobna2008", "obrobna2009", "test2008","test2009");
$dbs = array("lmr2011","obrobna2011","test2011");


foreach ($dbs as $db) {
    echo "databáze $db: ";
    $SRBD = mysqli_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or Die(mysqli_Error());
    $vysledek = mysqli_select_db($SRBD, $db);
    if($vysledek == 0)   { //nepovedlo se pripojeni k DB
        echo "CHYBA: Pøipojení k $db se nepodaøilo.<br />\n";
    }
    else {
          mysqli_query($SRBD, "SET NAMES 'latin2';");
        
          

          mysqli_query($SRBD, 'delimiter //');

          $f = fopen("sql/transakce_triggery.sql", "r");
          //$f = fopen("sql/drop_trigger.sql", "r");
          while (!feof ($f)) {
            $query .= fgets($f, 4096);
          }
          fclose ($f);
          

          foreach (explode('//', $query) as $sql) {
            mysqli_query($SRBD, $sql);
          }

          mysqli_query($SRBD, 'delimiter ;');

        if(mysqli_errno($SRBD) != 0 && mysqli_errno($SRBD)!=1064)   { //dotaz se neprovedl
            echo "CHYBA: ".mysqli_errno($SRBD)."<br />\n";
        }
        else {
            echo "OK<br />\n";
        }
        mysqli_close($SRBD);
    }

}


?>
<br /><br />
<a href="index.php">Zpìt na sklad</a>
