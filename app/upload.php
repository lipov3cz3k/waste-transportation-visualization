<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni - nahravani souboru
 * 
 * POST parametry:
 * modul_name - nazev modulu
 * modul_type - typ modulu
 * textové soubory
 * 
 */


//sessions
session_start();

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola administratora




//transformace txt souboru (z gamsu) do jsonu (rozparsovani a prevedeni do textoveho formatu json)
function transform_data_txt_to_json($file) {
  $txt_data = "[";
  
  //otevreni souboru
  if($file_data = fopen($file, "r")) {
    $upscenario_prev = "";
    $commodity = "";
    //cteni souboru po radcich
    while(($line = fgets($file_data)) !== false) {
      //scenar
      if(preg_match("/^<scenar cislo = /i", $line)) {
        preg_match("/<scenar cislo =\s*([\.\d]+)\s+skladkovaci poplatek =\s*([\.\d]+)\s+struktura =\s*([,\w]+)>/i", $line, $matches);
        $scenario = $matches[1];
        $upscenario = $matches[2];
        $load_structure = $matches[3];
        
        if($scenario == "" || $upscenario == "" || $load_structure == "") echo "error<br>";                               ///// !!!!
        
        
        
        //novy nadscenar
        if($upscenario != $upscenario_prev) {
          //$txt_data = rtrim($txt_data, "]");
          //pokud neni prvni v souboru
          if($upscenario_prev != "") {
            $txt_data = rtrim($txt_data, ",");
            $txt_data .= ']},';
          }
          
          $txt_data .= '{"nadscenar":"' . $upscenario . '",';
          $txt_data .= '"struktura":"' . $load_structure . '",';
          $txt_data .= '"scenare":[';
          $upscenario_prev = $upscenario;
        }
        else {
          //$txt_data = rtrim($txt_data, ",]");
          if(substr($txt_data, -1) == "]" && substr($txt_data, -2) == ",") {
            $txt_data = rtrim($txt_data, "]");
            $txt_data = rtrim($txt_data, ",");
          }
        }
        $txt_data .= '{"scenar": "' . $scenario . '",';
      }
      //konec scenare
      else if(preg_match("/<\/scenar>/i", $line)) {
        $scenario = "";
        $upscenario = "";
        $load_structure = "";
        
        $txt_data = rtrim($txt_data, ",");
        $txt_data .= '},';
        //$txt_data .= "<b>]</b>";
      }
      //komodita
      else if(preg_match("/<\w+>/i", $line)) {
        preg_match("/^<(\w+)>/i", $line, $matches);
        $commodity = $matches[1];
        $txt_data .= '"' . $commodity . '":[';
      }
      //konec komodity
      else if(preg_match("/<\/" . $commodity . ">/i", $line)) {
        $txt_data = rtrim($txt_data, ",");
        $txt_data .= '],';
      }
      //hrana
      else if(preg_match("/^\w+-\w+\s+\w+/", $line)) {
        preg_match("/^(\w+)-(\w+)\s+(\w+)/", $line, $matches);
        $node_a = $matches[1];
        $node_b = $matches[2];
        $load = $matches[3];
        $txt_data .= '[' . $node_a . ',' . $node_b . ',' . $load . '],';
      }
      else echo "error2<br>";
    }

    $txt_data = rtrim($txt_data, ",");
    $txt_data .= "]}";
  }
  
  fclose($file_data);
  
  $txt_data .= "]";
  //echo $txt_data;
  echo "--------------------<br>";
  //die();
  return $txt_data;
}





