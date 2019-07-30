<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class account{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function info(Request $request,Response $response){
        $args = $request->getQueryParams();
        if(empty($args['sid'])){
            return $response->withJson(array(
                'message' => 'sid QueryParams not set!'
            ))->withStatus(401);
        }
        try{
            $sql = "SELECT * FROM account WHERE sid = :sid";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid",$args['sid']);
            $stmt->execute();
            $info = $stmt->fetchAll();
            if(count($info) != 0){
                $detail = empty($info[0]['details']) ? $info[0]['details'] : json_decode($info[0]['details'], true);
                $info[0]['details'] = $detail;
                return $response->withJson($info);                   
            }else{
                // no user responses nothing
                return $response->withStatus(204);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
        }
    }
    public function Update_ImageURL(Request $request,Response $response,$args){
        $params = $request->getParsedBody();
        $sid = $args['sid']; // api/v1/account/{sid}/img
        $img_url = $params['img_url'];
        if(empty($sid)){
            return $response->withStatus(401)->withJson(array(
                'message' => 'sid QueryParams not set!'
            ));
        }
        if(empty($img_url)){
            return $response->withStatus(401)->withJson(array(
                'message' => 'img_url not set!'
            ));
        }

        try{
            // Select to check user exists or not !
            $sql = 'SELECT sid,img_url FROM account WHERE sid=:sid';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(count($result) > 0){
                try{
                    $sql = 'UPDATE account SET img_url=:img_url WHERE sid=:sid';
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("sid", $sid);
                    $stmt->bindParam("img_url",$img_url);
                    $stmt->execute();
                    return $response->withJson(array(
                        'message' => 'Updated img_url id='. $sid
                    ));
                }catch(PDOException $e){
                    $this->container->logger->addInfo($e);
                    return $response->withStatus(500);
                }
            }else{
                // not exists
                return $response->withStatus(401)->withJson(array(
                    'message' => 'Account not exists'
                ));
            }

        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(401);
        }
    }
        
    public function Deleteuser(Request $request,Response $response,$args){
        $sid = $args['sid'];
        if(empty($sid)){
            return $response->withStatus(403);
        }

        try{
            try{
                $sql = 'SELECT id FROM account WHERE sid=:sid';
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("sid", $sid);
                $stmt->execute();
                $user_id = $stmt->fetchAll();
                if(count($user_id) > 0){
                    try{
                        $sql = 'DELETE FROM sport WHERE fk_account_id=:id';
                        $stmt = $this->container->db->prepare($sql);
                        $stmt->bindParam("id", $user_id);
                        $stmt->execute();
                    }catch(PDOException $e){
                        $this->container->logger->addInfo($e);
                        return $response->withStatus(403);
                    }
                }
            }catch(PDOException $e){
                $this->container->logger->addInfo($e);
                return $response->withStatus(403);                
            }
            $sql = 'DELETE FROM account WHERE sid=:sid';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->execute();
            return $response->withJson(array(
                'message' => 'Delete User'
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
            return $response->withStatus(403);
        }
    }
    public function Adduser(Request $request,Response $response){
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');
        $sid = $params['sid'];
        $uni = strtolower($decoded['uni']);  
        $fname = $params['fname'];
        $lname = $params['lname'];
        $email = $params['email'];
        
        if(strlen($sid) != 13 || !is_numeric($sid)){
            return $response->withStatus(403)->withJson(array(
                "message" => "SID length invalid or not numeric"
            ));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $response->withStatus(403)->withJson(array(
                "message" => "Email invalid"
            ));
        }

        $hash = password_hash($params['password'], PASSWORD_DEFAULT);
        try{
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,pwd) VALUES (:sid,:uni,:fname,:lname,:email,:hash)';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("uni",  $uni);
            $stmt->bindParam("fname", $fname);
            $stmt->bindParam("lname", $lname);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("hash", $hash);
            $stmt->execute();
            $id = $this->container->db->lastInsertId();
            return $response->withJson(array(
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
    }
    
}
