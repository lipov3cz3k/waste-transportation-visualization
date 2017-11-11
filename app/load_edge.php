<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Nacteni dat hrany scenare / nadscenare
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * scenario - ID scenare
 * upscenario - ID nadscenare
 * node_a - ID uzlu a
 * node_b - ID uzlu b
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

//zadany scenario - informace v ramci scenare
if(isset($_POST['node_a']) && is_numeric($_POST['node_a']) &&
   isset($_POST['node_b']) && is_numeric($_POST['node_b']) &&
   isset($_POST['scenario']) && is_numeric($_POST['scenario'])) {
  
  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)'>◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)'>Přehled uzlů</a></p>";

  //ziskani struktury prepravy
  $result = $db->prepare('SELECT u.load_structure AS load_structure FROM scenario s, upscenario u WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id');
  $params = array(':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
  
  //info o uzlech
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name FROM node n1, node n2 WHERE n1.node_id = :node_a AND n2.node_id = :node_b');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $nodes = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<h2>Trasa " . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " - " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h2>\n";

  //prvni smer
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name, e.load_ab AS e_load_ab, e.scenario_id AS e_scenario_id FROM edge e, node n1, node n2 WHERE e.node_a = :node_a AND e.node_b = :node_b AND e.scenario_id = :scenario_id AND e.node_a = n1.node_id AND e.node_b = n2.node_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $edge_ab = $result->fetch(PDO::FETCH_ASSOC); 

  echo "<h3>" . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " → " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h3>";

  $list_commodity = explode(",", $upscenario['load_structure']);
  $list_load = explode(",", $edge_ab['e_load_ab']);

  //vytvoreni tabulky a grafu
  $chart1 = "";
  echo "<table>";
  echo "<th>Typ odpadu</th><th>Celkové množství [t]</th>";
  foreach($list_commodity as $i => $commodity) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($commodity, ENT_QUOTES) . "</td>";
    echo "<td>";
    $chart1 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "',";
    if(empty($edge_ab)) {
      echo "0";
      $chart1 .= "0";
    }
    else {
      echo htmlspecialchars($list_load[$i], ENT_QUOTES);
      $chart1 .= htmlspecialchars($list_load[$i], ENT_QUOTES);
    }
    echo "</td>";
    echo "</tr>";
    $chart1 .= "],";
  }
  echo "</table>";
  echo "</p>";

  echo "<div id='chart1'></div>";

  
  //druhy smer
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name, e.load_ab AS e_load_ab, e.scenario_id AS e_scenario_id FROM edge e, node n1, node n2 WHERE e.node_a = :node_b AND e.node_b = :node_a AND e.scenario_id = :scenario_id AND e.node_b = n1.node_id AND e.node_a = n2.node_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $edge_ba = $result->fetch(PDO::FETCH_ASSOC);

  echo "<h3>" . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " ← " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h3>";

  $list_commodity = explode(",", $upscenario['load_structure']);
  $list_load = explode(",", $edge_ba['e_load_ab']);

  //vytvoreni tabulky a grafu
  $chart2 = "";
  echo "<table>";
  echo "<th>Typ odpadu</th><th>Celkové množství [t]</th>";
  foreach($list_commodity as $i => $commodity) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($commodity, ENT_QUOTES) . "</td>";
    echo "<td>";
    $chart2 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "',";
    if(empty($edge_ba)) {
      echo "0";
      $chart2 .= "0";
    }
    else {
      echo htmlspecialchars($list_load[$i], ENT_QUOTES);
      $chart2 .= htmlspecialchars($list_load[$i], ENT_QUOTES);
    }
    echo "</td>";
    echo "</tr>";
    $chart2 .= "],";
  }
  echo "</table>";
  echo "</p>";

  echo "<div id='chart2'></div>";

  
  //zapis dat pro grafy
  echo "<script>";
  
  //graf 1 - mnozstvi prepravy po hrane jednotlivych komodit v jednom smeru
  echo "var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Typ nákladu');
    data1.addColumn('number', 'Přeprava');
    data1.addRows([";
  
  rtrim($chart1, ",");
  echo $chart1;
  echo "]);";

  //graf 2 - mnozstvi prepravy po hrane jednotlivych komodit ve druhem smeru
  echo "var data2 = new google.visualization.DataTable();
    data2.addColumn('string', 'Typ nákladu');
    data2.addColumn('number', 'Přeprava');
    data2.addRows([";
  
  rtrim($chart2, ",");
  echo $chart2;
  echo "]);";

  echo "</script>";
}








//zadany upscenario - informace v ramci nadscenare (agragace)
else if(isset($_POST['node_a']) && is_numeric($_POST['node_a']) &&
   isset($_POST['node_b']) && is_numeric($_POST['node_b']) &&
   isset($_POST['upscenario']) && is_numeric($_POST['upscenario'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)'>◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)'>Přehled uzlů</a></p>";

  //ziskani struktury prepravy
  $result = $db->prepare('SELECT u.load_structure AS load_structure '
          . 'FROM upscenario u '
          . 'WHERE u.upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
  
  //info o uzlech
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name '
          . 'FROM node n1, node n2 '
          . 'WHERE n1.node_id = :node_a AND n2.node_id = :node_b');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $nodes = $result->fetch(PDO::FETCH_ASSOC);
  
  //ziskani poctu scenaru v nadscenari
  $result = $db->prepare('SELECT count(*) AS scenario_count FROM scenario WHERE upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $count = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<h2>Trasa " . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " - " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h2>\n";
  echo "<p>";

  echo "Data " . htmlspecialchars($count['scenario_count'], ENT_QUOTES) . " scénářů</p>\n";
  
  //cdv informace o hrane
  $result = $db->prepare('SELECT e.length, e.load_capacity, e.load_height, e.aadt_max, e.aadt_mean, e.tv_mean, e.accidents '
          . 'FROM edge_upscenario e ' 
          . 'WHERE (e.node_a = :node_a AND e.node_b = :node_b) OR (e.node_a = :node_b AND e.node_b = :node_a) AND e.upscenario_id = :upscenario_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $edge = $result->fetch(PDO::FETCH_ASSOC); 

  if($edge['length'] == 0) $edge = $result->fetch(PDO::FETCH_ASSOC);
  echo "<p>";
  echo "Délka: " . $edge['length'] / 1000 . " km<br>";
  echo "Maximální nosnost: " . $edge['load_capacity'] . " t<br>";
  echo "Maximální výška: " . $edge['load_height'] . " m<br>";
  echo "Kapacita trasy: " . $edge['aadt_max'] . " vozidel/rok<br>";
  echo "Průměrné vytížení trasy: " . $edge['aadt_mean'] . " vozidel/rok<br>";
  echo "Průměrné vytížení trasy nákladními vozidly: " . $edge['tv_mean'] . " vozidel/rok<br>";
  if($edge['aadt_mean'] != 0) {
    $edge['tv_aadt'] = $edge['tv_mean'] / $edge['aadt_mean'];
    //echo "Průměrné vytížení trasy nákladními vozidly / průměrné vytížení trasy: " . $edge['tv_aadt'] . "<br>";
  }
  echo "Relativní nehodovost: " . $edge['accidents'] . " osobních nehod/milion vozkm/rok<br>";
  echo "</p>";

  
  
  

  //prvni smer
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name, e.load_ab AS e_load_ab, e.upscenario_id AS e_upscenario_id, e.length, e.load_capacity, e.load_height, e.aadt_max, e.aadt_mean, e.tv_mean, e.accidents '
          . 'FROM edge_upscenario e, node n1, node n2 ' 
          . 'WHERE e.node_a = :node_a AND e.node_b = :node_b AND e.upscenario_id = :upscenario_id AND e.node_a = n1.node_id AND e.node_b = n2.node_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $edge_ab = $result->fetch(PDO::FETCH_ASSOC); 

  echo "<h3>" . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " → " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h3>";
  
  $list_commodity = explode(",", $upscenario['load_structure']);
  $list_load = explode(",", $edge_ab['e_load_ab']);

  //vytvoreni tabulky a grafu
  $chart1 = "";
  echo "<table>";
  echo "<th>Typ odpadu</th><th>Průměrné množství [t]</th><th>Nákladních vozidel</th>";
  foreach($list_commodity as $i => $commodity) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($commodity, ENT_QUOTES) . "</td>";
    echo "<td>";
    $chart1 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "',";
    if(empty($edge_ab)) {
      echo "0";
      $chart1 .= "0";
    }
    else {
      echo htmlspecialchars(round($list_load[$i] / $count['scenario_count'], 1), ENT_QUOTES);
      $chart1 .= htmlspecialchars($list_load[$i], ENT_QUOTES);
    }
    echo "</td>";
    echo "<td>";
    echo round($list_load[$i] / $count['scenario_count'] / 20, 1);
    echo "</td>";
    echo "</tr>";
    $chart1 .= "],";
  }
  echo "</table>";
  echo "</p>";

  //echo "<div id='chart1'></div>";
  
  //histogram prepravy ab
  $result = $db->prepare('SELECT e.load_ab, e.scenario_id, s.name '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_a = :node_a AND e.node_b = :node_b AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3></h3>\n";

    $chart3 = "";
    
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $load_list = explode(",", $edge['load_ab']);
      
      $chart3 .= "[";
      foreach($load_list as $key => $val) {
        $chart3 .= $val . ",";
      }
      $chart3 = rtrim($chart3, ",");
      $chart3 .= "],";
    }
    
    echo "<div id='chart3'></div>";
    //echo $chart3;
  }
  
  //graf nehodovosti
  echo "<h3>Využitelnost trasy</h3>\n";
  $chart5 = "['Data', 'průměr v ČR', ''],
         ['AADT_mean/AADT_max',  " . 0.324 . ", " . ($edge_ab['aadt_max'] ? $edge_ab['aadt_mean'] / $edge_ab['aadt_max'] : 0) . "],
         ['TV_mean/AADT_max',  " . 0.074 . ", " . ($edge_ab['aadt_max'] ? $edge_ab['tv_mean'] / $edge_ab['aadt_max'] : 0) . "]";
  echo "<div id='chart5'></div>";




  //druhy smer
  $result = $db->prepare('SELECT n1.name AS n1_name, n2.name AS n2_name, e.load_ab AS e_load_ab, e.upscenario_id AS e_upscenario_id, e.length, e.load_capacity, e.load_height, e.aadt_max, e.aadt_mean, e.tv_mean, e.accidents  '
          . 'FROM edge_upscenario e, node n1, node n2 '
          . 'WHERE e.node_a = :node_b AND e.node_b = :node_a AND e.upscenario_id = :upscenario_id AND e.node_b = n1.node_id AND e.node_a = n2.node_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $edge_ba = $result->fetch(PDO::FETCH_ASSOC);

  echo "<h3>" . htmlspecialchars($nodes['n1_name'], ENT_QUOTES) . " ← " . htmlspecialchars($nodes['n2_name'], ENT_QUOTES) . "</h3>";
/*  echo "<p>";
  echo "Délka: " . $edge_ba['length'] / 1000 . " km<br>";
  echo "Maximální nosnost: " . $edge_ba['load_capacity'] . " t<br>";
  echo "Maximální výška: " . $edge_ba['load_height'] . " m<br>";
  echo "Kapacita trasy: " . $edge_ba['aadt_max'] . " vozidel/rok<br>";
  echo "Průměrné vytížení trasy: " . $edge_ba['aadt_mean'] . " vozidel/rok<br>";
  echo "Průměrné vytížení trasy nákladními vozidly: " . $edge_ba['tv_mean'] . " vozidel/rok<br>";
  if($edge_ba['aadt_mean'] != 0) {
    $edge_ba['tv_aadt'] = $edge_ba['tv_mean'] / $edge_ba['aadt_mean'];
    //echo "Průměrné vytížení trasy nákladními vozidly / průměrné vytížení trasy: " . $edge_ba['tv_aadt'] . "<br>";
  }
  echo "Relativní nehodovost: " . $edge_ba['accidents'] . " osobních nehod/milion vozkm/rok<br>";
  echo "</p>";
  */
  $list_commodity = explode(",", $upscenario['load_structure']);
  $list_load = explode(",", $edge_ba['e_load_ab']);

  //vytvoreni tabulky a grafu
  $chart2 = "";
  echo "<table>";
  echo "<th>Typ odpadu</th><th>Průměrné množství [t]</th><th>Nákladních vozidel</th>";
  foreach($list_commodity as $i => $commodity) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($commodity, ENT_QUOTES) . "</td>";
    echo "<td>";
    $chart2 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "',";
    if(empty($edge_ba)) {
      echo "0";
      $chart2 .= "0";
    }
    else {
      echo htmlspecialchars(round($list_load[$i] / $count['scenario_count'], 1), ENT_QUOTES);
      $chart2 .= htmlspecialchars($list_load[$i], ENT_QUOTES);
    }
    echo "</td>";
    echo "<td>";
    echo round($list_load[$i] / $count['scenario_count'] / 20, 1);
    echo "</td>";
    echo "</tr>";
    $chart2 .= "],";
  }
  echo "</table>";
  echo "</p>";

  //echo "<div id='chart2'></div>";
  
  //histogram prepravy ba
  $result = $db->prepare('SELECT e.load_ab, e.scenario_id, s.name '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_a = :node_b AND e.node_b = :node_a AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
  $params = array(':node_a' => $_POST['node_a'], ':node_b' => $_POST['node_b'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3></h3>\n";

    $chart4 = "";
    
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $load_list = explode(",", $edge['load_ab']);
      
      $chart4 .= "[";
      foreach($load_list as $key => $val) {
        $chart4 .= $val . ",";
      }
      $chart4 = rtrim($chart4, ",");
      $chart4 .= "],";
    }
    
    echo "<div id='chart4'></div>";
    //echo $chart4;
  }

  
  
  //zapis dat pro grafy
  echo "<script>";
  
  //graf 1 - mnozstvi prepravy po hrane jednotlivych komodit v jednom smeru
  echo "var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Typ nákladu');
    data1.addColumn('number', 'Přeprava');
    data1.addRows([";
  
  rtrim($chart1, ",");
  echo $chart1;
  echo "]);";

  //graf 2 - mnozstvi prepravy po hrane jednotlivych komodit ve druhem smeru
  echo "var data2 = new google.visualization.DataTable();
    data2.addColumn('string', 'Typ nákladu');
    data2.addColumn('number', 'Přeprava');
    data2.addRows([";
  
  rtrim($chart2, ",");
  echo $chart2;
  echo "]);";
  
  //graf 3 - histogram prepravy ab
  echo "var data3 = new google.visualization.DataTable();";
  foreach($list_commodity as $commodity) {
    echo "data3.addColumn('number', '" . $commodity . "');";
  }
  echo "data3.addRows([";
  $chart3 = rtrim($chart3, ",");
  echo $chart3;
  echo "]);";
  
  //graf 4 - histogram prepravy ba
  echo "var data4 = new google.visualization.DataTable();";
  foreach($list_commodity as $commodity) {
    echo "data4.addColumn('number', '" . $commodity . "');";
  }
  echo "data4.addRows([";
  $chart4 = rtrim($chart4, ",");
  echo $chart4;
  echo "]);";
  
  //graf 5 - nehodovost
  echo "var data5 = new google.visualization.arrayToDataTable([";
  echo $chart5;
  echo "]);";

  echo "</script>";
}
?>