<?php
/* Vizualizace svozu odpadu
 * 2016, Filip Hamsik
 * 
 * Knihovna
 * 
 */


//urovne pristupu
//a - zeměpisná úroveň podrobností
//0 = stát, 1 = kraj, 2 = ORP

//b - úroveň detailu zobrazení
//0 = modul, 1 = nadscénáře, 2 = scénáře

//c - úroveň detailu zařízení
//0 = sloučení všech, 1 = bez agregace
$permission_a = array("stát", "kraj", "ORP");
$permission_b = array("modul", "nadscénáře", "scénáře");
$permission_c = array("sloučení všech", "bez agregace");




//urovne uzivatelu
$user_status = array(1 => "uživatel", "administrátor");
const GUEST = 0; //neprihlaseny navstevnik
const USER = 1; //bezny uzivatel
const ADMINISTRATOR = 2; //administrator - muze vkladat data, provadet upravy




//kategorie typu uzlu
$node_type = array(0 => "Obec", "Obec s rozšířenou působností", "Spalovna");