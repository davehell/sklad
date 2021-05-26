<?php

include 'iSkladObecne.php';

$array = array('id','jmeno','sulinek');
$array2 = array('"ida"','"jmenao"','saulinek');
$order = array('id','jmeno');
$order2 = array('', 'ASC');

genericSQL('naka tabulka', $array, $array2,$order,$order2);


?>
