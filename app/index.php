<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Hlavni stranka
 * prihlasovani, registrace, seznam modulu
 * 
 * GET parametry:
 * login - stav prihlaseni
 * registration - stav registrace
 * 
 * 
 */


//sessions
session_start();

//knihovna
include_once 'library.php';

//nastaveni api key google developers
//https://console.developers.google.com/project/peaceful-elf-110615/apiui/credential

?>
<!doctype html>
<html>
  <head>
    <title>Vizualizace</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!--<script type="text/javascript" src="https://www.google.com/jsapi"></script>-->
  </head>
  <body>
    <div id="headline">
      <h1>Vizualizace</h1>
      <h3><span id="upscenario"></span><span id="scenario"></span></h3>
    </div>

<?php
if($_SESSION['logged'] > GUEST) {
?>
    <div id="settings">
<?php
if($_SESSION['logged'] == ADMINISTRATOR) {
?>
      <a href="settings.php">Administrace</a><br>
<?php
}
?>
      <a href="javascript:void(0)" id="panel_toggle">Schovat boční panel</a><br>
      <a href="logout.php">Odhlásit</a>
    </div>
<?php
}
?>
    <div id="panel">
<?php
if($_SESSION['logged'] > GUEST) {
?>
      <!--<div id="panel_menu">
        <ul>
          <a href="javascript:void(0)" ><li id="amoduly" style="background-color: #08bbf7">moduly/scénáře</li></a>
          <a href="javascript:void(0)" ><li id="ainfo">uzly</li></a>
          <a href="javascript:void(0)" ><li id="ainfo">hrany</li></a>
        </ul>
      </div>-->
      <div id="panel_content">
        <div id="tmodules"></div>
        <div id="tnodes" style="display: none;"></div>
        <div id="tinfo" style="display: none;"></div>
    </div>
<?php
}
else {
?>
    <div id="login_form">
<?php
if(isset($_GET['login']) && $_GET['login'] == "false")
  echo "<div class='login_error'>Špatné přihlašovací údaje</div>";
else if(isset($_GET['registration']) && $_GET['registration'] == "false")
  echo "<div class='login_error'>Zadaný e-mail je již registrován</div>";
else if(isset($_GET['registration']) && $_GET['registration'] == "password")
  echo "<div class='login_error'>Zadaná hesla musí být stejná</div>";
else if(isset($_GET['registration']) && $_GET['registration'] == "true")
  echo "<div class='login_ok'>Účet vytvořen, vyčkejte na schválení administrátory</div>";
?>
      <form method="post" action="login.php">
        <label for="login_email">E-mail:</label>
        <input type="email" name="email" id="login_email" />
        <label for="login_password">Heslo:</label>
        <input type="password" name="password" id="login_password" />
        <input type="submit" value="Přihlásit" id="login_submit"/>
        <a href="javascript:registration()">Registrace</a>
      </form>
    </div>
      
      
    <div id="registration_form">
      <form method="post" action="registration.php">
        <label for="registration_email">E-mail:</label>
        <input type="email" name="email" id="registration_email" />
        <label for="registration_password1">Heslo:</label>
        <input type="password" name="password" id="registration_password1" />
        <label for="registration_password2">Heslo znovu:</label>
        <input type="password" name="password2" id="registration_password2" />
        <input type="submit" value="Registrovat" id="registration_submit"/>
        <a href="javascript:registration()">Zpět na přihlášení</a>
        <p>Vstup do aplikace bude možný až po schválení administrátory.</p>
        <p>Očekávejte potvrzovací e-mail.</p>
      </form>
    </div>
<?php
}
?>
    </div>
    <div id="map"></div>
    <script>
var styles = [
{
"stylers": [
{ "visibility": "on" }
]
},{
"featureType": "road",
"elementType": "labels.icon",
"stylers": [
{ "visibility": "off" }
]
},{
"featureType": "road",
"elementType": "geometry",
"stylers": [
{ "color": "#808080" }
]
},{
"featureType": "landscape.man_made",
"stylers": [
{ "visibility": "on" },
{ "color": "#808080" }
]
},{
"featureType": "landscape.natural",
"stylers": [
{ "visibility": "on" },
{ "gamma": 0.84 }
]
},{
"featureType": "poi",
"elementType": "labels",
"stylers": [
{ "visibility": "off" }
]
},{
"featureType": "water",
"elementType": "geometry",
"stylers": [
{ "visibility": "on" },
{ "lightness": -13 }
]
},{
"featureType": "administrative",
"elementType": "labels",
"stylers": [
{ "visibility": "on" },
{ "weight": 4 }
]
}
];

