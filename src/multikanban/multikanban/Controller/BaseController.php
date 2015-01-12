<?php

namespace multikanban\multikanban\Controller;

use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use multikanban\multikanban\Application;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Model\Kanban;
use multikanban\multikanban\Model\Task;
use multikanban\multikanban\Model\Stats;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Api\ApiProblem;
use multikanban\multikanban\Api\ApiProblemException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Base controller class to hide Silex-related implementation details
 */
abstract class BaseController implements ControllerProviderInterface
{
    /**
     * @var \multikanban\multikanban\Application
     */
    protected $container;

    public function __construct(Application $app)
    {
        $this->container = $app;
    }

    abstract protected function addRoutes(ControllerCollection $controllers);

    public function connect(SilexApplication $app){
        
        $controllers = $app['controllers_factory'];

        $this->addRoutes($controllers);

        return $controllers;
    }

 

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->container['repository.user'];
    }

    /**
     * @return KanbanRepository
     */
    protected function getKanbanRepository()
    {
        return $this->container['repository.kanban'];
    }

    /**
     * @return TaskRepository
     */
    protected function getTaskRepository()
    {
        return $this->container['repository.task'];
    }

    /**
     * @return StatsRepository
     */
    protected function getStatsRepository()
    {
        return $this->container['repository.stats'];
    }

    /**
     * @return ApiTokenRepository
     */
    protected function getApiTokenRepository()
    {
        return $this->container['repository.api_token'];
    }

    /**
     * Is the current user logged in?
     *
     * @return boolean
     */
    public function isUserLoggedIn()
    {
        return $this->container['security']->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * @return User|null
     */
    public function getLoggedInUser()
    {
        if (!$this->isUserLoggedIn()) {
            return;
        }

        return $this->container['security']->getToken()->getUser();
    }

    /**
     * @param $obj
     * @return array
     */
    public function validate($obj)
    {
        return $this->container['api.validator']->validate($obj);
    }

    public function throwApiProblemValidationException(array $errors){

        $apiProblem = new ApiProblem(
            400,
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }

    public function checkValidation($data){

        $errors = $this->validate($data);
        if(!empty($errors)){

            $this->throwApiProblemValidationException($errors);
        }
    }

    public function checkNotFound($data){

        if(!$data){
            $apiProblem = new ApiProblem(404, ApiProblem::TYPE_NOT_FOUND);
            
            throw new ApiProblemException($apiProblem);  
        } 
    }

    protected function serialize($data, $group){

        $serializerContext = new SerializationContext();
        $serializerContext->setSerializeNull(true)
                          ->setGroups(array($group));

        return $this->container['serializer']
            ->serialize($data, 'json', $serializerContext);
    }

    protected function createApiResponse($data, $statusCode = 200, $group = "Default"){

        $json = $this->serialize($data, $group);

        $response = new Response($json, $statusCode, array(
            'Content-Type' => 'application/json'
        ));

        return $response;
    }

    protected function enforceUserSecurity(){

        if(!$this->getLoggedInUser()){
            throw new AccessDeniedException();
        }
    }

    // Checks that the logged in user is authorized to manipulate the resource
    public function enforceUserOwnershipSecurity($request_id, $kanban_u_id = null, $task_u_id = null){

        $this->enforceUserSecurity();

        $logged_id = $this->getLoggedInUser()->id;

        if($kanban_u_id){
            if($task_u_id){

                if($request_id != $logged_id || $kanban_u_id != $logged_id || $task_u_id != $logged_id){
                    throw new AccessDeniedException();
                }
            }else{
            
                if($request_id != $logged_id || $kanban_u_id != $logged_id){
                    throw new AccessDeniedException();
                }
            }
        }else{

            if($request_id != $logged_id){
                throw new AccessDeniedException();
            }
        }
    }

    protected function decodeRequestBodyIntoParameters(Request $request)
    {
        // allow for a possibly empty body
        if (!$request->getContent()) {
            $data = array();
        } else {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                $problem = new ApiProblem(
                    400,
                    ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
                );
                throw new ApiProblemException($problem);
            }
        }

        return new ParameterBag($data);
    }
}
