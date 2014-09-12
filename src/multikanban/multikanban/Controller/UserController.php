<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Repository\UserRepository;


class UserController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users', array($this, 'createAction'));
        $controllers->get('/users', array($this, 'getAllAction'));
        $controllers->get('/users/{id}', array($this, 'getAction'));
    }

    public function createAction(Request $request){

    	$data = json_decode($request->getContent(), true);

    	$user = new User();
    	$user->username = $data['username'];
    	$user->password = $data['password'];
    	$user->email = $data['email'];
        $user->registered = date("Y-m-d");

        //var_dump($user);

    	$this->getUserRepository()->save($user);

        return new Response("User successfully created, really", 201);
    }

    public function getAllAction(){

        $users = $this->getUserRepository()->findAll();

        $data = array();

        foreach ($users as $eachUser) {
            $eachArray = array();
            foreach ($eachUser as $key => $value) {
               $eachArray[$key] = $value;
            }
            array_push($data,$eachArray);
        }

        return new JsonResponse($data, 200);
    }

    public function getAction($id){

        $user = $this->getUserRepository()->findOneById($id);

        //var_dump($user);

        $data = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'registered' => $user->registered
        );

        return new JsonResponse($data, 200);
    }

}