const MODULES = 0;
const NODES = 1;
const INFO = 2;

var map;

var image_loader = "<img src='loader.gif' class='image_loader' alt='Načítání...' />";
var image_loader_small = "<img src='loader.gif' class='image_loader_small' alt='Načítání...' />";

var markerImage1;
var markerImage1s;
var markerImage2;
var markerImage2s;


//inicializace mapy
function initMap() {
  var styledMap = new google.maps.StyledMapType(styles, {name: "Mapa 2"});

  var mapOptions = {
      center: new google.maps.LatLng(49.7, 15.6),
      zoom: 8,
      mapTypeControlOptions: {
        mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
      },
      mapTypeControl: false
    };

  map = new google.maps.Map(document.getElementById('map'), mapOptions);

  map.mapTypes.set('map_style', styledMap);
  map.setMapTypeId('map_style');
  
  init_markers();
}




//nacteni google modulu pro grafy
//google.load("visualization", "1", {packages:["corechart"]});
google.charts.load('current', {'packages':['corechart']});




//schovani / zobrazeni registrace
function registration() {
  $("#login_form").toggle();
  $("#registration_form").toggle();
}




<?php
//neverejny js (pouze po prihlaseni)
if(isset($_SESSION['UID'])) {
?>

//zakladni inicializace
panel(MODULES);
list_modules(<?php echo htmlspecialchars($_SESSION['UID'], ENT_QUOTES); ?>);




//globalni - pole hran a uzlu
var edges = [];
var nodes = [];

//globalni - aktualni vybrany uzel
var selected_node = -1;




//smazani vsech hran na mape
function clear_edges() {
  for(i = 0; i < edges.length; i++) {
    edges[i].setMap(null);
  }
  edges = [];
}




//smazani vsech uzlu na mape
function clear_nodes() {
  for(i = 0; i < nodes.length; i++) {
    nodes[i][0].setMap(null);
  }
  nodes = [];
}




//inicializace markeru
function init_markers() {
   markerImage1 = new google.maps.MarkerImage('icon_circle_white.svg',
      new google.maps.Size(16, 16), //size
      new google.maps.Point(0, 0), //origin point
      new google.maps.Point(8, 8)); // offset point

     markerImage1s = new google.maps.MarkerImage('icon_circle_white_select.svg',
      new google.maps.Size(26, 26), //size
      new google.maps.Point(0, 0), //origin point
      new google.maps.Point(13, 13)); // offset point

     markerImage2 = new google.maps.MarkerImage('icon_square_red.svg',
      new google.maps.Size(16, 16), //size
      new google.maps.Point(0, 0), //origin point
      new google.maps.Point(8, 8)); // offset point

     markerImage2s = new google.maps.MarkerImage('icon_square_red_select.svg',
      new google.maps.Size(26, 26), //size
      new google.maps.Point(0, 0), //origin point
      new google.maps.Point(13, 13)); // offset point
}




//vykresleni vsech uzlu
function draw_nodes(map, user, upscenario, scenario) { 
  //odstraneni zvyrazneni bodu
  if(selected_node != -1) {
    deselect_node();
    selected_node = -1;
  }
  
  //smazani vsech uzlu na mape
  clear_nodes();
  
  //hranice zobrazovane oblasti
  var bounds = new google.maps.LatLngBounds();
  
  //zaslani pozadavku - vykresleni uzlu na mape
  if(scenario != null) var posting = $.post("map_nodes.php", {user: user, scenario: scenario});
  else if(upscenario != null) var posting = $.post("map_nodes.php", {user: user, upscenario: upscenario});
  posting.done(function(data) {
    var markers = stringtoarray(data);

    for(i = 0; i < markers.length; i++) {
      var position = new google.maps.LatLng(markers[i][2], markers[i][1]);
      bounds.extend(position);

      //bezne uzly
      if(markers[i][3] == "1") {
        var marker = new google.maps.Marker({
            position: position,
            map: map,
            icon: markerImage1
            //title: markers[i][0]
        });
      }
      //uzly - zarizeni
      else if(markers[i][3] == "2") {
        var marker = new google.maps.Marker({
            position: position,
            map: map,
            icon: markerImage2
            //title: markers[i][0]
        });
      }

      nodes.push([marker, markers[i][3]]);
      

      //prirazeni udalosti pri kliknuti na uzel 
      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          panel(NODES);
          //odstraneni zvyrazneni bodu
          if(selected_node != -1) deselect_node();
          //zvyrazneni bodu
          select_node(markers, marker, i);
          selected_node = i;

          load_node(user, upscenario, scenario, markers[i][0]);
        }
      })(marker, i));
    }
    
    //vycentrovani mapy
    map.fitBounds(bounds);
  });
}




