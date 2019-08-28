<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \PDOException;
use \DateTime;
use \Firebase\JWT\JWT;
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
            $this->container->logger->addInfo($e->getMessage());
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
            $this->container->logger->addInfo($e->getMessage());
            return $response->withStatus(401);
        }
    }
    
    public function Updateuser(Request $request,Response $response,$args){
        $user_id = $args['id'];// api/{id}/update
        $params = $request->getParsedBody();
        $sid = $params['sid'];
        $fname = $params['fname'];
        $lname = $params['lname'];
        $email = $params['email'];
        $gender = $params['gender'];
        if(empty($user_id) || empty($sid) || empty($fname) || empty($lname) || empty($email) || empty($gender)){
            return $response->withStatus(403);
        }
        try{
            $sql = 'UPDATE account SET sid=:sid,fname=:fname,lname=:lname,email=:email,gender=:gender WHERE id=:id';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("fname",$fname);
            $stmt->bindParam("lname",$lname);
            $stmt->bindParam("email",$email);
            $stmt->bindParam("gender",$gender);
            $stmt->execute();
            return $response->withJson(array(
                'message' => 'Update user'
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
            return $response->withStatus(401);
        }
    }
 
    public function Update_Details(Request $request,Response $response,$args){
        $user_id = $args['id'];// api/{id}/details
        $params = $request->getParsedBody();
        $details = $params['details'];
        $ob = json_decode($details);
        if($ob === null) {
            // $ob is null because the json cannot be decoded
            return $response->withStatus(403);
        }
        if(empty($user_id)){
            return $response->withStatus(403);
        }
        try{
            $sql = 'UPDATE account SET details=:details WHERE id=:id';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("id", $user_id);
            $stmt->bindParam("details", $details);
            $stmt->execute();
            return $response->withJson(array(
                'message' => 'Update user details'
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
            return $response->withStatus(401);
        }
    }
    public function Deleteuser(Request $request,Response $response,$args){
        $params = $request->getParsedBody();
        $sid = $params['sid'];
        $user_id = $args['id'];// api/{id}/delete
        if(empty($user_id)){
            return $response->withStatus(403);
        }
        try{
            try{
                $sql = 'DELETE FROM sport WHERE fk_account_id=:id';
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("id", $user_id);
                $stmt->execute();
            }catch(PDOException $e){
                $this->container->logger->addInfo($e->getMessage());
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
            $this->container->logger->addInfo($e->getMessage());
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
        $gender = $params['gender'];// Male= 1 Female= 2
        $details = $params['datails'];
        $role_type = empty($params['role_type']) ? 'B' : strtoupper($params['role_type']);

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
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,gender,pwd, details,type_role) VALUES (:sid,:uni,:fname,:lname,:email,:gender,:hash,:details,:type_role)';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("uni",  $uni);
            $stmt->bindParam("fname", $fname);
            $stmt->bindParam("lname", $lname);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("gender", $gender);
            $stmt->bindParam("hash", $hash);
            $stmt->bindParam("details", $details);
            $stmt->bindParam("type_role", $role_type);
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
            $this->container->logger->addInfo($e->getMessage());
            return $response->withJson(array(
                "code" => $e->getCode(),
                "message" => $e->getMessage()
            ))->withStatus($e->getCode());
        }
    }
    
    public function Addusers(Request $request,Response $response){
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');
        $uni = strtolower($decoded['uni']);
        try{
            foreach($params as $key=>$user){
                if(strlen($user['sid']) != 13 || !is_numeric($user['sid'])){
                    return $response->withStatus(403)->withJson(array(
                        "message" => "SID{".$key."} length invalid or not numeric"
                    ));
                }
                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    return $response->withStatus(403)->withJson(array(
                        "message" => "Email{".$key."} invalid"
                    ));
                }
                $params[$key]['uni'] = $uni;
            }
            
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,gender,pwd, details,type_role) VALUES ';
            $sql .= implode(',', array_map(function($el) {
                return '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
            }, $params));
            $sql .= ';';
            $args = array();
            foreach($params as $user) {
                $hash = password_hash($user['password'], PASSWORD_DEFAULT);
                array_push($args, $user['sid'], $user['uni'], $user['fname'], $user['lname'], $user['email'], $user['gender'], $hash, (empty($user['details']) ? NULL : $user['details']), empty($user['role_type']) ? 'B' : strtoupper($user['role_type']));
            }
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute($args);
            return $response->withJson(array(
                "message" => "Insert complete",
                "sql" => $sql,
                "params" => $args
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
    }
    public function Register(Request $request,Response $response){
        $params = $request->getParsedBody();
        // Require fk_account to register (user need to exist in account table first)
        $fk_account = $params['id'];// id form account table
        $username = $params['username'];
        $pwd = $params['pwd'];

        try{
            //TODO
            $sql = 'SELECT * FROM account_staff WHERE username = :username';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("username", $username);
            $result = $stmt->execute();
            $user = $result->fetchAll();
            if($user > 0){
                return $response->withJson(array(
                    "message" => "User : { " . $username . " } already exists"
                ))->withStatus(403);
            }else{
                // user not exist can register to database
                // check exists user in account to prevent database constraints
                $sql = 'SELECT * FROM account WHERE id = :id';
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("id", $fk_account);
                $result = $stmt->execute();
                $real_user = $result->fetchAll();
                if(count($real_user > 0)){
                    $hash = password_hash($pwd, PASSWORD_DEFAULT);
                    $sql = 'INSERT INTO account_staff(fk_account,username,pwd) VALUES (:fk_account,:username,:pwd)';
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("fk_account", $fk_account);
                    $stmt->bindParam("username", $username);
                    $stmt->bindParam("pwd", $hash);
                    $result = $stmt->execute();
                    return $response->withJson(array(
                        "message" => "User { " . $username . " } registed !"
                    ));

                }else{
                    return $response->withJson(array(
                        "message" => "User not exist in account"
                    ))->withStatus(401);
                }

            }
            

        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
    }

    public function Login(Request $request,Response $response){
        $params = $request->getParsedBody();
        $date = new DateTime();
        $username = $params['username'];
        $pwd = $params['pwd'];
        $current_dt = $date->format("Y-m-d H:i:s");
        try{
            //TODO
            $sql = 'SELECT * FROM account_staff WHERE username = :username';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("username", $username);
            $result = $stmt->execute();
            $user = $result->fetchAll();
            if(count($user) > 0){
                if(password_verify($pwd,$user[0]['pwd'])){
                    // Update last_login
                    try{
                        $sql = 'UPDATE account_staff SET last_login=:last_login WHERE username=:username';
                        $stmt = $this->container->db->prepare($sql);
                        $stmt->bindParam("username", $username);
                        $stmt->bindParam("last_login", $current_dt);
                        $stmt->execute();
                    }catch(PDOException $e){
                        $this->container->logger->addInfo($e->getMessage());
                    }
                    // GenToken
                    try{
                        // get role
                        $sql = 'SELECT type_role FROM account WHERE id=:id';
                        $stmt = $this->container->db->prepare($sql);
                        $stmt->bindParam("id", $user[0]['fk_account']);
                        $result = $stmt->execute();
                        $user_role = $result->fetchAll();
                    }catch(PDOException $e){
                        $this->container->logger->addInfo($e->getMessage());
                    }
                    // Token
                    $ipAddress = $request->getAttribute('ip_address');
                    $start_time = $date->getTimestamp();
                    $end_time = $start_time + 3600;
                    $uni = $request->getParsedBody()['uni'];
                    $settings = $this->container->get('settings')['token'];
                    $key = $settings['key'];
                    $token = array(
                        "iat" => $date->getTimestamp(),
                        "nbf" => $start_time,
                        "exp" => $end_time,
                        "roles" => $user_role[0]['type_role'],
                        "uni" => $uni,
                        "ip" => $ipAddress
                    );
                    $jwt = 'Bearer ' . JWT::encode($token, $key);
                    // insert token
                    $this->response = $response->withAddedHeader('Authorization' , $jwt);
                    // res back
                    return $this->response->withJson(array(
                        'message' => 'login complete!',
                        'id' => $user[0]['id'],
                        'role' => $user_role[0]['type_role']
                    ));
                    
                }else{
                    return $response->withJson(array(
                        "message" => "Password not match!"
                    ))->withStatus(401);
                }
            }else{
                return $response->withJson(array(
                    "message" => "User : { ". $username ." } not exists"
                ))->withStatus(401);
            }

        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
        
    }

}
