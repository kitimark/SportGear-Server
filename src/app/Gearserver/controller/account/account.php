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
            ));
        }
        try{
            $sql = "SELECT * FROM account WHERE sid = :sid";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("sid",$args['sid']);
            $stmt->execute();
            $info = $stmt->fetchAll();
            if(count($info) != 0){
                $detail = empty($info[0]['details']) ? $info[0]['details'] : json_decode($info[0]['details'], true);
                $info[0]['details'] = $detail;
                return $response->withJson($info);                   
            }else{
                // no user responses nothing
                return $response->withStatus(401);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
        }
    }
    
}
