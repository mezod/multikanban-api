<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Repository\UserRepository;
use multikanban\multikanban\Api\ApiProblemException;
use multikanban\multikanban\Api\ApiProblem;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use multikanban\multikanban\Security\Token\ApiToken;


class UserController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users', array($this, 'createAction'));
        $controllers->get('/users', array($this, 'getAllAction'));
        $controllers->get('/users/{id}', array($this, 'getAction'));
        $controllers->put('/users/{id}', array($this, 'updateAction'));
        $controllers->delete('/users/{id}', array($this, 'deleteAction'));
        $controllers->get('/tokens', array($this, 'loginAction'));
    }

    public function createAction(Request $request){

    	$data = $this->decodeRequestBodyIntoParameters($request);

    	$user = new User();
    	$user->username = $data->get('username');
    	$user->setPlainPassword($data->get('password'));
    	$user->email = $data->get('email');
        $user->registered = date("Y-m-d");
        $user->token = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        // Check validation error
        $this->checkValidation($user);

    	$this->getUserRepository()->save($user);

        $newUser = $this->getUserRepository()->findOneByUsername($user->username);

        return $this->createApiResponse($newUser, 201, 'security');
    }

    public function getAllAction(){

        // THIS SHOULDN'T BE PUBLIC

        $users = $this->getUserRepository()->findAll();

        return $this->createApiResponse($users, 200, 'default');
    }

    public function getAction($id){

        $this->enforceUserOwnershipSecurity($id);

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        return $this->createApiResponse($user, 200, 'default');

    }

    public function updateAction(Request $request, $id){

        $data = $this->decodeRequestBodyIntoParameters($request);

        $this->enforceUserOwnershipSecurity($id);

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        $emailChanged = false;

        if($data->has('password')) $user->setPlainPassword($data->get('password'));
        if($data->has('email') && $user->email != $data->get('email')){
            $user->email = $data->get('email');
            $emailChanged = true;
        }

        // Check validation error
        $this->checkValidation($user);

        $this->getUserRepository()->update($user, $emailChanged);

        return $this->createApiResponse($user, 200, 'default');
    }

    public function deleteAction($id){

        $this->enforceUserOwnershipSecurity($id);

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        $this->getUserRepository()->delete($user);

        return new Response(null, 204);
    }

    /*
     * Get the token of a user by a given basic auth
     */
    public function loginAction(){

        $this->enforceUserSecurity();

        $token = $this->getUserRepository()->findOneById($this->getLoggedInUser()->id);

        return $this->createApiResponse($token, 200, 'security');
    }
}
