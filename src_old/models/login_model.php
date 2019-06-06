<?php
/*
Error code
LOGIN0204 - Empty user and pwd
LOGIN0401 - Incorrect Password
LOGIN0404 - User not found

*/
include_once('db.php');
class login extends db{
    //ref PDO fetch https://php.net/manual/en/pdostatement.fetch.php
    public function userCount($user){
        try{
            $stmt = $this->pdo->prepare('SELECT account.sid FROM account WHERE account.sid = :sid',array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt->execute(array(':sid' => $user));
            $stmt->fetch(PDO::FETCH_ASSOC);
            return $stmt->rowCount();           
        }catch(Exception $exception){
            return null;
        }
    }
    public function loginVerify($user,$password){
        try{
            if($this->userCount($user) > 0){
                $stmt = $this->pdo->prepare('SELECT account.pwd FROM account WHERE account.sid = :sid',array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $stmt->execute(array(':sid'=>$user));
                $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($password,$stmt['account.pwd'])){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }catch(Exception $exception){
            return null;
        }
    }
    public function tokenVerify($token){
        //Not implemented
        return null;
    }
}