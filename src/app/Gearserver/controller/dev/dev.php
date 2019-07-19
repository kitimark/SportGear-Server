<?php 
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class dev{
    
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    public function sentMail_db($uni){
        try{
            $sql = 'SELECT * FROM account_uni WHERE uni=:uni';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("uni",  $uni);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(count($result) === 0){
                //TODO
                //will select real email(read from file or some other table) and auto generate pwd to insert into account_uni
                $json = file_get_contents("");
                /*
                {
                    "cmu":{
                        "email":"geargame30@eng.cmu.ac.th",
                        "name":"Chiang Mai University",
                    }
                }
                */
                $pwd = bin2hex(openssl_random_pseudo_bytes(4));
                $sql = 'INSERT INTO account_uni(uni,email,uni_full_name,uni_pwd) VALUES (:uni,:email,:uni_full_name,:uni_pwd)';
                $stmt->bindParam("uni",$uni);
                $stmt->bindParam("email",$email);
                $stmt->bindParam("uni_full_name",$uni_full_name);
                $stmt->bindParam("uni_pwd",$pwd);
                $stmt->execute();
                try{
                    $sql = 'SELECT * FROM account_uni WHERE uni=:uni';
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam("uni",  $uni);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    return $result;
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                    return false;
                }

            }else{
                return $result;
            }
        }catch(PDOException $e){
            $this->logger->addInfo($e);
            return false;
        }
    }
    public function sentMail(Request $request,Response $response){
        $params = $request->getParsedBody();
        if(empty($params['uni'])){
            return $response->withStatus(403);
        }
        $info = sentMail_db($params['uni']);
        if($info === false){
            return $response->withStatus(403);
        }
        
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 2;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'geargame30@eng.cmu.ac.th';                     // SMTP username
            $mail->Password   = 'geargame30';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('geargame30@eng.cmu.ac.th', 'Mailer');
            $mail->addAddress($info['email'], $info['uni_full_name']);     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            // Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Geargame 30 - Username and Password for ' + $info['uni_full_name'];
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return $response->write('Message has been sent');
        } catch (Exception $e) {
            $this->logger->addInfo($e);
            return $response->write("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            //return $response->withJson($mail->ErrorInfo)->withStatus(403);
        }
    }
    public function allRoutes(Request $request,Response $response){
        $allRoutes = [];
        $routes = $this->container->router->getRoutes();
        foreach ($routes as $route) {
            array_push($allRoutes, $route->getPattern());
        }
        return $response->withJson($allRoutes);
        //return $response->write(print_r($allRoutes));
    }
    public function devAdduser(Request $request,Response $response){
        # @params = email
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
    }
}
