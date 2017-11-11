<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Nastaveni aplikace
 * 
 * GET parametry:
 * page - urceni stranky
 * modul - ID modulu
 * status - kód stavu provedeni operace
 * 
 */

//sessions
session_start();

//knihovna
include_once 'library.php';

//pripojeni k databzi
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//overeni administratora
if(!isset($_SESSION['logged']) || $_SESSION['logged'] != ADMINISTRATOR) {
  session_destroy();
  header("Location: index.php");
  die();
}

//default nastaveni stranky
if(!isset($_GET['page'])) {
  $_GET['page'] = "modules";
}



?>
<!doctype html>
<html>
  <head>
    <title>Vizualizace - administrace</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
  </head>
  <body>
    <div id="page">
      <h1>Administrace</h1>
      <a href='index.php'>◄ Vizualizace</a><br>
      <p>
        <a href="settings.php?page=modules">Moduly</a> | 
        <a href="settings.php?page=permissions">Oprávnění k modulům</a> | 
        <a href="settings.php?page=upload">Nahrávání dat</a> | 
        <a href="settings.php?page=users">Uživatelé</a>
      </p>
<?php
//opravneni ----------------------------------------------------------------------------------------
if(isset($_GET['page']) && $_GET['page'] == "permissions") {
?>
      <div id="permissions">
        <h2>Oprávnění k modulům</h2>
<?php
//nevybrany konkretni modul
if(!isset($_GET['modul'])) {
  //vsechny dostupne moduly
  $result = $db->prepare('SELECT modul_id, name FROM modul ORDER BY name');
  $params = array();
  if(!$result->execute($params)) { exit("db chyba"); }
    
  while($modul = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<a href='settings.php?page=permissions&modul=" . $modul['modul_id'] . "'>" . $modul['name'] . "</a><br>";
  }
}
//konkretni modul
else if(isset($_GET['modul']) && is_numeric($_GET['modul'])) {
  $result = $db->prepare('SELECT modul_id, name FROM modul WHERE modul_id = :modul_id');
  $params = array('modul_id' => $_GET['modul']);
  if(!$result->execute($params)) { exit("db chyba"); }
  $modul = $result->fetch(PDO::FETCH_ASSOC);
  
  echo "<a href='settings.php?page=permissions'>◄ Zpět</a><br>";
  echo "<h4>" . $modul['name'] . "</h4>";
  
  //tabulka oprávnění
  echo "<table class='edit'>";
  echo "<th>Uživatel</th><th>Zeměpisná úroveň podrobností</th><th>Úroveň detailu zobrazení</th><th>Úroveň detailu zařízení</th><th></th><th></th>";
  
  //
  $result2 = $db->prepare('SELECT p.permission_id, p.p_a, p.p_b, p.p_c, u.email AS email FROM permission p, user u WHERE u.user_id = p.user_id AND p.modul_id = :modul_id');
  $params = array(':modul_id' => $modul['modul_id']);
  if(!$result2->execute($params)) { exit("db chyba"); }
  while($user = $result2->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr id='permission" . $user['permission_id'] . "'><td>" . $user['email'] . "</td>";
    
    //zemepisna uroven podrobnosti
    echo "<td><select name='p_a' id='p_a'>";
    foreach($permission_a as $key => $val) {
      if($key == $user['p_a']) echo "<option value='" . $key . "' selected='selected'>" . $val . "</option>";
      else echo "<option value='" . $key . "'>" . $val . "</option>";
    }
    echo "</select></td>";
    
    //uroven detailu zobrazeni
    echo "<td><select name='p_b' id='p_b'>";
    foreach($permission_b as $key => $val) {
      if($key == $user['p_b']) echo "<option value='" . $key . "' selected='selected'>" . $val . "</option>";
      else echo "<option value='" . $key . "'>" . $val . "</option>";
    }
    echo "</select></td>";
    
    //uroven detailu zarizeni
    echo "<td><select name='p_c' id='p_c'>";
    foreach($permission_c as $key => $val) {
      if($key == $user['p_c']) echo "<option value='" . $key . "' selected='selected'>" . $val . "</option>";
      else echo "<option value='" . $key . "'>" . $val . "</option>";
    }
    echo "</select></td>";
   
    echo "<td><button onclick='save_permission(" . $user['permission_id'] . ", " . $modul['modul_id'] . ")'>Uložit</button></td>";
    echo "<td><button onclick='delete_permission(" . $user['permission_id'] . ", " . $modul['modul_id'] . ")'>Odstranit</button></td>";
    
    echo "</tr>";
  }
  echo "</table>";
  
  
  //pridani uzivatelskych prav k modulu
  echo "přidat uživatele:";
  echo "<table class='add'><tr><td>";
  echo "<select name='user' id='user'>";
  //vyber uzivatelu, kter ijeste nemaji prava k tomuto modulu
  $result3 = $db->prepare('SELECT u.user_id, u.email FROM user u WHERE NOT EXISTS (SELECT p.user_id FROM permission p WHERE p.modul_id = :modul_id AND p.user_id = u.user_id)');
  $params = array(':modul_id' => $modul['modul_id']);
  if(!$result3->execute($params)) { exit("db chyba"); }
  while($user2 = $result3->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $user2['user_id'] . "'>" . $user2['email'] . "</option>";
  }
  echo "</select></td>";
  //zemepisna uroven podrobnosti
  echo "<td><select name='p_a' id='p_a'>";
  foreach($permission_a as $key => $val) {
    echo "<option value='" . $key . "'>" . $val . "</option>";
  }
  echo "</select></td>";
  //uroven detailu zobrazeni
  echo "<td><select name='p_b' id='p_b'>";
  foreach($permission_b as $key => $val) {
    echo "<option value='" . $key . "'>" . $val . "</option>";
  }
  echo "</select></td>";
  //uroven detailu zarizeni
  echo "<td><select name='p_c' id='p_c'>";
  foreach($permission_c as $key => $val) {
    echo "<option value='" . $key . "'>" . $val . "</option>";
  }
  echo "</select></td>";
  echo "<td><button onclick='add_permission(" . $modul['modul_id'] . ")'>Přidat</button></td>";
  echo "<td></td>";

  echo "</tr></table>";
  echo "</div>";
}
?>
        <script>
          function save_permission(permission, modul) {
            var posting = $.post("settings_permission_save.php", {
              permission: permission, 
              p_a: $('tr#permission' + permission + ' select#p_a').val(),
              p_b: $('tr#permission' + permission + ' select#p_b').val(),
              p_c: $('tr#permission' + permission + ' select#p_c').val()});
            
            posting.done(function(data) {
              window.location.replace("settings.php?page=permissions&modul=" + modul);
            });
          }
          
          function delete_permission(permission, modul) {
            var posting = $.post("settings_permission_delete.php", {permission: permission});
            posting.done(function(data) {
              window.location.replace("settings.php?page=permissions&modul=" + modul);
            });            
          }
          
          function add_permission(modul) {
            var posting = $.post("settings_permission_add.php", {
              user: $('table.add select#user').val(),
              modul: modul,
              p_a: $('table.add select#p_a').val(),
              p_b: $('table.add select#p_b').val(),
              p_c: $('table.add select#p_c').val()});
            posting.done(function(data) {
              window.location.replace("settings.php?page=permissions&modul=" + modul);
            });  
          }
        </script>
      </div>
<?php
}
//nahravani ----------------------------------------------------------------------------------------
else if(isset($_GET['page']) && $_GET['page'] == "upload") {
?>
      <div id="upload">
        <h2>Nahrávání dat</h2>
      
<?php
if(isset($_GET['status']) && $_GET['status'] == "upload_duplicated_name")
  echo "<span class='error'>Zadané jméno modulu je již použité</span>";
else if(isset($_GET['status']) && $_GET['status'] == "upload_data_format")
  echo "<span class='error'>Špatný formát dat</span>";
else if(isset($_GET['status']) && $_GET['status'] == "upload_ok")
  echo "<span class='ok'>Modul vytvořen</span>";
?>
        <form method="post" action="upload.php" enctype="multipart/form-data">
          <label for="modul_name">Název modulu: *</label>
          <input type="text" id="modul_name" name="modul_name" required />
          <label for="modul_type">Typ modulu: *</label>
          <select id="modul_type" name="modul_type">
            <option value="1">Svoz odpadu</option>
          </select>
          <label for="file_nodes">Soubor s uzly: *</label>
          <input type="file" id="file_nodes" name="file_nodes" required />
          <label for="file_data">Soubor s daty modulu: *</label>
          <input type="file" id="file_data" name="file_data" required />
          <label for="file_edges">Soubor s detailními informacemi o hranách:</label>
          <input type="file" id="file_edges" name="file_edges" />
          <label for="file_production">Soubor s historickými daty produkce:</label>
          <input type="file" id="file_production" name="file_production" />
          <label for="info">Popis modulu:</label>
          <textarea name="modul_info" id="info" maxlength="255"></textarea>
          <input type="submit" value="Vytvořit" />
        </form>

        <p>Nový modul bude přístupný pouze administrátorům aplikace.</p>
        <p>Zpřístupnění modulu vybraným uživatelským účtům je možné vytvořením nových oprávnění.</p>
      </div>
<?php
}
//sprava modulu ------------------------------------------------------------------------------------
else if(isset($_GET['page']) && $_GET['page'] == "modules") {
?>
      <div id="modules">
        <h2>Moduly</h2>
<?php

//mazani modulu
//prejmenovani modulu

$result = $db->prepare('SELECT modul_id, name FROM modul ORDER BY name');
  $params = array();
  if(!$result->execute($params)) { exit("db chyba"); }
    
  echo "<table>";
  while($modul = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr id='modul" . $modul['modul_id'] . "'>";
    echo "<td><input type='text' value='" . $modul['name'] . "' id='name' /></td>";
    echo "<td><button onclick='save_modul(" . $modul['modul_id'] . ")'>Uložit</button></td>";
    echo "<td><button onclick='delete_modul(" . $modul['modul_id'] . ")'>Smazat</button></td>";
    echo "</tr>";
  }
  echo "</table>";
  //echo "<p><a href='settings_modul_delete.php?modul=" . $modul['modul_id'] . "' title='Smazat modul' onclick='return confirm(\"Smazat modul?\");'>smazat modul</a></p>";
?>
        <script>
          function delete_modul(modul) {
            if(!confirm("Smazat modul?")) return;
            var posting = $.post("settings_modul_delete.php", {modul: modul});
            posting.done(function(data) {
              window.location.replace("settings.php?page=modules");
            });            
          }
          
          function save_modul(modul) {
            var posting = $.post("settings_modul_save.php", {
              modul: modul,
              name: $('tr#modul' + modul + ' input#name').val()});
            posting.done(function(data) {
              window.location.replace("settings.php?page=modules");
            });            
          }
        </script>
      </div>
<?php
}
//urcovani typu uzivatelu --------------------------------------------------------------------------
else if(isset($_GET['page']) && $_GET['page'] == "users") {
?>
      <div id="users">
        <h2>Uživatelé</h2>
<?php
//vsichni uzivatele
$result = $db->prepare('SELECT user_id, email, type FROM user ORDER BY email');
$params = array();
if(!$result->execute($params)) { exit("db chyba"); }

echo "<table>";
echo "<th>Uživatel</th><th>Úroveň</th><th></th><th></th>";
while($user = $result->fetch(PDO::FETCH_ASSOC)) {
  echo "<tr id='user" . $user['user_id'] . "'>";
  echo "<td>" . $user['email'] . "</td>";
  echo "<td><select name='user_type' id='user_type'>";
  foreach($user_status as $key => $val) {
    if($key == $user['type']) echo "<option value='" . $key . "' selected='selected'>" . $val . "</option>";
    else echo "<option value='" . $key . "'>" . $val . "</option>";
  }
  echo "<td><button onclick='save_user(" . $user['user_id'] . ")'>Uložit</button></td>";
  echo "<td><button onclick='delete_user(" . $user['user_id'] . ")'>Smazat</button></td>";
  echo "</tr>";
}
echo "</table>";
?>
        <script>
          
          function save_user(user) {
            var posting = $.post("settings_user_save.php", {
              user: user,
              type: $('tr#user' + user + ' select#user_type').val()});
            posting.done(function(data) {
              window.location.replace("settings.php?page=users");
            }); 
          }
          
          function delete_user(user) {
            var posting = $.post("settings_user_delete.php", {user: user});
            posting.done(function(data) {
              window.location.replace("settings.php?page=users");
            }); 
          }
        </script>
      </div>
<?php
}
?>
    </div>
  </body>
</html>