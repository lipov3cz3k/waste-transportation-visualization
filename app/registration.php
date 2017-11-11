<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * AJAX - Registrace uzivatele
 * 
 * POST parametry:
 * email - e-mail uzivatele
 * password - heslo
 * password2 - heslo pro overeni
 * 
 */

//sessions
session_start();

//pripojeni k db
require_once 'classes/Database.php';
$database = new Database();
$db = $database->connect();

//kontrola povinnych promennych
$email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
$password = (isset($_POST['password'])) ? $_POST['password'] : '';
$password2 = (isset($_POST['password2'])) ? $_POST['password2'] : '';

if (strcmp($password, $password2) !== 0) {
  header("Location: index.php?registration=password");
  die();
}

if(!empty($email) && !empty($password))
{
  //nalezeni pripadneho stejneho uzivatele
  $stm = $db->prepare('SELECT email FROM user WHERE email = :email');
  if(!$stm->execute(array(':email' => $email))) exit("db chyba");
  
  if($stm->rowCount() > 0)
  {
    header("Location: index.php?registration=false");
    die();
  }
  
  //registrace
  $stm = $db->prepare('INSERT INTO user (email, password, type) VALUES (:email, PASSWORD(:password), 1)');
  if(!$stm->execute(array(':email' => $email, ':password' => $password))) exit("db chyba");
  
  //poslani mailu adminovi
  $text = "Dobrý den,\nv administraci byla vytvořena nová registrace s e-mailovou adresou " . 
          htmlspecialchars($email, ENT_QUOTES) . ". Aktivaci tohoto účtu je možné provést v administraci.";  
  $headers = "FROM: noreply@filiphamsik.cz\r\nMIME-Version: 1.0\r\nContent-type: text/plain; charset='utf8'\r\nContent-Transfer-Encoding: 7bit\r\n";
  //@mail("administrator@mail.com", 'Nová registrace v aplikaci', $text, $headers);

  header('Location: index.php?registration=true');
}
?>