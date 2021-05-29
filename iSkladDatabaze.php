<?php
if($_SESSION['modul'] == "sklad") {
  $db = $_SESSION['modul'];
}
else {
  $db = $_SESSION['modul'].$_SESSION['rokArchiv'];
}
//echo $db;

if (($_SERVER["SERVER_NAME"] == "lmr") ||
    ($_SERVER["SERVER_NAME"] == "localhost") ||
    ($_SERVER["SERVER_NAME"] == "sklad"))
{
  define ("SQL_HOST","localhost");
  define ("SQL_DBNAME",$db);
  define ("SQL_USERNAME","root");
  define ("SQL_PASSWORD","root");
}
else {
  define ("SQL_HOST","localhost");
  define ("SQL_DBNAME",$db);
  define ("SQL_USERNAME","root");
  define ("SQL_PASSWORD","");
}

?>