//transformace txt souboru (z excelu) do jsonu (rozparsovani a prevedeni do textoveho formatu json)
function transform_nodes_txt_to_json($file) {
  $txt_nodes = '[';
  
  //otevreni souboru
  if($file_nodes = fopen($file, "r")) {
    //indikator prvniho radku
    $first_line = true;
    
    
    
    //cteni souboru po radcich
    while(($line = fgets($file_nodes)) !== false) {
      //zpracovani prvniho radku
      if($first_line) {
        $first_line = false;
        continue;
      }
      //dalsi radky (jednotlive uzly)
      else {
        $line = explode("\t", $line);
        
        $txt_nodes .= '{';
        $txt_nodes .= '"id":' . $line[0] . ',';
        $txt_nodes .= '"name":"' . $line[1] . '",';
        $txt_nodes .= '"region":"' . $line[2] . '",';
        $txt_nodes .= '"country":"' . $line[3] . '",';
        $txt_nodes .= '"lng":' . $line[4] . ',';
        $txt_nodes .= '"lat":' . $line[5] . ',';
        
        if($line[0] < 10000) $node_type = 1;
        else if($line[0] >= 10000 && $line[0] < 20000) $node_type = 2;
        else $node_type = 1;
        $txt_nodes .= '"type":' . $node_type . ',';
        
        $txt_nodes .= '"info":"' . $line[6] . '"';
        
        $txt_nodes .= '},';
      }
    }
    
    $txt_nodes = rtrim($txt_nodes, ",");
  }
  
  $txt_nodes .= ']';
  
  fclose($file_nodes);
  //echo $txt_nodes;
  //echo "--------------------<br>";
  //die();
  return $txt_nodes;
}




//ulozeni uzlu ze souboru vsech uzlu
function save_nodes_json_to_db($db, $json_nodes, $modul_db_id) {
  //pomocne pole pro prevod indexu uzlu (node_id je primarni klic pro vschny uzly vsech modulu)
  $nodes_id = array();
  
  //prubezna kontrola chyby
  $error = false;
  
  //pruchod jsonu uzlu
  foreach($json_nodes as $key => $node) {
    //echo $key . "<br>";
    //kontrola vyskytu povinnych atributu
    if(isset($node['id']) && is_numeric($node['id']) &&
       isset($node['name']) &&
       isset($node['region']) &&
       isset($node['country']) &&
       isset($node['lng']) && is_numeric($node['lng']) &&
       isset($node['lat']) && is_numeric($node['lat']) &&
       isset($node['type']) && is_numeric($node['type'])) {

      //vlozeni uzlu do db
      $result2 = $db->prepare('INSERT INTO node (id_original, name, type, modul_id, longitude, latitude, country, region) VALUES (:id_original, :name, :type, :modul_id, :lng, :lat, :country, :region)');
      $params = array(':id_original' => $node['id'], ':name' => $node['name'], ':type' => $node['type'], ':modul_id' => $modul_db_id, ':lng' => $node['lng'], ':lat' => $node['lat'], ':country' => $node['country'], ':region' => $node['region']);
      if(!$result2->execute($params)) { exit("db4 chyba"); }

      echo "uloženo<br>";
      //zjisteni skutecneho node_id v db
      $node_db_id = $db->lastInsertId();

      //pridani nepovinneho atributu info

      //vlozeni do pomocneho pole prevodu indexu
      $nodes_id[$node['id']] = $node_db_id;
    }
    else {
      $error = true;
      break;
    }
  }
  
  if($error) {
    header("Location: settings.php?status=upload_data_format#upload");
    die();
  }
  
  return $nodes_id;
}



