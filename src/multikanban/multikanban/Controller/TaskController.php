<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\Task;
use multikanban\multikanban\Repository\TaskRepository;
use multikanban\multikanban\Repository\KanbanRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class TaskController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users/{user_id}/kanbans/{kanban_id}/tasks', array($this, 'createAction'));
        $controllers->get('/users/{user_id}/kanbans/{kanban_id}/tasks', array($this, 'getAllAction'));
        $controllers->get('/users/{user_id}/kanbans/{kanban_id}/tasks/{state}', array($this, 'getByStateAction'));
        $controllers->get('/users/{user_id}/completedtasks', array($this, 'getCompletedAction'));
        $controllers->put('/users/{user_id}/kanbans/{kanban_id}/tasks/{id}', array($this, 'updateAction'));
        $controllers->delete('/users/{user_id}/kanbans/{kanban_id}/tasks/{id}', array($this, 'deleteAction'));
    }

   	public function createAction(Request $request, $user_id, $kanban_id){

        $data = $this->decodeRequestBodyIntoParameters($request);

        //Check user owns kanban
        $kanban = $this->getKanbanRepository()->findOneById($kanban_id);
        $this->checkNotFound($kanban);
        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);
    	
    	$task = new Task();
    	$task->user_id = $user_id;
    	$task->kanban_id = $kanban_id;
    	$task->text = $data->get('text');
    	$task->dateCreated = date("Y-m-d");
    	$task->dateCompleted = null;
    	$task->position = 0;
    	$task->state = 'backlog';

        $this->getTaskRepository()->increaseBacklogPosition($kanban_id);

        // Check validation error
        $this->checkValidation($task);

    	$task->id = $this->getTaskRepository()->save($task);

        return $this->createApiResponse($task, 201);
    }

    public function getAllAction($user_id, $kanban_id){

        $kanban = $this->getKanbanRepository()->findOneById($kanban_id);
        $this->checkNotFound($kanban);
        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);

        $tasks = $this->getTaskRepository()->findAll($kanban_id);

        return $this->createApiResponse($tasks, 200);
    }

    public function getByStateAction($user_id, $kanban_id, $state){

        $kanban = $this->getKanbanRepository()->findOneById($kanban_id);
        $this->checkNotFound($kanban);
        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);

        $tasks = $this->getTaskRepository()->findAllByState($kanban_id, $state);

        return $this->createApiResponse($tasks, 200);
    }

    public function getCompletedAction($user_id){

        $this->enforceUserOwnershipSecurity($user_id);

        $completedTasks = $this->getTaskRepository()->findCompleted($user_id);

        return $this->createApiResponse($completedTasks, 200);
    }

    public function updateAction(Request $request, $user_id, $kanban_id, $id){

        $data = $this->decodeRequestBodyIntoParameters($request);

        $task = $this->getTaskRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($task);

        $kanban = $this->getKanbanRepository()->findOneById($kanban_id);
        $this->checkNotFound($kanban);
        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id, $task->user_id);

        $task->text = $data->get('text');

        $positionOrStateChanged = false;
        $position = $data->get('position');
        $state = $data->get('state');
        if($task->position != $position ||  $task->state != $state){
            $positionOrStateChanged = true;
            $oldPosition = $task->position;
            $oldState = $task->state;
            $task->position = $position;
            $task->state = $state;
        }
        
        if(!$task->dateCompleted && ($state == 'done' || $state == 'archive')){
        	$task->dateCompleted = date("Y-m-d");
        }

        // Check validation error
        $this->checkValidation($task);

        // Update kanbans last edited date
        $this->getKanbanRepository()->updateLastEditedDate($kanban);

        if($positionOrStateChanged) $this->getTaskRepository()->updatePositions($kanban_id, $oldPosition, $task->position, $oldState, $task->state);
        $this->getTaskRepository()->update($task);

        return $this->createApiResponse($task, 200);
    }

    public function deleteAction(Request $request, $user_id, $kanban_id, $id){

        $task = $this->getTaskRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($task);

        $kanban = $this->getKanbanRepository()->findOneById($kanban_id);
        $this->checkNotFound($kanban);
        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id, $task->user_id);

        $this->getTaskRepository()->updatePositionsDelete($kanban_id, $task->position);

        $this->getTaskRepository()->delete($task);

        return new Response(null, 204);
    }
}
