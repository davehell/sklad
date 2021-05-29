<?php

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include "iTransakceFunkce.php";
include "aktualizaceKonzistenceMnozstvi.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$noveSRBD=spojeniSRBD($_SESSION["modul"].$_SESSION["rokArchiv"]);

//zjisteni, jestli uz doklad s inventurou existuje
$sql2 = 'select id from doklady where skupina="Inventura"';
$vysledek2 = mysqli_Query($sql2, $noveSRBD) or Die(mysqli_Error());
$data2 = mysqli_Fetch_Array($vysledek2);

if(mysqli_num_rows($vysledek2) != 0) {
    //zjistime si jeho id a dalsi info
    $idDokladu = $data2["id"];
    
    //smazeme jeho transakce
    $sql4 = 'delete from transakce where id_dokladu='.$idDokladu;
    mysqli_Query($sql4, $noveSRBD) or Die(mysqli_Error());
}
else {
    //vytvoreni noveho dokladu + zjisteni jeho id
    $now = strtotime("1.1.".$_SESSION["rokArchiv"]);
    mysqli_Query('INSERT INTO doklady (id, c_dokladu, skupina, datum, prod_kategorie, typ_vyroby)
    VALUES (0, "Inventura", "Inventura", '.$now.', null, null)', $noveSRBD) or Die(mysqli_Error());
    
    $vysledek3 = mysqli_Query($sql2, $noveSRBD) or Die(mysqli_Error());
    $data3 = mysqli_Fetch_Array($vysledek3);
    $idDokladu = $data3["id"];
}


$stareSRBD=spojeniSRBD($_SESSION["modul"].($_SESSION["rokArchiv"]-1));

$sql = 'select id, IFNULL(mnozstvi,"0") as mnozstvi, IFNULL(prum_cena,"NULL") as prum_cena from zbozi';
$vysledek = mysqli_Query($sql, $stareSRBD) or Die(mysqli_Error());

$noveSRBD=spojeniSRBD($_SESSION["modul"].$_SESSION["rokArchiv"]);
while($data = mysqli_Fetch_Array($vysledek)) {
    $idZbozi = $data["id"];
    $mnozstvi = $data["mnozstvi"];
    $cenaMJ = $data["prum_cena"];
    
    $strPom ='INSERT INTO transakce(id, id_zbozi, id_dokladu, mnozstvi, cena_MJ, cena_KOO)
    VALUES (0, '.$idZbozi.', '.$idDokladu.', '.$mnozstvi.', '.$cenaMJ.', NULL)';
    //echo $strPom."<br/>";
    
    //vlozeni jednotlivych transakci
    mysqli_Query($strPom, $noveSRBD) or Die(mysqli_Error());
    
    //prepocet mnozstvi zbozi na sklade
}//while

// upraveni na zaklade odpisu z vyroby
// $dot_odpisy = 'SELECT Z.id as id,  prum_cena, sum(TD.mnozstvi) as mnozstvi            
//        FROM zbozi as Z                                                                
//        JOIN (SELECT VOT.id_zbozi, -(VOT.mnozstvi) as mnozstvi, VOT.d_id, D.datum      
//              FROM doklady as D                                                        
//              JOIN (SELECT VO.id_zbozi, VO.mnozstvi, T.id_dokladu as d_id              
//                    FROM vyroba_odpisy as VO                                           
//                    JOIN transakce as T ON T.id = VO.id_vyroby                         
//              ) as VOT ON D.id = VOT.d_id )                                            
//        AS TD on Z.id = TD.id_zbozi                                                    
//        GROUP BY Z.id';                                                                
// $stareSRBD=spojeniSRBD($_SESSION["modul"].($_SESSION["rokArchiv"]-1));                
// $vysledek = mysqli_Query($sql, $stareSRBD) or Die(mysqli_Error());                      
//                                                                                       
// $noveSRBD=spojeniSRBD($_SESSION["modul"].$_SESSION["rokArchiv"]);                     
// while($data = mysqli_Fetch_Array($vysledek)) {                                         
//     $idZbozi = $data["id"];                                                           
//     $mnozstvi = $data["mnozstvi"];                                                    
//     $cenaMJ = $data["prum_cena"];                                                     
//                                                                                       
// }                                                                                   


$ct = new constistenceTester($noveSRBD);
$ct->vypisy = "0";
$ct->testAllAmounts();


session_register('hlaseniOK');
$_SESSION['hlaseniOK'] = $texty['inventuraZLonskaOK'];
header("Location: " . $soubory['hlavniStranka']);
exit;
