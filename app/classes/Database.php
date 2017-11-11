<?php
/* GPX vizualizer
 * Webovy system pro publikovani GPS dat
 * 2014, Filip Hamsik
 * 
 * Trida pro pripojeni k databazi
 * 
 */

class Database {
    
    static private $instance = NULL;
    
    //pristupove udaje k databazi
    protected $server = "";      //doplnte
    protected $dbname = "";      //doplnte
    protected $username = "";    //doplnte
    protected $password = "";    //doplnte
    
    protected $db;
  
    //vytvoreni spojeni s databazi
    public function connect() {
        $this->db = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->dbname, $this->username, $this->password);
        $this->db->query('SET NAMES UTF8');

        return $this->db;
    }
}
