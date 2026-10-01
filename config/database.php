<?php
class Database {
    private $host       = HOST;
    private $db_name    = DB_NAME;
    private $username   = USERNAME;
    private $password   = PASSWORD;    
    private $port       = PORT;
    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection(){
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");

            $stmt = $this->conn->prepare("SELECT ZonaHoraria FROM account ");
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                
            //$timezone = 'America/Mexico_City'; // O cualquier valor de DateTimeZone::listIdentifiers()
            $timezone = $account['ZonaHoraria'];
            $dt = new DateTime('now', new DateTimeZone($timezone));
            $offset = $dt->format('P'); // Retorna algo como "-06:00" o "+02:00"
            $this->conn->exec("SET time_zone = '{$offset}'");            

        }catch(PDOException $exception){
            // En un entorno de producción, registra este error, no lo muestres al cliente
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}


class DatabaseLogin {
    //private $host = "localhost";
    //private $db_name = "juls";
    //private $username = "root";
    //private $password = "";

    private $host       = HOST;
    private $db_name    = DB_NAMEP;
    private $username   = USERNAME;
    private $password   = PASSWORD;    
    private $port       = PORT;

    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection(){
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        }catch(PDOException $exception){
            // En un entorno de producción, registra este error, no lo muestres al cliente
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}


?>