<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - smazani opravneni uzivatele
 * 
 * POST parametry:
 * permission - ID opravneni
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['permission']) && is_numeric($_POST['permission'])) {
  
  //smazani opravneni
  $result = $db->prepare('DELETE FROM permission WHERE permission_id = :permission_id');
  $params = array(':permission_id' => $_POST['permission']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php?page=permissions");

?>