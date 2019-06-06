<?php
class db{
    //config
    private $host = 'localhost';
    private $username = 'gearsport';
    private $password = 'Z2VhcnNwb3J0';
    private $dbname = 'gearsport';
    
    protected $pdo;
    //private $stmt;

    public function __construct() {
        $pdo = null;
        try{
            $pdo = new PDO('mysql:host='.$this->host.';dbname='.$this->dbname.';charset=utf8', $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        }catch(PDOException $exception){
            echo $exception;
        }
    }
    /*
    public function query($query){
        $this->stmt = $this->pdo->prepare($query,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));  
    }

    public function execute(){  
        return $this->stmt->execute();  
    }

    public function rowCount(){  
        return $this->stmt->rowCount();  
        }
    */
}
