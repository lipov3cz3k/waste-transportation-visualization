<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Seznam dostupnych scenaru
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
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


//kontrola parametru
if(isset($_POST['user']) && is_numeric($_POST['user'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  //vsechny dostupne moduly
  $result = $db->prepare('SELECT s.scenario_id, s.name '
          . 'FROM scenario s, permission p, upscenario u '
          . 'WHERE s.upscenario_id = :upscenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 2');
  $params = array(':user_id' => $_POST['user'], ':upscenario_id' => $_POST['upscenario']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  
  echo "Scánář: <select id='sel_u" . $_POST['upscenario'] . "'>\n";
  echo "<option disabled='disabled' selected='selected'>-- vyberte scénář --</option>";
  
  while($scenario = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . htmlspecialchars($scenario['scenario_id'], ENT_QUOTES) . "'>";
    echo htmlspecialchars($scenario['name'], ENT_QUOTES);
    echo "</option>";
  }
  echo "</select>\n";
  
  echo "<a id='a1_u" . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . "' href='javascript:void(0)' onclick='load_scenario(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['scenario_id'], ENT_QUOTES) . ", \"INFO\", 0)'>scénář</a> ";
  echo "<a id='a2_u" . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . "' href='javascript:void(0)' onclick='load_scenario(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($scenario['scenario_id'], ENT_QUOTES) . ", \"NODES\", 0)'>uzly</a>";
  
  echo '<script>'
  . '$("#sel_u' . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . '").change(function(){'
  . '$("#a1_u' . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . '").attr("onclick", "load_scenario(' . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ', " + $("#sel_u' . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . '").val() + ", \"INFO\", 0)");'
  . '$("#a2_u' . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . '").attr("onclick", "load_scenario(' . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ', " + $("#sel_u' . htmlspecialchars($_POST['upscenario'], ENT_QUOTES) . '").val() + ", \"NODES\", 0)");'        
  . '});</script>';
}
?>