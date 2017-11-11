<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Nacteni informaci o nadscenari
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
if(isset($_POST['upscenario']) && is_numeric($_POST['upscenario'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }


  //info o modulu a nadscenari
  $result = $db->prepare('SELECT u.name AS u_name, u.upscenario_id AS u_id, u.load_structure AS u_load_structure '
          . 'FROM upscenario u, permission p '
          . 'WHERE u.upscenario_id = :upscenario_id AND p.user_id = :user_id AND u.modul_id = p.modul_id AND p.p_b >= 1');
  $params = array(':upscenario_id' => $_POST['upscenario'], 'user_id' => $_POST['user']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
   
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)' >◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)' >Uzly</a></p>";
  
  echo "<h3>" . $upscenario['u_name'] . "</h3>";
    
  //seznam komodit
  
  $list = explode(",", $upscenario['u_load_structure']);
  echo "<p>";
  foreach($list as $i => $commodity) {
    echo "<a href='javascript:void(0)' class='a_select' onclick='draw_edges(map, " . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($upscenario['u_id'], ENT_QUOTES) . ", null, " . ($i + 1) . ")'>" . $commodity . "</a><br>";
  }
  echo "<a href='javascript:void(0)' class='a_select' onclick='draw_edges(map, " . htmlspecialchars($_SESSION['UID'], ENT_QUOTES) . ", " . htmlspecialchars($upscenario['u_id'], ENT_QUOTES) . ", null, 0)'>všechny</a><br>";
  echo "</p>";

  echo "<p>Nastavení hranice zvýraznění hran:<br>";
  echo "<input type='number' id='highlight_limit' step='0.1' />";
  echo "<button id='highlight'>Zobrazit</button>";
  echo "</p>";
  
  
  //data pro histogram
  $result = $db->prepare('SELECT e.edge_upscenario_id, e.accidents '
          . 'FROM edge_upscenario e '
          . 'WHERE e.upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    //vytvoreni tabulky a grafu
    $chart1 = "";
    
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      if($edge['accidents'] == 0 || $edge['accidents'] > 2) continue;
      $chart1 .= "['" . $edge['edge_upscenario_id'] . "'," . $edge['accidents'] . "],";
    }
  }
    
  echo "<div id='chart1'></div>";
  //echo $chart1;
  
  echo '<script>';
    
  //graf 1 - histogram nehodovosti
  echo "var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Hrana');
    data1.addColumn('number', 'Relativní nehodovost');
    data1.addRows([";
  $chart1 = rtrim($chart1, ",");
  echo $chart1;
  echo "]);";
  
  echo '

    $(".a_select").click(function(){
      $(".a_select").css("font-weight", "normal");
      $(this).css("font-weight", "bold");
    });
    
    $("button#highlight").click(function(){
      //alert(45 + $("input#highlight_limit").val());
      highlight_edges(' . $_SESSION['UID'] . ', $("input#highlight_limit").val(), ' . htmlspecialchars($upscenario['u_id'], ENT_QUOTES) . ');
    });
  </script>';
}
?>