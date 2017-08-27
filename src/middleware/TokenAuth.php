<?php

namespace Middleware;
use Slim\Http\Request;
use Slim\Http\Response;
class TokenAuth
{
    private $container;
    private $apiVersion;

    //roles
    private $userRole;
    private $adminRole;

    public function __construct($container) {
        $this->container = $container;
        $this->apiVersion = $container['version'];
        /**
         * roles are not dynamic
         * role with higher access level is higher number
         **/
        $this->userRole = 1;
        $this->adminRole = 2;
    }
    function ACL($path, $method){
        //init access list
        $accessList = array(
            array(
                'role' => $this->userRole,
                'path' => $this->apiVersion . "/addresses",
                'method' => ['GET', 'POST']
            ),

            array(
                'role' => $this->adminRole,
                'path' => $this->apiVersion . "/users",
                'method' => ['GET']
            ),

            array(
                'role' => $this->adminRole,
                'path' => $this->apiVersion . "/products",
                'method' => ['POST']
            ),

        );

        //search access list
        foreach ($accessList as $value) {
            foreach ($value['method'] as $valueMethod) {
                if($value['path'] == $path && $valueMethod == $method){
                    return $value;
                }
            }
        }
    }
    public function denyAccess(){
        http_response_code(401);
        exit;
    }

    public function checkUserRole($accessRule, $_userRole){
        if($_userRole == 'user')
            $_userRole = $this->userRole;
        else if($_userRole == 'admin')
            $_userRole = $this->adminRole;

        //check the role access
        if($_userRole >= $accessRule)
            return true;
    }
    public function __invoke(Request $request, $response, $next)
    {
        $token = null;
        if(isset($request->getHeader('token')[0]))
            $token = $request->getHeader('token')[0];

        //same format as api route
        $route = $request->getAttribute('route');
        $path = $route->getPattern();
        $method = $request->getMethod();
        $accessRule = $this->ACL($path, $method);

        if(isset($accessRule) && $token != null){
            $checkToken = $this->container->UsersCtrl->validateToken($token);
            if($checkToken != null)
            {
                /**
                 * accessRule defined by dev
                 * checkToken retrieve from db
                 **/
                if($this->checkUserRole($accessRule['role'], $checkToken['role'])){
                    $this->container->UsersCtrl->updateUserToken($token);
                }
                else
                    $this->denyAccess();
            }
            else
            {
                $this->denyAccess();
            }
        }
        else if(isset($accessRule) && $token == null)
            $this->denyAccess();

        $response = $next($request, $response);
        return $response;
    }
}