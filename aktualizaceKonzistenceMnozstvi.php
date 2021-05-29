<?php


/*
  1.dotaz ktery zjisti mnozstvi a] podle transakci od zacatku
                                b] podle aktualniho mnozstvi sklad
  2. test shody
     a] =  ... OK
     b] != ... i)  aktualizace mnozstvi podle transakci
               ii) aktualizace transakce inventura podle mnozstvi
*/

header("Content-Type: text/html; charset=iso-8859-2");
include_once "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);


class constistenceTester{

var $type = "trans";      // typ aktualizace a]trans b]amount, viz zacatek
private $SRBD;            // SRBD - priradi se v konstruktoru
public $vypisy = "1";     // nastaveni detailnejsich vypisu
public $editDB = true;    // detekce povoleni upravy DB - lze tak napr. vypnout editaci a povolit pouze vypisy
//const vypisy = "0";

const dotazTrans = "select id, nazev, c_vykresu, (IFNULL(sum(mnozstvi),0)) as mnozstvi from
      ((SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      LEFT JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_MJ, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Nákup','Inventura')  )
      as TD on Z.id = TD.id_zbozi
      GROUP BY Z.id)
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      LEFT JOIN (SELECT T.id_zbozi, -(T.mnozstvi) as mnozstvi, T.cena_MJ, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Prodej','Zmetkování')  )
      as TD on Z.id = TD.id_zbozi
      GROUP BY Z.id)
      UNION ALL
      (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
      FROM zbozi as Z
      LEFT JOIN (SELECT T.id_zbozi, T.mnozstvi, T.cena_KOO, D.skupina
            FROM transakce as T
            JOIN doklady as D ON D.id=T.id_dokladu
            WHERE D.skupina in('Výroba','Kooperace')  )
      as TD on Z.id = TD.id_zbozi
      GROUP BY Z.id)
      UNION ALL
       (SELECT Z.id, Z.zac_c_vykresu, nazev, c_vykresu, prum_cena, sum(TD.mnozstvi) as mnozstvi, (Z.prum_cena) as cena_MJ
       FROM zbozi as Z
       JOIN (SELECT VOT.id_zbozi, -(VOT.mnozstvi) as mnozstvi, VOT.d_id, D.datum
             FROM doklady as D
             JOIN (SELECT VO.id_zbozi, VO.mnozstvi, T.id_dokladu as d_id
                   FROM vyroba_odpisy as VO
                   JOIN transakce as T ON T.id = VO.id_vyroby
             ) as VOT ON D.id = VOT.d_id)
       AS TD on Z.id = TD.id_zbozi
       GROUP BY Z.id)
      ) as TSUMY GROUP BY id ORDER BY nazev, c_vykresu
";

const dotazAmount =   "select id, nazev, c_vykresu, (Z.mnozstvi + IFNULL(T2.mnozstvi,0)) AS mnozstvi
                  from zbozi as Z left join (SELECT T.id_zbozi, T.mnozstvi as mnozstvi
                                  FROM transakce as T
                                  join doklady D on T.id_dokladu=D.id
                                  WHERE D.skupina = 'Rezervace') as T2
                  on Z.id=T2.id_zbozi
                  ORDER BY nazev, c_vykresu";

//konstruktory
    public function __construct($nSRBD = -1){
    if($nSRBD==-1)
      $this->SRBD = spojeniSRBD();
    else
      $this->SRBD = $nSRBD;
    }
    


//metody
    public function testAllAmounts(){

    

//    echo $this->dotaz;
    // ulozeni vysledku do
    $result1 = mysqli_Query(self::dotazTrans, $this->SRBD) or Die(mysqli_Error());

    $result2 = mysqli_Query(self::dotazAmount, $this->SRBD) or Die(mysqli_Error());
    
    $numrows1 = mysqli_num_rows($result1);
    $numrows2 = mysqli_num_rows($result2);

    $updated = 0;

    if(($numrows1 == $numrows2) && ($numrows1 != 0))
    {
      //iterace pres kurzory
      while(($data1 = mysqli_fetch_array($result1)) && ($data2 = mysqli_fetch_array($result2)))
      {
         if($data1['mnozstvi'] == $data2['mnozstvi'])
         {
                //vse ok
         }
         else {
            self::act($data1['id'],$data1['mnozstvi']);
            $updated++;
         }

      }

    }
    else
    {
      //echo $numrows1 . '  ' . $numrows2 . '  ';

    }

    //echo "Bylo aktualizovano celkem: '$updated'";
    }



    private function act($id, $amount)
    {
      if($this->type == "trans")
        self::actByTransactions($id,$amount);
      else
        self::actByAmount($id,$amount);
    }

    private function actByTransactions($id, $amount){
        if($this->vypisy) {
            echo "aktualizuju podle transakci  id:'$id' amount:'$amount' <br />";
        }
          
        
        if($this->editDB)
        {
          $query = "UPDATE zbozi SET mnozstvi = '$amount' WHERE id='$id'";
          mysqli_Query($query, $this->SRBD);
        }
    }

    //nastavi inventuru na amount = pozor mozna bude treba si pohrat s prum.cenama a podobne
    private function actByAmount($id, $amount){
        if($this->vypisy) {
            echo "aktualizuju podle mnozstvi <br />";
        }
          
        if($this->editDB)
        {
          $query = "UPDATE transakce SET mnozstvi = '$amount' WHERE id='$id'";
          mysqli_Query($query, $this->SRBD);
        }

    }
    
    /**
     * vypise ve vhodnem tvaru polozky u kterych je chyba nebo nejakou peknou hlasku ze neni
     */
    public function echoInconsistentItems()
    {
    $result1 = mysqli_Query(self::dotazTrans, $this->SRBD) or Die(mysqli_Error());

    $result2 = mysqli_Query(self::dotazAmount, $this->SRBD) or Die(mysqli_Error());
    
    $numrows1 = mysqli_num_rows($result1);
    $numrows2 = mysqli_num_rows($result2);

    $updated = 0;

    if(($numrows1 == $numrows2) && ($numrows1 != 0))
    {

      //iterace pres kurzory
      while(($data1 = mysqli_fetch_array($result1)) && ($data2 = mysqli_fetch_array($result2)))
      {
         if($data1['mnozstvi'] == $data2['mnozstvi'])
         {
                //vse ok
         }
         else {
                if($updated==0)
                {
                        echo "<p class=\"hlaseniChyba\">Chyby v mno¾ství na skladì.</p> ";
           echo "<table>
            <thead>
              <th>zbo¾í[è_výkresu]</th>
              <th>uvedené mno¾ství</th>
              <th>správné mno¾ství</th>
            </thead><tbody>";
                }
         
                echo '
                    <tr';
                    if($updated%2==0)
                      echo ' class="sudyRadek"';
                    echo'>';

            echo '
                    <td>'.$data1['nazev'] .'['.$data1['c_vykresu'].' ] </td>
                    <td>'. $data2['mnozstvi'].' </td>
                    <td>'.$data1['mnozstvi'].'</td>
                  </tr>';
            $updated++;
         }
      }
      
      if($updated>0)
      {
        echo "</tbody>
            <tfoot>
              <tr>
                <td><b>Celkem:</b> $updated polo¾ek</td>
                <td></td>
                <td></td>
              </tr>
            </tfoot>
            </table>";
      }
      else
        echo "<p class=\"hlaseniOK\">V¹echno mno¾ství ve skladì je v poøádku.</p> ";
    }
   }
}


//$ct = new constistenceTester;
//$ct->vypisy = "1";
//$ct->echoInconsistentItems();
//$ct->testAllAmounts();

?>
