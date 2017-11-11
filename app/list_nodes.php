<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Seznam dostupnych uzlu pro scenar / nadscenar
 * 
 * POST parametry:
 * scenario - ID scenare
 * upscenario - ID nadscenare
 * 
 */

//knihovna
//require_once 'library.php';


//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();


//zadany scenario - seznam pro dany scenar
if(isset($_POST['scenario']) && is_numeric($_POST['scenario'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }
  
  //info o modulu
  $result = $db->prepare('SELECT m.modul_id FROM modul m, upscenario u, scenario s WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = m.modul_id');
  $params = array(':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $modul = $result->fetch(PDO::FETCH_ASSOC);
  
  //vsechny uzly scenare
  $result = $db->prepare('SELECT * FROM node WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $modul['modul_id']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)' >◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(INFO)' >Scénář</a></p>";
  echo "<h3>Uzly</h3>";
  
  while($node = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='node' id='node" . htmlspecialchars($node['node_id'], ENT_QUOTES) . "'>\n";
      echo "<a href='javascript:void(0)' onclick='load_node(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", null, " . htmlspecialchars($_POST['scenario'], ENT_QUOTES) . ", " . htmlspecialchars($node['node_id'], ENT_QUOTES) . ");'>";
      echo "<span class='modul_name'>" . htmlspecialchars($node['name'], ENT_QUOTES) . "</span>\n";
      echo "</a>";      
    echo "</div>\n";
  }
}






//zadany upscenario - seznam pro nadscenar
else if(isset($_POST['upscenario']) && is_numeric($_POST['upscenario'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  //info o modulu
  $result = $db->prepare('SELECT m.modul_id FROM modul m, upscenario u WHERE u.upscenario_id = :upscenario_id AND u.modul_id = m.modul_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $modul = $result->fetch(PDO::FETCH_ASSOC);
  
  //vsechny uzly scenare
  $result = $db->prepare('SELECT * FROM node WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $modul['modul_id']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)' >◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(INFO)' >Nadscénář</a></p>";
  echo "<h3>Uzly</h3>";
  
  while($node = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='node' id='node" . htmlspecialchars($node['node_id'], ENT_QUOTES) . "'>\n";
      echo "<a href='javascript:void(0)' onclick='select_node(markers, marker, ); load_node(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . ", null, " . htmlspecialchars($node['node_id'], ENT_QUOTES) . ");'>";
      echo "<span class='modul_name'>" . htmlspecialchars($node['name'], ENT_QUOTES) . "</span>\n";
      echo "</a>";      
    echo "</div>\n";
  }
}
?>