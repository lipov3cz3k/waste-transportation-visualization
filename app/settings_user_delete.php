<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - smazani uzivatele
 * 
 * POST parametry:
 * user - ID uzivatele
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['user']) && is_numeric($_POST['user'])) {
  
  //smazani opravneni
  $result = $db->prepare('DELETE FROM user WHERE user_id = :user_id');
  $params = array(':user_id' => $_POST['user']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php?page=users");

?>