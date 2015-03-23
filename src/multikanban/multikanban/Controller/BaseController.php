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

    // Creates the initial example kanbans and tasks for a new user
    public function initialLoad($user){

        $this->loadPhotographyExample($user);
        $this->loadTodolistExample($user);
        $this->loadMultikanbanExample($user);   
    }

    public function loadPhotographyExample($user){
        $kanban = new Kanban();
        $kanban->user_id = (float) $user->id;
        $kanban->title = "Photography";
        $kanban->slug = "photography";
        $kanban->dateCreated = date("Y-m-d");
        $kanban->lastEdited = $kanban->dateCreated;
        $kanban->position = (float) 0;

        $kanban->id = (float) $this->getKanbanRepository()->save($kanban);


            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "Read about what camera to buy";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'doing';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "Ask Jake about what camera should I buy";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "buy camera";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join a photography course";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 2;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join next barcelona photowalk";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 3;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
        
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join barcelona's photography meetup";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 4;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join photography subreddit";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 5;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "read about landscape photography";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get a flickr account";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get panorama maker";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 2;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
        
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get a second battery";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 3;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "timelapse of Barcelona";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 4;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "underwater photography";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 5;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
        
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "learn about bird photography";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 6;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
    } 

    public function loadTodolistExample($user){
        $kanban = new Kanban();
        $kanban->user_id = (float) $user->id;
        $kanban->title = "To do list";
        $kanban->slug = "to-do-list";
        $kanban->dateCreated = date("Y-m-d");
        $kanban->lastEdited = $kanban->dateCreated;
        $kanban->position = (float) 1;

        $kanban->id = (float) $this->getKanbanRepository()->save($kanban);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get Ratatat tickets";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'doing';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "throw the garbage";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "do the shopping";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "upload last trip pics";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 2;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "call Tània";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 3;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "visit grandma";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 4;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get car checked";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 5;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "download Interstellar";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 6;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "make a reservation for barça's next game";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 7;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get a wifi amplifier";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "unsubscribe from the gym";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get batteries for the mouse";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 2;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "plan holidays";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 3;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "learn to play the guitar";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 4;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "fix bike";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 5;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "learn french";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 6;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "renew domain names";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 7;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "finish personal blog";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 8;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);      
    }  

    public function loadMultikanbanExample($user){
        $kanban = new Kanban();
        $kanban->user_id = (float) $user->id;
        $kanban->title = "Multikanban";
        $kanban->slug = "multikanban";
        $kanban->dateCreated = date("Y-m-d");
        $kanban->lastEdited = $kanban->dateCreated;
        $kanban->position = (float) 2;

        $kanban->id = (float) $this->getKanbanRepository()->save($kanban);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "register to multikanban";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 0;
            $task->state = 'archive';

            $this->getTaskRepository()->save($task);    

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "read about how to use multikanban in the bottom left corner";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 1;
            $task->state = 'archive';

            $this->getTaskRepository()->save($task);  

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "read multikanban's help in the bottom left corner";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 2;
            $task->state = 'archive';

            $this->getTaskRepository()->save($task); 
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get things done";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 0;
            $task->state = 'done';

            $this->getTaskRepository()->save($task); 

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "feel good";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 1;
            $task->state = 'done';

            $this->getTaskRepository()->save($task); 

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "browse around the kanban examples";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = date("Y-m-d");
            $task->position = 2;
            $task->state = 'done';

            $this->getTaskRepository()->save($task); 

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "create my first kanban boards";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'doing';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "get familiar with multikanban";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "read about productivity tools";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'todo';

            $this->getTaskRepository()->save($task);
            
            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join #kanban on freenode";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 0;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);

            $task = new Task();
            $task->user_id = $user->id;
            $task->kanban_id = $kanban->id;
            $task->text = "join /r/kanban in reddit";
            $task->dateCreated = date("Y-m-d");
            $task->dateCompleted = null;
            $task->position = 1;
            $task->state = 'backlog';

            $this->getTaskRepository()->save($task);
    }  
}
