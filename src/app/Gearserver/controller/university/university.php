<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
use Gearserver\controller\mail as mailsys;
use \Datetime;
use \PDOException;


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
        $params = $request->getQueryParams();
        if(empty($decoded['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ))->withStatus(403);
        }

        $gender = (empty($params['gender']) ? null : $params['gender']); 

        try{
            $sql = "SELECT id,sid,email,fname,lname,gender,details,img_url FROM account WHERE uni = :uni";
            $sql .= (empty($gender) ? '' : ' AND gender = :gender');
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$decoded['uni']);
            if (!empty($gender)) $stmt->bindParam("gender", $gender);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $result = array_map(function($data){
                return array(
                    'id' => $data['id'],
                    "sid" => $data["sid"],
                    "firstName" => $data["fname"],
                    "lastName" => $data["lname"],
                    "gender" => $data["gender"],
                    "email" => $data["email"],
                    "details" => json_decode($data["details"], true),
                    "img_url" => $data["img_url"]
                );
            }, $result);
            return $response->withJson($result);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
            return $response->withStatus(401);
        }
 
   }

/*
    public function Login(Request $request,Response $response){
        
        $params = $request->getParsedBody();
        $date = new DateTime();
        $current_dt = $date->format("Y-m-d H:i:s");
        if(empty($params['uni']) || empty($params['pwd'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ))->withStatus(401);
        }
        if (filter_var($params['uni'], FILTER_VALIDATE_EMAIL)){
            try{
                $sql = "SELECT id,email,uni,uni_full_name,uni_pwd FROM account_uni WHERE email = :email";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("email",$params['uni']);
                $stmt->execute();
                $result = $stmt->fetchAll();
                if(count($result) > 0){
                    if(password_verify($params['pwd'], $result[0]['uni_pwd'])){
                        // update last_login
                        try{
                            $sql = "UPDATE account_uni SET last_login=:current_dt WHERE email = :email";
                            $stmt = $this->container->db->prepare($sql);
                            $stmt->bindParam("email",$params['uni']);
                            $stmt->bindParam("current_dt",$current_dt);
                            $stmt->execute();
                        }catch(PDOException $e){
                            $this->container->logger->addInfo($e->getMessage());
                            return $response->withStatus(401);
                        }
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
                $this->container->logger->addInfo($e->getMessage());
                return $response->withStatus(401);
            }
        }else{
            try{
                $sql = "SELECT id,email,uni,uni_full_name,uni_pwd FROM account_uni WHERE uni = :uni";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("uni",$params['uni']);
                $stmt->execute();
                $result = $stmt->fetchAll();
                if(count($result) > 0){
                    if(password_verify($params['pwd'], $result[0]['uni_pwd'])){
                        // update last_login
                        try{
                            $sql = "UPDATE account_uni SET last_login=:current_dt WHERE uni = :uni";
                            $stmt = $this->container->db->prepare($sql);
                            $stmt->bindParam("uni",$params['uni']);
                            $stmt->bindParam("current_dt",$current_dt);
                            $stmt->execute();
                        }catch(PDOException $e){
                            $this->container->logger->addInfo($e->getMessage());
                            return $response->withStatus(401);
                        }
                        
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
                $this->container->logger->addInfo($e->getMessage());
                return $response->withStatus(401);
            }
        }
    }
*/
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
            $this->container->logger->addInfo($e->getMessage());
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
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE account_uni SET uni_pwd=:uni_pwd WHERE uni = :uni";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("uni",$decoded['uni']);
                $stmt->bindParam("uni_pwd",$hash);
                $stmt->execute();
                return $response->withStatus(200);
            }else{
                return $response->withStatus(403);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
            return $response->withStatus(500);
        }
    }
    public function Register(Request $req , Response $res){
        $msg = "";//get message to res back
        $mail = new mail($this->container);

        /*
        {
            "id" : 15136
        }
         */
        $param = $req->getParsedBody();
        $id = $param['id'];
        try{
            // get mail_info
            $sql = 'SELECT * FROM mail_info WHERE id=:id';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("id",$id);
            $stmt->execute();
            $result = $stmt->fetchAll();
            
            // maping data
            $email = $result[0]['email'];
            $uni = $result[0]['uni'];
            $username = $result[0]['temp_username'];
            $password = $result[0]['temp_password'];
            $fullname = $result[0]['fullname'];
            $fname = $result[0]['owner_fname'];
            $lname = $result[0]['owner_lname'];
            // insert into real table account_uni
            $this->container->db->beginTransction();
            $sql = 'INSERT INTO account_uni VALUES (":uni",":uni_full_name")';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$uni);
            $stmt->bindParam("uni_full_name",$fullname);
            $stmt->execute();
            $this->container->db->commit();
            $msg .= "| insert to account_uni - complate |";

            // insert into account
            // type_role U for university
            $this->container->db->beginTransction();
            $sql = 'INSERT INTO account(uni,fname,lname,type_role,email) VALUES (":uni",":fname",":lname","U",":email")';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$uni);
            $stmt->bindParam("fname",$fname);
            $stmt->bindParam("lname",$lname);
            $stmt->bindParam("email",$email);
            $stmt->execute();
            $this->container->db->commit();
            $msg .= "| insert to account - complate |";

            // select id back to get fk_account in account_staff
            $sql = 'SELECT id FROM account WHERE email=:email AND type_role="U"';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("email",$email);
            $stmt->execute();
            $fk_account = $stmt->fetchAll();

            // hash password to insert into account_staff
            $hash_password = password_hash($password,PASSWORD_DEFAULT);

            // insert for login
            $this->container->db->beginTransction();
            $sql = 'INSERT INTO account_staff(fk_account,username,password) VALUES (:fk_account,:username,:password)';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("fk_account",$fk_account[0]['fk_account']);
            $stmt->bindParam("username",$username);
            $stmt->bindParam("password",$hash_password);
            $stmt->execute();
            $this->container->db->commit();
            $msg .= "| insert to account_staff - complate |";

            // sent to mail
            // prepare data
            
            $data = array(
                "email" => $email,
                "username" => $username,
                "password" => $password,
                "fullname" => $fullname
            );
            
            if($mail->uni_register($data)){
                $msg .= "| sent mail - complate |";
                return $res->withJson(array(
                    "message" => $msg
                ));
            }else{
                $msg .= "| sent mail - incomplate |";
                return $res->withJson(array(
                    "message" => $msg
                ))->withStatus(403);
            }

        }catch(PDOException $err){
            $this->container->logger->error($err->getMessage());
            $this->container->rollback();
            return $res->withJson(array(
                "message" => $msg,
                "error_msg" => $err->getMessage()
            ))->withStatus(403);
        }
    }

}