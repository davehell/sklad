<?php
$db = "";
if(isset($_SESSION['modul'])) {
  if($_SESSION['modul'] == "sklad") {
    $db = $_SESSION['modul'];
  }
  else {
    $db = $_SESSION['modul'].$_SESSION['rokArchiv'];
  }
}
//echo $db;

if (($_SERVER["SERVER_NAME"] == "lmr") ||
    ($_SERVER["SERVER_NAME"] == "localhost") ||
    ($_SERVER["SERVER_NAME"] == "sklad"))
{
  if (!defined("SQL_HOST")) define ("SQL_HOST","localhost");
  if (!defined("SQL_DBNAME")) define ("SQL_DBNAME",$db);
  if (!defined("SQL_USERNAME")) define ("SQL_USERNAME","root");
  if (!defined("SQL_PASSWORD")) define ("SQL_PASSWORD","root");
}
else {
  if (!defined("SQL_HOST")) define ("SQL_HOST","localhost");
  if (!defined("SQL_DBNAME")) define ("SQL_DBNAME",$db);
  if (!defined("SQL_USERNAME")) define ("SQL_USERNAME","root");
  if (!defined("SQL_PASSWORD")) define ("SQL_PASSWORD","");
}

?>
