<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Odhlaseni uzivatele
 * 
 */


session_start();
$_SESSION['email'] = "";
$_SESSION['logged'] = 0;
$_SESSION['UID'] = 0;
session_destroy();
header("Location: index.php");

?>