//vykresleni vsech hran
function draw_edges(map, user, upscenario, scenario, commodity, limit) {
  //smazani vsech hran na mape
  clear_edges();
  
  //zaslani pozadavku - vykresleni hran na mape
  if(scenario != null) var posting = $.post("map_edges.php", {user: user, scenario: scenario, commodity: commodity});
  else if(upscenario != null) var posting = $.post("map_edges.php", {user: user, upscenario: upscenario, commodity: commodity, limit: limit});
  posting.done(function(data) {
    var directions = stringtoarray(data);
    
    for (i = 0; i < directions.length; i++) {
      var flightPlanCoordinates = [
        new google.maps.LatLng(directions[i][3], directions[i][2]),
        new google.maps.LatLng(directions[i][5], directions[i][4])
      ];

      //bezne hrany (modre)
      if(directions[i][6] == "1") {
        var edge = new google.maps.Polyline({
          path: flightPlanCoordinates,
          geodesic: true,
          strokeColor: '#0096c8',
          strokeOpacity: 1,
          strokeWeight: 6,
        });
      }
      //zvyraznene hrany
      else if(directions[i][6] == "2") {
        var edge = new google.maps.Polyline({
          path: flightPlanCoordinates,
          geodesic: true,
          strokeColor: '#ff0000',
          strokeOpacity: 1,
          strokeWeight: 6,
        });
      }

      edges.push(edge);
      edge.setMap(map);

      // Allow each marker to have an info window    
      google.maps.event.addListener(edge, 'click', (function(edge, i) {
        return function() {
          panel(INFO);
          //odstraneni zvyrazneni bodu
          if(selected_node != -1) deselect_node();
          
          if(scenario != null) load_edge(user, null, scenario, directions[i][0], directions[i][1]);
          else if(upscenario != null) load_edge(user, upscenario, null, directions[i][0], directions[i][1]);
        }
      })(edge, i));
    }
  });
}




//zvyrazneni hran podle limitu
function highlight_edges(user, limit, upscenario) {
  draw_edges(map, user, upscenario, null, 0, limit);
}




//zruseni vyberu (oznaceni) uzlu
function deselect_node() {
  if(Array.isArray(nodes)) {
    if(nodes[selected_node][1] == "1") nodes[selected_node][0].setOptions({icon: markerImage1});
    else if(nodes[selected_node][1] == "2") nodes[selected_node][0].setOptions({icon: markerImage2});
  }
}




//vyber (oznaceni) uzlu
function select_node(markers, marker, i) {
  if(markers[i][3] == "1") marker.setOptions({icon: markerImage1s});
  else if(markers[i][3] == "2") marker.setOptions({icon: markerImage2s});
}




//prepnuti panelu
function panel(item) {
  if(item == MODULES) {
    $("#amoduly").css("background-color", "#08bbf7");
    $("#auzly").css("background-color", "#0096c8");
    $("#ainfo").css("background-color", "#0096c8");
    $("#tmodules").show();
    //load_modules(<?php echo htmlspecialchars($_SESSION['UID'], ENT_QUOTES); ?>);
    $("#tnodes").hide();
    $("#tinfo").hide();
  }
  else if(item == NODES) {
    $("#amoduly").css("background-color", "#0096c8");
    $("#auzly").css("background-color", "#08bbf7");
    $("#ainfo").css("background-color", "#0096c8");
    $("#tmodules").hide();
    $("#tinfo").hide();
    $("#tnodes").show();
  }
  else if(item == INFO) {
    $("#amoduly").css("background-color", "#0096c8");
    $("#auzly").css("background-color", "#0096c8");
    $("#ainfo").css("background-color", "#08bbf7");
    $("#tinfo").show();
    $("#tmodules").hide();
    $("#tnodes").hide();
  }
}


