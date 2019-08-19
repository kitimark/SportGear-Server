<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class sport{
        
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function ListSport(Request $request,Response $response){
        try{    
            $sql = "SELECT * FROM sport";
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchall();
            $obj = array();
            foreach ($result as $value) {
                $data = array(
                    "_id" => $value['id'],
                    "eachTeam" => (int)$value['each_team'],
                    "teams" => (int)$value['teams']
                );
                if (empty($obj[$value['sport_name']])){
                    $obj[$value['sport_name']] = array(
                        "type" => array(
                            $value['sport_type'] => $data
                        )
                    );
                }else{
                    $obj[$value['sport_name']]["type"] += array(
                        $value['sport_type'] => $data
                    );
                }
            }
            return $response->withJson($obj);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
        }
    }

    public function TeamIDByType(Request $request,Response $response){
        $params = $request->getQueryParams();
        if(empty($params['type']) || empty($params['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        try{
            $sql = "SELECT sport_team.id as sport_id ,account.id
            FROM account
            JOIN sport_player
            ON account.id = sport_player.fk_account_id
            JOIN sport_team
            ON sport_team.id = sport_player.fk_team_id
            JOIN sport
            ON sport.id = sport_team.fk_sport_id
            WHERE sport_team.uni = :uni AND sport_player.fk_sport_id = :id
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$params['uni']);
            $stmt->bindParam("id",$params['type']);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_GROUP);
            return $response->withJson($result);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e);
        }
    }
    
}