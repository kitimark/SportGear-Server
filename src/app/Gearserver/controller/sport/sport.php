<?php
namespace Gearserver\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \PDOException;
class sport{
        
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function ListSport(Request $request,Response $response){
        $params = $request->getQueryParams();
        $lang = (empty($params['lang']) ? "" : "_{$params['lang']}");
        try{    
            $sql = "SELECT id, sport_name{$lang}, sport_type{$lang}, each_team, teams, gender FROM sport";
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchall();
            $obj = array();
            foreach ($result as $value) {
                $data = array(
                    "_id" => $value['id'],
                    "eachTeam" => (int)$value['each_team'],
                    "teams" => (int)$value['teams'],
                    "gender" => $value['gender']
                );
                if (empty($obj[$value["sport_name{$lang}"]])){
                    $obj[$value["sport_name{$lang}"]] = array(
                        "type" => array(
                            $value["sport_type{$lang}"] => $data
                        )
                    );
                }else{
                    $obj[$value["sport_name{$lang}"]]["type"] += array(
                        $value['sport_type'] => $data
                    );
                }
            }
            return $response->withJson($obj);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
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
            $this->container->logger->addInfo($e->getMessage());
        }
    }
    // searchByid
    public function SearchByid(Request $request,Response $response){
        $params = $request->getQueryParams();
        $decoded = $request->getAttribute('jwt');
        if(empty($params['team_name']) || empty($params['sport_id'] || empty($decoded['uni']))){
            return $response->withStatus(400)
                ->withJson(array(
                    'status' => 'error',
                    'message' => 'QueryParams not set!'
                ));
        }
        try{
            $sql = "SELECT id FROM sport_team WHERE team_name=:team_name 
                AND fk_sport_id=:sport_id AND uni=:uni";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("team_name",$params['team_name']);
            $stmt->bindParam("sport_id",$params['sport_id']);
            $stmt->bindParam("uni",$decoded['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if (count($result) != 0){
                return $response->withJson($result[0]);
            }else{
                return $response->withStatus(204);
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
    }

    // ListTeamByuni
    public function ListTeamByUni(Request $request,Response $response){
        $params = $request->getQueryParams();
        if(empty($params['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        try{
            $sql = "SELECT *
            FROM sport_player 
            JOIN account 
            ON sport_player.fk_account_id = account.id
            WHERE account.uni = :uni
             ";
            $stmt = $this->container->db->prepare($sql);
            
            $stmt->bindParam("uni",$params['uni']);
            $stmt->execute();
            $result = $stmt->fetchAll;
            return $response->withJson($result);

        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
    }

    // pleyerBytype
    public function ListPlayerByType(Request $request,Response $response){
        $params = $request->getQueryParams();
        if(empty($params['type']) || empty($params['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        try{
            
            $sql = "SELECT sport.id as sport_id,sport_team.team_name,sport_team.id as team_id,account.id,account.sid,account.fname,account.lname
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
            $result = $stmt->fetchAll();
            $result = groupArray($result,'sport_id');
            $keys = array_keys($result);
            for($i = 0 ; $i < count($keys) ; $i++){
                $result[$keys[$i]] = groupArray($result[$keys[$i]],'team_name');
            }
            return $response->withJson($result);
            //return $this->response->write(print_r($result,true));

            
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());                    
        }
    }

    // teamByType
    public function ListTeamByType(Request $request,Response $response){
        // type = 1001
        // uni = cmu
        $params = $request->getQueryParams();
        $decoded = $request->getAttribute('jwt');
        if(empty($params['type']) || empty($decoded['uni']) || empty($params['team_id'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        try{
            $sql = "SELECT sport_team.id as team_id,account.id as account_id,account.sid,account.fname,account.lname
            FROM account
            JOIN sport_player
            ON account.id = sport_player.fk_account_id
            JOIN sport_team
            ON sport_team.id = sport_player.fk_team_id
            WHERE account.uni = :uni AND sport_player.fk_sport_id = :id AND sport_player.fk_team_id = :teamid
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("uni",$decoded['uni']);
            $stmt->bindParam("id",$params['type']);
            $stmt->bindParam("teamid",$params['team_id']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $result = array_map(function($data){
                return array(
                    "id" => $data['account_id'],
                    "firstName" => $data['fname'],
                    "lastName" => $data['lname'],
                    "sid" => $data['sid'],
                    "team_id" => $data['team_id']
                );
            }, $result);
            return $response->withJson($result);
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());                    
        }
    }
    // Addteam
    public function AddTeam(Request $request,Response $response){
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');
        //$this->logger->addInfo(print_r($params));
        if(empty($params['team_name']) || empty($params['sport_id']) || empty($decoded['uni'])){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        try{
            $sql = "INSERT INTO sport_team(team_name,fk_sport_id,uni) VALUES (:team_name,:sport_id,:uni)";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("team_name",$params['team_name']);
            $stmt->bindParam("sport_id",$params['sport_id']);
            $stmt->bindParam("uni",$decoded['uni']);
            $stmt->execute();
            $id = $this->container->db->lastInsertId();
            return $response->withJson(array(
                'id' => $id,
                'message' => 'Added team'
            ));
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
    }

    public function AddPlayer(Request $request,Response $response){
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');
        if(empty($params['sport_id']) || empty($params['team_id'] || empty($params['account'][0]))){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }
        // check permission of university
        try{
            $sql = "SELECT uni FROM sport_team WHERE id=:id";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("id", $params['team_id']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if ($result[0]['uni'] != $decoded['uni']){
                return $response->withStatus(403)
                                ->withJson(array("message" => "permission denied"));
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }
        $messageResponse = "Added ID to team_id " . $params['team_id'] ." : ";
        for($index = 0 ; $index < count($params['account_id']);$index++){
            try{
                $sql = "INSERT INTO sport_player(fk_team_id,fk_account_id,fk_sport_id) VALUES (:team_id,:account_id,:sport_id)";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("team_id",$params['team_id']);
                $stmt->bindParam("account_id",$params['account_id'][$index]);
                $stmt->bindParam("sport_id",$params['sport_id']);
                $stmt->execute();
                $messageResponse .= $params['account'][$index] . ' ';
            }catch(PDOException $e){
                $this->container->logger->addInfo($e->getMessage());          
            }
        }

        $response->withJson(array(
            "message" => $messageResponse
        ));
    }

    public function UpdatePlayer(Request $request ,Response $response){
        $params = $request->getParsedBody();
        $decoded = $request->getAttribute('jwt');
        if(empty($params['sport_id']) || empty($params['team_id'] || empty($params['account'][0]))){
            return $response->withJson(array(
                'status' => 'error',
                'message' => 'QueryParams not set!'
            ));
        }

        // check permission of university
        try{
            $sql = "SELECT uni FROM sport_team WHERE id=:id";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("id", $params['team_id']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if ($result[0]['uni'] != $decoded['uni']){
                return $response->withStatus(403)
                                ->withJson(array("message" => "permission denied"));
            }
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());
        }

        try{
            $sql = "DELETE FROM sport_player WHERE fk_team_id = :team_id";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindParam("team_id",$params['team_id']);
            $stmt->execute();
        }catch(PDOException $e){
            $this->container->logger->addInfo($e->getMessage());                             
        }
        for($index = 0 ; $index < count($params['account_id']);$index++){
            try{
                $sql = "INSERT INTO sport_player(fk_team_id,fk_account_id,fk_sport_id) VALUES (:team_id,:account_id,:sport_id)";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindParam("team_id",$params['team_id']);
                $stmt->bindParam("account_id",$params['account_id'][$index]);
                $stmt->bindParam("sport_id",$params['sport_id']);
                $stmt->execute();
            }catch(PDOException $e){
                $this->container->logger->addInfo($e->getMessage());          
            }
        }
            
    }

}