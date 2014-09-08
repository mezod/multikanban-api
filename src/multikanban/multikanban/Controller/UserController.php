<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Repository\UserRepository;


class UserController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users', array($this, 'createAction'));
    }

    public function createAction(Request $request){

    	$data = json_decode($request->getContent(), true);

    	$user = new User();
    	$user->username = $data['username'];
    	$user->password = $data['password'];
    	$user->email = $data['email'];

    	$this->getUserRepository()->save($user);

    	return "works";
    }

}
