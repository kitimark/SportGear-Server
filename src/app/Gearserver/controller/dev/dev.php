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
    protected function sentMail_db($uni){
        $file_url = __DIR__ . '/uniMap.json';
        try{
            $sql = 'SELECT * FROM account_uni WHERE uni=:uni';
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",  $uni);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(count($result) === 0){
                //TODO
                //will select real email(read from file or some other table) and auto generate pwd to insert into account_uni
                $json = file_get_contents($file_url);//load from url
                
                /*
                {
                    "cmu":{
                        "email":"geargame30@eng.cmu.ac.th",
                        "name":"Chiang Mai University",
                    }
                }
                */

                $data = json_decode($json,true);
                if(!array_key_exists($uni, $data)){
                    return false;
                }
                $email = $data[$uni]['email'];
                $uni_full_name = $data[$uni]['name'];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                $pwd = bin2hex(openssl_random_pseudo_bytes(4));
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $sql = 'INSERT INTO account_uni(uni,email,uni_full_name,uni_pwd) VALUES (:uni,:email,:uni_full_name,:uni_pwd)';
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("uni",$uni);
                $stmt->bindParam("email",$email);
                $stmt->bindParam("uni_full_name",$uni_full_name);
                $stmt->bindParam("uni_pwd",$hash);
                $stmt->execute();
                try{
                    $sql = 'SELECT * FROM account_uni WHERE uni=:uni';
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("uni",  $uni);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    $result[0]['uni_pwd'] = $pwd;
                    return $result;
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                    return false;
                }

            }else{
                try{
                    //if exists update it with new password
                    $pwd = bin2hex(openssl_random_pseudo_bytes(4));
                    $hash = password_hash($pwd, PASSWORD_DEFAULT);
                    $sql = 'UPDATE account_uni SET uni_pwd=:uni_pwd WHERE uni=:uni';
                    $stmt = $this->container->db->prepare($sql);
                    $stmt->bindParam("uni",$uni);
                    $stmt->bindParam("uni_pwd",$hash);
                    $stmt->execute();
                    $result[0]['uni_pwd'] = $pwd;
                    return $result;
                }catch(PDOException $e){
                    $this->logger->addInfo($e);
                    return false;
                }
            }
        }catch(PDOException $e){
            $this->logger->addInfo($e);
            return false;
        }
    }
    public function sentMail(Request $request,Response $response){
        $params = $request->getParsedBody();
        $file_url = '';
        if(empty($params['uni'])){
            return $response->withStatus(403);
        }
        $info = $this->sentMail_db($params['uni']);
        if($info === false){
            return $response->withStatus(403);
        }
        
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->CharSet = "utf-8";
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'geargame30@eng.cmu.ac.th';                     // SMTP username
            $mail->Password   = 'geargame30';                               // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('geargame30@eng.cmu.ac.th', 'Geargame30');
            $mail->addAddress($info[0]['email'], $info[0]['uni_full_name']);     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            // Attachments
            //$mail->addStringAttachment(file_get_contents($file_url), 'account');
            //$mail->addAttachment('');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Geargame 30 - Username and Password for ' . $info[0]['uni_full_name'];
            $mail->Body = '<html><head><style>* { margin:0;padding:0;}* { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }
            </style><meta name="viewport" content="width=device-width" /><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body bgcolor="#FFFFFF">
            <table" bgcolor="#fff"><tr><td></td><td><div><table bgcolor="#fff"><tr><td><img style="height: 60px" src="https://drive.google.com/uc?export=view&id=1vBPAbRqHsZ-mS1Ybr82rACKm6DDDZOlK" /></td>
            <td align="right"><h6>Geargame30</h6></td></tr></table></div></td><td></td></tr></table><table><tr><td></td><td" bgcolor="#FFFFFF"><div><table><tr><td><h3>การเข้าใช้งานระบบลงทะเบียน</h3><p>USERNAME & PASSWORD สำหรับการเข้าสู้ระบบลงทะเบียน Geargame30 ณ คณะวิศวกรรมศาสตร์ มหาวิทยาลัยเชียงใหม่ ในวันที่ 23 - 28 ธันวาคม 2561</p>
            <p>USERNAME : ' . $info[0]['uni'] . '<br>PASSWORD : ' . $info[0]['uni_pwd'] . '<br></p><a href="geargame30.eng.cmu.ac.th/log-in">เข้าสู้เว็บไซต์</a><br><a href="https://drive.google.com/file/d/1l8ETUy_wN4F3qakQhMTVsTwMdBc81_U_/view?usp=sharing">ดาวน์โหลด Template excel สำหรับ Import</a></td></tr></table></div></td><td></td></tr></table></body></html>';
            $mail->AltBody = 'Username : ' . $info[0]['uni'] . " Password : " . $info[0]['uni_pwd'];

            $mail->send();
            return $response->write('Message has been sent to ' . $info[0]['email']);
        } catch (Exception $e) {
            $this->logger->addInfo($e);
            return $response->write("Message could not be sent. Mailer Error: {$mail->ErrorInfo}")->withStatus(403);
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
