<?php
include 'fuc.php';
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
                //will return token here but not implement yet
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

//get info from id (will change to token later)
$app->get('/user/{id}',function(Request $request,Response $response,$args){
    try{
        $sql = "SELECT * FROM account WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id",$args['id']);
        $stmt->execute();
        $info = $stmt->fetchAll();
        return $this->response->withJson($info);

    }catch(PDOException $e){
        $this->logger->addInfo($e->message); 
    }
});
//user update info by id (will change to token later)
$app->patch('/user/img/{id}',function(Request $request,Response $response,$args){
    $directory = $this->get('upload_directory');

    $img = $request->getUploadedFiles();
    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['img'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
    }

    try{
        $sql = "UPDATE account SET img_url=:filename WHERE id=:id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id",$args['id']);
        $stmt->bindParam("img_url",$args['filename']);
        $stmt->execute();
        return $this->response->withJson(array(
            'message' => 'Updated Picture!',
            'img_url' => $filename
        ));

    }catch(PDOException $e){
        $this->logger->addInfo($e->message); 

    }


});

//add user for testing
$app->post('/user/test/add',function(Request $request,Response $response){
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