$("#amoduly").click(function(){
  panel(MODULES);
});

$("#auzly").click(function(){
  panel(NODES);
});

$("#ainfo").click(function(){
  panel(INFO);
});




//schovani / zobrazeni bocniho panelu
$("#panel_toggle").click(function(){
  if($("#panel").is(":visible")) {
          $("#panel").hide();
          $("#map").css("left", "0");
          google.maps.event.trigger(map, "resize");
          $("#panel_toggle").text("Zobrazit boční panel");
  }
  else {
          $("#panel").show();
          $("#map").css("left", "450px");
          $("#panel_toggle").text("Schovat boční panel");
  }
});




//prevod textu do dvourozmerneho pole
//string - vstup ve formatu item11, item12, item13; item 21, item 22 ...
//array - vystup ve formatu [[item11, item12, item13], [item21, item22 ...] ...]
function stringtoarray(string) {
  array = [];
  string = string.split(";");
  
  for(i = 0; i < string.length; i++) {
    row = string[i].split(",");
    item = [];

    for(j = 0; j < row.length; j++) {
      item.push(row[j]);
    }
    array.push(item);
  }
  return array;
}




//nacteni vsech modulu
function list_modules(user) {
  $("#tmodules").append(image_loader);

  //zaslani pozadavku
  var posting = $.post("list_modules.php",
  {
    user: user
  });
  //vypis vysledku
  posting.done(function(data) {
    if(data.length > 0) {
      $("#tmodules").empty();
      $("#tmodules").append(data);
    }
    else {
      $("#tmodules").empty();
      $("#tmodules").prepend("<div class='empty'>prázdné</div>");
    }
  });
}




//nacteni vsech nadscenaru v danem modulu
function list_upscenarios(user, modul) {
  
  $("#modul" + modul).append("<div class='upscenarios'>" + image_loader_small + "</div>");

  //zaslani pozadavku
  var posting = $.post("list_upscenarios.php",
  {
    user: user,
    modul: modul
  });
  //vypis vysledku
  posting.done(function(data) {
    if(data.length > 0) {
      $("#modul" + modul + " .modul_icon").html("▼");
      $("#modul" + modul + " .upscenarios").empty().append(data);
    }
    else {
      $("#modul" + modul + " .modul_icon").html("▼");
      $("#modul" + modul + " .upscenarios").empty().append("<span class='empty'>prázdné</span>");
    }
  });
}




//nacteni vsech scenaru v danem nadscenari
function list_scenarios(user, upscenario) {
  //zavreni modulu
  if($("#upscenario" + upscenario).has("div.scenarios").length) {
    $("#upscenario" + upscenario + " div.scenarios").remove();
    $("#upscenario" + upscenario + " .modul_icon").html("►");
  }
  //jinak otevreni modulu
  else {
    //$("#upscenario" + upscenario).empty();
    $("#upscenario" + upscenario).append("<div class='scenarios'>" + image_loader_small + "</div>");
    //zaslani pozadavku
    var posting = $.post("list_scenarios.php",
    {
      user: user,
      upscenario: upscenario
    });
    //vypis vysledku
    posting.done(function(data) {      
      if(data.length > 0) {
        $("#upscenario" + upscenario + " .modul_icon").html("▼");
        $("#upscenario" + upscenario + " .scenarios").empty().append(data);
      }
      else {
        $("#upscenario" + upscenario + " .modul_icon").html("▼");
        $("#upscenario" + upscenario).append("<div class='scenarios'><span class='empty'>prázdné</span></div>");
      }
    });
  }
}




/////////////////// list \\\\\\\    \\\\\\\ load ////////////////////




//nacteni modulu
function load_modul(user, modul) {
  //zavreni modulu
  if($("#modul" + modul).has("div.upscenarios").length) {
    $("#modul" + modul + " div.upscenarios").remove();
    $("#modul" + modul + " .modul_icon").html("►");
    $("h3").empty();
    $("h3").append("<span id='upscenario'></span><span id='scenario'></span>");
  }
  //otevreni modulu
  else {
    list_upscenarios(user, modul);  
    
    $("h1").empty();
    $("h1").append(image_loader_small);

    //zaslani pozadavku - nadpis
    var posting = $.post("load_headline.php", {user: user, modul: modul});
    posting.done(function(data) {
      if(data.length > 0) {
        $("h1").empty();
        $("h1").append(data);
        $("h3").empty();
        $("h3").append("<span id='upscenario'></span><span id='scenario'></span>");
      }
    });
  }
}




