<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\Stats;
use multikanban\multikanban\Repository\StatsRepository;


class StatsController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

        $controllers->get('/stats', array($this, 'getAppStatsAction'));
        $controllers->get('/users/{user_id}/stats', array($this, 'getUserStatsAction'));
        $controllers->get('/kanbans/{kanban_id}/stats', array($this, 'getKanbanStatsAction'));
    }

   	public function getAppStatsAction(){

   		$stats = $this->getStatsRepository()->getAppStats();

   		$statsArray = array(
   			'numberUsers' => $stats->numberUsers,
   			'numberKanbans' => $stats->numberKanbans,
   			'numberTasks' => $stats->numberTasks,
   			'numberCompletedTasks' => $stats->numberCompletedTasks
   		);

   		return new JsonResponse($statsArray, 200);
   	}

   	public function getUserStatsAction($user_id){

   		$stats = $this->getStatsRepository()->getUserStats($user_id);

   		$statsArray = array(
   			'numberKanbans' => $stats->numberKanbans,
   			'numberTasks' => $stats->numberTasks,
   			'numberCompletedTasks' => $stats->numberCompletedTasks
   		);

   		return new JsonResponse($statsArray, 200);
   	}

   	public function getKanbanStatsAction($kanban_id){

   		$stats = $this->getStatsRepository()->getKanbanStats($kanban_id);

   		$statsArray = array(
   			'numberTasks' => $stats->numberTasks,
   			'numberCompletedTasks' => $stats->numberCompletedTasks
   		);

   		return new JsonResponse($statsArray, 200);
   	}
}
