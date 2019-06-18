<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT; // use for generate token

include 'fuc.php';

$app->get('/routes',function(Request $request,Response $response){
    return $this->response->withJson($this->allRoutes);
});
#GET INFOMATION
//get info from id (will change to token later)

$app->group('/api/v1',function() use ($app){
    /*
     */
    $app->group('/sport',function() use ($app){
        $app->get('/id', function(Request $request,Response $response){
            $params = $request->getQueryParams();
            if(empty($params['team_name']) || empty($params['sport_id'] || empty($params['uni']))){
                return $this->response->withStatus(400)
                    ->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
            }
            try{
                $sql = "SELECT id FROM sport_team WHERE team_name=:team_name 
                    AND fk_sport_id=:sport_id AND uni=:uni";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("team_name",$params['team_name']);
                $stmt->bindParam("sport_id",$params['sport_id']);
                $stmt->bindParam("uni",$params['uni']);
                $stmt->execute();
                $result = $stmt->fetchAll();
                return $this->response->withJson($result[0]);
            }catch(PDOException $e){
                $this->logger->addInfo($e);
            }
        });
        $app->group('/list',function() use ($app){
            $app->get('/info',function(Request $request,Response $response){
                try{    
                    $sql = "SELECT * FROM sport";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchall();
                    return $this->response->withJson($result);
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                }
            });
            $app->get('/teamidBytype',function(Request $request,Response $response){
                $params = $request->getQueryParams();
                if(empty($params['type']) || empty($params['uni'])){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                try{
                    $sql = "SELECT sport_team.id as sport_id ,account.id
                    FROM account
                    JOIN sport_player
                    ON account.id = sport_player.fk_account_id
                    JOIN sport_team
                    ON sport_team.id = sport_player.fk_team_id
                    JOIN sport
                    ON sport.id = sport_team.fk_sport_id
                    WHERE sport_team.uni = :uni AND sport_player.fk_sport_id = :id
                    ";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("uni",$params['uni']);
                    $stmt->bindParam("id",$params['type']);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_GROUP);
                    return $this->response->withJson($result);
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                }
            });
            $app->get('/teamByuniversity',function(Request $request,Response $response){
                $params = $request->getQueryParams();
                if(empty($params['uni'])){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                try{
                    $sql = "SELECT *
                    FROM sport_player 
                    JOIN account 
                    ON sport_player.fk_account_id = account.id
                    WHERE account.uni = :uni
                     ";
                    $stmt = $this->db->prepare($sql);
                    
                    $stmt->bindParam("uni",$params['uni']);
                    $stmt->execute();
                    $result = $stmt->fetchAll;
                    return $this->response->withJson($result);

                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                }
            });
            $app->get('/pleyerBytype',function(Request $request,Response $response){
                $params = $request->getQueryParams();
                if(empty($params['type']) || empty($params['uni'])){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                //TODO
                try{
                    //
                    $sql = "SELECT sport.id as sport_id,sport_team.team_name,sport_team.id as team_id,account.id,account.sid,account.fname,account.lname
                    FROM account
                    JOIN sport_player
                    ON account.id = sport_player.fk_account_id
                    JOIN sport_team
                    ON sport_team.id = sport_player.fk_team_id
                    JOIN sport
                    ON sport.id = sport_team.fk_sport_id
                    WHERE sport_team.uni = :uni AND sport_player.fk_sport_id = :id
                    ";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("uni",$params['uni']);
                    $stmt->bindParam("id",$params['type']);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    $result = groupArray($result,'sport_id');
                    $keys = array_keys($result);
                    for($i = 0 ; $i < count($keys) ; $i++){
                        $result[$keys[$i]] = groupArray($result[$keys[$i]],'team_name');
                    }
                    return $this->response->withJson($result);
                    //return $this->response->write(print_r($result,true));

                    
                }catch(PDOException $e){
                    $this->logger->addInfo($e);                    
                }
            });
            $app->get('/teamBytype',function(Request $request,Response $response){
                // type = 1001
                // uni = cmu
                $params = $request->getQueryParams();
                if(empty($params['type']) || empty($params['uni']) || empty($params['team_id'])){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                try{
                    $sql = "SELECT sport_team.id as team_id,account.id as account_id,account.sid,account.fname,account.lname
                    FROM account
                    JOIN sport_player
                    ON account.id = sport_player.fk_account_id
                    JOIN sport_team
                    ON sport_team.id = sport_player.fk_team_id
                    WHERE account.uni = :uni AND sport_player.fk_sport_id = :id AND sport_player.fk_team_id = :teamid
                    ";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("uni",$params['uni']);
                    $stmt->bindParam("id",$params['type']);
                    $stmt->bindParam("teamid",$params['team_id']);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    return $this->response->withJson($result);
                }catch(PDOException $e){
                    $this->logger->addInfo($e);                    
                }
            });
            $app->post('/addTeam',function(Request $request , Response $response){
                $params = $request->getParsedBody();
                //$this->logger->addInfo(print_r($params));
                if(empty($params['team_name']) || empty($params['sport_id']) || empty($params['uni'])){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                try{
                    $sql = "INSERT INTO sport_team(team_name,fk_sport_id,uni) VALUES (:team_name,:sport_id,:uni)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("team_name",$params['team_name']);
                    $stmt->bindParam("sport_id",$params['sport_id']);
                    $stmt->bindParam("uni",$params['uni']);
                    $stmt->execute();
                    $id = $this->db->lastInsertId();
                    return $this->response->withJson(array(
                        'id' => $id,
                        'message' => 'Added team'
                    ));
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                }

            });
            $app->post('/addPlayer',function(Request $request , Response $response){
                $params = $request->getParsedBody();
                if(empty($params['sport_id']) || empty($params['team_id'] || empty($params['account'][0]))){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                for($index = 0 ; $index < count($params['account_id']);$index++){
                    try{
                        $sql = "INSERT INTO sport_player(fk_team_id,fk_account_id,fk_sport_id) VALUES (:team_id,:account_id,:sport_id)";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam("team_id",$params['team_id']);
                        $stmt->bindParam("account_id",$params['account_id'][$index]);
                        $stmt->bindParam("sport_id",$params['sport_id']);
                        $stmt->execute();
                    }catch(PDOException $e){
                        $this->logger->addInfo($e);          
                    }
                }
            });
            $app->patch('/addPlayer',function(Request $request , Response $response){
                $params = $request->getParsedBody();
                if(empty($params['sport_id']) || empty($params['team_id'] || empty($params['account'][0]))){
                    return $this->response->withJson(array(
                        'status' => 'error',
                        'message' => 'QueryParams not set!'
                    ));
                }
                try{
                    $sql = "DELETE FROM sport_player WHERE fk_team_id = :team_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("team_id",$params['team_id']);
                    $stmt->execute();
                }catch(PDOException $e){
                    $this->logger->addInfo($e);                             
                }
                for($index = 0 ; $index < count($params['account_id']);$index++){
                    try{
                        $sql = "INSERT INTO sport_player(fk_team_id,fk_account_id,fk_sport_id) VALUES (:team_id,:account_id,:sport_id)";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam("team_id",$params['team_id']);
                        $stmt->bindParam("account_id",$params['account_id'][$index]);
                        $stmt->bindParam("sport_id",$params['sport_id']);
                        $stmt->execute();
                    }catch(PDOException $e){
                        $this->logger->addInfo($e);          
                    }
                }
            });
        });
        $app->group('/search',function() use ($app){
            $app->get('/{id}',function(Request $request,Response $response,$args){
                try{
                    $sql = "SELECT * FROM sport WHERE id=:id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("id",$args['id']);
                    $stmt->execute();
                    $result = $stmt->fatchAll();
                    return $this->response->withJson($result);
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                }
            });
        });
    });
    $app->group('/university',function() use ($app){
        $app->post('/login',function(Request $request,Response $response){
            $params = $request->getParsedBody();
            if(empty($params['uni']) || empty($params['pwd'])){
                return $this->response->withJson(array(
                    'status' => 'error',
                    'message' => 'QueryParams not set!'
                ));
            }
            try{
                $sql = "SELECT id,uni,uni_full_name,uni_pwd FROM account_uni WHERE uni = :uni";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("uni",$params['uni']);
                $stmt->execute();
                $result = $stmt->fetchAll();
                if(count($result) > 0){
                    if(password_verify($params['pwd'], $result[0]['uni_pwd'])){
                        //return token in header
                        $date = new DateTime();
                        $start_time = $date->getTimestamp();
                        $end_time = $start_time + 3600;
                        $settings = $this->get('settings')['token'];
                        $key = $settings['key'];
                        $token = array(
                            "iat" => $date->getTimestamp(),
                            "nbf" => $start_time,
                            "exp" => $end_time,
                            "roles" => ['university']
                        );
                        $jwt = 'Bearer ' . JWT::encode($token, $key);
                        $this->response = $response->withAddedHeader('Authorization' , $jwt);
                        //encoded id and sid before return
                        return $this->response->withJson(array(
                            'message' => 'login complete! return id',
                            'id' => $result[0]['id'],
                            'uni' => $result[0]['uni'],
                            'fullname' => $result[0]['uni_full_name']
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
                $this->logger->addInfo($e);
                echo $e;
            }
        });
        $app->get('/info/{uni}',function(Request $request,Response $response,$args){
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
                $this->logger->addInfo($e); 
            }
        });
    });
    $app->group('/users', function () use ($app) {

        #GET
        //get info by id
        $app->get('/info',function(Request $request,Response $response){
            $args = $request->getQueryParams();
            if(empty($args['sid'])){
                return $this->response->withJson(array(
                    'message' => 'QueryParams not set!'
                ));
            }
            try{
                $sql = "SELECT * FROM account WHERE sid = :sid";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("sid",$args['sid']);
                $stmt->execute();
                $info = $stmt->fetchAll();
                if(count($info) != 0){
                    $detail = empty($info[0]['details']) ? $info[0]['details'] : json_decode($info[0]['details'], true);
                    $info[0]['details'] = $detail;
                    return $this->response->withJson($info);                   
                }else{
                    // no user responses nothing
                    return $this->response;
                }
            }catch(PDOException $e){
                $this->logger->addInfo($e);
            }
        });

        #POST
        ##Add user
        $app->post('',function(Request $request,Response $response){
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
                $id = $this->db->lastInsertId();
                return $this->response->withJson(array(
                    "id" => $id,
                    "sid" => $sid,
                    "uni" => $uni,
                    "fname" => $fname,
                    "lname" => $lname,
                    "email" => $email,
                    "pwd_hash" => $hash,
            ));

            }catch(PDOException $e){
                $this->logger->addInfo($e);
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
                $this->logger->addInfo($e); 
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
                $this->logger->addInfo($e);
            }
        });
    });
});
//add user for testing
$app->post('/user/test/add',function(Request $request,Response $response){
    $params = $request->getParsedBody();
    if(!empty($params['username'])){
        $hash = password_hash($params['username'], PASSWORD_DEFAULT);
        $characters = '0123456789';
        $sid = "";
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
            $this->logger->addInfo($e);
            return $this->response->write($e);
        }
    }else{
        return $this->response->write('error');
    } 
});