//nacteni nadscenare
function load_upscenario(user, upscenario) {
  $("h1").empty();
  $("h1").append(image_loader_small);
  $("h3").empty();
  $("h3").append(image_loader_small);
  $("#tinfo").empty();
  $("#tinfo").append(image_loader);
  
  //zaslani pozadavku - nadpis
  var posting = $.post("load_headline.php", {user: user, upscenario: upscenario, all: "true"});
  posting.done(function(data) {
    if(data.length > 0) {
      $("#headline").empty().append(data);
    }
  });
   
  //zaslani pozadavku - nadscenar
  var posting = $.post("load_upscenario.php", {user: user, upscenario: upscenario});
  posting.done(function(data) {
    if(data.length > 0) {
      $("#tinfo").empty().append(data);
      var options = {'title':'',
                   'width':420,
                   'height':200,
                   'chartArea': {'width': '80%', 'height': '70%'},
                   'colors': ['#0096c8', '#ff0000', '#00ff00', '#733673', '#ffaa00', '#0d0c64'],
                   'legend':'none',
                   histogram: { bucketSize: 0.1, maxNumBuckets: 20, minValue: 0, maxValue: 2 }
                   };

      //inicializace grafu
      try {
        var chart1 = new google.visualization.Histogram(document.getElementById('chart1'));
        chart1.draw(data1, options);
      } catch(err) {}
    }
  });
  
  //zaslani pozadavku - seznam uzlu
  var posting = $.post("list_nodes.php", {user: user, upscenario: upscenario});
  posting.done(function(data) {
    if(data.length > 0) $("#tnodes").empty().append(data);
  });
  
  panel(INFO);

  draw_nodes(map, user, upscenario, null);
  draw_edges(map, user, upscenario, null, 0);

}




//nacteni scenare
function load_scenario(user, scenario, panel_id, commodity) {
  $("h1").empty();
  $("h1").append(image_loader_small);
  $("h3").empty();
  $("h3").append(image_loader_small);
  $("#tinfo").empty();
  $("#tinfo").append(image_loader);
  $("#tnodes").empty();
  $("#tnodes").append(image_loader);
  
  //zaslani pozadavku - nadpis
  var posting = $.post("load_headline.php", {user: user, scenario: scenario, all: "true"});
  posting.done(function(data) {
    if(data.length > 0) {
      $("#headline").empty();
      $("#headline").append(data);
    }
  });


  //zaslani pozadavku - info o scenari
  var posting = $.post("load_scenario.php", {user: user, scenario: scenario});
  posting.done(function(data) {
    if(data.length > 0) $("#tinfo").empty().append(data);
  });
  

  //zaslani pozadavku - seznam uzlu
  var posting = $.post("list_nodes.php", {user: user, scenario: scenario});
  posting.done(function(data) {
    if(data.length > 0) $("#tnodes").empty().append(data);
  });
  
  switch(panel_id) {
    case "NODES": panel(NODES); break;
    case "INFO": panel(INFO); break;
  }

  draw_nodes(map, user, null, scenario);
  draw_edges(map, user, null, scenario, commodity);
}




