<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - ulozeni uzivatele
 * 
 * POST parametry:
 * user - ID uzivatele
 * type - typ uzivatele
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['user']) && is_numeric($_POST['user']) &&
   isset($_POST['type']) && is_numeric($_POST['type'])) {

  $result = $db->prepare('UPDATE user SET type = :type WHERE user_id = :user_id');
  $params = array(':type' => $_POST['type'], ':user_id' => $_POST['user']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php?page=users");

?>