//ulozeni dat scenaru a nadscenaru
function save_data_json_to_db($db, $json_data, $nodes_id, $modul_db_id) {
  var_dump($json_data);
  
  //pruchod jsonu dat - nadscenare
  foreach($json_data as $key => $upscenario) {
    $load_structure = explode(",", $upscenario['struktura']);
    
    //ulozeni nadscenare
    $result = $db->prepare('INSERT INTO upscenario (modul_id, name, user_id, load_structure) VALUES (:modul_id, :name, :user_id, :load_structure)');
    $params = array(':modul_id' => $modul_db_id, ':name' => $upscenario['nadscenar'], ':user_id' => $_SESSION['UID'], ':load_structure' => $upscenario['struktura']);
    if(!$result->execute($params)) { exit("db3 chyba"); }

    //zjisteni upscenario_id
    $upscenario_db_id = $db->lastInsertId();
    
    //pomocne pole pro ulozeni agregace (celkovy soucet vsech hran v nadscenari)
    $load_total = array();

    //pruchod scenarem
    foreach($upscenario['scenare'] as $scenario) {
      //ulozeni scenare
      $result = $db->prepare('INSERT INTO scenario (upscenario_id, name, user_id) VALUES (:upscenario_id, :name, :user_id)');
      $params = array(':upscenario_id' => $upscenario_db_id, ':name' => $scenario['scenar'], ':user_id' => $_SESSION['UID']);
      if(!$result->execute($params)) { exit("db2 chyba"); }
      
      //zjisteni scenario_id
      $scenario_db_id = $db->lastInsertId();
      
      //pomocne pole pro ulozeni vsech hran
      $load_scenario = array();
      
      //zpracovani kazde komodity - naplneni pomocnych poli pro scenar a pro agregaci
      foreach($load_structure as $i => $commodity) {
        //zpracovani kazde hrany (pokud je scenar neprazdny)
        if(is_array($scenario[$commodity])) {
          foreach($scenario[$commodity] as $n => $edge) {
            //pridani hodnoty hrany dane komodity do pomocneho pole
            $load_scenario[$edge[0]][$edge[1]][$i] = $edge[2];
            $load_total[$edge[0]][$edge[1]][$i] += $edge[2];
          }
          //doplneni chybejich hran hodnotou 0
          foreach($load_scenario as $j => $node_a) {
            foreach($load_scenario[$j] as $k => $node_b) {
              if(count($load_scenario[$j][$k]) < $i + 1) {
                for($m = 0; $m <= $i; $m++) {
                  if(!isset($load_scenario[$j][$k][$m])) {
                    $load_scenario[$j][$k][$m] = 0;
                  }
                  if(!isset($load_total[$j][$k][$m])) {
                    $load_total[$j][$k][$m] = 0;
                  }
                }
              }
            }
          }
        }
      }
      
      //vytvoreni sql prikazu pro hromadne (po kazdem celem scenari) ulozeni hran
      $query = 'INSERT INTO edge (node_a, node_b, scenario_id, load_ab) VALUES ';
      foreach($load_scenario as $node_a => $item) {
        foreach($load_scenario[$node_a] as $node_b => $item) {
          $query .= '(' . $nodes_id[$node_a] . ',' . ($node_b>213?$nodes_id[$node_a]:$nodes_id[$node_b]) . ',' . $scenario_db_id . ',"';      // !!!!!
          for($m = 0; $m < count($load_structure); $m++){
            $query .= $load_scenario[$node_a][$node_b][$m];
            if($m + 1 < count($load_structure)) $query .= ',';
          }
          $query .= '"),';
        }
      }
      $query = rtrim($query, ',');
      //echo $query;

      //ulozeni do db
      $result = $db->prepare($query);
      $params = array();
      if(!$result->execute($params)) { exit("db1 chyba"); }
    }
    
    //vytvoreni sql prikazu pro hromadne (po kazdem celem nadscenari) ulozeni agregaci hran
    $query = 'INSERT INTO edge_upscenario (node_a, node_b, upscenario_id, load_ab) VALUES ';
    foreach($load_total as $node_a => $item) {
      foreach($load_total[$node_a] as $node_b => $item) {
        $query .= '(' . $nodes_id[$node_a] . ',' . ($node_b>213?$nodes_id[$node_a]:$nodes_id[$node_b]) . ',' . $upscenario_db_id . ',"';      // !!!!!
        for($m = 0; $m < count($load_structure); $m++){
          $query .= $load_total[$node_a][$node_b][$m];
          if($m + 1 < count($load_structure)) $query .= ',';
        }
        $query .= '"),';
      }
    }
    $query = rtrim($query, ',');
    //echo $query;

    //ulozeni do db
    $result = $db->prepare($query);
    $params = array();
    if(!$result->execute($params)) { exit("db6 chyba"); }
    
    echo "UPSCENARIO: " . $db->lastInsertId() . "<br>";
    
    print "<pre>";
    print_r($load_total);
    print "</pre>";
      
  }
}



//vytvoreni pole originalnich id uzlu ulozenych v db k danemu modulu
function get_nodes_id($db, $modul_id) {
  $nodes_id = array();
  
  $result = $db->prepare('SELECT node_id, id_original FROM node WHERE modul_id = :modul_id');
  $params = array(':modul_id' => $modul_id);
  if(!$result->execute($params)) { exit("db9 chyba"); }
  
  while($node = $result->fetch(PDO::FETCH_ASSOC)) {
    $nodes_id[$node['id_original']] = $node['node_id'];
  }
  
  return $nodes_id;
}