//nacteni uzlu do panelu
function load_node(user, upscenario, scenario, node) {
  $("#tinfo").empty();
  $("#tinfo").append(image_loader);

  //zaslani pozadavku - vycentrovani mapy na souradnice bodu
  var posting = $.post("get_node_gps.php", {user: user, node: node});
  posting.done(function(data) {
    if(data.length > 0) {
      data = data.split(",");
      map.setCenter(new google.maps.LatLng(parseFloat(data[1]), parseFloat(data[0])));
    }
  }); 

  //zaslani pozadavku - info o uzlu
  var posting = $.post("load_node.php", {user: user, upscenario: upscenario, scenario: scenario, node: node});
  posting.done(function(data) {
    $("#tinfo").empty();
    
    var options = {'title':'',
                   'width':420,
                   'height':200,
                   'chartArea': {'width': '80%', 'height': '70%'},
                   'colors': ['#0096c8', '#ff0000', '#00ff00', '#733673', '#ffaa00', '#0d0c64'],
                   'legend':'none'};

    if(data.length > 0) $("#tinfo").empty().append(data);
    
    //inicializace grafu
    try {
      var chart1 = new google.visualization.ColumnChart(document.getElementById('chart1'));
      chart1.draw(data1, options);
    } catch(err) {}
    try {
      var chart2 = new google.visualization.ColumnChart(document.getElementById('chart2'));
      chart2.draw(data2, options);
    } catch(err) {}
    try {
      var chart3 = new google.visualization.LineChart(document.getElementById('chart3'));
      chart3.draw(data3, options);
    } catch(err) {}
    try {
      var chart4 = new google.visualization.Histogram(document.getElementById('chart4'));
      chart4.draw(data4, options);
    } catch(err) {}
    try {
      var chart5 = new google.visualization.Histogram(document.getElementById('chart5'));
      chart5.draw(data5, options);
    } catch(err) {}
    try {
      var chart6 = new google.visualization.Histogram(document.getElementById('chart6'));
      chart6.draw(data6, options);
    } catch(err) {}
    try {
      var chart7 = new google.visualization.Histogram(document.getElementById('chart7'));
      chart7.draw(data7, options);
    } catch(err) {}
    try {
      var chart8 = new google.visualization.Histogram(document.getElementById('chart8'));
      chart8.draw(data8, options);
    } catch(err) {}
    try {
      var chart9 = new google.visualization.Histogram(document.getElementById('chart9'));
      chart9.draw(data9, options);
    } catch(err) {}
    try {
      var chart10 = new google.visualization.Histogram(document.getElementById('chart10'));
      chart10.draw(data10, options);
    } catch(err) {}
    try {
      var chart11 = new google.visualization.Histogram(document.getElementById('chart11'));
      chart11.draw(data11, options);
    } catch(err) {}
  });
  
  panel(INFO);
}




//nacteni hrany do panelu
function load_edge(user, upscenario, scenario, node_a, node_b) {
  $("#tinfo").empty();
  $("#tinfo").append(image_loader);

  //zaslani pozadavku
  if(scenario != null) var posting = $.post("load_edge.php", {user: user, scenario:scenario, node_a: node_a, node_b: node_b});
  else if(upscenario != null) var posting = $.post("load_edge.php", {user: user, upscenario:upscenario, node_a: node_a, node_b: node_b});
  
  posting.done(function(data) {
    $("#tinfo").empty();
                 
    var options = {'title':'',
                   'width':420,
                   'height':200,
                   'chartArea': {'width': '80%', 'height': '70%'},
                   'colors': ['#0096c8', '#ff0000', '#00ff00', '#733673', '#ffaa00', '#0d0c64'],
                   'legend':'none'};
                 
    var options5 = {'title':'',
                   'width':420,
                   'height':200,
                   'chartArea': {'width': '80%', 'height': '70%'},
                   'colors': ['#ff0000', '#0096c8', '#00ff00', '#733673', '#ffaa00', '#0d0c64'],
                   'legend':'none'};
                 
    if(data.length > 0) $("#tinfo").empty().append(data);  

    try {
      var chart1 = new google.visualization.ColumnChart(document.getElementById('chart1'));
      chart1.draw(data1, options);
    } catch(err) {}
    try {
      var chart2 = new google.visualization.ColumnChart(document.getElementById('chart2'));
      chart2.draw(data2, options);
    } catch(err) {}
    try {
      var chart3 = new google.visualization.Histogram(document.getElementById('chart3'));
      chart3.draw(data3, options);
    } catch(err) {}
    try {
      var chart4 = new google.visualization.Histogram(document.getElementById('chart4'));
      chart4.draw(data4, options);
    } catch(err) {}
    try {
      var chart5 = new google.visualization.ColumnChart(document.getElementById('chart5'));
      chart5.draw(data5, options5);
    } catch(err) {}
  });
}


<?php
//konec neverejneho js
}
?>

          </script>
          <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDzPVNY5SOWTmNtfudriOFaUchW_VS5YV0&callback=initMap" async defer></script>
  </body>
</html>