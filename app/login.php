<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Prihlaseni uzivatele
 * 
 * POST parametry:
 * email - prihlasovaci e-mail
 * password - heslo
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

if(!empty($email) && !empty($password))
{
  //nalezeni uzivatele
  $stm = $db->prepare('SELECT user_id, email, type FROM user WHERE email = :email AND password = PASSWORD(:password)');
  if(!$stm->execute(array(':email' => $email, ':password' => $password))) exit("db chyba");

  if($stm->rowCount() > 0)
  {
    $row = $stm->fetch(PDO::FETCH_ASSOC);

    $_SESSION['email'] = $email;
    $_SESSION['logged'] = $row['type'];
    $_SESSION['UID'] = $row['user_id'];

    header('Location: index.php');
  }
  //spatne zadane udaje
  else
  {
    $_SESSION['email'] = '';
    $_SESSION['logged'] = 0;
    header("Location: index.php?login=false");
  }
  mysql_free_result($result);
}
//chyba pri prihlasovani
else
{
  $_SESSION['email'] = '';
  $_SESSION['logged'] = 0;
  $_SESSION['UID'] = 0;
  header("Location: index.php?login=false");
}
?>