<?php 
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class dev{
    
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
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
