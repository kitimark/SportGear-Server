<?php
include 'fuc.php';
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT; // use for generate token


### University PART ###
$app->post('/uni/login',function(Request $request,Response $response){

});
### USER PART ###

#GET INFOMATION
//get info from id (will change to token later)
$app->group('/api/v1',function() use ($app){
    /*
     */
    $app->group('/users', function () use ($app) {

        #GET
        //get info by id
        $app->get('/info/{id}',function(Request $request,Response $response,$args){
            try{
                $sql = "SELECT * FROM account WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("id",$args['id']);
                $stmt->execute();
                $info = $stmt->fetchAll();
                if(count($info) != 0){
                    $detail = empty($info[0]['details']) ? $info[0]['details'] : json_decode($info[0]['details'], true);
                    $info[0]['details'] = $detail;
                    return $this->response->withJson($info);                   
                }else{
                    return $this->response->withJson(array(
                        'message' => 'User not found!'
                    ));
                }
            }catch(PDOException $e){
                $this->logger->addInfo($e->message); 
            }
        });

        #POST
        ##Add user
        $app->post('/add',function(Request $request,Response $response){
            $params = $request->getParsedBody();
            $sid = $params['sid'];
            $uni = $params['uni'];
            $fname = $params['fname'];
            $lname = $params['lname'];
            $email = $params['email'];
            $hash = password_hash($params['password'], PASSWORD_DEFAULT);
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
        });
        #LOGIN
        //login the user
        $app->post('/login',function(Request $request,Response $response){
            $params = $request->getParsedBody();
            try{
                $sql = "SELECT id,sid,pwd FROM account WHERE email = :username";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("username",$params['username']);
                $stmt->execute();
                $result = $stmt->fetchAll();
                if(count($result) > 0){
                    if(password_verify($params['password'], $result[0]['pwd'])){
                        //return token in header
                        $date = new DateTime();
                        $start_time = $date->getTimestamp();
                        $end_time = $start_time + 1800;
                        $settings = $this->get('settings')['token'];
                        $key = $settings['key'];
                        $token = array(
                            "iat" => $date->getTimestamp(),
                            "nbf" => $start_time,
                            "exp" => $end_time
                        );
                        $jwt = 'Bearer ' . JWT::encode($token, $key);
                        $this->response = $response->withAddedHeader('Authorization' , $jwt);
                        //encoded id and sid before return
                        return $this->response->withJson(array(
                            'message' => 'login complete! return id,sid',
                            'id' => base64_encode($result[0]['id']),
                            'sid' => base64_encode($result[0]['sid'])
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
        #PATCH
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
    });
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