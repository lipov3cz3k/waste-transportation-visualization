<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Vytvoreni JS pole s daty hran scenare / nadscenare pro zobrazeni na mape
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * scenario - ID scenare
 * upscenario - ID nadscenare
 * commodity - vyber konkretni komodity k zobrazeni
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


//overeni uzivatele
if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
  //die();
}

//hrany pro scenar
if(isset($_POST['scenario']) && is_numeric($_POST['scenario']) && 
   isset($_POST['commodity']) && is_numeric($_POST['commodity'])) {
 
  //vsechny dostupne hrany
  $result = $db->prepare('SELECT e.node_a, e.node_b, e.load_ab AS load_ab, n1.longitude AS a_lon, n1.latitude AS a_lat, n2.longitude AS b_lon, n2.latitude AS b_lat FROM edge e, node n1, node n2 WHERE e.scenario_id = :scenario_id AND e.node_a = n1.node_id AND e.node_b = n2.node_id');
  $params = array(':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  //vyber hran podle komodit
  //kontrola smyslu zadane komodity (post[commodity])
  
  //vsechny komodity
  if($_POST['commodity'] == 0) {
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      echo "" . htmlspecialchars($edge['node_a'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['node_b'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lat'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lat'], ENT_QUOTES) . ",";
      echo "" . 1 . "";
      echo ";";
    }
  }
  //vybrana komodita
  else {
    //scenar
    /*
    $result2 = $db->prepare('SELECT load_structure FROM scenario WHERE scenario_id = :scenario_id');       // kontrola existence scenare
    $params = array(':scenario_id' => $_POST['scenario']);
    $scenario = $result2->fetch(PDO::FETCH_ASSOC);
    
    $list = split_load_structure($scenario['load_structure']);*/
      
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      
      //preskoceni hran s nulovou prepravou
      //$list = split_load_structure($edge['load_ab']);
      $list = explode(",", $edge['load_ab']);
      if($list[$_POST['commodity'] - 1] == 0) { continue; }
      
      echo "" . htmlspecialchars($edge['node_a'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['node_b'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lat'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lat'], ENT_QUOTES) . ",";
      echo "" . 1 . "";
      echo ";";      
    }
  }
}







//hrany pro nadscenar
else if(isset($_POST['upscenario']) && is_numeric($_POST['upscenario']) && 
   isset($_POST['commodity']) && is_numeric($_POST['commodity'])) {
  
  //vsechny dostupne hrany
  $result = $db->prepare('SELECT e.node_a, e.node_b, e.load_ab AS load_ab, n1.longitude AS a_lon, n1.latitude AS a_lat, n2.longitude AS b_lon, n2.latitude AS b_lat, e.accidents '
          . 'FROM edge_upscenario e, node n1, node n2 '
          . 'WHERE e.upscenario_id = :upscenario_id AND e.node_a = n1.node_id AND e.node_b = n2.node_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  //vyber hran podle komodit
  //kontrola smyslu zadane komodity (post[commodity])
  
  //vsechny komodity
  if($_POST['commodity'] == 0) {

    //pokud je zadan limit pro zvyrazneni hran
    if(isset($_POST['limit']))
    {
      while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "" . htmlspecialchars($edge['node_a'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['node_b'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['a_lon'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['a_lat'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['b_lon'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['b_lat'], ENT_QUOTES) . ",";
        if($edge['accidents'] > $_POST['limit']) echo "" . 2 . "";
        else echo "" . 1 . "";   
        echo ";";
      }
    }
    //vsechny hrany stejnou barvou
    else {
      while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "" . htmlspecialchars($edge['node_a'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['node_b'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['a_lon'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['a_lat'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['b_lon'], ENT_QUOTES) . ",";
        echo "" . htmlspecialchars($edge['b_lat'], ENT_QUOTES) . ",";
        echo "" . 1 . "";
        echo ";";
      }
    }
  }
  //vybrana komodita
  else {
    //scenar
    /*
    $result2 = $db->prepare('SELECT load_structure FROM scenario WHERE scenario_id = :scenario_id');       // kontrola existence scenare
    $params = array(':scenario_id' => $_POST['scenario']);
    $scenario = $result2->fetch(PDO::FETCH_ASSOC);
    
    $list = split_load_structure($scenario['load_structure']);*/
      
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      
      //preskoceni hran s nulovou prepravou
      //$list = split_load_structure($edge['load_ab']);
      $list = explode(",", $edge['load_ab']);
      if($list[$_POST['commodity'] - 1] == 0) { continue; }
      
      echo "" . htmlspecialchars($edge['node_a'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['node_b'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['a_lat'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lon'], ENT_QUOTES) . ",";
      echo "" . htmlspecialchars($edge['b_lat'], ENT_QUOTES) . ",";
      echo "" . 1 . "";
      echo ";";      
    }
  }
}
?>