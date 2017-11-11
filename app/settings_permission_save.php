<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - ulozeni opravneni
 * 
 * POST parametry:
 * permission - ID opravneni
 * p_a - opravneni a
 * p_b - opravneni b
 * p_c - opravneni c
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['permission']) && is_numeric($_POST['permission']) &&
   isset($_POST['p_a']) && is_numeric($_POST['p_a']) &&
   isset($_POST['p_b']) && is_numeric($_POST['p_b']) &&
   isset($_POST['p_c']) && is_numeric($_POST['p_c'])) {
  
  $result = $db->prepare('UPDATE permission SET p_a = :p_a, p_b = :p_b, p_c = :p_c WHERE permission_id = :permission_id');
  $params = array(':p_a' => $_POST['p_a'], ':p_b' => $_POST['p_b'], ':p_c' => $_POST['p_c'], ':permission_id' => $_POST['permission']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php?page=permissions");

?>