//$uzly = true;
$data = true;

//kontrola parametru a nahranych souboru
if(isset($_POST['modul_name']) &&
   isset($_POST['modul_type']) && is_numeric($_POST['modul_type']) &&
   $_FILES && 
   $_FILES['file_nodes']['error'] == UPLOAD_ERR_OK &&
   $_FILES['file_data']['error'] == UPLOAD_ERR_OK &&
   in_array(strtolower(pathinfo($_FILES["file_nodes"]["name"], PATHINFO_EXTENSION)), array("txt")) &&
   in_array(strtolower(pathinfo($_FILES["file_data"]["name"], PATHINFO_EXTENSION)), array("txt")) &&
   mime_content_type($_FILES["file_nodes"]["tmp_name"]) == "text/plain" &&
   mime_content_type($_FILES["file_data"]["tmp_name"]) == "text/plain"
   ) {

if($uzly) {
  
  //transformace txt souboru (z excelu) do jsonu (rozparsovani a prevedeni do textoveho formatu json)
  //nacteni json souboru uzlu
  $json_nodes = json_decode(transform_nodes_txt_to_json($_FILES["file_nodes"]["tmp_name"]), true);
  var_dump($json_nodes);
}

if($data) {
  //transformace txt souboru (z gamsu) do jsonu (rozparsovani a prevedeni do textoveho formatu json)
  //nacteni json souboru (nad)scenaru
  $json_data = json_decode(transform_data_txt_to_json($_FILES["file_data"]["tmp_name"]), true);
  
  //ulozeni uzlu ze souboru vsech uzlu
  
 
  

  //var_dump($json_nodes); 
  var_dump($json_data);
  echo "--------------------<br>";
} 
  

 
  
  /*
  //zakladni validace jsonu
  if(!is_array($json_nodes) || !is_array($json_data)) {
    //header("Location: settings.php?status=upload_data_format#upload");
    echo "<br>spatny format <br>";
    die();
  }
*/

if($uzly) {


    //kontrola vyskytu modulu se stejnym jmenem
  $result = $db->prepare('SELECT name FROM modul WHERE name = :name');
  $params = array(':name' => $_POST['modul_name']);
  if(!$result->execute($params)) { exit("db6 chyba"); }
  
  if($result->rowCount() > 0) {
    header("Location: settings.php?status=upload_duplicated_name#upload");
    die();
  }
  
  //vytvoreni noveho modulu
  $result = $db->prepare('INSERT INTO modul (name, user_id, type, info) VALUES (:name, :user_id, :type, :info)');
  $params = array(':name' => $_POST['modul_name'], ':user_id' => $_SESSION['UID'], ':type' => $_POST['modul_type'], ':info' => $_POST['modul_info']);
  if(!$result->execute($params)) { exit("db5 chyba"); }

  //zjisteni modul_id
  $modul_db_id = $db->lastInsertId();
  
  echo "<br>MODUL: " . $modul_db_id . "<br>";

  

  //ulozeni uzlu ze souboru vsech uzlu
  $nodes_id = save_nodes_json_to_db($db, $json_nodes, $modul_db_id);
  die();

}

////////////////////////////////////////////////////
 
$modul_db_id = 95;
if($data) {
  
  //vytvoreni pole originalnich id uzlu ulozenych v db k danemu modulu
  $nodes_id = get_nodes_id($db, $modul_db_id);
  
  //ulozeni dat scenaru a nadscenaru
  save_data_json_to_db($db, $json_data, $nodes_id, $modul_db_id);
  
}
  





die();

  
  //// ulozeni detailnich dat hran ////
  //vytvoreni pole originalnich id uzlu ulozenych v db k danemu modulu
  $nodes_id = get_nodes_id($db, $modul_db_id);
  
  //otevreni souboru
  if($file_edges = fopen($_FILES["file_edges"]["tmp_name"], "r")) {
    //indikator prvniho radku (zahlavi s roky)
    $first_line = true;
    
    //pomocne pole pro zapisovane roky
    //$i = 0;
    
    //pole s prectenymi daty (k zapisu do db)
    //$data_production = array();
    
    //cteni souboru po radcich
    while(($line = fgets($file_edges)) !== false) {
      //zpracovani prvniho radku
      if($first_line) {
        $first_line = false;
      }
      //dalsi radky (jednotlive hrany)
      else {
        $line = explode("\t", $line);
        $node_a = $line[0];
        $node_b = $line[1];
        $edge_length = $line[2];
        $load_capacity = $line[3];
        $load_height = $line[4];
        $aadt_max = $line[5];
        $aadt_mean = $line[6];
        $tv_mean = $line[7];
        $accidents = $line[8];
        
        //kontrola dat
        
        //ulozeni do db
        $query = 'UPDATE edge_upscenario SET '
                . 'length=:edge_length, '
                . 'load_capacity=:load_capacity,'
                . 'load_height=:load_height,'
                . 'aadt_max=:aadt_max,'
                . 'aadt_mean=:aadt_mean,'
                . 'tv_mean=:tv_mean,'
                . 'accidents=:accidents '
                . 'WHERE upscenario_id = :upscenario_id AND node_a = :node_a AND node_b = :node_b';
        $result = $db->prepare($query); 
        $params = array(':upscenario_id' => 108, 
            ':node_a' => $nodes_id[$node_a],
            ':node_b' => $nodes_id[$node_b],
            ':edge_length' => $edge_length,
            ':load_capacity' => $load_capacity,
            ':load_height' => $load_height,
            ':aadt_max' => $aadt_max,
            ':aadt_mean' => $aadt_mean,
            ':tv_mean' => $tv_mean,
            ':accidents' => $accidents);
        if(!$result->execute($params)) { exit("db-edges chyba"); }
        }
    }
    
    //zavreni souboru
    fclose($file_edges);
  //chaba pri otvirani souboru 
  } else {
    
  } 
  
  
  
  
  
  
  //// ulozeni dat produkce ////
  //otevreni souboru
  if($file_production = fopen($_FILES["file_production"]["tmp_name"], "r")) {
    //indikator prvniho radku (zahlavi s roky)
    $first_line = true;
    
    //pomocne pole pro zapisovane roky
    $years = array();
    $i = 0;
    
    //pole s prectenymi daty (k zapisu do db)
    $data_production = array();
    
    //cteni souboru po radcich
    while(($line = fgets($file_production)) !== false) {
      //zpracovani prvniho radku
      if($first_line) {
        $line = explode("\t", $line);
        foreach($line as $key => $year) {
          //prvni prazdna bunka
          if($key == 0) continue;
          
          $years[$i] = trim($year);
          $i++;
        }
        $first_line = false;
      }
      //dalsi radky (jednotlive uzly)
      else {
        $line = explode("\t", $line);
        foreach($line as $key => $val) {
          if($key == 0) continue;
          $data_production[$line[0]][$years[$key - 1]] = trim($val);
        }
      }
    }
    
    print_r($data_production);
    
    //zapis zahlavi hostorickych dat uzlu k modulu (roky tabulky)
    $query = 'UPDATE modul SET production="';
    foreach($years as $year) {
      $query .= $year . ',';
    }
    $query = rtrim($query, ',');
    $query .= '" WHERE modul_id = :modul_id';
    $result = $db->prepare($query); 
    $params = array(':modul_id' => $modul_db_id);
    if(!$result->execute($params)) { exit("db7 chyba"); }
    
    //zapis dat uzlu do databaze
    foreach($data_production as $node_id => $production) {
      $query = 'UPDATE node SET production="';
      foreach($production as $cell) {
        $query .= $cell . ',';
      }
      $query = rtrim($query, ',');
      $query .= '" WHERE modul_id = :modul_id AND node_id = :node_id';
      $result = $db->prepare($query); 
      $params = array(':modul_id' => $modul_db_id, ':node_id' => $nodes_id[$node_id]);
      if(!$result->execute($params)) { exit("db8 chyba"); }
    }
    
    //zavreni souboru
    fclose($file_production);
  } else {
      // error opening the file.
  } 
  

}


?>