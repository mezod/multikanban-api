<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\Kanban;
use multikanban\multikanban\Repository\KanbanRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use multikanban\multikanban\Util\Util;


class KanbanController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){
    	
    	$controllers->post('/users/{user_id}/kanbans', array($this, 'createAction'));
        $controllers->get('/users/{user_id}/kanbans', array($this, 'getAllAction'));
        $controllers->get('/users/{user_id}/kanbans/{id}', array($this, 'getAction'));
        $controllers->put('/users/{user_id}/kanbans/{id}', array($this, 'updateAction'));
        $controllers->delete('/users/{user_id}/kanbans/{id}', array($this, 'deleteAction'));
    }

   	public function createAction(Request $request, $user_id){

        $this->enforceUserOwnershipSecurity($user_id);

    	$data = $this->decodeRequestBodyIntoParameters($request);

    	$kanban = new Kanban();
    	$kanban->user_id = $user_id;
    	$kanban->title = $data->get('title');
        $kanban->slug = Util::slugify($data->get('title'));
    	$kanban->dateCreated = date("Y-m-d");
    	$kanban->lastEdited = $kanban->dateCreated;
    	$kanban->position = $this->getKanbanRepository()->getKanbanPosition($user_id);

        // Check validation error
        $this->checkValidation($kanban);

    	$kanban->id = $this->getKanbanRepository()->save($kanban);

        return $this->createApiResponse($kanban, 201);
    }

    public function getAllAction($user_id){

        $this->enforceUserOwnershipSecurity($user_id);

        $kanbans = $this->getKanbanRepository()->findAll($user_id);

        return $this->createApiResponse($kanbans, 200);
    }

    public function getAction($user_id, $id){

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);

        return $this->createApiResponse($kanban, 200);
    }

    public function updateAction(Request $request, $user_id, $id){

        $data = $this->decodeRequestBodyIntoParameters($request);

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);

        $title = $data->get('title');
        if($kanban->title != $title){
            $kanban->title = $title;
            $kanban->slug = Util::slugify($title);
        }

        $positionChanged = false;
        $position = $data->get('position');
        if($kanban->position != $position){
            $positionChanged = true; 
            $oldPosition = $kanban->position;
            $kanban->position = $position;
        } 

        // Check validation error
        $this->checkValidation($kanban);

        if($positionChanged) $this->getKanbanRepository()->updatePositions($user_id, $oldPosition, $kanban->position);
        $this->getKanbanRepository()->update($kanban);


        return $this->createApiResponse($kanban, 200);
    }

    public function deleteAction($user_id, $id){

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        $this->enforceUserOwnershipSecurity($user_id, $kanban->user_id);

        $this->getKanbanRepository()->updatePositionsDelete($user_id, $kanban->position);

        $this->getKanbanRepository()->delete($kanban);

        return new Response(null, 204);
    }

}
