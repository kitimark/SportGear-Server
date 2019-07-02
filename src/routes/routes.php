<?php

use Gearserver\controller\dev;
use Gearserver\controller\account;
use Gearserver\controller\university;
use Gearserver\controller\sport;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// dev
$app->get('/routes',Gearserver\controller\dev::class . ':allRoutes');
// add user for testing
$app->post('/user/test/add',Gearserver\controller\dev::class . ':devAdduser');

$app->group('/api/v1',function() use ($app){
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
            $app->get('/info',Gearserver\controller\sport::class . ':ListSport');
            $app->get('/teamidBytype',Gearserver\controller\sport::class . ':TeamIDByType');
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
                try{
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
        $app->post('/login',Gearserver\controller\university::class . ':Login');
        /*

        $app->post('/upload/csv',function(Request $request,Response $response){
            $directory = $this->get('upload_directory');
            $uploadedFiles = $request->getUploadedFiles();
            print_r($uploadedFiles);
            if (empty($uploadedFiles['dummyPlayerList'])) {
                throw new Exception('No file has been send');
            }
            $uploadedFile = $uploadedFiles['dummyPlayerList'];
            print_r($uploadedFile);
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = moveUploadedFile($directory, $uploadedFile);
                $response->write('uploaded ' . $filename . '<br/>');
            }
            return $this->response->write(is_array($uploadedFiles));
             
            $allowed =  array('csv');
            $filename = $_FILES['video_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!in_array($ext,$allowed) ) {
                echo 'error';
            }
             
        });
        */
        
        /*
        # NOT USE
        
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
        */
    });
    $app->group('/users', function () use ($app) {
        $app->get('/info', Gearserver\controller\account::class . ':info');
        $app->post('',Gearserver\controller\account::class . ':Adduser');
        //login the user
        /*
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
                            "exp" => $end_time,
                            "roles" => ['user']
                        );
                        $jwt = 'Bearer ' . JWT::encode($token, $key);
                        $this->response = $response->withAddedHeader('Authorization' , $jwt);
                        //encoded id and sid before return
                        return $this->response->withJson(array(
                            'message' => 'login complete! return id,sid',
                            'id' => $result[0]['id'],
                            'sid' => $result[0]['sid']
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
        */
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
