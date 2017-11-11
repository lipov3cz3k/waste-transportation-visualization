<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - pridani opravneni uzivatele
 * 
 * POST parametry:
 * user - ID uzivatele
 * modul - ID modulu
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
if(isset($_POST['user']) && is_numeric($_POST['user']) &&
   isset($_POST['modul']) && is_numeric($_POST['modul']) &&
   isset($_POST['p_a']) && is_numeric($_POST['p_a']) &&
   isset($_POST['p_b']) && is_numeric($_POST['p_b']) &&
   isset($_POST['p_c']) && is_numeric($_POST['p_c'])) {

  //kontrola existence uz existujicho opravneni
  $result = $db->prepare('SELECT permission_id FROM permission WHERE user_id = :user AND modul_id = :modul');
  $params = array(':user' => $_POST['user'], ':modul' => $_POST['modul']);
  if(!$result->execute($params)) { exit("db chyba"); }
  if($result->rowCount() > 0) {
    die();
  }

  //vlozeni opravneni
  $result = $db->prepare('INSERT INTO permission (user_id, modul_id, p_a, p_b, p_c) VALUES (:user_id, :modul_id, :p_a, :p_b, :p_c)');
  $params = array(':user_id' => $_POST['user'], ':modul_id' => $_POST['modul'], ':p_a' => $_POST['p_a'], ':p_b' => $_POST['p_b'], ':p_c' => $_POST['p_c']);
  if(!$result->execute($params)) { exit("db chyba"); }
}

//header("Location: settings.php?page=permissions");

?>