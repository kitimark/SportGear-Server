<?php

use Gearserver\controller\dev;
use Gearserver\controller\account;
use Gearserver\controller\university;
use Gearserver\controller\sport;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// dev
$app->get('/routes',Gearserver\controller\dev::class . ':allRoutes');
// send a mail
$app->post('/mail',Gearserver\controller\dev::class . ':sentMail');
// add user for testing
$app->post('/user/test/add',Gearserver\controller\dev::class . ':devAdduser');

$app->group('/api/v1',function() use ($app){
    $app->group('/sport',function() use ($app){
        $app->get('/id', Gearserver\controller\sport::class . ':SearchByid');

        $app->group('/list',function() use ($app){
            $app->get('/info',Gearserver\controller\sport::class . ':ListSport');
            $app->get('/teamidBytype',Gearserver\controller\sport::class . ':TeamIDByType');
            $app->get('/teamByuniversity',Gearserver\controller\sport::class . ':ListTeamByUni');
            $app->get('/pleyerBytype',Gearserver\controller\sport::class . ':ListPlayerByType');
            $app->get('/teamBytype',Gearserver\controller\sport::class . ':ListTeamByType');
            $app->post('/addTeam',Gearserver\controller\sport::class . ':AddTeam');
            $app->post('/addPlayer',Gearserver\controller\sport::class . ':AddPlayer');
            $app->patch('/Player',Gearserver\controller\sport::class . 'UpdatePlayer');
            
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
        $app->get('', Gearserver\controller\university::class . ':Session');
        $app->group('/users' ,function() use($app){
            $app->get('/info', Gearserver\controller\university::class . ':Info');
        });
        $app->post('/password_change',Gearserver\controller\university::class . ':PasswordChange');
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
        $app->post('',Gearserver\controller\account::class . ':Addusers');
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
