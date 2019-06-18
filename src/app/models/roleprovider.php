<?php 
namespace Tkhamez\Slim\RoleAuth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteInterface;

class RoleProvider implements RoleProviderInterface{
    public function __construct(){

    }
    public function __invoke(Request $request, Response $response,callable $next){
        $request = $request->withAttribute('roles',$this->getRoles($request));
        return $next($request, $response);
    }
    public function getRoles(ServerRequestInterface $request){
        $token = $request->getAttribute("token"); //return from jwt
        $roles = $token['roles'];
        return is_array($roles) ? $roles : [];
    }
}