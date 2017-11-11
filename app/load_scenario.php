<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Nacteni dat o scenari
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * scenario - ID scenare
 * 
 */

//knihovna
//require_once 'library.php';


//drive scenario.php

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();


//kontrola parametru
if(isset($_POST['scenario']) && is_numeric($_POST['scenario'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }


  //info o modulu, nadscenari a scenari
  $result = $db->prepare('SELECT m.name AS m_name, m.info AS m_info, u.name AS u_name, u.upscenario_id AS u_id, s.name AS s_name, s.scenario_id AS s_id, u.load_structure AS u_load_structure '
          . 'FROM modul m, upscenario u, scenario s, permission p '
          . 'WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = m.modul_id AND u.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 2');
  $params = array(':scenario_id' => $_POST['scenario'], ':user_id' => $_POST['user']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $scenario = $result->fetch(PDO::FETCH_ASSOC);
   
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)' >◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)' >Přehled uzlů</a></p>";
  
  echo "<h3>" . $scenario['s_name'] . "</h3>";
  
  //seznam komodit
  $list = explode(",", $scenario['u_load_structure']);
  echo "<p>";
  foreach($list as $i => $commodity) {
    echo "<a href='javascript:void(0)' class='a_select' onclick='draw_edges(map, " . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['u_id'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['s_id'], ENT_QUOTES) . ", " . ($i + 1) . ")'>" . $commodity . "</a><br>";
  }
  echo "<a href='javascript:void(0)' class='a_select' onclick='draw_edges(map, " . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['u_id'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['s_id'], ENT_QUOTES) . ", 0)'>všechny</a><br>";
  echo "</p>";
  
  
  echo '<script>
    $(".a_select").click(function(){
      $(".a_select").css("font-weight", "normal");
      $(this).css("font-weight", "bold");
    });
  </script>';
}
?>