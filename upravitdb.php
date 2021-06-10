<?php
define ("SQL_HOST","localhost");
define ("SQL_USERNAME","root");
define ("SQL_PASSWORD","");
$dbs = array("lmr2010", "obrobna2010", "test2010");


foreach ($dbs as $db) {
    echo "databáze $db: ";
    $SRBD = mysqli_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or Die(mysqli_Error());
    $vysledek = mysqli_select_db($SRBD, $db);
    if($vysledek == 0)   { //nepovedlo se pripojeni k DB
        echo "CHYBA: Pøipojení k $db se nepodaøilo.<br />\n";
    }
    else {
        mysqli_query($SRBD, "SET NAMES 'latin2';");
        mysqli_query($SRBD, 'ALTER TABLE `doklady` CHANGE `typ_vyroby` `typ_vyroby` enum("Montá¾","Obrobna","Svaøovna","Montá¾ vozíkù","Montá¾ blokù","Obrobna bloky")');
        if(mysqli_errno($SRBD) != 0)   { //dotaz se neprovedl
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
