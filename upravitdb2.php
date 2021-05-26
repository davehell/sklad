<?php
define ("SQL_HOST","localhost");
define ("SQL_USERNAME","root");
define ("SQL_PASSWORD","");
//$dbs = array("lmr2008","lmr2009","obrobna2008", "obrobna2009", "test2008","test2009");
$dbs = array("lmr2011","obrobna2011","test2011");


foreach ($dbs as $db) {
    echo "databáze $db: ";
    $SRBD = MySQL_Connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or Die(MySQL_Error());
    $vysledek = MySQL_Select_Db($db, $SRBD);
    if($vysledek == 0)   { //nepovedlo se pripojeni k DB
        echo "CHYBA: Pøipojení k $db se nepodaøilo.<br />\n";
    }
    else {
          mysql_query("SET NAMES 'latin2';", $SRBD);
        
          

          mysql_query('delimiter //', $SRBD);

          $f = fopen("sql/transakce_triggery.sql", "r");
          //$f = fopen("sql/drop_trigger.sql", "r");
          while (!feof ($f)) {
            $query .= fgets($f, 4096);
          }
          fclose ($f);
          

          foreach (explode('//', $query) as $sql) {
            mysql_query($sql, $SRBD);
          }

          mysql_query('delimiter ;', $SRBD);

        if(mysql_errno() != 0 && mysql_errno()!=1064)   { //dotaz se neprovedl
            echo "CHYBA: ".mysql_errno()."<br />\n";
        }
        else {
            echo "OK<br />\n";
        }
        mysql_close($SRBD);
    }

}


?>
<br /><br />
<a href="index.php">Zpìt na sklad</a>
