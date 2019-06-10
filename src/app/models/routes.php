<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
//user

//login the user
$app->post('/user/login',function(Request $request,Response $response){
    $params = $request->getParsedBody();
    try{
        $sql = "SELECT id,pwd FROM account WHERE email = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("username",$params['username']);
        $stmt->execute();
        $hash = $stmt->fetchAll();
        if(count($hash) > 0){
            if(password_verify($params['password'], $hash[0]['pwd'])){
                return $this->response->withJson(array(
                    'message' => 'login complete! return id',
                    'id' => $hash[0]['id']
                ));
            }else{
                return $this->response->withJson(array(
                    'message' => 'password not match'
                ));
            }  
        }else{
            return $this->response->withJson(array(
                'message' => 'User not found!'
            ));
        }
    }catch(PDOException $e){
        $this->logger->addInfo($e->message); 
    }

});

//get info from id;

//add user for testing
$app->post('/user/test/add',function($request,$response){
    $params = $request->getParsedBody();
    if(!empty($params['username'])){
        $hash = password_hash($params['username'], PASSWORD_DEFAULT);
        $characters = '0123456789';
        for ($i = 0; $i < 13; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $sid .= $characters[$index]; 
        } 
        $fname = $lname = $params['username'];
        $email = $params['username'] .'@testing.localhost';
        try{
            $sql = 'INSERT INTO account(sid,fname,lname,email,pwd) VALUES (:sid,:fname,:lname,:email,:hash)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("fname", $fname);
            $stmt->bindParam("lname", $lname);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("hash", $hash);
            $stmt->execute();
            return $this->response->withJson(array(
                "sid" => $sid,
                "fname" => $fname,
                "lname" => $lname,
                "email" => $email,
                "pwd_hash" => $hash,
        ));

        }catch(PDOException $e){
            $this->logger->addInfo($e->message);
        }
    }else{
        return $this->response->write('error');
    }
    
    
});