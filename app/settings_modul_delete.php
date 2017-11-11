<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - smazani celeho modulu
 * 
 * POST parametry:
 * modul - ID modulu
 * 
 */

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola parametru
if(isset($_POST['modul']) && is_numeric($_POST['modul'])) {
  
  //nalezeni vsech nadscenaru daneho modulu
  $result = $db->prepare('SELECT upscenario_id FROM upscenario WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $_POST['modul']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  //pro vsechny nadscenare
  while($upscenario = $result->fetch(PDO::FETCH_ASSOC)) {
    //nalezeni vsech scenaru daneho nadscenare
    $result2 = $db->prepare('SELECT scenario_id FROM scenario WHERE upscenario_id = :upscenario_id');
    $params = array(':upscenario_id' => $upscenario['upscenario_id']);
    if(!$result2->execute($params)) { exit("db chyba"); }
    
    //pro vsechny scenare
    while($scenario = $result2->fetch(PDO::FETCH_ASSOC)) {
      //smazani hran daneho scenare
      $result3 = $db->prepare('DELETE FROM edge WHERE scenario_id = :scenario_id');
      $params = array(':scenario_id' => $scenario['scenario_id']);
      if(!$result3->execute($params)) { exit("db chyba"); }
    }
    
    //smazani vsech scenaru daneho nadscenare
    $result4 = $db->prepare('DELETE FROM scenario WHERE upscenario_id = :upscenario_id');
    $params = array(':upscenario_id' => $upscenario['upscenario_id']);
    if(!$result4->execute($params)) { exit("db chyba"); }
    
    //smazani vsech hran agregaci daneho nadscenare
    $result5 = $db->prepare('DELETE FROM edge_upscenario WHERE upscenario_id = :upscenario_id');
    $params = array(':upscenario_id' => $upscenario['upscenario_id']);
    if(!$result5->execute($params)) { exit("db chyba"); }
  }
  
  //smazani vsech nadscenaru daneho modulu
  $result6 = $db->prepare('DELETE FROM upscenario WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $_POST['modul']);
  if(!$result6->execute($params)) { exit("db chyba"); }
  
  //smazani vsech uzlu daneho modulu
  $result7 = $db->prepare('DELETE FROM node WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $_POST['modul']);
  if(!$result7->execute($params)) { exit("db chyba"); }
  
  //smazani vsech opravneni daneho modulu
  $result8 = $db->prepare('DELETE FROM permission WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $_POST['modul']);
  if(!$result8->execute($params)) { exit("db chyba"); }
  
  //smazani modulu
  $result9 = $db->prepare('DELETE FROM modul WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $_POST['modul']);
  if(!$result9->execute($params)) { exit("db chyba"); }
}

header("Location: settings.php#permissions")

?>