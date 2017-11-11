<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Seznam dostupnych nadscenaru
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * modul - ID modulu
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
  
  //vsechny dostupne nadscenare
  $result = $db->prepare('SELECT u.upscenario_id, u.name, p.p_b '
          . 'FROM upscenario u, permission p '
          . 'WHERE p.user_id = :user_id AND u.modul_id = p.modul_id AND u.modul_id = :modul_id AND p.p_b >= 1 '
          . '');
  $params = array(':user_id' => $_POST['user'], ':modul_id' => $_POST['modul']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  while($upscenario = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='upscenario' id='upscenario" . htmlspecialchars($upscenario['upscenario_id'], ENT_QUOTES) . "'>\n";
      echo "<a href='javascript:void(0)' onclick='load_upscenario(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($upscenario['upscenario_id'], ENT_QUOTES) . ");'>";
      echo "<span class='scenario_name'>" . htmlspecialchars($upscenario['name'], ENT_QUOTES) . "</span>\n";
      echo "</a>";
      //pri opravneni na scenare
      if($upscenario['p_b'] >= 2) {
        echo " (<a href='javascript:void(0)' onclick='list_scenarios(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($upscenario['upscenario_id'], ENT_QUOTES) . ");'>scénáře</a>)";
      }
    echo "</div>\n";
  }
}
?>