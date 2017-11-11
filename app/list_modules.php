<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Seznam dostupnych modulu
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
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
  
  //vsechny dostupne moduly (podle opravneni)
  $result = $db->prepare('SELECT m.modul_id, m.name FROM modul m, permission p WHERE m.modul_id = p.modul_id AND p.user_id = :user_id ORDER BY m.name');
  $params = array(':user_id' => $_POST['user']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  
  while($modul = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='modul' id='modul" . htmlspecialchars($modul['modul_id'], ENT_QUOTES) . "'>\n";
      echo "<a href='javascript:void(0)' onclick='load_modul(" . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($modul['modul_id'], ENT_QUOTES) . ");'>";
      echo "<span class='modul_icon'>►</span>";// ▼ ◄ ▲
      echo "<span class='modul_name'>" . htmlspecialchars($modul['name'], ENT_QUOTES) . "</span>\n";
      echo "</a>";      
    echo "</div>\n";
  }
}
?>