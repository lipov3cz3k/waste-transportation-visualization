<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Souradnice uzlu
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * node - ID uzlu
 * 
 * chybi kontrola opravneni
 */

//knihovna
//require_once 'library.php';

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['node']) && is_numeric($_POST['node'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }

  //vsechny dostupne moduly
  $result = $db->prepare('SELECT longitude, latitude FROM node WHERE node_id = :node_id');
  $params = array(':node_id' => $_POST['node']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  
  $node = $result->fetch(PDO::FETCH_ASSOC);
    echo htmlspecialchars($node['longitude'], ENT_QUOTES) . "," . htmlspecialchars($node['latitude'], ENT_QUOTES);
 }

?>