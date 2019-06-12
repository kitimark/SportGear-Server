<?php
include 'fuc.php';
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
### USER PART ###

#GET INFOMATION
//get info from id (will change to token later)
$app->get('/user/info/{id}',function(Request $request,Response $response,$args){
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
//get info from id (will change to token later)
$app->get('/user/uni/info/{uni}',function(Request $request,Response $response,$args){
    try{
        $sql = "SELECT * FROM account WHERE uni = :uni";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("uni",$args['uni']);
        $stmt->execute();
        $infos = $stmt->fetchAll();
        for($index = 0 ; $index < count($infos);$index++){
            $detail = json_decode($infos[$index]['details'], true);
            $infos[$index]['details'] = $detail;
        }
        return $this->response->withJson($infos);

    }catch(PDOException $e){
        $this->logger->addInfo($e->message); 
    }
});
#GET DETAILS
//get details by id
$app->get('/user/details/{id}',function(Request $request,Response $response,$args){
    try{
        $sql = "SELECT details FROM account WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id",$args['id']);
        $stmt->execute();
        $info = $stmt->fetchAll();
        $detail = json_decode($infos[$index]['details'], true);
        $infos[$index]['details'] = $detail;
        return $this->response->withJson($info);       
    }catch(PDOException $e){
        $this->logger->addInfo($e->message); 
    }

});
#LOGIN
//login the user
$app->post('/user/login',function(Request $request,Response $response){
    $params = $request->getParsedBody();
    try{
        $sql = "SELECT id,sid,pwd FROM account WHERE email = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("username",$params['username']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result) > 0){
            if(password_verify($params['password'], $result[0]['pwd'])){
                //will return token here but not implement yet
                $date = new DateTime();
                $start_time = $date->getTimestamp();
                $end_time = $start_time + 1800;
                $key = "testing";
                $token = array(
                    "iat" => $date->getTimestamp(),
                    "nbf" => $start_time,
                    "exp" => $end_time
                );
                $jwt = JWT::encode($token, $key);
                return $this->response->withJson(array(
                    'message' => 'login complete! return id',
                    'id' => $result[0]['id'],
                    'token' => 'Bearer '. $jwt
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

# UPDATE PICTURE
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

#UPDATE DETAILS (stored with JSON format only!)
$app->patch('/user/details/{id}',function(Request $request,Response $response,$args){
    $params = $request->getParsedBody();
    try{
        $sql = "UPDATE account SET details=:details WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("details",json_encode($params));
        $stmt->bindParam("id",$args['id']);
        $stmt->execute();
        return $this->response->withJson(array(
            'message' => 'Updated details'
        ));

    }catch(PDOException $e){
        $this->logger->addInfo($e->message);
    }
});
//Sport
#GET type of sports
$app->get('/sports/type',function(Request $request,Response $response){
    try{
        $sql = "SELECT * from sport";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $types = $stmt->fetchAll();
        for($index = 0 ; $index < count($types);$index++){
            $detail = json_decode($types[$index]['details'], true);
            $types[$index]['details'] = $detail;
        }
        return $this->response->withJson($types);

    }catch(PDOException $e){
        $this->logger->addInfo($e->message);
    }
});

#GET type of sport by name, type
$app->get('/sport/type',function(Request $request,Response $response){
    $args = $request->getQueryParams();
    try{
        $sql = "SELECT * from sport WHERE sport_name = 'badminton' AND json_extract(`details`, '$.type') = :type";
        //SELECT * from sport WHERE sport_name = 'badminton' AND JSON_CONTAINS(details,'singles men','$.type')
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("type",$args['type']);
        $stmt->execute();
        $type = $stmt->fetchAll();
        $detail = json_decode($type[0]['details'], true);
        $type[0]['details'] = $detail;
        return $this->response->withJson($type);
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
        $uni = 'cmu';
        try{
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,pwd) VALUES (:sid,:uni,:fname,:lname,:email,:hash)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("uni",  $uni);
            $stmt->bindParam("fname", $fname);
            $stmt->bindParam("lname", $lname);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("hash", $hash);
            $stmt->execute();
            return $this->response->withJson(array(
                "sid" => $sid,
                "uni" => $uni,
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