<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Vytvoreni nadpisu stranky
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * modul - ID modulu
 * upscenario - ID nadscenare
 * scenario - ID scenare
 * all - prepinac vseho (true/false)
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


//nazev modulu
if(isset($_POST['modul']) && is_numeric($_POST['modul']) && 
        isset($_POST['user'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }

  //nazev modulu
  $result = $db->prepare('SELECT m.name '
          . 'FROM modul m, permission p '
          . 'WHERE m.modul_id = :modul_id AND m.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 0');
  $params = array(':modul_id' => $_POST['modul'], ':user_id' => $_POST['user']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  $modul = $result->fetch(PDO::FETCH_ASSOC);
  
  echo htmlspecialchars($modul['name'], ENT_QUOTES);
}



//nazev nadscenare
else if(isset($_POST['upscenario']) && is_numeric($_POST['upscenario']) && 
        !isset($_POST['all']) && 
        isset($_POST['user'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }

  //nazev nadscenare
  $result = $db->prepare('SELECT u.name '
          . 'FROM upscenario u, permission p '
          . 'WHERE u.upscenario_id = :upscenario_id AND u.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 1');
  $params = array(':upscenario_id' => $_POST['upscenario'], ':user_id' => $_POST['user']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
  
  echo htmlspecialchars($upscenario['name'], ENT_QUOTES);
}



//nazev scenare
else if(isset($_POST['scenario']) && is_numeric($_POST['scenario']) && 
        !isset($_POST['all']) && 
        isset($_POST['user'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }

  //nazev scenare
  $result = $db->prepare('SELECT s.name '
          . 'FROM scenario s, upscenario u, permission p '
          . 'WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 2');
  $params = array(':scenario_id' => $_POST['scenario'], ':user_id' => $_POST['user']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  $scenario = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "Scénář " . htmlspecialchars($scenario['name'], ENT_QUOTES);
}





//vse po nadscenar
else if(isset($_POST['all']) && 
        $_POST['all'] === "true" &&
        isset($_POST['upscenario']) && is_numeric($_POST['upscenario']) && 
        isset($_POST['user'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user']) {
    die();
  }
  
  //nazvy
  $result = $db->prepare('SELECT m.name AS m_name, u.name AS u_name '
          . 'FROM modul m, upscenario u, permission p '
          . 'WHERE u.upscenario_id = :upscenario_id AND u.modul_id = m.modul_id AND m.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 1');
  $params = array(':upscenario_id' => $_POST['upscenario'], ':user_id' => $_POST['user']);
  
  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  $data = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<h1>" . htmlspecialchars($data['m_name'], ENT_QUOTES) . "</h1>\n";
  echo "<h3><span id='upscenario'>" . htmlspecialchars($data['u_name'], ENT_QUOTES) . "</span>\n";
  echo "<span id='scenario'></span></h3>";
}




//vse po scenar
else if(isset($_POST['all']) && $_POST['all'] === "true" &&
        isset($_POST['scenario']) && is_numeric($_POST['scenario']) && 
        isset($_POST['user'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  //nazvy
  $result = $db->prepare('SELECT s.user_id AS user, m.name AS m_name, u.name AS u_name, s.name AS s_name '
          . 'FROM modul m, upscenario u, scenario s, permission p '
          . 'WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = m.modul_id AND m.modul_id = p.modul_id AND p.user_id = :user_id AND p.p_b >= 2');
  $params = array(':scenario_id' => $_POST['scenario'], ':user_id' => $_POST['user']);

  //provedeni dotazu
  if(!$result->execute($params)) { exit("db chyba"); }
  $data = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<h1>" . htmlspecialchars($data['m_name'], ENT_QUOTES) . "</h1>\n";
  echo "<h3><span id='upscenario'>" . htmlspecialchars($data['u_name'], ENT_QUOTES) . "</span> | \n";
  echo "<span id='scenario'>Scénář " . htmlspecialchars($data['s_name'], ENT_QUOTES) . "</span></h3>";
}
?>