<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Nacteni dat uzlu scenare / nadscenare
 * 
 * POST parametry:
 * user - UID prihlaseneho uzivatele
 * upscenario - ID scenare
 * scenario - ID nadscenare
 * node - ID uzlu
 * 
 * chybi kontrola opravneni
 */

//knihovna
require_once 'library.php';

//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//zadany scenario - vypis konkretnich dat z daneho scenare
if(isset($_POST['node']) && is_numeric($_POST['node']) &&
   isset($_POST['scenario']) && is_numeric($_POST['scenario'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }
  
  //ziskani informaci o uzlu
  $result = $db->prepare('SELECT n.* '
          . 'FROM modul m, upscenario u, scenario s, node n '
          . 'WHERE node_id = :node_id AND s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id AND u.modul_id = m.modul_id AND n.modul_id = m.modul_id');
  $params = array(':node_id' => $_POST['node'], ':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $node = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)'>◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)'>Přehled uzlů</a></p>";
  echo "<h2>" . htmlspecialchars($node['name'], ENT_QUOTES) . "</h2>\n";
  echo "<p>GPS: " . htmlspecialchars($node['longitude'], ENT_QUOTES) . ", " . htmlspecialchars($node['latitude'], ENT_QUOTES) . "<br>\n";
  echo "Kraj: " . htmlspecialchars($node['region'], ENT_QUOTES) . " (" . htmlspecialchars($node['country'], ENT_QUOTES) . ")<br>\n";
  echo "" . htmlspecialchars($node['info'], ENT_QUOTES) . "<br>\n";
  echo "" . htmlspecialchars($node_type[$node['type']], ENT_QUOTES) . "</p>\n";

  //zjisteni struktury komodit ze scenare
  $result = $db->prepare('SELECT u.load_structure '
          . 'FROM upscenario u, scenario s '
          . 'WHERE s.scenario_id = :scenario_id AND s.upscenario_id = u.upscenario_id');
  $params = array(':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
  $commodity_list = explode(",", $upscenario['load_structure']);
  
  //odvoz
  $result = $db->prepare('SELECT n.name AS n_name, e.load_ab AS e_load_ab '
          . 'FROM edge e, node n '
          . 'WHERE e.node_a = :node_id AND e.node_b = n.node_id AND e.scenario_id = :scenario_id');
  $params = array(':node_id' => $_POST['node'], ':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Odvoz</h3>\n";

    //vytvoreni tabulky jednotlivych mest a celkove tabulky
    $total = array();
    $edges_count = 0;
    echo "<table>";
    echo "<th>Směr</th><th>Typ odpadu</th><th>Celkové množství [t]</th>";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      //pocitadlo zobrazovanych hran (kvuli zobrazeni celkove tabulky)
      $edges_count++;

      echo "<tr><td rowspan='" . count($commodity_list) . "'>";
      echo "→ " . $edge['n_name'];
      echo "</td>";
      $load_list = explode(",", $edge['e_load_ab']);
      foreach ($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $load_list[$commodity_key] . "</td>";
        $total[$commodity] += $load_list[$commodity_key];
        echo "</tr>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</p>";

    //celkova tabulka odvozu
    if($edges_count > 1) {
      echo "<p>";
      echo "<table>";
      echo "<tr><td rowspan='" . count($commodity_list) . "'>Celkem</td>";
      foreach ($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $total[$commodity] . "</td>";
        echo "</tr>";
      }
      echo "</table>";

      echo "</p>";
    }
  }
  
  
  //dovoz
  $result = $db->prepare('SELECT n.name AS n_name, e.load_ab AS e_load_ab '
          . 'FROM edge e, node n '
          . 'WHERE e.node_b = :node_id AND e.node_a = n.node_id AND e.scenario_id = :scenario_id');
  $params = array(':node_id' => $_POST['node'], ':scenario_id' => $_POST['scenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Dovoz</h3>\n";

    //vytvoreni tabulky jednotlivych mest a celkove tabulky
    $total = array();
    $edges_count = 0;
    echo "<table>";
    echo "<th>Směr</th><th>Typ odpadu</th><th>Celkové množství [t]</th>";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      //pocitadlo zobrazovanych hran (kvuli zobrazeni celkove tabulky)
      $edges_count++;

      echo "<tr><td rowspan='" . count($commodity_list) . "'>";
      echo "← " . $edge['n_name'];
      echo "</td>";
      $load_list = explode(",", $edge['e_load_ab']);
      foreach ($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $load_list[$commodity_key] . "</td>";
        $total[$commodity] += $load_list[$commodity_key];
        echo "</tr>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</p>";

    //celkova tabulka dovozu
    if($edges_count > 1) {
      echo "<p>";
      echo "<table>";
      echo "<tr><td rowspan='" . count($commodity_list) . "'>Celkem</td>";
      foreach ($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $total[$commodity] . "</td>";
        echo "</tr>";
      }
      echo "</table>";

      echo "</p>";
    }
  }

  
  //produkce
  if(!empty($node['production'])) {
    echo "<h3>Produkce</h3>\n";

    $result = $db->prepare('SELECT production '
            . 'FROM modul '
            . 'WHERE modul_id = :modul_id');
    $params = array(':modul_id' => $node['modul_id']);
    if(!$result->execute($params)) { exit("db chyba"); }
    $modul = $result->fetch(PDO::FETCH_ASSOC);

    $years = explode(",", $modul['production']);
    $productions = explode(",", $node['production']);
    $chart3 = "";

    foreach($years as $key => $year) {
      $chart3 .= "['" . $year . "'," . $productions[$key] . "],";
    }
    echo "<div id='chart3'></div>";
  }
  
  
  //echo "<h3>Zpracování</h3>\n";
    
  
  
  
  //zapis dat pro grafy
  echo "<script>";

  //graf 3 - historicky vyvoj produkce v uzlu
  echo "var data3 = new google.visualization.DataTable();
    data3.addColumn('string', 'Rok');
    data3.addColumn('number', 'Přeprava');
    data3.addRows([";
  $chart3 = rtrim($chart3, ",");
  echo $chart3;
  echo "]);";

  echo "</script>";
}









//nezadany scenario, ale zadany upscenario - vypis agregovanych dat o uzlu v celem nadscenari
else if(isset($_POST['node']) && is_numeric($_POST['node']) &&
  isset($_POST['upscenario']) && is_numeric($_POST['upscenario'])) {

  //overeni uzivatele
  if($_SESSION['UID'] != $_POST['user'] || !isset($_POST['user'])) {
    die();
  }

  //ziskani informaci o uzlu
  $result = $db->prepare('SELECT * FROM node WHERE node_id = :node_id');
  $params = array(':node_id' => $_POST['node']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $node = $result->fetch(PDO::FETCH_ASSOC);
  
  //ziskani poctu scenaru v nadscenari
  $result = $db->prepare('SELECT count(*) AS scenario_count '
          . 'FROM scenario '
          . 'WHERE upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $count = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<p><a href='javascript:void(0)' onclick='panel(MODULES)'>◄ Přehled modulů</a> | <a href='javascript:void(0)' onclick='panel(NODES)'>Přehled uzlů</a></p>";
  echo "<h2>" . htmlspecialchars($node['name'], ENT_QUOTES) . "</h2>\n";
  echo "<p>GPS: " . htmlspecialchars($node['longitude'], ENT_QUOTES) . ", " . htmlspecialchars($node['latitude'], ENT_QUOTES) . "<br>\n";
  echo "Kraj: " . htmlspecialchars($node['region'], ENT_QUOTES) . " (" . htmlspecialchars($node['country'], ENT_QUOTES) . ")<br>\n";
  echo "" . htmlspecialchars($node['info'], ENT_QUOTES) . "<br>\n";
  echo "" . htmlspecialchars($node_type[$node['type']], ENT_QUOTES) . "<br>\n";
  echo "Data " . htmlspecialchars($count['scenario_count'], ENT_QUOTES) . " scénářů</p>\n";

  //zjisteni struktury komodit ze scenare
  $result = $db->prepare('SELECT u.load_structure '
          . 'FROM upscenario u '
          . 'WHERE u.upscenario_id = :upscenario_id');
  $params = array(':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $upscenario = $result->fetch(PDO::FETCH_ASSOC);
  $commodity_list = explode(",", $upscenario['load_structure']);
  
/*
  //odvoz
  $result = $db->prepare('SELECT n.name AS n_name, e.load_ab AS e_load_ab '
          . 'FROM edge_upscenario e, node n '
          . 'WHERE e.node_a = :node_id AND e.node_b = n.node_id AND e.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Odvoz</h3>\n";

    //vytvoreni tabulky a grafu
    $chart1 = "";
    $total = array();
    $edges_count = 0;
    echo "<table>";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $edges_count++;
      echo "<tr><td rowspan='" . count($commodity_list) . "'>";
      echo "→ " . $edge['n_name'];
      echo "</td>";
      $load_list = explode(",", $edge['e_load_ab']);
      foreach($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $load_list[$commodity_key] . "</td>";
        $total[$commodity] += $load_list[$commodity_key];
        echo "</tr>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</p>";

    //celkova tabulka odvozu
    if($edges_count > 1) {
      echo "<p>";
      echo "<table>";
      echo "<tr><td rowspan='" . count($commodity_list) . "'>Celkem</td>";
    }
    foreach ($commodity_list as $commodity_key => $commodity) {
      if($edges_count > 1) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . $total[$commodity] . "</td>";
        echo "</tr>";
      }
      if(!isset($total[$commodity])) $total[$commodity] = 0;
      $chart1 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "'," . $total[$commodity] . "],";
    }
    if($edges_count > 1) {
      echo "</table>";
      echo "</p>";
    }

    if($edges_count > 0) echo "<div id='chart1'></div>";
    //echo $chart1;
  }
  */
  
  //odvoz 2
  $result = $db->prepare('SELECT n.name AS n_name, n.node_id AS n_id, e.load_ab AS e_load_ab '
          . 'FROM edge_upscenario e, node n '
          . 'WHERE e.node_a = :node_id AND e.node_b = n.node_id AND e.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Odvoz</h3>\n";
    
    //vytvoreni tabulky a grafu
    $chart1 = "";
    $total = array();
    $edges_count = 0;
    echo "<table>";
    echo "<th>Směr</th><th>Typ odpadu</th><th>Průměrné množství [t]</th><th>Směrodatná odchylka [t]</th>";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      
      $load_list = explode(",", $edge['e_load_ab']);
      //vypocet rozptylu - smerodatne odchylky
      $var = array();
      $result2 = $db->prepare('SELECT e.load_ab '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_a = :node_id AND e.node_b = :node_b AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
      $params = array(':node_id' => $_POST['node'], ':node_b' => $edge['n_id'], ':upscenario_id' => $_POST['upscenario']);
      if(!$result2->execute($params)) { exit("db rozptyl chyba"); }
      while($edge2 = $result2->fetch(PDO::FETCH_ASSOC)) {
        $load_list2 = explode(",", $edge2['load_ab']);
        foreach($commodity_list as $commodity_key => $commodity) {
          //echo pow($load_list2[$commodity_key] - ($load_list[$commodity_key] / $count['scenario_count']), 2) . " ";
          $var[$commodity_key] += pow($load_list2[$commodity_key] - ($load_list[$commodity_key] / $count['scenario_count']), 2);
          //echo $var[$commodity_key] . " ";
        }
        
      }
      
      $edges_count++;
      echo "<tr><td rowspan='" . count($commodity_list) . "'>";
      echo "→ " . $edge['n_name'];
      echo "</td>";
      
      foreach($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . round($load_list[$commodity_key] / $count['scenario_count'], 1) . "</td><td>" . round(sqrt($var[$commodity_key] / $count['scenario_count']), 1) . "</td>";
        $total[$commodity] += $load_list[$commodity_key];
        echo "</tr>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</p>";

    //celkova tabulka odvozu
    if($edges_count > 1) {
      echo "<p>";
      echo "<table>";
      echo "<tr><td rowspan='" . count($commodity_list) . "'>Průměrné množství celkem [t]</td>";
    }
    foreach ($commodity_list as $commodity_key => $commodity) {
      if($edges_count > 1) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . round($total[$commodity] / $count['scenario_count'], 1) . "</td>";
        echo "</tr>";
      }
      if(!isset($total[$commodity])) $total[$commodity] = 0;
      $chart1 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "'," . $total[$commodity] / $count['scenario_count'] . "],";
    }
    if($edges_count > 1) {
      echo "</table>";
      echo "</p>";
    }

    //if($edges_count > 0) echo "<div id='chart1'></div>";
    //echo $chart1;
  }
  
  
  //zpracovani 3 - histogramy zvlast - odvoz
  $result = $db->prepare('SELECT e.load_ab, e.scenario_id, s.name '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_a = :node_id AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    //echo "<h3>Odvoz</h3>\n";

    $chart5 = "";
    
    $scenario_id = 0;
    $scenario_load = array();
    $scenario_name = "";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $load_list = explode(",", $edge['load_ab']);
      
      //hrana stejneho scenare - pricteni do pole
      if($edge['scenario_id'] == $scenario_id) {
        foreach($load_list as $key => $val) {
          $scenario_load[$key] += $val;
        }
      }
      //hrana dalsiho scenare - zapis do grafu a vynulovani a znovuinicializace pole
      else {
        //zruseni vypisu u prvniho zaznamu
        if($scenario_id != 0) {
          //zapis do grafu
          
          foreach($scenario_load as $key => $val) {
            $chart5[$key] .= "[";
            $chart5[$key] .= $val . ",";
            $chart5[$key] = rtrim($chart5[$key], ",");
            $chart5[$key] .= "],";
          }
        }
        
        //vynulovani pole
        $scenario_load = array();
        foreach($commodity_list as $key => $commodity) {
          $scenario_load[$key] = 0;
        }
        
        //inicializace hodnot
        foreach($load_list as $key => $val) {
          $scenario_load[$key] = $val;
        }
        
        $scenario_id = $edge['scenario_id'];
        $scenario_name = $edge['name'];
      }
    }
    
    //zapis do grafu po posledni hrane
    foreach($scenario_load as $key => $val) {
      $chart5[$key] .= "[";
      $chart5[$key] .= $val . ",";
      $chart5[$key] = rtrim($chart5[$key], ",");
      $chart5[$key] .= "],";
    }
    
    echo "<h4>" . $commodity_list[0] . "</h4>";
    echo "<div id='chart8'></div>";
    echo "<h4>" . $commodity_list[1] . "</h4>";
    echo "<div id='chart9'></div>";
    echo "<h4>" . $commodity_list[2] . "</h4>";
    echo "<div id='chart10'></div>";
    echo "<h4>" . $commodity_list[3] . "</h4>";
    echo "<div id='chart11'></div>";
    //print_r($chart5);
  }
  
  
  
  
  //dovoz
  $result = $db->prepare('SELECT n.name AS n_name, n.node_id AS n_id, e.load_ab AS e_load_ab '
          . 'FROM edge_upscenario e, node n '
          . 'WHERE e.node_b = :node_id AND e.node_a = n.node_id AND e.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Dovoz</h3>\n";
    //vytvoreni tabulky a grafu
    $chart2 = "";
    $total = array();
    $edges_count = 0;
    echo "<table>";
    echo "<th>Směr</th><th>Typ odpadu</th><th>Průměrné množství [t]</th><th>Směrodatná odchylka [t]</th>";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      
      $load_list = explode(",", $edge['e_load_ab']);
      //vypocet rozptylu - smerodatne odchylky
      $var = array();
      $result2 = $db->prepare('SELECT e.load_ab '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_b = :node_id AND e.node_a = :node_a AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
      $params = array(':node_id' => $_POST['node'], ':node_a' => $edge['n_id'], ':upscenario_id' => $_POST['upscenario']);
      if(!$result2->execute($params)) { exit("db rozptyl chyba"); }
      while($edge2 = $result2->fetch(PDO::FETCH_ASSOC)) {
        $load_list2 = explode(",", $edge2['load_ab']);
        foreach($commodity_list as $commodity_key => $commodity) {
          //echo pow($load_list2[$commodity_key] - ($load_list[$commodity_key] / $count['scenario_count']), 2) . " ";
          $var[$commodity_key] += pow($load_list2[$commodity_key] - ($load_list[$commodity_key] / $count['scenario_count']), 2);
          //echo $var[$commodity_key] . " ";
        }
      }
      
      $edges_count++;
      echo "<tr><td rowspan='" . count($commodity_list) . "'>";
      echo "← " . $edge['n_name'];
      echo "</td>";
      
      
      foreach($commodity_list as $commodity_key => $commodity) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . round($load_list[$commodity_key] / $count['scenario_count'], 1) . "</td><td>" . round(sqrt($var[$commodity_key] / $count['scenario_count']), 1) . "</td>";
        $total[$commodity] += $load_list[$commodity_key];
        echo "</tr>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</p>";


    //celkova tabulka dovozu
    if($edges_count > 1) {
      echo "<p>";
      echo "<table>";
      echo "<tr><td rowspan='" . count($commodity_list) . "'>Průměrné množství celkem [t]</td>";
    }
    foreach ($commodity_list as $commodity_key => $commodity) {
      if($edges_count > 1) {
        if($commodity_key > 0) echo "<tr>";
        echo "<td>" . $commodity . "</td><td>" . round($total[$commodity] / $count['scenario_count'], 1) . "</td>";
        echo "</tr>";
      }
      if(!isset($total[$commodity])) $total[$commodity] = 0;
      $chart2 .= "['" . htmlspecialchars($commodity, ENT_QUOTES) . "'," . $total[$commodity] / $count['scenario_count'] . "],";
    }
    if($edges_count > 1) {
      echo "</table>";
      echo "</p>";
    }

    //if($edges_count > 0) echo "<div id='chart2'></div>";
    //echo $chart2;
  }
  
  

  //zpracovani 3 - histogramy zvlast - dovoz
  $result = $db->prepare('SELECT e.load_ab, e.scenario_id, s.name '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_b = :node_id AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    //echo "<h3>Dovoz</h3>\n";

    $chart4 = "";
    
    $scenario_id = 0;
    $scenario_load = array();
    $scenario_name = "";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $load_list = explode(",", $edge['load_ab']);
      
      //hrana stejneho scenare - pricteni do pole
      if($edge['scenario_id'] == $scenario_id) {
        foreach($load_list as $key => $val) {
          $scenario_load[$key] += $val;
        }
      }
      //hrana dalsiho scenare - zapis do grafu a vynulovani a znovuinicializace pole
      else {
        //zruseni vypisu u prvniho zaznamu
        if($scenario_id != 0) {
          //zapis do grafu
          
          foreach($scenario_load as $key => $val) {
            $chart4[$key] .= "[";
            $chart4[$key] .= $val . ",";
            $chart4[$key] = rtrim($chart4[$key], ",");
            $chart4[$key] .= "],";
          }
          
        }
        
        //vynulovani pole
        $scenario_load = array();
        foreach($commodity_list as $key => $commodity) {
          $scenario_load[$key] = 0;
        }
        
        //inicializace hodnot
        foreach($load_list as $key => $val) {
          $scenario_load[$key] = $val;
        }
        
        $scenario_id = $edge['scenario_id'];
        $scenario_name = $edge['name'];
      }
    }
    
    //zapis do grafu po posledni hrane
    foreach($scenario_load as $key => $val) {
      $chart4[$key] .= "[";
      $chart4[$key] .= $val . ",";
      $chart4[$key] = rtrim($chart4[$key], ",");
      $chart4[$key] .= "],";
    }
    
    echo "<h4>" . $commodity_list[0] . "</h4>";
    echo "<div id='chart4'></div>";
    echo "<h4>" . $commodity_list[1] . "</h4>";
    echo "<div id='chart5'></div>";
    echo "<h4>" . $commodity_list[2] . "</h4>";
    echo "<div id='chart6'></div>";
    echo "<h4>" . $commodity_list[3] . "</h4>";
    echo "<div id='chart7'></div>";
    //print_r($chart4);
  }
  
  
  
  
  
  //produkce
  if(!empty($node['production'])) {
    echo "<h3>Produkce</h3>\n";
    
    $result = $db->prepare('SELECT production '
            . 'FROM modul '
            . 'WHERE modul_id = :modul_id');
    $params = array(':modul_id' => $node['modul_id']);
    if(!$result->execute($params)) { exit("db chyba"); }
    $modul = $result->fetch(PDO::FETCH_ASSOC);
    
    $years = explode(",", $modul['production']);
    $productions = explode(",", $node['production']);
    $chart3 = "";

    foreach($years as $key => $year) {
      $chart3 .= "['" . $year . "'," . $productions[$key] . "],";
    }
    echo "<div id='chart3'></div>";
    //echo $chart3;
  }
  
  
  /*
  //zpracovani 2 - histogram
  $result = $db->prepare('SELECT e.load_ab, e.scenario_id, s.name '
          . 'FROM edge e, scenario s '
          . 'WHERE e.node_b = :node_id AND e.scenario_id = s.scenario_id AND s.upscenario_id = :upscenario_id');
  $params = array(':node_id' => $_POST['node'], ':upscenario_id' => $_POST['upscenario']);
  if(!$result->execute($params)) { exit("db chyba"); }
  
  if($result->rowCount() > 0) {
    echo "<h3>Zpracování</h3>\n";

    $chart4 = "";
    
    $scenario_id = 0;
    $scenario_load = array();
    $scenario_name = "";
    while($edge = $result->fetch(PDO::FETCH_ASSOC)) {
      $load_list = explode(",", $edge['load_ab']);
      
      //hrana stejneho scenare - pricteni do pole
      if($edge['scenario_id'] == $scenario_id) {
        foreach($load_list as $key => $val) {
          $scenario_load[$key] += $val;
        }
      }
      //hrana dalsiho scenare - zapis do grafu a vynulovani a znovuinicializace pole
      else {
        //zruseni vypisu u prvniho zaznamu
        if($scenario_id != 0) {
          //zapis do grafu
          $chart4 .= "[";
          foreach($scenario_load as $key => $val) {
            $chart4 .= $val . ",";
          }
          $chart4 = rtrim($chart4, ",");
          $chart4 .= "],";
        }
        
        //vynulovani pole
        $scenario_load = array();
        foreach($commodity_list as $key => $commodity) {
          $scenario_load[$key] = 0;
        }
        
        //inicializace hodnot
        foreach($load_list as $key => $val) {
          $scenario_load[$key] = $val;
        }
        
        $scenario_id = $edge['scenario_id'];
        $scenario_name = $edge['name'];
      }
    }
    
    //zapis do grafu po posledni hrane
    $chart4 .= "[";
    foreach($scenario_load as $key => $val) {
      $chart4 .= $val . ",";
    }
    $chart4 = rtrim($chart4, ",");
    $chart4 .= "],";
    
    echo "<div id='chart4'></div>";
    echo $chart4;
  }*/
  
  //zapis dat pro grafy
  echo "<script>";
  
  //graf 1 - mnozstvi prepravy jednotlivych komodit z uzlu
  echo "var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Typ nákladu');
    data1.addColumn('number', 'Přeprava');
    data1.addRows([";
  $chart1 = rtrim($chart1, ",");
  echo $chart1;
  echo "]);";

  //graf 2 - mnozstvi prepravy jednotlivych komodit do uzlu
  echo "var data2 = new google.visualization.DataTable();
    data2.addColumn('string', 'Typ nákladu');
    data2.addColumn('number', 'Přeprava');
    data2.addRows([";
  $chart2 = rtrim($chart2, ",");
  echo $chart2;
  echo "]);";
  
  //graf 3 - historicky vyvoj produkce v uzlu
  echo "var data3 = new google.visualization.DataTable();
    data3.addColumn('string', 'Rok');
    data3.addColumn('number', 'Přeprava');
    data3.addRows([";
  $chart3 = rtrim($chart3, ",");
  echo $chart3;
  echo "]);";
  
  //graf 4 - zpracovani - histogram dovozu podle scenaru
  foreach($commodity_list as $key => $commodity) {
    echo "var data" . ($key + 4) . " = new google.visualization.DataTable();";
    echo "data" . ($key + 4) . ".addColumn('number', '" . $commodity_list[$key] . "');";
    echo "data" . ($key + 4) . ".addRows([";
    $chart4[$key] = rtrim($chart4[$key], ",");
    echo $chart4[$key];
    echo "]);";
  }
  
   //graf 5 - zpracovani - histogram dovozu podle scenaru
  foreach($commodity_list as $key => $commodity) {
    echo "var data" . ($key + 8) . " = new google.visualization.DataTable();";
    echo "data" . ($key + 8) . ".addColumn('number', '" . $commodity_list[$key] . "');";
    echo "data" . ($key + 8) . ".addRows([";
    $chart5[$key] = rtrim($chart5[$key], ",");
    echo $chart5[$key];
    echo "]);";
  }

  echo "</script>";
}
?>