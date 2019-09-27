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
//$app->post('/mail',Gearserver\controller\dev::class . ':sentMail');
$app->group('/mail', function() use ($app){
    $app->get('listinfo',Gearserver\controller\mail::class . ':getMailinfo');
    $app->post('genuserpwd',Gearserver\controller\dev::class . ':gen_temp_user_pwd');
});
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
        $app->get('', Gearserver\controller\university::class . ':Session');
        $app->patch('/password',Gearserver\controller\university::class . ':PasswordChange');
        $app->post('/login',Gearserver\controller\university::class . ':Login');
        $app->group('/users' ,function() use($app){
            $app->post('/image', Gearserver\controller\account::class . ':Upload_Image_Local');
            $app->get('/info', Gearserver\controller\university::class . ':Info');
            $app->delete('', Gearserver\controller\account::class . ':DeleteUsers');
        });
    });
    $app->group('/users', function () use ($app) {
        $app->get('/info', Gearserver\controller\account::class . ':info');
        $app->post('',Gearserver\controller\account::class . ':Addusers');
    });
});
