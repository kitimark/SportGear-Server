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

    public function sentMail(Request $request,Response $response){
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
            $mail->addAddress('alonereview@gmail.com', 'Tester');     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            // Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return $response->write('Message has been sent');
        } catch (Exception $e) {
            //return $response->write("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            $response->withJson($mail->ErrorInfo);
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
