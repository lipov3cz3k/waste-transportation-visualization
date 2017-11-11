<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Vytvoreni JS pole s daty uzlu scenare / nadscenare pro zobrazeni na mape
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * scenario - ID scenare
 * upscenario - ID nadscenare
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


//body pro scenar
if(isset($_POST['scenario']) && is_numeric($_POST['scenario'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  //info o modulu
  $result = $db->prepare('SELECT m.modul_id FROM modul m, upscenario u, scenario s WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = m.modul_id');
  $params = array(':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $modul = $result->fetch(PDO::FETCH_ASSOC);
  
  //vyber vsech uzlu daneho scenare
  $result = $db->prepare('SELECT * FROM node WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $modul['modul_id']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  
  $output = "";
  
  while($node = $result->fetch(PDO::FETCH_ASSOC)) {
    $output .= "" . htmlspecialchars($node['node_id'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['longitude'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['latitude'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['type'], ENT_QUOTES) . "";
    $output .= ";";
  }

  $output = rtrim($output, ";");
  echo $output;
}





//body pro nadscenar
else if(isset($_POST['upscenario']) && is_numeric($_POST['upscenario'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }
  
  //vyber jednoho scenare z nadscenare - predpoklad stejnych uzlu u vsec scenaru!
  $result = $db->prepare('SELECT scenario_id FROM scenario s WHERE upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $scenario = $result->fetch(PDO::FETCH_ASSOC);
  
  //vyber vsech uzlu daneho scenare
  $result = $db->prepare('SELECT n.* FROM node n, upscenario u, modul m WHERE u.upscenario_id = :upscenario_id AND u.modul_id = m.modul_id AND n.modul_id = m.modul_id');            // vyber atributu
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  $output = "";
  
  while($node = $result->fetch(PDO::FETCH_ASSOC)) {
    $output .= "" . htmlspecialchars($node['node_id'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['longitude'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['latitude'], ENT_QUOTES) . ",";
    $output .= "" . htmlspecialchars($node['type'], ENT_QUOTES) . "";
    $output .= ";";      
  }

  $output = rtrim($output, ";");
  echo $output;
}
?>