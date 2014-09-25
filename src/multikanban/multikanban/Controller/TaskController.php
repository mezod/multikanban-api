<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\Task;
use multikanban\multikanban\Repository\TaskRepository;


class TaskController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users/{user_id}/kanbans/{kanban_id}/tasks', array($this, 'createAction'));
        $controllers->get('/users/{user_id}/kanbans/{kanban_id}/tasks', array($this, 'getAllAction'));
        $controllers->get('/users/{user_id}/completedtasks', array($this, 'getCompletedAction'));
        $controllers->put('/users/{user_id}/kanbans/{kanban_id}/tasks/{id}', array($this, 'updateAction'));
        $controllers->delete('/users/{user_id}/kanbans/{kanban_id}/tasks/{id}', array($this, 'deleteAction'));
    }

   	public function createAction(Request $request, $user_id, $kanban_id){

    	$data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);
    	
        // Check that the $user_id corresponds to the actual logged in user...

    	$task = new Task();
    	$task->user_id = $user_id;
    	$task->kanban_id = $kanban_id;
    	$task->text = $data['text'];
    	$task->dateCreated = date("Y-m-d");
    	$task->dateCompleted = null;
    	$task->position = 0;
    	$task->state = 'backlog';

        $this->getTaskRepository()->increaseBacklogPosition($kanban_id);

        //var_dump($user);

        // Check validation error
        $this->checkValidation($task);

    	$task_id = $this->getTaskRepository()->save($task);

        //$newTask = $this->getTaskRepository()->findOneByUsername($user->username);

        $taskArray = array(
        	'id' => $task_id,
            'user_id' => $task->user_id,
            'kanban_id' => $task->kanban_id,
            'text' => $task->text,
            'dateCreated' => $task->dateCreated,
            'dateCompleted' => $task->dateCompleted,
            'position' => $task->position,
            'state' => $task->state
        );

        return new JsonResponse($taskArray, 201);
    }

    public function getAllAction($user_id, $kanban_id){

        $tasks = $this->getTaskRepository()->findAll($kanban_id);

        $data = array();

        foreach ($tasks as $eachTask) {
            $eachArray = array();
            foreach ($eachTask as $key => $value) {
               $eachArray[$key] = $value;
            }
            array_push($data, $eachArray);
        }

        return new JsonResponse($data, 200);
    }

    public function getCompletedAction($user_id){

        $completedTasks = $this->getTaskRepository()->findCompleted($user_id);

        $data = array();

        foreach ($completedTasks as $eachTask) {
            $eachArray = array();
            foreach ($eachTask as $key => $value) {
               $eachArray[$key] = $value;
            }
            array_push($data, $eachArray);
        }

        return new JsonResponse($completedTasks, 200);
    }

    public function updateAction(Request $request, $user_id, $kanban_id, $id){

        $task = $this->getTaskRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($task);

        $data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);

        $task->text = $data['text'];
        if($task->position != $data['position'] ||  $task->state != $data['state']){
            $this->getTaskRepository()->updatePositions($kanban_id, $task->position, $data['position'], $task->state, $data['state']);
        }
        $task->position = $data['position'];
        $task->state = $data['state'];
        if(!$task->dateCompleted && ($data['state'] == 'done' || $data['state'] == 'archive')){
        	$task->dateCompleted = date("Y-m-d");
        }

        // Check validation error
        $this->checkValidation($task);

        $this->getTaskRepository()->update($task);

        $taskArray = array(
            'id' => $task->id,
            'user_id' => $task->user_id,
            'kanban_id' => $task->kanban_id,
            'text' => $task->text,
            'dateCreated' => $task->dateCreated,
            'dateCompleted' => $task->dateCompleted,
            'position' => $task->position,
            'state' => $task->state
        );

        return new JsonResponse($taskArray, 200);
    }

    public function deleteAction(Request $request, $user_id, $kanban_id, $id){

        $task = $this->getTaskRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($task);

        $this->getTaskRepository()->updatePositionsDelete($kanban_id, $task->position);

        $this->getTaskRepository()->delete($task);

        return new Response(null, 204);
    }
}
