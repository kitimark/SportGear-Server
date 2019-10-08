<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \PDOException;
use Slim\Http\UploadedFile;
use \DateTime;
use \Firebase\JWT\JWT;
use Gearserver\controller\mail as mailsys;

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
    public function Upload_Image_Local(Request $request,Response $response,$args){
        // upload img in local machine
        // @param int $id
        // @return string json_massage
        $params = $request->getQueryParams();
        $id = $params['id'];// key for select user
    
        $files = $request->getUploadedFiles();
        $directory  = '/app/upload_local/img';
        $supported_image = array(
            'gif',
            'jpg',
            'jpeg',
            'png'
        );
        $file = $files['picture'];
        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        
        if ($file->getError() === UPLOAD_ERR_OK) {
            // filter files type
            // TODO
            try{
                $filename = $this->moveUploadedFile($directory, $file);
                $sql = 'UPDATE account SET img_url = :img_name WHERE id = :id';
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("img_name", $filename);
                $stmt->bindParam("id", $id);
                $stmt->execute();
                
                return $response->withJson(array(
                    "massage" => "uploaded" . $filename
                ));
                
            }catch(PDOException $e){
                $this->container->logger->addInfo($e->getMessage());
                return $response->withJson(array(
                    "message" => $e->getMessage()
                ))->withStatus(500);
            }
        }
    }

    // Ref http://www.slimframework.com/docs/v3/cookbook/uploading-files.html

    private function moveUploadedFile($directory, UploadedFile $uploadedFile){
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
    
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
        return $filename;
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

    public function DeleteUsers(Request $request, Response $response) {
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');

        $users = $params['users'];

        try {
            $this->container->db->beginTransaction();
            $sql = "DELETE FROM account WHERE uni = :uni AND sid = :sid";
            $stmt = $this->container->db->prepare($sql);
            
            foreach ($users as $user) {
                $stmt->execute(array(':uni'=>$decoded['uni'], ':sid'=>$user));
            }
            $this->container->db->commit();
            return $response;
        } catch(PDOException $e) {
            $this->container->db->rollBack();
            $this->container->logger->addInfo($e);
            return $response->withStatus(500);
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
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,gender,details,type_role) VALUES (:sid,:uni,:fname,:lname,:email,:gender,:details,:type_role)';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("sid", $sid);
            $stmt->bindParam("uni",  $uni);
            $stmt->bindParam("fname", $fname);
            $stmt->bindParam("lname", $lname);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("gender", $gender);
            //$stmt->bindParam("hash", $hash);
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
                "email" => $email
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
            
            $sql = 'INSERT INTO account(sid,uni,fname,lname,email,gender, details,type_role) VALUES ';
            $sql .= implode(',', array_map(function($el) {
                return '(?, ?, ?, ?, ?, ?, ?, ?)';
            }, $params));
            $sql .= ';';
            $args = array();
            foreach($params as $user) {
                //$hash = password_hash($user['password'], PASSWORD_DEFAULT);
                array_push($args, $user['sid'], $user['uni'], $user['fname'], $user['lname'], $user['email'], $user['gender'], (empty($user['details']) ? NULL : $user['details']), empty($user['role_type']) ? 'B' : strtoupper($user['role_type']));
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

    public function Register(Request $req,Response $res){
        $params = $req->getParsedBody();
        $mail_info_id = $params['id'];
        $sendmail = empty($params['sendmail']) ? false : is_bool($params['sendmail']) ? $params['sendmail'] : false;
        if(empty($mail_info_id)){
            return $res->withJson(array(
                "message" => "mail_info_id is NULL"
            ))->withStatus(404);
        }
        try{
            // load data from mail_info
            $sql = "SELECT * FROM mail_info WHERE id=:id";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParams("id",$mail_info_id);
            $stmt->execute();
            $mail_user = $stmt->fetchAll();
            // check user exist or not
            if(count($mail_user) > 0){
                // check university exist or not (normally not exist)
                $uni = $mail_user[0]['uni'];
                $uni_full_name = $mail_user[0]['fullname'];
                $sql = "SELECT * FROM account_uni WHERE uni=:uni";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParams("uni",$uni);
                $stmt->execute();
                $university = $stmt->fetchAll();
                if(count($university) == 0){
                    // Insert account_uni
                    $sql = "INSERT INTO account_uni(uni,uni_full_name) VALUES (:uni,:uni_full_name)";
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParams("uni",$uni);
                    $stmt->bindParams("uni_full_name",$uni_full_name);
                    $stmt->execute();
                }

                // Check account
                $email = $mail_user[0]['email'];
                $sql = "SELECT * FROM account WHERE email=:email";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParams("email",$email);
                $stmt->execute();
                $user_account = $stmt->fetchAll();
                if(count($user_account) == 0){
                    // Insert account
                    $fname = $mail_user[0]['owner_fname'];
                    $lname = $mail_user[0]['owner_lname'];
                    $type_role = "U"; // for university
                    $sql = "INSERT INTO account(uni,fname,lname,type_role,email) VALUES (:uni,:fname,:lname,:type_role,:email)";
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParams(":uni",$uni);
                    $stmt->bindParams(":fname",$fname);
                    $stmt->bindParams(":lname",$lname);
                    $stmt->bindParams(":type_role",$type_role);
                    $stmt->bindParams(":email",$email);
                    $stmt->execute();
                    // get fk_account for login
                    $email = $mail_user[0]['email'];
                    $sql = "SELECT * FROM account WHERE email=:email";
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParams("email",$email);
                    $stmt->execute();
                    $user_account = $stmt->fetchAll();
                    $fk_account = $user_account[0]['id'];

                }else{
                    $fk_account = $user_account[0]['id'];
                }

                // check account_staff exist or not
                $username = $mail_user[0]['temp_username'];
                $password = $mail_user[0]['temp_pwd'];
                $password_hash = password_hash($password,PASSWORD_DEFAULT);
                $sql = "SELECT * FROM account_staff WHERE username=:username";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("username",$username);
                $stmt->execute();
                $account_staff = $stmt->fetchAll();

                if(count($account_staff) > 0){
                    // if password not match update password from mail_info
                    if(!password_verify($password,$account_staff[0]['pwd'])){
                        $sql = "UPDATE account_staff SET pwd=:password_hash WHERE id=:id";
                        $stmt = $this->container->db->prepare($sql);
                        $stmt->bindParam("password_hash",$password_hash);
                        $stmt->bindParam("id",$account_staff[0]['id']);
                        $stmt->execute();
                    }
                }else{
                    // INSERT account_staff
                    $sql = "INSERT INTO account_staff(fk_account,username,pwd) VALUES (:fk_account,:username,:pwd)";
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("fk_account",$fk_account);
                    $stmt->bindParam("username",$username);
                    $stmt->bindParam("pwd",$password_hash);
                    $stmt->execute();
                }

                // sentmail
                if($sendmail){
                    $data = array(
                        "email" => $email,
                        "username" => $username,
                        "password" => $password,
                        "fullname" => $uni_full_name
                    );
                    $mailer = new mail($this->container);
                    if($mailer->uni_register($data)){
                        return $res->withJson(array(
                            "message" => "register complete with send a mail"
                        ));
                    }else{
                        return $res->withJson(array(
                            "message" => "register complete without send a mail(ERROR)"
                        ));
                    }
                }else{
                    return $res->withJson(array(
                        "message" => "register complete without send a mail"
                    ));
                }

                
            }else{
                return $res->withJson(array(
                    "message" => "User not exist in mail_info, Please send a infomation to staff"
                ))->withStatus(404);
            }
        }catch(PDOException $err){
            $this->container->logger->error($err->getMessage());
            return $res->withJson(array(
                "message" => $err->getMessage()
            ))->withStatus(404);
        }
        
    }
    /*public function Register(Request $request,Response $response){
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
    */
    
    private function generate_jwt_token($infomation,$expire){
        $expire = empty($expire) ? 3600 : $expire;      
        $settings = $this->container->get('settings')['token'];
        $key = $settings['key'];
        $date = new DateTime();
        $start_time = $date->getTimestamp();
        $end_time = $start_time + $expire;
        $token = array(
            "iat" => $date->getTimestamp(),
            "nbf" => $start_time,
            "exp" => $end_time,
            "roles" => array($infomation['roles']),
            "uni" => $infomation['uni'],
        );
        $jwt = 'Bearer ' . JWT::encode($token, $key);
        return $jwt;
    }
    public function Login(Request $req,Response $res){
        /*
        {
            "username":"cmu16556165",
            "password":"1234"
        }
         */
        $ts = new DateTime(); //$date->format('Y-m-d H:i:s');
        $params = $req->getParsedBody();
        $username = $params['username'];
        $password = $params['password'];
        try{
            // select username & hash password to verify user
            $sql = 'SELECT account.id AS id,
            account.sid AS sid,
            account.uni AS uni,
            account.fname AS fname,
            account.lname AS lname,
            account.type_role AS type_role,
            account.email AS email,
            account_staff.fk_account AS id_login,
            account_staff.username AS username,
            account_staff.pwd AS pwd
            FROM account_staff
            JOIN ON  
            WHERE account_staff.username=:username';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("username",$username);
            $stmt->execute();
            $user = $stmt->fetchAll();
            if(empty($user)>0){
                // verify a password
                if(password_verify($password,$user[0]['pwd'])){
                    // update last_login
                    $sql = 'UPDATE account_staff SET last_login=:last_login WHERE id=:id ';
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("last_login",$ts->format('Y-m-d H:i:s'));
                    $stmt->bindParam("id",$user[0]['id_login']);
                    $stmt->execute();
                    // TODO gen jwt & select data form account table back may be will change to join to imporve a performance
                    $token_info = array(
                        "uni" => $user[0]['uni'],
                        "roles" => $user[0]['type_role']
                    );
                    $this->res = $res->withAddedHeader('Authorization' , $this->generate_jwt_token($token_info,360000));
                    return $this->res->withJson($user);//return token and infomation


                }else{
                    // password not match
                    return $res->withJson(array(
                        "message" => "password not match"
                    ))->withStatus(404);
                }
            }else{
                // not found user
                return $res->withJson(array(
                    "message" => $username . " not found"
                ))->withStatus(404);
            }
        }catch(PDOException $err){
            $this->container->logger->error($err->getMessage());
            return $res->withJson(array(
                "message" => $err->getMessage()
            ))->withStatus(500);
        }
    }
/*
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
                        "roles" => ['staff'],
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
*/
}
