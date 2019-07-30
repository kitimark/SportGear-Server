<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
use \Datetime;


class university{
    
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    private function JWTtoken(Request $request){
        $ipAddress = $request->getAttribute('ip_address');
        $date = new DateTime();
        $start_time = $date->getTimestamp();
        $end_time = $start_time + 3600;
        $uni = $request->getParsedBody()['uni'];
        $settings = $this->container->get('settings')['token'];
        $key = $settings['key'];
        $token = array(
            "iat" => $date->getTimestamp(),
            "nbf" => $start_time,
            "exp" => $end_time,
            "roles" => ['university'],
            "uni" => $uni,
            "ip" => $ipAddress
        );
        $jwt = 'Bearer ' . JWT::encode($token, $key);
        return $jwt;
    }

    #return sid and info
    public function Info(Request $request,Response $response){
        $decoded = $request->getAttribute('jwt');
        if(empty($decoded['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ))->withStatus(403);
        }

        try{
            $sql = "SELECT id,sid,email,fname,lname,details,img_url FROM account WHERE uni = :uni";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$decoded['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $result = array_map(function($data){
                return array(
                    'id' => $data['id'],
                    "sid" => $data["sid"],
                    "firstName" => $data["fname"],
                    "lastName" => $data["lname"],
                    "email" => $data["email"],
                    "details" => json_decode($data["details"], true),
                    "img_url" => $data["img_url"]
                );
            }, $result);
            return $response->withJson($result);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(401);
        }
    }

    public function Login(Request $request,Response $response){
        
        $params = $request->getParsedBody();

        if(empty($params['uni']) || empty($params['pwd'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ))->withStatus(401);
        }

        try{
            $sql = "SELECT id,uni,uni_full_name,uni_pwd FROM account_uni WHERE uni = :uni";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$params['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(count($result) > 0){
                if(password_verify($params['pwd'], $result[0]['uni_pwd'])){
                    $this->response = $response->withAddedHeader('Authorization' , $this->JWTtoken($request));
                    return $this->response->withJson(array(
                        'message' => 'login complete!',
                        'id' => $result[0]['id'],
                        'uni' => $result[0]['uni'],
                        'fullname' => $result[0]['uni_full_name']
                    ));
                }else{
                    return $response->withJson(array(
                        'message' => 'password not match'
                    ))->withStatus(401);
                }  
            }else{
                return $response->withJson(array(
                    'message' => 'User not found!'
                ))->withStatus(401);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(401);
        }
    }

    public function Session(Request $request, Response $response){
        $decoded = $request->getAttribute('jwt');
        try {
            $sql = "SELECT id,uni,uni_full_name FROM account_uni WHERE uni=:uni";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni", $decoded['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(array(
                'id' => $result[0]['id'],
                'uni' => $result[0]['uni'],
                'fullname' => $result[0]['uni_full_name']
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(401);
        }
    }

    public function PasswordChange(Request $request,Response $response,$args){
        //uni get from api/{uni}/passwordchange
        $decoded = $request->getAttribute('jwt');
        $params = $request->getParsedBody();
        $old_password = $params['old_password'];
        $password = $params['password'];
        $confirm_password = $params['confirm_password'];
        try{
            $sql = "SELECT uni_pwd FROM account_uni WHERE uni = :uni";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$decoded['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(password_verify($old_password,$result[0]['uni_pwd']) && $password === $confirm_password){
                try{
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE account_uni SET uni_pwd=:uni_pwd WHERE uni = :uni";
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("uni",$decoded['uni']);
                    $stmt->bindParam("uni_pwd",$hash);
                    $stmt->execute();
                    return $response->withStatus(200);
                }catch(PDOException $e){
                    $this->container->logger->addInfo($e);
                    return $response->withStatus(403);
                }
            }else{
                return $response->withStatus(403);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(403);
        }
    }


}