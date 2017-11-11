<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - ulozeni modulu
 * 
 * POST parametry:
 * modul - ID modulu
 * name - novy nazev
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['modul']) && is_numeric($_POST['modul']) &&
   isset($_POST['name'])) {
  
  $result = $db->prepare('UPDATE modul SET name = :name WHERE modul_id = :modul_id');
  $params = array(':name' => $_POST['name'], ':modul_id' => $_POST['modul']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php?page=modules");

?>