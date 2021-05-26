<?php
define ("SQL_HOST","localhost");
define ("SQL_USERNAME","root");
define ("SQL_PASSWORD","");
$dbs = array("lmr2010", "obrobna2010", "test2010");


foreach ($dbs as $db) {
    echo "databáze $db: ";
    $SRBD = MySQL_Connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or Die(MySQL_Error());
    $vysledek = MySQL_Select_Db($db, $SRBD);
    if($vysledek == 0)   { //nepovedlo se pripojeni k DB
        echo "CHYBA: Pøipojení k $db se nepodaøilo.<br />\n";
    }
    else {
        mysql_query("SET NAMES 'latin2';", $SRBD);
        mysql_query('ALTER TABLE `doklady` CHANGE `typ_vyroby` `typ_vyroby` enum("Montá¾","Obrobna","Svaøovna","Montá¾ vozíkù","Montá¾ blokù","Obrobna bloky")', $SRBD);
        if(mysql_errno() != 0)   { //dotaz se neprovedl
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
