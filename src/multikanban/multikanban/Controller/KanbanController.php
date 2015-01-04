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

        if(!$this->getLoggedInUser()){
            throw new AccessDeniedException();
        }

    	$data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);

    	// Check that the $user_id corresponds to the actual logged in user...

    	$kanban = new Kanban();
    	$kanban->user_id = $user_id;
    	$kanban->title = $data['title'];
        $kanban->slug = Util::slugify($data['title']);
    	$kanban->dateCreated = date("Y-m-d");
    	$kanban->lastEdited = $kanban->dateCreated;
    	$kanban->position = $this->getKanbanRepository()->getKanbanPosition($user_id);

        // Check validation error
        $this->checkValidation($kanban);

    	$kanban->id = $this->getKanbanRepository()->save($kanban);

        return $this->createApiResponse($kanban, 201);
    }

    public function getAllAction($user_id){

        $kanbans = $this->getKanbanRepository()->findAll($user_id);

        return $this->createApiResponse($kanbans, 200);
    }

    public function getAction($user_id, $id){

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        return $this->createApiResponse($kanban, 200);
    }

    public function updateAction(Request $request, $user_id, $id){

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        $data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);

        if($kanban->title != $data['title']){
            $kanban->title = $data['title'];
            $kanban->slug = Util::slugify($data['title']);
        }

        if($kanban->position != $data['position']){
            $this->getKanbanRepository()->updatePositions($user_id, $kanban->position, $data['position']);
        }
        $kanban->position = $data['position'];

        // Check validation error
        $this->checkValidation($kanban);

        $this->getKanbanRepository()->update($kanban);

        return $this->createApiResponse($kanban, 200);
    }

    public function deleteAction(Request $request, $user_id, $id){

        $kanban = $this->getKanbanRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($kanban);

        $this->getKanbanRepository()->updatePositionsDelete($user_id, $kanban->position);

        $this->getKanbanRepository()->delete($kanban);

        return new Response(null, 204);
    }
}
